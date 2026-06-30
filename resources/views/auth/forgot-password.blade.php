<x-guest-layout>
    <div class="auth-heading">
        <div class="auth-heading__eyebrow">Réinitialisation</div>
        <h1 class="auth-heading__title">Mot de passe oublié</h1>
        <p class="auth-heading__copy">Saisissez votre email et nous vous enverrons un lien de réinitialisation sécurisé.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap mt-4">
            <a class="text-sm font-semibold text-blue-600 hover:text-blue-700" href="{{ route('login') }}">Retour à la connexion</a>
            <x-primary-button>
                Envoyer le lien
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
