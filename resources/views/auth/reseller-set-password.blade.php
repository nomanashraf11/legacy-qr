<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
        </x-slot>

        <div class="flex items-center justify-center pb-4 pt-2">
            <img width="150" height="150" src="{{ asset('images/logo/1234.png') }}" alt="Living Legacy">
        </div>
        <h2 class="text-center text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Set Your Password</h2>
        <p class="text-center text-sm text-gray-600 dark:text-gray-400 mb-6">
            Hello {{ $name }}, choose a password to access your Reseller Portal.
        </p>

        <form method="POST" action="{{ route('reseller.setPassword') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autofocus autocomplete="new-password" placeholder="Enter your password"
                    minlength="8" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password"
                    placeholder="Confirm your password" />
            </div>

            <x-validation-errors class="mb-4 mt-4" />

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-6">
                <x-button>
                    {{ __('Set Password & Login') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
