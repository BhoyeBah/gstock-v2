<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reçu {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background:#eee; margin:0; padding:20px; color:#000; }
        .ticket { width: 320px; margin: 0 auto; background:#fff; padding: 18px; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
        .center { text-align:center; }
        .muted { color:#555; }
        h2 { margin:0 0 2px; font-size:18px; }
        table { width:100%; border-collapse:collapse; font-size:12px; }
        th, td { padding:2px 0; text-align:left; }
        td.num, th.num { text-align:right; }
        .divider { border-top:1px dashed #000; margin:8px 0; }
        .totline { display:flex; justify-content:space-between; font-size:13px; margin:2px 0; }
        .grand { font-weight:bold; font-size:15px; }
        .actions { width:320px; margin: 12px auto; display:flex; gap:8px; }
        .btn { flex:1; text-align:center; padding:8px; border-radius:6px; text-decoration:none; color:#fff; border:none; cursor:pointer; font-size:13px; }
        .btn-print { background:#4e73df; }
        .btn-back { background:#858796; }
        @media print {
            body { background:#fff; padding:0; }
            .ticket { box-shadow:none; width: 100%; }
            .actions { display:none; }
        }
    </style>
</head>
<body>
    @php
        $currency = optional($setting)->currency ?: 'FCFA';
        $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ').' '.$currency;
        $paid = (int) $invoice->payments->where('amount_paid', '>', 0)->sum('amount_paid');
    @endphp

    <div class="ticket">
        <div class="center">
            <h2>{{ optional($invoice->tenant)->name ?? 'Reçu de caisse' }}</h2>
            <div class="muted" style="font-size:12px;">Reçu de vente</div>
        </div>
        <div class="divider"></div>

        <div style="font-size:12px;">
            <div><strong>N° :</strong> {{ $invoice->invoice_number }}</div>
            <div><strong>Date :</strong> {{ optional($invoice->invoice_date)->format('d/m/Y') }} {{ $invoice->created_at->format('H:i') }}</div>
            <div><strong>Client :</strong> {{ optional($invoice->contact)->fullname ?? 'Client de passage' }}</div>
        </div>
        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th>Article</th>
                    <th class="num">Qté</th>
                    <th class="num">P.U.</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ optional($item->product)->name ?? '—' }}</td>
                        <td class="num">{{ $item->quantity }}</td>
                        <td class="num">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                        <td class="num">{{ number_format($item->total_line, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="divider"></div>

        <div class="totline grand"><span>TOTAL</span><span>{{ $fmt($invoice->total_invoice) }}</span></div>
        <div class="totline"><span>Payé</span><span>{{ $fmt($paid) }}</span></div>
        <div class="totline"><span>Reste dû</span><span>{{ $fmt($invoice->balance) }}</span></div>

        <div class="divider"></div>
        <div class="center muted" style="font-size:12px;">
            @if ($invoice->balance > 0)
                Vente à crédit — solde restant {{ $fmt($invoice->balance) }}
            @else
                Merci de votre visite !
            @endif
        </div>
    </div>

    <div class="actions">
        <a href="{{ route('pos.index') }}" class="btn btn-back">Nouvelle vente</a>
        <button type="button" class="btn btn-print" onclick="window.print()">Imprimer</button>
    </div>
</body>
</html>
