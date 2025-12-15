<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DYMO-STOCK | La Gestion Cloud Intelligente</title>

    <!-- Polices & Icones -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


    <!-- ==============================================
         SEO STANDARD
         ============================================== -->
    <meta name="description"
        content="Gérez vos stocks multi-dépôts, factures et comptabilité avec DYMO-STOCK. La solution SaaS cloud complète pour les PME en Afrique de l'Ouest. Essai gratuit sans engagement.">
    <meta name="keywords"
        content="gestion de stock, logiciel facturation, SaaS Sénégal, ERP PME, gestion commerciale cloud, multi-entrepôts, DYMO-STOCK">
    <meta name="author" content="Dymo Technologies">
    <meta name="robots" content="index, follow">
    <!-- Remplacez par votre vrai domaine -->
    <link rel="canonical" href="https://gstock.dymotechnologie.com/">

    <!-- ==============================================
         OPEN GRAPH (Facebook, LinkedIn, WhatsApp)
         ============================================== -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://gstock.dymotechnologie.com/">
    <meta property="og:title" content="DYMO-STOCK | Pilotez votre entreprise avec précision">
    <meta property="og:description"
        content="Découvrez la solution de gestion tout-en-un : Stocks, Ventes, Achats et Finances. Simplifiez votre quotidien dès aujourd'hui.">
    <!-- Image de partage (idéalement 1200x630px) -->
    <meta property="og:image" content="{{ asset('assets/img/logo/logo.png') }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="DYMO-STOCK">

    <!-- ==============================================
         TWITTER CARD (X)
         ============================================== -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://gstock.dymotechnologie.com/">
    <meta name="twitter:title" content="DYMO-STOCK | La Gestion Cloud Intelligente">
    <meta name="twitter:description"
        content="Logiciel de gestion de stock et facturation pour les PME. Testez gratuitement notre solution cloud sécurisée.">
    <meta name="twitter:image" content="{{ asset('assets/img/logo/logo.png') }}">

    <!-- ==============================================
         ICONES & THEME MOBILE
         ============================================== -->
    <!-- Couleur de la barre d'adresse sur mobile (Chrome/Safari) -->
    <meta name="theme-color" content="#4F46E5">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/logo/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/{{ asset('assets/img/logo/favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/{{ asset('assets/img/logo/favicon.png') }}">


    <style>
        /* --- RESET & VARIABLES --- */
        :root {
            --primary: #4F46E5;
            /* Indigo 600 */
            --primary-light: #818CF8;
            --primary-dark: #4338ca;
            --secondary: #10B981;
            /* Emerald 500 */
            --dark: #0F172A;
            /* Slate 900 */
            --gray: #64748B;
            --light: #F8FAFC;
            --white: #FFFFFF;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            background-color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        body.no-scroll {
            overflow: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: 0.3s ease;
        }

        ul {
            list-style: none;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .pricing-toggle-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 32px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        input:checked+.slider {
            background-color: var(--primary);
        }

        input:checked+.slider:before {
            transform: translateX(28px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        /* --- ANIMATIONS --- */
        @keyframes float {
            0% {
                transform: translateY(0px) perspective(1000px) rotateY(-5deg) rotateX(2deg);
            }

            50% {
                transform: translateY(-20px) perspective(1000px) rotateY(-5deg) rotateX(2deg);
            }

            100% {
                transform: translateY(0px) perspective(1000px) rotateY(-5deg) rotateX(2deg);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- COMPONENTS --- */
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem 2rem;
            font-weight: 600;
            border-radius: 9999px;
            transition: all 0.3s ease;
            gap: 0.5rem;
            font-size: 1rem;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.23);
            background: var(--primary-dark);
        }

        .btn-white {
            background: white;
            color: var(--dark);
            border: 1px solid #E2E8F0;
        }

        .btn-white:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: #F8FAFC;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-purple {
            background: #EEF2FF;
            color: #4F46E5;
        }

        .badge-green {
            background: #ECFDF5;
            color: #059669;
        }

        /* --- HEADER --- */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .nav {
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.25rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark);
            z-index: 1001;
        }

        .logo i {
            color: var(--primary);
        }

        /* Navigation Links - Mobile */
        .nav-links {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 300px;
            height: 100vh;
            background: white;
            flex-direction: column;
            padding: 6rem 2rem;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
            transition: 0.4s ease;
            display: flex;
            gap: 2rem;
            z-index: 999;
        }

        .nav-links.active {
            right: 0;
        }

        .nav-links a {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
        }

        .nav-actions {
            display: none;
        }

        .mobile-toggle {
            display: block;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 1001;
            color: var(--dark);
        }

        /* Desktop Header */
        @media (min-width: 1024px) {
            .nav {
                height: 80px;
            }

            .logo {
                font-size: 1.5rem;
            }

            .mobile-toggle {
                display: none;
            }

            .nav-links {
                position: static;
                height: auto;
                width: auto;
                background: transparent;
                flex-direction: row;
                padding: 0;
                box-shadow: none;
                gap: 2.5rem;
            }

            .nav-links a {
                font-size: 0.95rem;
                color: var(--gray);
                font-weight: 500;
            }

            .nav-links a:hover {
                color: var(--primary);
            }

            .nav-actions {
                display: flex;
                gap: 1rem;
            }

            .nav-links .mobile-only-btn {
                display: none;
            }
        }

        /* --- HERO --- */
        .hero {
            padding: 120px 0 60px;
            background: radial-gradient(circle at top right, #EEF2FF 0%, transparent 40%),
                radial-gradient(circle at bottom left, #FDF2F8 0%, transparent 40%);
            overflow: hidden;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            align-items: center;
        }

        .hero-content {
            text-align: center;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, #1e293b, #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            font-size: 1.125rem;
            color: var(--gray);
            margin-bottom: 2rem;
            margin-left: auto;
            margin-right: auto;
            max-width: 100%;
        }

        .hero-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }

        .hero-trust {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            align-items: center;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .hero-visual {
            position: relative;
            margin-top: 1rem;
        }

        @media (min-width: 768px) {
            .hero-content h1 {
                font-size: 3.5rem;
            }

            .hero-buttons {
                flex-direction: row;
                justify-content: center;
            }

            .hero-trust {
                flex-direction: row;
                gap: 2rem;
                justify-content: center;
            }
        }

        @media (min-width: 1024px) {
            .hero {
                padding: 180px 0 100px;
            }

            .hero-grid {
                grid-template-columns: 1fr 1fr;
                gap: 4rem;
                text-align: left;
            }

            .hero-content {
                text-align: left;
            }

            .hero-buttons {
                justify-content: flex-start;
            }

            .hero-content h1 {
                font-size: 4rem;
                line-height: 1.1;
            }

            .hero-trust {
                justify-content: flex-start;
            }
        }

        /* Abstract Dashboard UI */
        .dashboard-mockup {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            padding: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transform: none;
            width: 100%;
        }

        @media (min-width: 1024px) {
            .dashboard-mockup {
                padding: 1.5rem;
                border-radius: 20px;
                transform: perspective(1000px) rotateY(-5deg) rotateX(2deg);
                animation: float 6s ease-in-out infinite;
            }

            .dashboard-mockup:hover {
                transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
            }
        }

        .mockup-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 1rem;
        }

        .mockup-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: #F8FAFC;
            padding: 0.75rem;
            border-radius: 12px;
        }

        /* --- FEATURES GRID --- */
        .features {
            padding: 5rem 0;
            background: #fff;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 3rem;
        }

        .section-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -0.01em;
            line-height: 1.2;
        }

        .bento-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .feature-card {
            padding: 2rem;
            border-radius: 20px;
            background: white;
            border: 1px solid #F1F5F9;
            transition: all 0.4s ease;
            box-shadow: var(--shadow-sm);
        }

        @media (min-width: 768px) {
            .bento-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }
        }

        @media (min-width: 1024px) {
            .features {
                padding: 8rem 0;
            }

            .section-header h2 {
                font-size: 2.5rem;
            }

            .section-header {
                margin-bottom: 5rem;
            }

            .bento-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .feature-card {
                padding: 2.5rem;
            }
        }

        .feature-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            font-size: 1.25rem;
            transition: 0.3s;
        }

        .icon-blue {
            background: #EEF2FF;
            color: var(--primary);
        }

        .icon-green {
            background: #ECFDF5;
            color: var(--secondary);
        }

        .icon-orange {
            background: #FFF7ED;
            color: #F97316;
        }

        .icon-pink {
            background: #FDF2F8;
            color: #EC4899;
        }

        .feature-card:hover .feature-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-card h3 {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .feature-card p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* --- DETAILED FEATURES --- */
        .detail-section {
            padding: 4rem 0;
            background: var(--light);
        }

        .detail-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            align-items: center;
        }

        .detail-content h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            margin-top: 1rem;
        }

        .detail-visual {
            width: 100%;
            height: 380px;
            background: #F8FAFC;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .detail-visual img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        @media (min-width: 1024px) {
            .detail-section {
                padding: 6rem 0;
            }

            .detail-row {
                grid-template-columns: 1fr 1fr;
                gap: 6rem;
            }

            .detail-row.reverse .detail-content {
                order: 1;
            }

            .detail-row.reverse .detail-visual {
                order: -1;
            }
        }

        .check-list li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
            color: var(--gray);
            font-weight: 500;
        }

        .check-list i {
            color: var(--secondary);
            background: #D1FAE5;
            padding: 4px;
            border-radius: 50%;
            font-size: 0.8rem;
            margin-top: 3px;
            flex-shrink: 0;
        }

        /* --- PRICING --- */
        .pricing {
            padding: 5rem 0;
        }

        .pricing-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-top: 3rem;
        }

        .price-card {
            border: 1px solid #E2E8F0;
            border-radius: 24px;
            padding: 2.5rem 1.5rem;
            text-align: center;
            transition: 0.4s;
            background: white;
            position: relative;
        }

        @media (min-width: 1024px) {
            .pricing {
                padding: 8rem 0;
            }

            .pricing-cards {
                grid-template-columns: repeat(3, 1fr);
                gap: 2rem;
            }

            .price-card {
                padding: 3rem 2rem;
            }

            .price-card.popular {
                border-color: var(--primary);
                box-shadow: var(--shadow-lg);
                transform: scale(1.05);
                z-index: 10;
            }
        }

        @media (max-width: 1023px) {
            .price-card.popular {
                border-color: var(--primary);
                box-shadow: var(--shadow-md);
                border-width: 2px;
            }
        }

        .price-val {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            margin: 1rem 0;
        }

        .price-period {
            font-size: 1rem;
            color: var(--gray);
            font-weight: 500;
        }

        /* --- CTA --- */
        .cta-section {
            background: var(--dark);
            padding: 4rem 1.5rem;
            position: relative;
            overflow: hidden;
            text-align: center;
            color: white;
        }

        .cta-bg-glow {
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.4) 0%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Country selector */
        .country-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .country-selector select {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            cursor: pointer;
        }

        @media (min-width: 1024px) {
            .cta-section {
                padding: 6rem 0;
            }

            .cta-bg-glow {
                width: 600px;
                height: 600px;
            }
        }

        /* --- FOOTER --- */
        .footer {
            background: #fff;
            padding: 3rem 0 2rem;
            border-top: 1px solid #E2E8F0;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
            margin-bottom: 3rem;
            text-align: center;
        }

        .footer-col-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .footer ul {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            align-items: center;
        }

        @media (min-width: 768px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
                text-align: left;
            }

            .footer-col-brand,
            .footer ul {
                align-items: flex-start;
            }
        }

        @media (min-width: 1024px) {
            .footer-grid {
                grid-template-columns: 1.5fr 1fr 1fr 1fr;
                gap: 2rem;
            }

            .footer {
                padding: 4rem 0 2rem;
            }
        }

        /* --- MODAL & OVERLAYS --- */

        /* Mobile Menu Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: 0.3s;
            backdrop-filter: blur(2px);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Buttons */
        .mobile-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
            width: 100%;
        }

        .mobile-buttons .btn {
            width: 100%;
        }

        /* CONTACT MODAL */
        .contact-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3000;
            backdrop-filter: blur(4px);
        }

        .contact-modal.active {
            display: flex;
        }

        .contact-modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            max-width: 420px;
            width: 90%;
            position: relative;
            box-shadow: var(--shadow-lg);
            animation: fadeUp 0.4s ease;
            text-align: center;
        }

        .contact-close {
            position: absolute;
            top: 14px;
            right: 18px;
            font-size: 1.6rem;
            cursor: pointer;
            color: var(--gray);
            transition: 0.2s;
        }

        .contact-close:hover {
            color: var(--dark);
        }

        .contact-list {
            list-style: none;
            padding: 0;
            margin-bottom: 2rem;
            margin-top: 1rem;
        }

        .contact-list li {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 0.8rem;
            font-weight: 500;
            color: var(--dark);
        }

        .contact-list i {
            color: var(--primary);
        }
    </style>
