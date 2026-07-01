<x-guest-layout>
    <div class="auth-heading">
        <div class="auth-heading__eyebrow">Sécurisation</div>
        <h1 class="auth-heading__title">Choisir un nouveau mot de passe</h1>
        <p class="auth-heading__copy">Définissez un mot de passe solide pour reprendre l’accès à votre espace.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="auth-form">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="form-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap mt-4">
            <a class="text-sm font-semibold text-blue-600 hover:text-blue-700" href="{{ route('login') }}">Retour à la connexion</a>
            <x-primary-button>
                Réinitialiser
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
