@extends('layouts.app', [
    'title' => 'test',
    'description' => 'testing javascript encryption/decryption'
])

@section('content')

    <form id="testForm">
        <input type="password" id="password">
        <input type="text" id="plain">
        <button type="button" id="encryptionBtn">Encrypt</button>

        <p id="encrypted"></p>

        <button type="button" id="decryptionBtn">Decrypt</button>

        <p id="decrypted"></p>
    </form>
    <script>
        const salt = "{{ auth()->user()->salt }}";
        const key = "{{ auth()->user()->key }}";
        const iv = "{{ auth()->user()->iv }}";

        const encryptionBtn = document.querySelector('#encryptionBtn');
        const decryptionBtn = document.querySelector('#decryptionBtn');
        const passwordInput = document.querySelector('#password');
        const plaintextInput = document.querySelector('#plain');
        const encryptedText = document.querySelector('#encrypted');
        const decryptedText = document.querySelector('#decrypted');

        encryptionBtn.addEventListener("click", async function () {
            const saltBytes = new Uint8Array(base64ToArrayBuffer(salt));

            const encryptedKeyBytes = base64ToArrayBuffer(key);

            // Get Password Based Key
            const passwordBasedKey = await getPasswordBasedKey(passwordInput.value, saltBytes);

            const ivBytes = new Uint8Array(base64ToArrayBuffer(iv));

            const decryptedKeyBytesBase64 = await decryptMessage(passwordBasedKey, encryptedKeyBytes, ivBytes);

             const secretKey = await bytesToAESKey(decryptedKeyBytesBase64);
             console.log({secretKey})

            encryptedText.textContent = bytesToBase64(await encryptMessage(plaintextInput.value, secretKey, ivBytes));

            decryptedText.textContent = await decryptMessage(secretKey, base64ToArrayBuffer(encryptedText.textContent), ivBytes)
        })

        function base64ToArrayBuffer(base64) {
            var binary_string = window.atob(base64);
            var len = binary_string.length;
            var bytes = new Uint8Array(len);
            for (var i = 0; i < len; i++) {
                bytes[i] = binary_string.charCodeAt(i);
            }
            return bytes.buffer;
        }

        async function decryptMessage(key, ciphertext, iv) {
            // The iv value is the same as that used for encryption
            let some = await window.crypto.subtle.decrypt({name: "AES-GCM", iv}, key, ciphertext);
            let decoder = new TextDecoder();
            return decoder.decode(some)
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

        function encryptMessage(plaintext, key, iv) {
            const enc = new TextEncoder();
            const encoded = enc.encode(plaintext);

            // iv will be needed for decryption

            return window.crypto.subtle.encrypt(
                {name: "AES-GCM", iv: iv},
                key,
                encoded
            );
        }

        async function bytesToAESKey(decryptedKeyBytes) {
            const ssf = new Uint8Array(base64ToArrayBuffer(decryptedKeyBytes));

           return window.crypto.subtle.importKey(
                "raw",
               ssf,
                // new Uint8Array(base64ToArrayBuffer(decryptedKeyBytes)) ,
                "AES-GCM",
                true,
                ["encrypt", "decrypt"]
            );
        }

    </script>

@endsection
