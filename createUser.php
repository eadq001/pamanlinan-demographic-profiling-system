<?php
// Use $conn if defined, otherwise create it
if (!isset($conn)) {
    $conn = new mysqli("localhost", "root", "", "pamanlinan_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $message = "Username and password are required.";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE user_name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($exists);
        $stmt->fetch();
        $stmt->close();

        if ($exists > 0) {
            $message = "Username already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_ARGON2ID);
            $stmt = $conn->prepare("INSERT INTO users (user_name, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);
            if ($stmt->execute()) {
                $message = "User created successfully!";
            } else {
                $message = "Error creating user.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f8fb; }
        .signup-container {
            max-width: 350px;
            margin: 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 32px 28px;
        }
        h2 { text-align: center; color: #057570; }
        form { display: flex; flex-direction: column; gap: 16px; }
        input[type="text"], input[type="password"] {
            padding: 9px 12px;
            border: 1px solid #bbb;
            border-radius: 5px;
            font-size: 1em;
        }
        button {
            background: #057570;
            color: #fff;
            border: none;
            padding: 10px 0;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #049b8a; }
        .message { text-align: center; margin-bottom: 10px; color: #c00; }
        .success { color: #057570; }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <?php if ($message): ?>
            <div class="message<?= $message === "User created successfully!" ? ' success' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="text" name="username" placeholder="Username" required maxlength="50">
            <input type="password" name="password" placeholder="Password" required minlength="4">
            <button type="submit">Create Account</button>
        </form>
    </div>
</body>
</html>