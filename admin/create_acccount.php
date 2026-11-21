<?php
// --- BACKEND PHP LOGIC ---
// If the request is a POST submission, handle the database insertion and return JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Dynamically include db.php using the project root path
    // Option A: Relative to current file directory (e.g., if this file is in /admin/)
    require_once __DIR__ . '/../db.php';

    // Option B (Alternative): Absolute path from XAMPP root folder
    // require_once $_SERVER['DOCUMENT_ROOT'] . '/virtual-phone/db.php';

    // Get JSON input from the JavaScript fetch request
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit;
    }

    try {
        // Check if the username already exists
        $stmt = $pdo->prepare("SELECT id FROM vphone_admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username already exists.']);
            exit;
        }

        // Securely hash the password (compatible with your phpMyAdmin database setup)
        $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $created_at = date('Y-m-d H:i:s');

        // Insert into vphone_admins table
        $insertStmt = $pdo->prepare("INSERT INTO vphone_admins (username, password_hash, created_at) VALUES (?, ?, ?)");
        $insertStmt->execute([$username, $password_hash, $created_at]);

        echo json_encode(['success' => true, 'message' => 'Admin inserted successfully!']);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred while inserting data: ' . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin - VPhone</title>
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1e293b;
            --border-color: #cbd5e1;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            text-align: center;
            color: #0f172a;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: var(--primary-hover);
        }

        #response-message {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .success { color: #16a34a; }
        .error { color: #dc2626; }
    </style>
</head>
<body>

    <div class="card">
        <h2>Add VPhone Admin</h2>
        <form id="adminForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" id="submitBtn">Insert Admin</button>
        </form>
        <div id="response-message"></div>
    </div>

    <script>
        document.getElementById('adminForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('response-message');
            const submitBtn = document.getElementById('submitBtn');

            messageDiv.textContent = 'Processing...';
            messageDiv.className = '';
            submitBtn.disabled = true;

            try {
                // Submitting to the current file ''
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });

                const result = await response.json();

                if (result.success) {
                    messageDiv.textContent = result.message;
                    messageDiv.className = 'success';
                    document.getElementById('adminForm').reset();
                } else {
                    messageDiv.textContent = result.message;
                    messageDiv.className = 'error';
                }
            } catch (error) {
                messageDiv.textContent = 'A network or server error occurred.';
                messageDiv.className = 'error';
            } finally {
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>