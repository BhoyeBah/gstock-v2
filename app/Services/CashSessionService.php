<?php

namespace App\Services;

use App\Models\CashSession;
use Illuminate\Support\Facades\Auth;

/**
 * Sprint 2 - Fonctionnalité 10 : clôture journalière de caisse.
 */
class CashSessionService
{
    /**
     * Ouvre une session de caisse pour un wallet (tiroir-caisse).
     */
    public function open(string $walletId, int $openingAmount, ?string $note = null): CashSession
    {
        if ($openingAmount < 0) {
            throw new \DomainException("Le fonds d'ouverture ne peut pas être négatif.");
        }

        $alreadyOpen = CashSession::open()->where('wallet_id', $walletId)->exists();

        if ($alreadyOpen) {
            throw new \DomainException('Une session de caisse est déjà ouverte pour ce moyen de paiement.');
        }

        return CashSession::create([
            'user_id' => Auth::id(),
            'wallet_id' => $walletId,
            'status' => CashSession::STATUS_OPEN,
            'opening_amount' => $openingAmount,
            'opened_at' => now(),
            'note' => $note,
        ]);
    }

    /**
     * Ferme une session : calcule le montant attendu (fonds + encaissements)
     * et l'écart avec le montant physiquement compté.
     */
    public function close(CashSession $session, int $countedAmount, ?string $note = null): CashSession
    {
        if (! $session->isOpen()) {
            throw new \DomainException('Cette session de caisse est déjà clôturée.');
        }

        if ($countedAmount < 0) {
            throw new \DomainException('Le montant compté ne peut pas être négatif.');
        }

        $expected = $session->opening_amount + $session->collectedAmount();

        $session->update([
            'status' => CashSession::STATUS_CLOSED,
            'expected_amount' => $expected,
            'counted_amount' => $countedAmount,
            'difference' => $countedAmount - $expected,
            'closed_at' => now(),
            'note' => $note ?? $session->note,
        ]);

        return $session;
    }
}