</head>

<body>

    <!-- Overlay for mobile menu -->
    <div class="overlay" id="menuOverlay"></div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <!-- Brand -->
                <a class="logo" href="/">
                    <div class="sidebar-brand-icon">
                        <img src="{{ asset('assets/img/logo/favicon.png') }}" alt="DYMO STOCK" class="sidebar-logo">
                    </div>
                    <span>DYMO-STOCK</span>
                </a>

                <!-- Mobile Toggle Button -->
                <div class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </div>

                <!-- Navigation Links -->
                <ul class="nav-links" id="navLinks">
                    <li><a href="#fonctionnalites" class="nav-link">Fonctionnalités</a></li>
                    <li><a href="#inventory" class="nav-link">Stocks</a></li>
                    <li><a href="#finance" class="nav-link">Finance</a></li>
                    <li><a href="#pricing" class="nav-link">Tarifs</a></li>

                    <!-- Mobile only buttons -->
                    <li class="mobile-only-btn mobile-buttons">
                        <a href="{{ route('login') }}" class="btn btn-white">Connexion</a>
                        <!-- JS Class added here for modal -->
                        <button class="btn btn-primary js-open-modal">Essai Gratuit</button>
                    </li>
                </ul>

                <!-- Desktop Actions -->
                <div class="nav-actions">
                    <a href="{{ route('login') }}" class="btn btn-white" style="padding: 0.6rem 1.2rem;">Connexion</a>
                    <!-- JS Class added here for modal -->
                    <button class="btn btn-primary js-open-modal" style="padding: 0.6rem 1.2rem;">Essai Gratuit</button>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content reveal active">
                    <span class="badge badge-purple" style="margin-bottom: 1.5rem;">Version 2.0 Disponible</span>
                    <h1>Pilotez votre entreprise avec <span style="color: var(--primary);">précision</span>.</h1>
                    <p>La solution SaaS complète pour la gestion de stocks multi-dépôts, la facturation et la
                        comptabilité. Conçu pour les PME modernes.</p>
                    <div class="hero-buttons">
                        <!-- JS Class added here for modal -->
                        <button class="btn btn-primary js-open-modal">
                            Commencer maintenant <i class="fas fa-arrow-right"></i>
                        </button>
                        {{-- <a href="#fonctionnalites" class="btn btn-white">
                            <i class="fas fa-play"></i> Démo Vidéo
                        </a> --}}
                    </div>
                    <div class="hero-trust">
                        <span><i class="fas fa-check-circle" style="color: var(--secondary);"></i> 30 jours
                            gratuits</span>
                        <span><i class="fas fa-check-circle" style="color: var(--secondary);"></i> Pas de carte
                            requise</span>
                    </div>
                </div>

                <!-- Abstract UI Visualization -->
                <div class="hero-visual">
                    <div class="dashboard-mockup">
                        <div class="mockup-header">
                            <div style="font-weight: 700; font-size: 1.1rem;">Tableau de bord</div>
                            <div style="display: flex; gap: 0.5rem;">
                                <div style="width: 10px; height: 10px; border-radius: 50%; background: #EF4444;"></div>
                                <div style="width: 10px; height: 10px; border-radius: 50%; background: #F59E0B;"></div>
                                <div style="width: 10px; height: 10px; border-radius: 50%; background: #10B981;"></div>
                            </div>
                        </div>
                        <div class="mockup-stats">
                            <div class="stat-card">
                                <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.5rem;">Revenus
                                    (Mois)</div>
                                <div style="font-size: 1.2rem; font-weight: 800; color: var(--primary);">4.2M FCFA
                                </div>
                                <div style="font-size: 0.75rem; color: var(--secondary); margin-top: 0.2rem;"><i
                                        class="fas fa-arrow-up"></i> +12%</div>
                            </div>
                            <div class="stat-card">
                                <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.5rem;">Stock
                                    Critique</div>
                                <div style="font-size: 1.2rem; font-weight: 800; color: #EF4444;">12</div>
                                <div style="font-size: 0.75rem; color: var(--gray); margin-top: 0.2rem;">Produits</div>
                            </div>
                        </div>
                        <!-- Fake List -->
                        <div style="background: #F8FAFC; border-radius: 12px; padding: 1rem;">
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 0.8rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600;">
                                <span>Factures</span>
                                <span>Statut</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; font-size: 0.85rem;">
                                <div><i class="fas fa-file-invoice"
                                        style="color: var(--gray); margin-right: 8px;"></i>
                                    #001</div>
                                <span class="badge badge-green">Payée</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                                <div><i class="fas fa-file-invoice"
                                        style="color: var(--gray); margin-right: 8px;"></i>
                                    #002</div>
                                <span class="badge badge-purple">Validée</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="features" id="fonctionnalites">
        <div class="container">
            <div class="section-header reveal">
                <span class="badge badge-purple">Fonctionnalités Clés</span>
                <h2>Une suite complète d'outils</h2>
                <p style="color: var(--gray);">Tout ce dont vous avez besoin pour gérer vos stocks, vos finances et vos
                    équipes au même endroit.</p>
            </div>

            <div class="bento-grid">
                <!-- Card 1: Stocks -->
                <div class="feature-card reveal">
                    <div class="feature-icon-wrapper icon-blue"><i class="fas fa-cubes"></i></div>
                    <h3>Gestion Multi-Stock</h3>
                    <p>Gérez plusieurs entrepôts (Warehouses) et effectuez des transferts de stock en toute simplicité.
                        Suivi des unités et catégories.</p>
                </div>

                <!-- Card 2: CRM -->
                <div class="feature-card reveal">
                    <div class="feature-icon-wrapper icon-green"><i class="fas fa-users"></i></div>
                    <h3>Clients & Fournisseurs</h3>
                    <p>Centralisez vos contacts. Historique complet des achats, ventes et soldes pour chaque client et
                        fournisseur.</p>
                </div>

                <!-- Card 3: Finance -->
                <div class="feature-card reveal">
                    <div class="feature-icon-wrapper icon-orange"><i class="fas fa-file-invoice-dollar"></i></div>
                    <h3>Facturation & Paiements</h3>
                    <p>Créez des factures pro, validez les paiements, gérez les impayés et suivez vos dépenses
                        (Expenses) quotidiennes.</p>
                </div>

                <!-- Card 4: Access -->
                <div class="feature-card reveal">
                    <div class="feature-icon-wrapper icon-pink"><i class="fas fa-user-shield"></i></div>
                    <h3>Rôles & Permissions</h3>
                    <p>Contrôle total sur qui voit quoi. Créez des rôles personnalisés pour vos employés (Admin,
                        Vendeur, Gestionnaire).</p>
                </div>

                <!-- Card 5: Inventory -->
                <div class="feature-card reveal">
                    <div class="feature-icon-wrapper icon-blue"><i class="fas fa-clipboard-check"></i></div>
                    <h3>Inventaires & Retours</h3>
                    <p>Effectuez des inventaires physiques, ajustez les stocks et gérez les retours produits clients ou
                        fournisseurs.</p>
                </div>

                <!-- Card 6: Reports -->
                <div class="feature-card reveal">
                    <div class="feature-icon-wrapper icon-green"><i class="fas fa-chart-pie"></i></div>
                    <h3>Rapports Détaillés</h3>
                    <p>Journaux comptables, rapports produits, analyse fournisseurs. Prenez des décisions basées sur la
                        data.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Section: Inventory -->
    <section class="detail-section" id="inventory">
        <div class="container">
            <div class="detail-row reveal">
                <div class="detail-content">
                    <span class="badge badge-green">Logistique Avancée</span>
                    <h3>Maîtrisez votre chaîne logistique</h3>
                    <p style="color: var(--gray); margin-bottom: 2rem;">Fini les erreurs de stock. Notre système de
                        gestion intelligent vous alerte et vous permet de suivre chaque mouvement.</p>
                    <ul class="check-list">
                        <li><i class="fas fa-check"></i> Multi-entrepôts & Transferts internes</li>
                        <li><i class="fas fa-check"></i> Alertes de stock bas configurables</li>
                        <li><i class="fas fa-check"></i> Gestion des unités et catégories produits</li>
                        <li><i class="fas fa-check"></i> Sorties de stock (Stock Out) et ajustements</li>
                    </ul>
                </div>
                <div class="detail-visual">
                    <!-- Placeholder Image (remplacez src par votre image réelle) -->
                    <img src="{{ asset('assets/img/dasboard_dymostock.png') }}" alt="Interface gestion de stock">
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Section: Finance -->
    <section class="detail-section" style="background: white;" id="finance">
        <div class="container">
            <div class="detail-row reverse reveal">
                <div class="detail-content">
                    <span class="badge badge-purple">Finance & Compta</span>
                    <h3>Facturation fluide et rapide</h3>
                    <p style="color: var(--gray); margin-bottom: 2rem;">De la création du devis à la validation du
                        paiement, tout est automatisé pour vous faire gagner du temps.</p>
                    <ul class="check-list">
                        <li><i class="fas fa-check"></i> Factures Clients & Fournisseurs (PDF)</li>
                        <li><i class="fas fa-check"></i> Suivi des impayés en temps réel</li>
                        <li><i class="fas fa-check"></i> Gestion des dépenses (Expenses)</li>
                        <li><i class="fas fa-check"></i> Validation et Workflow de paiement</li>
                    </ul>
                </div>
                <div class="detail-visual">
                    <!-- Placeholder Image (remplacez src par votre image réelle) -->
                    <img src="{{ asset('assets/img/interface_facture.png') }}" alt="Interface facturation">
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-header reveal active">
                <h2>Tarifs Transparents</h2>
                <p>Choisissez le plan adapté à la taille de votre entreprise.</p>
            </div>

            <!-- Toggle Switch Fonctionnel -->

            <div class="pricing-toggle-container">
                <span class="toggle-label">Mensuel</span>
                <label class="switch">
                    <input type="checkbox" id="pricingToggle">
                    <span class="slider round"></span>
                </label>
                <span class="toggle-label">Annuel <span class="badge badge-green">-25%</span></span>
                <div class="country-selector">
                    <label for="countrySelect">Choisissez votre pays :</label>
                    <select id="countrySelect">
                        <option value="uemoa" data-currency="FCFA">UEMOA (FCFA)</option>
                        <option value="gn" data-currency="GNF">Guinée (GNF)</option>
                    </select>
                </div>
            </div>

            <div class="pricing-cards reveal active">
                <!-- Card Starter -->
                <div class="price-card">
                    <h3>Starter</h3>
                    <!-- data-base-price = Prix mensuel sans réduction -->
                    <div class="price-val" data-base-price="15">
                        <span class="amount">15</span>k
                        <span style="font-size: 1rem; color: var(--gray);" class="currency">FCFA</span>
                    </div>
                    <p class="price-period">Par mois</p>
                    <ul class="check-list" style="text-align: left; margin: 2rem 0;">
                        <li><i class="fas fa-check"></i> 1 Entrepôt</li>
                        <li><i class="fas fa-check"></i> 2 Utilisateurs</li>
                        <li><i class="fas fa-check"></i> Facturation illimitée</li>
                        <li><i class="fas fa-check"></i> Support Email</li>
                    </ul>
                    <a href="#" class="btn btn-white js-open-modal" style="width: 100%;">Choisir Starter</a>
                </div>

                <!-- Card Business (Populaire par défaut) -->
                <div class="price-card popular">
                    <div
                        style="position: absolute; top: 0; left: 50%; transform: translate(-50%, -50%); background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                        POPULAIRE</div>
                    <h3>Business</h3>
                    <div class="price-val" data-base-price="25" style="color: var(--primary);">
                        <span class="amount">25</span>k
                        <span style="font-size: 1rem; color: var(--gray);" class="currency">FCFA</span>
                    </div>
                    <p class="price-period">Par mois</p>
                    <ul class="check-list" style="text-align: left; margin: 2rem 0;">
                        <li><i class="fas fa-check"></i> Multi-Entrepôts (3)</li>
                        <li><i class="fas fa-check"></i> 5 Utilisateurs</li>
                        <li><i class="fas fa-check"></i> Rôles personnalisés</li>
                        <li><i class="fas fa-check"></i> Support Prioritaire</li>
                        <li><i class="fas fa-check"></i> Rapports avancés</li>
                    </ul>
                    <button class="btn btn-primary js-open-modal" style="width: 100%;">Essayer Business</button>
                </div>

                <!-- Card Entreprise (Pas de changement de prix car sur devis) -->
                <div class="price-card">
                    <h3>Entreprise</h3>
                    <div class="price-val">Sur devis</div>
                    <p class="price-period">Contactez-nous</p>
                    <ul class="check-list" style="text-align: left; margin: 2rem 0;">
                        <li><i class="fas fa-check"></i> Entrepôts illimités</li>
                        <li><i class="fas fa-check"></i> Utilisateurs illimités</li>
                        <li><i class="fas fa-check"></i> API Access</li>
                        <li><i class="fas fa-check"></i> Formation dédiée</li>
                        <li><i class="fas fa-check"></i> Marque blanche</li>
                    </ul>
                    <button class="btn btn-white js-open-modal" style="width: 100%;">Contacter</button>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="cta-bg-glow"></div>
        <div class="container" style="position: relative; z-index: 2;">
            <h2 style="font-size: 2.2rem; margin-bottom: 1.5rem; line-height: 1.2;">Prêt à moderniser votre gestion ?
            </h2>
            <p
                style="font-size: 1.1rem; opacity: 0.8; margin-bottom: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Rejoignez des centaines d'entreprises qui font confiance à DYMO-STOCK pour leur croissance.
            </p>
            <button class="btn btn-primary js-open-modal"
                style="padding: 1rem 3rem; font-size: 1.1rem; background: white; color: var(--primary);">
                Créer un compte gratuit
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col-brand">
                    <a class="logo" href="/">
                        <div class="sidebar-brand-icon">
                            <img src="{{ asset('assets/img/logo/favicon.png') }}" alt="DYMO STOCK" class="logo">
                        </div>
                        <span>DYMO-STOCK</span>
                    </a>

                    <p style="color: var(--gray); font-size: 0.9rem; max-width: 300px; margin-top: 5px;">
                        La solution SaaS de référence pour la gestion commerciale en Afrique de l'Ouest.
                    </p>
                </div>
                <div>
                    <h4 style="margin-bottom: 1rem;">Produit</h4>
                    <ul style="color: var(--gray); font-size: 0.9rem;">
                        <li><a href="#fonctionnalites">Fonctionnalités</a></li>
                        <li><a href="#pricing">Tarifs</a></li>
                        <li><a href="#">Mises à jour</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="margin-bottom: 1rem;">Support</h4>
                    <ul style="color: var(--gray); font-size: 0.9rem;">
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">API</a></li>
                        <li><a href="#" class="js-open-modal">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="margin-bottom: 1rem;">Légal</h4>
                    <ul style="color: var(--gray); font-size: 0.9rem; margin-bottom: 1rem;">
                        <li><a href="#">Confidentialité</a></li>
                        <li><a href="#">Conditions</a></li>
                    </ul>
                    <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                        <a href="https://www.facebook.com/profile.php?id=61584711315209" target="_blank"
                            style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;
                   background: #1877F2; color: white; padding: 0.5rem 1rem; border-radius: 9999px;
                   font-weight: 600; text-decoration: none; transition: transform 0.2s;">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://wa.me/221788364007?text=Je%20veux%20profiter%20de%20votre%20offre%20gratuite"
                            target="_blank"
                            style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;
                   background: #25D366; color: white; padding: 0.5rem 1rem; border-radius: 9999px;
                   font-weight: 600; text-decoration: none; transition: transform 0.2s;">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>

            </div>
            <div
                style="text-align: center; border-top: 1px solid #F1F5F9; padding-top: 2rem; color: var(--gray); font-size: 0.9rem;">
                &copy; <span id="year"></span> Dymo Technologies. Tous droits réservés.
            </div>
        </div>
    </footer>

    <!-- CONTACT MODAL STRUCTURE -->
    <div class="contact-modal" id="contactModal">
        <div class="contact-modal-content">
            <span class="contact-close">&times;</span>

            <h3>Contactez-nous</h3>
            <p style="color: var(--gray); margin-bottom: 1.5rem;">
                Pour activer votre essai gratuit, contactez notre équipe.
            </p>

            <ul class="contact-list">
                <li><i class="fas fa-phone"></i> +221 78 836 40 07</li>
                <li><i class="fas fa-envelope"></i>contact@dymotechnologie.com</li>
                <li><i class="fas fa-location-dot"></i> Dakar, Sénégal</li>
            </ul>
            <a href="https://wa.me/221788364007?text=Je%20veux%20profiter%20de%20votre%20offre%20gratuite"
                target="_blank" class="btn btn-primary" style="width:100%;">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>

        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const toggle = document.getElementById('pricingToggle');
            const countrySelect = document.getElementById('countrySelect');
            const priceCards = document.querySelectorAll('.price-card');

            const pricesByCountry = {
                uemoa: {
                    starter: 15,
                    business: 25
                },
                gn: {
                    starter: 255,
                    business: 425
                }
            };

            function updatePrices() {
                const isAnnual = toggle.checked;
                const country = countrySelect.value;

                priceCards.forEach(card => {
                    const plan = card.querySelector('h3').textContent.toLowerCase();
                    const priceVal = card.querySelector('.price-val .amount');
                    const currencySpan = card.querySelector('.price-val .currency');

                    if (!priceVal) return;

                    let basePrice;
                    if (plan.includes('starter')) basePrice = pricesByCountry[country].starter;
                    else if (plan.includes('business')) basePrice = pricesByCountry[country].business;
                    else return;

                    let finalPrice = isAnnual ? Math.round(basePrice * 12 * 0.75) : basePrice;
                    priceVal.textContent = finalPrice;
                    currencySpan.textContent = countrySelect.selectedOptions[0].dataset.currency;

                    // Effet visuel
                    priceVal.style.transform = "scale(1.2)";
                    setTimeout(() => priceVal.style.transform = "scale(1)", 200);
                });

                document.body.classList.toggle('pricing-annual', isAnnual);
            }

            toggle.addEventListener('change', updatePrices);
            countrySelect.addEventListener('change', updatePrices);

            // Initialisation
            updatePrices();

            // --- DYNAMIC YEAR ---
            document.getElementById('year').textContent = new Date().getFullYear();

            // --- MOBILE MENU ---
            const mobileToggle = document.getElementById('mobileToggle');
            const navLinks = document.getElementById('navLinks');
            const menuOverlay = document.getElementById('menuOverlay');
            const navItems = document.querySelectorAll('.nav-link');

            function toggleMenu() {
                navLinks.classList.toggle('active');
                menuOverlay.classList.toggle('active');
                document.body.classList.toggle('no-scroll');
            }

            if (mobileToggle) mobileToggle.addEventListener('click', toggleMenu);
            if (menuOverlay) menuOverlay.addEventListener('click', toggleMenu);

            // Close menu when clicking a nav link
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    if (navLinks.classList.contains('active')) {
                        toggleMenu();
                    }
                });
            });

            // --- HEADER SCROLL EFFECT ---
            window.addEventListener('scroll', () => {
                const header = document.querySelector('.header');
                if (window.scrollY > 50) {
                    header.style.background = 'rgba(255, 255, 255, 0.95)';
                    header.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
                } else {
                    header.style.background = 'rgba(255, 255, 255, 0.8)';
                    header.style.boxShadow = 'none';
                }
            });

            // --- SCROLL ANIMATION (REVEAL) ---
            const observerOptions = {
                threshold: 0.1,
                rootMargin: "0px 0px -50px 0px"
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

            // --- CONTACT MODAL ---
            const contactModal = document.getElementById('contactModal');
            const modalTriggers = document.querySelectorAll('.js-open-modal');
            const modalClose = document.querySelector('.contact-close');

            const openModal = (e) => {
                e.preventDefault();
                contactModal.classList.add('active');
                // Close mobile menu if open
                if (navLinks.classList.contains('active')) {
                    toggleMenu();
                }
                // Ensure body lock is maintained
                document.body.classList.add('no-scroll');
            };

            const closeModal = () => {
                contactModal.classList.remove('active');
                document.body.classList.remove('no-scroll');
            };

            modalTriggers.forEach(btn => btn.addEventListener('click', openModal));

            if (modalClose) modalClose.addEventListener('click', closeModal);

            // Close modal when clicking outside content
            window.addEventListener('click', (e) => {
                if (e.target === contactModal) {
                    closeModal();
                }
            });
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });

        });
    </script>

    <noscript>
        <style>
            body {
                display: none;
            }
        </style>
    </noscript>

</body>

</html>
