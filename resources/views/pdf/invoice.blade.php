<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#0F172A; background:#FFFFFF; }

        .header { background:#0F172A; padding:24px 32px; display:flex; justify-content:space-between; align-items:center; }
        .header-brand { font-size:18px; font-weight:bold; color:#FFFFFF; letter-spacing:-.02em; }
        .header-badge { font-size:9px; font-weight:bold; text-transform:uppercase; letter-spacing:.08em; color:#10B981; }

        .title-bar { padding:20px 32px; display:flex; justify-content:space-between; align-items:flex-start; border-bottom:1px solid #E2E8F0; }
        .title-text { font-size:20px; font-weight:bold; color:#0F172A; }
        .title-number { font-size:11px; color:#64748B; margin-top:4px; }
        .status-badge { font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:.05em; padding:5px 12px; border-radius:6px; }
        .status-paid { background:rgba(16,185,129,.12); color:#166534; }
        .status-unpaid { background:rgba(42,63,126,.1); color:#334155; }
        .status-overdue { background:rgba(224,92,58,.12); color:#C04020; }
        .status-cancelled { background:rgba(120,120,120,.12); color:#555; }

        .info-section { display:flex; padding:24px 32px; gap:40px; }
        .info-block { flex:1; }
        .info-label { font-size:9px; text-transform:uppercase; letter-spacing:.06em; color:#64748B; margin-bottom:4px; }
        .info-value { font-size:13px; font-weight:bold; color:#0F172A; margin-bottom:2px; }
        .info-sub { font-size:10px; color:#64748B; }

        .amount-section { padding:0 32px 24px; }
        table { width:100%; border-collapse:collapse; }
        thead th { background:#0F172A; color:#FFFFFF; padding:10px 14px; font-size:10px; text-transform:uppercase; letter-spacing:.04em; font-weight:bold; text-align:left; }
        thead th.num { text-align:right; }
        tbody td { padding:12px 14px; border-bottom:1px solid #E2E8F0; font-size:11px; }
        tbody td.num { text-align:right; }
        tfoot td { padding:14px; font-size:14px; font-weight:bold; }
        tfoot td.num { text-align:right; color:#0F172A; }

        .footer { padding:20px 32px; border-top:1px solid #E2E8F0; font-size:9px; color:#94A3B8; line-height:1.6; }
    </style>
</head>
<body>
    <div class="header">
        <span class="header-brand">Dougsi</span>
        <span class="header-badge">Facture</span>
    </div>

    <div class="title-bar">
        <div>
            <div class="title-text">{{ $invoice->invoice_number }}</div>
            <div class="title-number">
                Émise le {{ $invoice->issued_at?->format('d/m/Y') }}
                @if ($invoice->due_at) · Échéance le {{ $invoice->due_at->format('d/m/Y') }} @endif
            </div>
        </div>
        <span class="status-badge status-{{ $invoice->status }}">
            {{ match($invoice->status) { 'paid'=>'Payée','unpaid'=>'Impayée','overdue'=>'En retard','cancelled'=>'Annulée',default=>ucfirst($invoice->status) } }}
        </span>
    </div>

    <div class="info-section">
        <div class="info-block">
            <div class="info-label">Facturé à</div>
            <div class="info-value">{{ $invoice->school?->name }}</div>
            @if ($invoice->school?->email)
                <div class="info-sub">{{ $invoice->school->email }}</div>
            @endif
            @if ($invoice->school?->phone)
                <div class="info-sub">{{ $invoice->school->phone }}</div>
            @endif
        </div>
        <div class="info-block">
            <div class="info-label">Abonnement</div>
            <div class="info-value">{{ $invoice->subscription?->plan?->name ?? 'Montant négocié' }}</div>
            @if ($invoice->subscription)
                <div class="info-sub">{{ $invoice->subscription->cycleLabel() }}</div>
            @endif
        </div>
    </div>

    <div class="amount-section">
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="num">Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Abonnement Dugsi
                        @if ($invoice->subscription) — {{ $invoice->subscription->cycleLabel() }} @endif
                    </td>
                    <td class="num">{{ number_format($invoice->amount, 0, ',', ' ') }} FDJ</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="num">{{ number_format($invoice->amount, 0, ',', ' ') }} FDJ</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        Dougsi — Plateforme de gestion scolaire. Facture générée automatiquement.
    </div>
</body>
</html>
