<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                --auth-primary: #1d4ed8;
                --auth-primary-deep: #0f172a;
                --auth-surface: rgba(255, 255, 255, 0.95);
                --auth-border: rgba(15, 23, 42, 0.08);
                --auth-shadow: 0 24px 70px rgba(15, 23, 42, 0.16);
            }

            body.auth-shell {
                font-family: 'Inter', system-ui, sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(29, 78, 216, 0.18), transparent 26%),
                    radial-gradient(circle at bottom right, rgba(16, 185, 129, 0.12), transparent 22%),
                    linear-gradient(135deg, #eaf2ff 0%, #f8fafc 60%, #eef6ef 100%);
                min-height: 100vh;
            }

            .auth-grid {
                min-height: 100vh;
                display: grid;
                grid-template-columns: 1.1fr minmax(360px, 460px);
            }

            .auth-hero {
                padding: 3rem;
                color: #fff;
                background:
                    radial-gradient(circle at top right, rgba(255, 255, 255, 0.12), transparent 28%),
                    linear-gradient(160deg, #0f172a 0%, #1d4ed8 55%, #0f172a 100%);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                position: relative;
                overflow: hidden;
            }

            .auth-hero::before,
            .auth-hero::after {
                content: '';
                position: absolute;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.08);
                filter: blur(8px);
            }

            .auth-hero::before {
                width: 320px;
                height: 320px;
                top: -110px;
                right: -90px;
            }

            .auth-hero::after {
                width: 220px;
                height: 220px;
                bottom: -80px;
                left: -50px;
            }

            .auth-brand {
                position: relative;
                z-index: 1;
                display: inline-flex;
                align-items: center;
                gap: .85rem;
                margin-bottom: 2rem;
                color: #fff;
                text-decoration: none;
            }

            .auth-brand__logo {
                width: 54px;
                height: 54px;
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.95);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
            }

            .auth-brand__title {
                font-size: 1.35rem;
                font-weight: 800;
                line-height: 1;
            }

            .auth-brand__meta {
                font-size: .86rem;
                opacity: .82;
            }

            .auth-hero__title {
                position: relative;
                z-index: 1;
                max-width: 34rem;
                font-size: clamp(2rem, 4vw, 3.4rem);
                line-height: 1.02;
                font-weight: 800;
                margin-bottom: 1rem;
            }

            .auth-hero__copy {
                position: relative;
                z-index: 1;
                max-width: 34rem;
                color: rgba(255, 255, 255, 0.84);
                font-size: 1.02rem;
                line-height: 1.7;
            }

            .auth-points {
                position: relative;
                z-index: 1;
                display: grid;
                gap: .85rem;
                margin-top: 2rem;
                max-width: 28rem;
            }

            .auth-point {
                display: flex;
                align-items: center;
                gap: .75rem;
                padding: .85rem 1rem;
                border-radius: 16px;
                background: rgba(255, 255, 255, 0.09);
                border: 1px solid rgba(255, 255, 255, 0.10);
            }

            .auth-point i {
                color: #93c5fd;
            }

            .auth-panel {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }

            .auth-card {
                width: 100%;
                max-width: 480px;
                background: var(--auth-surface);
                border: 1px solid var(--auth-border);
                border-radius: 28px;
                box-shadow: var(--auth-shadow);
                backdrop-filter: blur(14px);
                padding: 2rem;
            }

            .auth-heading {
                margin-bottom: 1.5rem;
            }

            .auth-heading__eyebrow {
                font-size: .75rem;
                text-transform: uppercase;
                letter-spacing: .14em;
                font-weight: 800;
                color: var(--auth-primary);
            }

            .auth-heading__title {
                font-size: 1.75rem;
                font-weight: 800;
                margin: .2rem 0 .35rem;
                color: var(--auth-primary-deep);
            }

            .auth-heading__copy {
                color: #64748b;
                line-height: 1.6;
            }

            .auth-form .form-group {
                margin-bottom: 1rem;
            }

            .auth-form .form-control {
                border-radius: 14px;
                border: 1px solid rgba(15, 23, 42, 0.12);
                padding: .85rem 1rem;
                min-height: 48px;
            }

            .auth-form .form-control:focus {
                border-color: var(--auth-primary);
                box-shadow: 0 0 0 3px rgba(29, 78, 216, .10);
            }

            .auth-form .btn {
                border-radius: 14px;
                min-height: 48px;
                font-weight: 700;
            }

            .auth-footer {
                margin-top: 1rem;
                color: #64748b;
                font-size: .92rem;
            }

            @media (max-width: 991.98px) {
                .auth-grid {
                    grid-template-columns: 1fr;
                }

                .auth-hero {
                    display: none;
                }

                .auth-panel {
                    padding: 1rem;
                }

                .auth-card {
                    padding: 1.5rem;
                }
            }
        </style>
    </head>
    <body class="auth-shell text-gray-900 antialiased">
        <div class="auth-grid">
            <aside class="auth-hero">
                <div>
                    <a href="/" class="auth-brand">
                        <span class="auth-brand__logo">
                            <x-application-logo class="w-11 h-11 fill-current text-blue-700" />
                        </span>
                        <span>
                            <span class="auth-brand__title">{{ config('app.name', 'StockPro') }}</span>
                            <span class="auth-brand__meta d-block">Mini-ERP SaaS multi-tenant</span>
                        </span>
                    </a>

                    <h1 class="auth-hero__title">Une interface plus claire pour vendre, stocker et piloter plus vite.</h1>
                    <p class="auth-hero__copy">
                        StockPro aide les boutiques, pharmacies, grossistes et PME à garder un flux simple, lisible et
                        professionnel du stock jusqu’au paiement.
                    </p>
                </div>

                <div class="auth-points">
                    <div class="auth-point">
                        <i class="fas fa-shield-alt"></i>
                        <span>Isolation multi-tenant et permissions par plan</span>
                    </div>
                    <div class="auth-point">
                        <i class="fas fa-cash-register"></i>
                        <span>POS, documents commerciaux et clôture de caisse</span>
                    </div>
                    <div class="auth-point">
                        <i class="fas fa-chart-line"></i>
                        <span>Tableaux de bord et rapports clairs pour décider vite</span>
                    </div>
                </div>
            </aside>

            <main class="auth-panel">
                <div class="auth-card">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
            </div>
        </div>
    </body>
</html>
