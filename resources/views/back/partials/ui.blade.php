<style>
    :root {
        --sp-primary: #1d4ed8;
        --sp-primary-deep: #0f172a;
        --sp-success: #16a34a;
        --sp-warning: #d97706;
        --sp-danger: #dc2626;
        --sp-info: #0284c7;
        --sp-surface: #ffffff;
        --sp-bg: #f4f7fb;
        --sp-border: rgba(15, 23, 42, 0.08);
        --sp-text: #0f172a;
        --sp-muted: #64748b;
        --sp-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        --sp-shadow-soft: 0 10px 25px rgba(15, 23, 42, 0.06);
        --sp-radius: 22px;
    }

    body {
        background:
            radial-gradient(circle at top left, rgba(29, 78, 216, 0.06), transparent 24%),
            radial-gradient(circle at top right, rgba(16, 185, 129, 0.05), transparent 20%),
            var(--sp-bg);
        color: var(--sp-text);
    }

    .container-fluid {
        padding-top: 1.35rem;
        padding-bottom: 1.35rem;
    }

    .page-hero,
    .panel-card,
    .soft-card,
    .document-shell,
    .metric-card,
    .summary-card,
    .table-card,
    .empty-state {
        background: var(--sp-surface);
        border: 1px solid var(--sp-border) !important;
        border-radius: var(--sp-radius) !important;
        box-shadow: var(--sp-shadow-soft) !important;
    }

    .page-hero {
        padding: 1.4rem 1.5rem;
    }

    .page-hero--accent {
        background:
            linear-gradient(135deg, rgba(29, 78, 216, 0.98), rgba(15, 23, 42, 0.96));
        color: #fff;
        border: none;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
    }

    .page-hero__eyebrow {
        font-size: .72rem;
        letter-spacing: .14em;
        text-transform: uppercase;
        font-weight: 800;
        opacity: .78;
    }

    .page-hero__title {
        font-size: clamp(1.4rem, 2vw, 2rem);
        font-weight: 800;
        line-height: 1.1;
        margin: .15rem 0 .4rem;
    }

    .page-hero__subtitle {
        color: inherit;
        opacity: .82;
        margin-bottom: 0;
    }

    .metric-card {
        padding: 1.2rem 1.25rem;
        height: 100%;
    }

    .metric-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
    }

    .metric-card__label {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 800;
        color: var(--sp-muted);
    }

    .metric-card__value {
        font-size: clamp(1.35rem, 2vw, 2rem);
        font-weight: 800;
        line-height: 1;
        color: var(--sp-text);
    }

    .metric-card__meta {
        color: var(--sp-muted);
        font-size: .9rem;
    }

    .table-card .table,
    .data-table {
        margin-bottom: 0;
    }

    .data-table thead th,
    .table-card thead th {
        background: #f8fafc;
        color: var(--sp-muted);
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 800;
        border-top: none;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        padding-top: 1rem;
        padding-bottom: 1rem;
        white-space: nowrap;
    }

    .data-table tbody td,
    .table-card tbody td {
        vertical-align: middle;
        padding-top: .95rem;
        padding-bottom: .95rem;
        border-color: rgba(15, 23, 42, 0.06);
    }

    .data-table tbody tr:hover,
    .table-card tbody tr:hover {
        background: rgba(59, 130, 246, 0.03);
    }

    .status-pill,
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .36rem .75rem;
        font-size: .73rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .status-pill--success,
    .badge-soft--success {
        background: rgba(22, 163, 74, .1);
        color: #166534;
    }

    .status-pill--warning,
    .badge-soft--warning {
        background: rgba(217, 119, 6, .12);
        color: #92400e;
    }

    .status-pill--danger,
    .badge-soft--danger {
        background: rgba(220, 38, 38, .1);
        color: #991b1b;
    }

    .status-pill--info,
    .badge-soft--info {
        background: rgba(2, 132, 199, .1);
        color: #075985;
    }

    .status-pill--neutral,
    .badge-soft--neutral {
        background: rgba(100, 116, 139, .12);
        color: #334155;
    }

    .action-button,
    .btn {
        border-radius: 14px;
    }

    .btn-primary,
    .btn-success {
        box-shadow: 0 10px 22px rgba(29, 78, 216, .12);
    }

    .page-header,
    .page-header-modern,
    .page-header-vente,
    .warehouse-header,
    .main-card,
    .info-card,
    .card,
    .card.stats-card,
    .card.vente-card,
    .card-hover,
    .card-body,
    .main-table-card {
        border-color: var(--sp-border) !important;
        box-shadow: var(--sp-shadow-soft) !important;
        border-radius: var(--sp-radius) !important;
    }

    .page-header,
    .page-header-modern,
    .page-header-vente,
    .warehouse-header {
        background: linear-gradient(135deg, rgba(29, 78, 216, 0.98), rgba(15, 23, 42, 0.96)) !important;
        color: #fff !important;
        border: none !important;
    }

    .page-header h1,
    .page-header h3,
    .page-header-modern h1,
    .page-header-vente h1,
    .warehouse-header h1,
    .warehouse-header h3 {
        color: #fff !important;
    }

    .card-header,
    .card-header-clean,
    .table-card-header {
        background: #f8fafc !important;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08) !important;
        color: var(--sp-text) !important;
    }

    .thead-light th,
    .table thead th,
    .clean-table thead th,
    .modern-table thead th {
        background: #f8fafc !important;
        color: var(--sp-muted) !important;
        border-top: 0 !important;
    }

    .clean-table,
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .clean-table tbody td,
    .modern-table tbody td,
    .table tbody td {
        border-color: rgba(15, 23, 42, 0.06) !important;
    }

    .modal-content {
        border-radius: 1.25rem !important;
        border: 1px solid rgba(15, 23, 42, 0.08) !important;
        overflow: hidden;
    }

    .empty-state {
        padding: 2rem 1.5rem;
        text-align: center;
        color: var(--sp-muted);
    }

    .empty-state__icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        margin: 0 auto 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(29, 78, 216, 0.08);
        color: var(--sp-primary);
    }

    .section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .section-title h2,
    .section-title h3,
    .section-title h4 {
        margin-bottom: 0;
        font-weight: 800;
        color: var(--sp-text);
    }

    .section-title p {
        margin-bottom: 0;
        color: var(--sp-muted);
    }

    .modern-header {
        background: linear-gradient(135deg, rgba(29, 78, 216, 0.98), rgba(15, 23, 42, 0.96));
        border-bottom: none;
        padding: 1.15rem 1.4rem;
        color: #fff;
    }

    .modern-header .modal-title {
        display: flex;
        align-items: center;
        gap: .55rem;
        font-weight: 800;
        font-size: 1.05rem;
    }

    .modern-header .close {
        color: #fff;
        opacity: .95;
        text-shadow: none;
        font-size: 1.5rem;
        font-weight: 300;
    }

    .modern-body {
        background: #f8fbff;
        padding: 1.5rem;
        max-height: min(72vh, 720px);
        overflow-y: auto;
    }

    .modern-footer {
        background: #fff;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
        padding: 1rem 1.4rem;
        gap: .75rem;
    }

    .modern-label {
        display: block;
        margin-bottom: .45rem;
        font-size: .86rem;
        font-weight: 700;
        color: var(--sp-text);
    }

    .modern-input-group {
        margin-bottom: 1rem;
    }

    .modern-input-wrapper {
        position: relative;
    }

    .modern-input-wrapper .input-icon {
        position: absolute;
        left: .95rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--sp-muted);
        z-index: 2;
        pointer-events: none;
    }

    .modern-input-group .form-control,
    .modern-input-group select.form-control,
    .modern-input-group textarea.form-control {
        border-radius: 16px;
        border: 1px solid rgba(15, 23, 42, 0.1);
        background: #fff;
        box-shadow: none;
    }

    .modern-input-group .form-control {
        min-height: 48px;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .modern-input-group .modern-input-wrapper .form-control {
        padding-left: 2.8rem;
    }

    .modern-input-group .form-control:focus,
    .modern-input-group select.form-control:focus,
    .modern-input-group textarea.form-control:focus {
        border-color: rgba(29, 78, 216, 0.45);
        box-shadow: 0 0 0 .18rem rgba(29, 78, 216, 0.12);
    }

    .modern-checkbox {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .9rem 1rem;
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .modern-checkbox input[type="checkbox"],
    .modern-checkbox input[type="radio"] {
        margin-top: .2rem;
        width: 1.05rem;
        height: 1.05rem;
        accent-color: var(--sp-primary);
    }

    .modern-checkbox label {
        margin-bottom: 0;
        font-weight: 700;
        color: var(--sp-text);
    }

    .btn-modern {
        border-radius: 14px;
        padding: .7rem 1.15rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        border: none;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
    }

    .btn-modern:hover {
        transform: translateY(-1px);
    }

    .btn-modern.btn-primary {
        background: linear-gradient(135deg, var(--sp-primary), #4f46e5);
        color: #fff;
    }

    .btn-modern.btn-secondary {
        background: #e2e8f0;
        color: var(--sp-text);
    }

    .btn-modern.btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
    }

    .gap-1 {
        gap: .25rem !important;
    }

    .gap-2 {
        gap: .5rem !important;
    }

    .gap-3 {
        gap: 1rem !important;
    }

    .gap-4 {
        gap: 1.5rem !important;
    }

    .rounded-2xl {
        border-radius: 1.35rem !important;
    }

    .text-start {
        text-align: left !important;
    }

    .info-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: .8rem 0;
        border-bottom: 1px dashed rgba(15, 23, 42, 0.08);
    }

    .info-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .info-row__label {
        color: var(--sp-muted);
        font-size: .9rem;
    }

    .info-row__value {
        font-weight: 700;
        color: var(--sp-text);
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .9rem 1rem;
        border-radius: 18px;
        background: #fff;
        border: 1px solid var(--sp-border);
        text-decoration: none;
        color: var(--sp-text);
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }

    .quick-action:hover {
        transform: translateY(-1px);
        box-shadow: var(--sp-shadow-soft);
        border-color: rgba(29, 78, 216, .18);
        text-decoration: none;
        color: var(--sp-text);
    }

    .quick-action__icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, #1d4ed8, #0f172a);
    }

    .quick-action__title {
        font-weight: 800;
        line-height: 1.1;
    }

    .quick-action__meta {
        font-size: .82rem;
        color: var(--sp-muted);
    }

    @media (max-width: 767.98px) {
        .page-hero,
        .metric-card,
        .panel-card,
        .soft-card,
        .document-shell,
        .table-card {
            border-radius: 18px;
        }

        .container-fluid {
            padding-top: .9rem;
            padding-bottom: .9rem;
        }
    }

    body .page-header {
        background: linear-gradient(135deg, #0f172a, #1d4ed8) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        border-radius: 24px !important;
        padding: 1.4rem 1.5rem !important;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.14) !important;
        color: #fff !important;
        margin-bottom: 1.5rem !important;
    }

    body .page-header h1,
    body .page-header h2,
    body .page-header h3,
    body .page-header h4,
    body .page-header p {
        color: inherit !important;
    }

    body .stats-card,
    body .search-section,
    body .invoice-list-section,
    body .panel-card,
    body .modal-content,
    body .login-card,
    body .card {
        border: 1px solid rgba(15, 23, 42, 0.08) !important;
        border-radius: 20px !important;
        box-shadow: var(--sp-shadow-soft) !important;
    }

    body .stats-card {
        overflow: hidden !important;
    }

    body .stats-card .card-body,
    body .search-section .card-body,
    body .invoice-list-section .card-body,
    body .modal-body {
        padding: 1.25rem !important;
    }

    body .search-section .card-header,
    body .invoice-list-section .card-header,
    body .modal-header {
        background: linear-gradient(135deg, rgba(29, 78, 216, 0.96), rgba(15, 23, 42, 0.92)) !important;
        color: #fff !important;
        border: 0 !important;
    }

    body .invoice-table thead th,
    body .data-table thead th {
        background: #eef2ff !important;
        color: #334155 !important;
    }

    body .invoice-table tbody tr,
    body .data-table tbody tr {
        border-bottom: 1px solid rgba(15, 23, 42, 0.06) !important;
    }

    body .invoice-table tbody tr:hover,
    body .data-table tbody tr:hover {
        background: rgba(29, 78, 216, 0.03) !important;
    }

    body .page-link {
        border: 0 !important;
        border-radius: 12px !important;
        color: var(--sp-primary) !important;
    }

    body .page-item.active .page-link {
        background: var(--sp-primary) !important;
        color: #fff !important;
    }

    body .alert {
        border-radius: 16px !important;
    }

    body .btn-primary,
    body .btn-success,
    body .btn-danger,
    body .btn-warning,
    body .btn-info {
        border: 0 !important;
    }
</style>
