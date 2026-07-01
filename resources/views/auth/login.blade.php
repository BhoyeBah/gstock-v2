<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/logo/favicon.png') }}">
    <title>Connexion — GStock</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #4f46e5;
            --brand-600: #4338ca;
            --brand-700: #3730a3;
            --ink: #0f172a;
            --soft: #475569;
            --muted: #94a3b8;
            --border: #e6e9f0;
            --ring: 0 0 0 4px rgba(79, 70, 229, .15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--ink);
            background: #f6f8fb;
            min-height: 100vh;
        }
        .shell { display: grid; grid-template-columns: 1.05fr .95fr; min-height: 100vh; }

        /* ---------- Panneau de marque ---------- */
        .brandside {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(1200px 500px at -10% -20%, rgba(124,58,237,.55), transparent 55%),
                radial-gradient(900px 600px at 120% 120%, rgba(56,189,248,.25), transparent 50%),
                linear-gradient(160deg, #111a30 0%, #0f172a 60%, #0b1020 100%);
            color: #fff;
            padding: 56px 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .brandside::after {
            content: ""; position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px);
            background-size: 22px 22px; opacity: .5; pointer-events: none;
        }
        .brand-top { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; }
        .brand-logo { width: 44px; height: 44px; border-radius: 12px; background: #fff; display: grid; place-items: center; box-shadow: 0 8px 24px rgba(0,0,0,.25); }
        .brand-logo img { width: 30px; height: 30px; object-fit: contain; }
        .brand-name { font-size: 22px; font-weight: 800; letter-spacing: .3px; }
        .brand-name span { color: #a5b4fc; }

        .brand-hero { position: relative; z-index: 1; max-width: 460px; }
        .brand-hero h1 { font-size: 40px; line-height: 1.12; font-weight: 800; letter-spacing: -.02em; margin-bottom: 18px; }
        .brand-hero p { color: #cbd5e1; font-size: 16px; line-height: 1.6; }
        .features { position: relative; z-index: 1; display: grid; gap: 14px; margin-top: 6px; }
        .feature { display: flex; align-items: center; gap: 12px; color: #e2e8f0; font-size: 15px; }
        .feature .tick { width: 26px; height: 26px; border-radius: 8px; background: rgba(165,180,252,.16); border: 1px solid rgba(165,180,252,.3); display: grid; place-items: center; color: #c7d2fe; font-size: 13px; flex: none; }
        .brand-foot { position: relative; z-index: 1; color: #64748b; font-size: 13px; }

        /* ---------- Formulaire ---------- */
        .formside { display: flex; align-items: center; justify-content: center; padding: 40px 24px; }
        .card { width: 100%; max-width: 400px; }
        .card h2 { font-size: 26px; font-weight: 800; letter-spacing: -.01em; margin-bottom: 6px; }
        .card .sub { color: var(--soft); font-size: 14.5px; margin-bottom: 30px; }

        .group { margin-bottom: 20px; }
        .lbl { display: block; font-size: 13.5px; font-weight: 600; color: var(--soft); margin-bottom: 8px; }
        .field { position: relative; }
        .field .ic { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; fill: var(--muted); pointer-events: none; transition: fill .2s; }
        .input {
            width: 100%; padding: 13px 44px 13px 42px;
            border: 1.5px solid var(--border); border-radius: 12px;
            font-family: inherit; font-size: 15px; color: var(--ink);
            background: #fff; outline: none; transition: border-color .18s, box-shadow .18s;
        }
        .input::placeholder { color: #aab3c4; }
        .input:focus { border-color: var(--brand); box-shadow: var(--ring); }
        .input:focus + .ic { fill: var(--brand); }
        .input.error { border-color: #ef4444; }
        .toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); padding: 4px; display: grid; place-items: center; }
        .toggle:hover { color: var(--brand); }
        .err { color: #dc2626; font-size: 12.5px; margin-top: 7px; display: flex; align-items: center; gap: 5px; }

        .options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .remember { display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; font-size: 14px; color: var(--soft); }
        .remember input { width: 17px; height: 17px; accent-color: var(--brand); cursor: pointer; }
        .link { color: var(--brand); font-weight: 600; font-size: 14px; text-decoration: none; }
        .link:hover { color: var(--brand-700); }

        .btn {
            width: 100%; padding: 14px; border: none; border-radius: 12px; cursor: pointer;
            font-family: inherit; font-size: 15.5px; font-weight: 700; color: #fff;
            background: var(--brand); box-shadow: 0 8px 20px rgba(79,70,229,.28);
            transition: transform .15s, background .15s, box-shadow .15s;
        }
        .btn:hover { background: var(--brand-600); transform: translateY(-1px); box-shadow: 0 12px 26px rgba(79,70,229,.34); }
        .btn:active { transform: translateY(0); }

        .alert { padding: 12px 15px; border-radius: 12px; margin-bottom: 22px; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .alert.info { background: #eef2ff; color: var(--brand-700); border: 1px solid #e0e7ff; }
        .mobile-brand { display: none; }

        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .brandside { display: none; }
            .formside { padding: 32px 20px; }
            .mobile-brand { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 26px; }
            .mobile-brand .brand-logo { width: 40px; height: 40px; }
            .mobile-brand .brand-name { color: var(--ink); font-size: 20px; }
            .mobile-brand .brand-name span { color: var(--brand); }
        }
    </style>
</head>

<body>
    <div class="shell">
        <!-- Panneau marque -->
        <aside class="brandside">
            <div class="brand-top">
                <div class="brand-logo"><img src="{{ asset('assets/img/logo/logo.png') }}" alt="GStock"></div>
                <div class="brand-name">G<span>Stock</span></div>
            </div>

            <div class="brand-hero">
                <h1>Pilotez votre stock, vos ventes et votre trésorerie.</h1>
                <p>Une plateforme de gestion claire et rapide : point de vente, factures, inventaire et rapports — au même endroit.</p>
            </div>

            <div class="features">
                <div class="feature"><span class="tick">✓</span> Vente rapide au comptoir en moins de 30 secondes</div>
                <div class="feature"><span class="tick">✓</span> Suivi de stock FIFO et alertes en temps réel</div>
                <div class="feature"><span class="tick">✓</span> Rapports de caisse & tableaux de bord clairs</div>
            </div>

            <div class="brand-foot">© {{ date('Y') }} GStock — Gestion de stock intelligente.</div>
        </aside>

        <!-- Formulaire -->
        <main class="formside">
            <div class="card">
                <div class="mobile-brand">
                    <div class="brand-logo"><img src="{{ asset('assets/img/logo/logo.png') }}" alt="GStock"></div>
                    <div class="brand-name">G<span>Stock</span></div>
                </div>

                <h2>Bon retour 👋</h2>
                <p class="sub">Connectez-vous pour accéder à votre espace de gestion.</p>

                @if (session('status'))
                    <div class="alert info"><span>ℹ️</span><span>{{ session('status') }}</span></div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="group">
                        <label for="email" class="lbl">{{ __('Email') }}</label>
                        <div class="field">
                            <input type="email" id="email" name="email"
                                class="input @error('email') error @enderror" placeholder="votre@email.com"
                                value="{{ old('email') }}" required autofocus autocomplete="username">
                            <svg class="ic" viewBox="0 0 24 24"><path d="M20 4H4C2.9 4 2.01 4.9 2.01 6L2 18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 8L12 13L4 8V6L12 11L20 6V8Z"/></svg>
                        </div>
                        @error('email')<div class="err"><span>⚠</span><span>{{ $message }}</span></div>@enderror
                    </div>

                    <div class="group">
                        <label for="password" class="lbl">{{ __('Mot de passe') }}</label>
                        <div class="field">
                            <input type="password" id="password" name="password"
                                class="input @error('password') error @enderror" placeholder="••••••••" required
                                autocomplete="current-password">
                            <svg class="ic" viewBox="0 0 24 24"><path d="M18 8H17V6C17 3.24 14.76 1 12 1C9.24 1 7 3.24 7 6V8H6C4.9 8 4 8.9 4 10V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V10C20 8.9 19.1 8 18 8ZM12 17C10.9 17 10 16.1 10 15C10 13.9 10.9 13 12 13C13.1 13 14 13.9 14 15C14 16.1 13.1 17 12 17ZM15.1 8H8.9V6C8.9 4.29 10.29 2.9 12 2.9C13.71 2.9 15.1 4.29 15.1 6V8Z"/></svg>
                            <button type="button" class="toggle" id="togglePassword" aria-label="Afficher le mot de passe">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M12 6C15.79 6 19.17 8.13 20.82 11.5C19.17 14.87 15.79 17 12 17C8.21 17 4.83 14.87 3.18 11.5C4.83 8.13 8.21 6 12 6Z" fill="currentColor"/><circle cx="12" cy="11.5" r="2.5" fill="#fff"/></svg>
                            </button>
                        </div>
                        @error('password')<div class="err"><span>⚠</span><span>{{ $message }}</span></div>@enderror
                    </div>

                    <div class="options">
                        <label for="remember_me" class="remember">
                            <input type="checkbox" id="remember_me" name="remember">
                            <span>{{ __('Se souvenir de moi') }}</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="link">Mot de passe oublié ?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn">{{ __('Se connecter') }}</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        if (togglePassword) {
            togglePassword.addEventListener('click', () => {
                passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            });
        }
        document.querySelectorAll('.alert').forEach(a => {
            setTimeout(() => { a.style.transition = 'opacity .5s'; a.style.opacity = '0'; setTimeout(() => a.remove(), 500); }, 6000);
        });
    </script>
</body>

</html>
