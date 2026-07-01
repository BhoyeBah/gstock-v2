<x-guest-layout>
    <div class="auth-heading">
        <div class="auth-heading__eyebrow">Vérification</div>
        <h1 class="auth-heading__title">Confirmer votre mot de passe</h1>
        <p class="auth-heading__copy">Cette étape protège les actions sensibles du compte.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
        @csrf

        <!-- Password -->
        <div class="form-group">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="d-flex justify-content-end mt-4">
            <x-primary-button>
                Confirmer
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
