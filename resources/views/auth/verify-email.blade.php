<x-guest-layout>
    <div class="auth-heading">
        <div class="auth-heading__eyebrow">Vérification email</div>
        <h1 class="auth-heading__title">Confirmez votre adresse email</h1>
        <p class="auth-heading__copy">Un lien de validation a été envoyé. Vérifiez votre boîte mail pour activer le compte.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success border-0 shadow-sm">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Renvoyer le lien
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn btn-link text-decoration-none font-semibold text-slate-600">
                Se déconnecter
            </button>
        </form>
    </div>
</x-guest-layout>
