<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Wallet;
use App\Services\CashSessionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashSessionController extends Controller
{
    public function __construct(private CashSessionService $service) {}

    public function index()
    {
        $wallets = Wallet::where('is_active', true)->orderBy('name')->get();

        $openSessions = CashSession::open()
            ->with('wallet', 'user')
            ->get();

        $sessions = CashSession::with('wallet', 'user')
            ->orderByDesc('opened_at')
            ->paginate(15);

        return view('back.sales.cash_sessions', compact('wallets', 'openSessions', 'sessions'));
    }

    public function open(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $data = $request->validate([
            'wallet_id' => ['required', 'uuid', Rule::exists('wallets', 'id')->where('tenant_id', $tenantId)],
            'opening_amount' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->service->open($data['wallet_id'], (int) $data['opening_amount'], $data['note'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Session de caisse ouverte.');
    }

    public function close(Request $request, CashSession $cashSession)
    {
        $data = $request->validate([
            'counted_amount' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->service->close($cashSession, (int) $data['counted_amount'], $data['note'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('cash-sessions.show', $cashSession->id)
            ->with('success', 'Caisse clôturée.');
    }

    public function show(CashSession $cashSession)
    {
        $cashSession->load(['wallet', 'user', 'payments.invoice']);

        return view('back.sales.cash_session_show', compact('cashSession'));
    }
}
