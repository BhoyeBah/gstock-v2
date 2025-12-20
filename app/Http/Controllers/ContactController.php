<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $type)
    {
        // 1. Valider que le type est bien 'clients' ou 'fournisseurs'
        $this->validateType($type);
        $contactModelType = rtrim($type, 's'); // 'client' ou 'fournisseur'

        // 2. Calculer les statistiques sur TOUS les contacts du type donné (AVANT le filtrage de la liste)
        // On utilise des requêtes séparées pour s'assurer que les totaux sont globaux
        $statsQuery = Contact::type($contactModelType);

        $totalContacts = (clone $statsQuery)->count();
        $activeContacts = (clone $statsQuery)->where('is_active', true)->count();
        $inactiveContacts = $totalContacts - $activeContacts;

        // 3. Construire la requête de base pour la liste des contacts
        $query = Contact::type($contactModelType)
            ->withSum(['invoices as balance_total' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }], 'balance');

        // 4. Appliquer les filtres de recherche et de statut s'ils existent dans la requête
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('fullname', 'like', "%{$searchTerm}%")
                    ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status') === 'active' ? true : false;
            $query->where('is_active', $status);
        }

        // 5. Paginer les résultats FINAUX (après filtrage)
        $contacts = $query->latest()->paginate(10);

        // Très important : ajouter les paramètres de la requête à la pagination
        // pour que les filtres persistent lors du changement de page.
        $contacts->appends($request->query());

        // 6. Préparer les données pour la vue
        $contactType = $type === 'clients' ? 'Clients' : 'Fournisseurs';

        // 7. Retourner la vue avec toutes les données nécessaires
        return view('back.contacts.index', compact(
            'contacts',
            'type',
            'contactType',
            'totalContacts',
            'activeContacts',
            'inactiveContacts',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactRequest $request)
    {
        //
        $path = $request->path();
        $type = $request->type;

        // Vérifie que le type est bien dans le path
        if (! str_contains($path, $type)) {
            abort(403, "Action non autorisée : le type ne correspond pas à l'URL.");
        }

        Contact::create($request->validated());
        $contactType = $request->type === 'client' ? 'Client' : 'Fournisseur';

        return back()->with('success', "Le $contactType a été enrégistré avec success");

    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact, string $type)
    {
        //
        $this->checkAuthorization($contact, $type);

        $contact = Contact::with(['invoices.payments'])
            ->findOrFail($contact->id);

        return view('back.contacts.show', compact('contact', 'type'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact, string $type)
    {
        //
        $this->checkAuthorization($contact, $type);

        return view('back.contacts.edit', compact('contact', 'type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactRequest $request, Contact $contact, string $type)
    {

        $this->checkAuthorization($contact, $type);

        $data = $request->validated();
        $contact->update($data);

        // Redirige avec un message de succès
        return redirect()->route($type.'.index')
            ->with('success', 'Contact mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact, string $type)
    {
        //
        $this->checkAuthorization($contact, $type);

        if ($contact->is_active) {
            return back()->with('error', 'Impossible de supprimer un contact actif. Veuillez le désactiver d’abord.');
        }
        $contact->delete();

        return back()->with('success', 'Contact supprimé avec succès');
    }

    public function validateType(string $type): void
    {
        if (! in_array($type, ['clients', 'suppliers'])) {
            abort(404, 'Page inexistante');
        }
    }

    /**
     * Active / désactive un client.
     */
    public function toggleActive(string $id, string $type)
    {
        $contactType = $type === 'clients' ? 'Client' : 'Fournisseur';

        $client = Contact::findOrFail($id);
        $client->is_active = ! $client->is_active;
        $client->save();

        $message = $client->is_active
            ? "Le $contactType a été activé avec succès."
            : "Le $contactType a été désactivé avec succès.";

        return redirect()->back()->with('success', $message);
    }

    public function checkAuthorization(Contact $contact, string $type)
    {

        if ($contact->type !== rtrim($type, 's')) {
            abort(403, "Vous n'êtes pas autorisé à effectuer cette opération.");
        }

    }
}
