<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f83e5">
    <title>Create account · Virtual Phone</title>
    <style>
        * { box-sizing: border-box; }
        body { min-height: 100vh; display: grid; place-items: center; margin: 0; padding: 20px; background: #f3f6fc; color: #172033; font: 15px/1.5 "Segoe UI", Arial, sans-serif; }
        main { width: min(420px, 100%); padding: 30px; border: 1px solid #e1e7f0; border-radius: 20px; background: #fff; box-shadow: 0 18px 55px #20345a1a; }
        h1 { margin: 0 0 6px; font-size: 25px; }
        p { margin: 0 0 22px; color: #667085; }
        label { display: block; margin: 15px 0 6px; font-size: 13px; font-weight: 700; }
        input { width: 100%; padding: 12px 13px; border: 1px solid #cbd4e2; border-radius: 10px; font: inherit; }
        input:focus { outline: 3px solid #4f83e533; border-color: #4f83e5; }
        button { width: 100%; margin-top: 20px; padding: 13px; border: 0; border-radius: 10px; background: #4f63d8; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        button:disabled { opacity: .65; cursor: wait; }
        #message { min-height: 22px; margin: 14px 0 0; font-size: 13px; }
        #message.error { color: #b42318; }
        #message.success { color: #067647; }
        a { display: block; margin-top: 17px; color: #4055c8; text-align: center; text-decoration: none; }
        .hint { margin: 12px 0 0; font-size: 12px; }
    </style>
</head>
<body>
    <main>
        <h1>Create your account</h1>
        <p>Choose a username and password for your Virtual Phone.</p>
        <form id="registerForm">
            <label for="username">Username</label>
            <input id="username" name="username" autocomplete="username" minlength="3" maxlength="50" required>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="6" maxlength="72" required>
            <label for="confirmPassword">Confirm password</label>
            <input id="confirmPassword" name="confirm_password" type="password" autocomplete="new-password" minlength="6" maxlength="72" required>
            <p class="hint">After creating your account, link a verified Google account in Settings to enable password recovery.</p>
            <button id="submitButton" type="submit">Create account</button>
        </form>
        <p id="message" role="status" aria-live="polite"></p>
        <a href="/virtual-phone/user/user_phone_dashboard.html">Back to sign in</a>
    </main>
    <script>
        const form = document.getElementById('registerForm');
        const button = document.getElementById('submitButton');
        const message = document.getElementById('message');

        form.addEventListener('submit', async event => {
            event.preventDefault();
            message.textContent = '';
            message.className = '';
            button.disabled = true;
            try {
                const response = await fetch('/virtual-phone/user/api/register_api.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        username: form.elements.username.value.trim(),
                        password: form.elements.password.value,
                        confirm_password: form.elements.confirm_password.value
                    })
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'The account could not be created.');
                message.textContent = 'Account created. Opening your Virtual Phone…';
                message.className = 'success';
                window.location.assign('/virtual-phone/user/user_phone_dashboard.html');
            } catch (error) {
                message.textContent = error.message || 'A network or server error occurred.';
                message.className = 'error';
            } finally {
                button.disabled = false;
            }
        });
    </script>
</body>
</html>
