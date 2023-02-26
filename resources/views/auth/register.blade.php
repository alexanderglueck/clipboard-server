@extends('layouts.app', [
    'title' => 'Register',
    'description' => 'Sign-up and start storing your memories with ' . config('app.app.name') . '.'
])

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Register') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('register') }}" id="registerForm">
                            @csrf

                            <input type="hidden" name="salt" value="" id="salt"/>
                            <input type="hidden" name="key" value="" id="key"/>
                            <input type="hidden" name="iv" value="" id="iv"/>

                            <div class="row mb-3">
                                <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                                <div class="col-md-6">
                                    <input id="name" type="text"
                                           class="form-control @error('name') is-invalid @enderror" name="name"
                                           value="{{ old('name') }}" required autocomplete="name" autofocus>

                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="email"
                                       class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email"
                                           class="form-control @error('email') is-invalid @enderror" name="email"
                                           value="{{ old('email') }}" required autocomplete="email">

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="password"
                                       class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password"
                                           class="form-control @error('password') is-invalid @enderror" name="password"
                                           required autocomplete="new-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="password-confirm"
                                       class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button id="registerBtn" type="submit" class="btn btn-primary" disabled>
                                        {{ __('Register') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const registerBtn = document.querySelector('#registerBtn');
            const registerForm = document.querySelector('#registerForm')
            const passwordInput = document.querySelector('#password')
            const passwordConfirmInput = document.querySelector('#password-confirm')
            const saltInput = document.querySelector('#salt')
            const keyInput = document.querySelector('#key')
            const ivInput = document.querySelector('#iv')

            registerForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                registerBtn.disabled = true;

                // Get salt
                const cryptoSalt = await getSalt();

                saltInput.value = bytesToBase64(cryptoSalt)

                // Get Password Based Key
                const passwordBasedKey = await getPasswordBasedKey(passwordInput.value, cryptoSalt);

                // Get IV
                const iv = await getIV();

                ivInput.value = bytesToBase64(iv);

                // Get Secret Key
                const secretKey = await getSecretKey();
                const secretKeyBytes = await window.crypto.subtle.exportKey("raw", secretKey);
                const encryptedSecretKey = await encrypt(bytesToBase64(secretKeyBytes), passwordBasedKey, iv);

                keyInput.value = bytesToBase64(encryptedSecretKey)

                // Hash password
                const passwordHash = await digestMessage(passwordInput.value);

                passwordInput.value = bytesToBase64(passwordHash)

                const passwordConfirmHash = await digestMessage(passwordConfirmInput.value);

                passwordConfirmInput.value = bytesToBase64(passwordConfirmHash)

                submitForm();
            })

            registerBtn.disabled = false;

            function submitForm() {
                console.log("done");
                // registerForm.submit();
                registerBtn.disabled = false;
            }

            async function digestMessage(message) {
                const encoder = new TextEncoder();
                const data = encoder.encode(message);
                return window.crypto.subtle.digest('SHA-256', data);
            }

            function bytesToBase64(byteArray) {
                let binary = '';
                let bytes = new Uint8Array(byteArray);
                let len = bytes.byteLength;
                for (let i = 0; i < len; i++) {
                    binary += String.fromCharCode(bytes[i]);
                }
                return window.btoa(binary);
            }

            /*
            Get some key material to use as input to the deriveKey method.
            The key material is a password supplied by the user.
            */
            function getKeyMaterial(password) {
                const enc = new TextEncoder();
                return window.crypto.subtle.importKey(
                    "raw",
                    enc.encode(password),
                    "PBKDF2",
                    false,
                    ["deriveBits", "deriveKey"],
                );
            }

            async function getPasswordBasedKey(plaintext, salt) {
                const keyMaterial = await getKeyMaterial(plaintext);

                return await window.crypto.subtle.deriveKey(
                    {
                        name: "PBKDF2",
                        salt,
                        iterations: 100000,
                        hash: "SHA-256",
                    },
                    keyMaterial,
                    {"name": "AES-GCM", "length": 256},
                    true,
                    ["encrypt", "decrypt"],
                );
            }

            async function getSalt() {
                return window.crypto.getRandomValues(new Uint8Array(16))
            }

            async function getSecretKey() {
                return window.crypto.subtle.generateKey(
                    {
                        name: "AES-GCM",
                        length: 256
                    },
                    true,
                    ["encrypt", "decrypt"]
                );
            }

            async function encrypt(plaintext, key, iv) {
                const enc = new TextEncoder();
                const encodedSecret = enc.encode(plaintext);
                console.log({ encodedSecret })
                console.log({ plaintext })

                return window.crypto.subtle.encrypt(
                    {name: "AES-GCM", iv: iv},
                    key,
                    encodedSecret
                );
            }

            async function getIV() {
                return window.crypto.getRandomValues(new Uint8Array(12));
            }
        })
    </script>
@endsection
