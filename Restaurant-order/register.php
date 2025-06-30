<?php
session_start();
// Redirect if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

// Get error message if exists and clear it
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="Assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f8f8f8, #e0f7fa);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h3>Register</h3>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="register_process.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required pattern="[A-Za-z\s]{2,50}">
                <div class="invalid-feedback">Please enter a valid name (2-50 characters, letters only)</div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <div class="invalid-feedback">Please enter a valid email address</div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8"
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                <div class="invalid-feedback">Password must be at least 8 characters long and include uppercase, lowercase, and numbers</div>
            </div>

            <button type="submit">Register</button>
        </form>

        <div class="text-center mt-3">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script>
        // Enhanced form validation
        document.querySelector("form").addEventListener("submit", function(e) {
            const name = document.getElementById("name");
            const email = document.getElementById("email");
            const password = document.getElementById("password");
            let valid = true;

            // Name validation
            if (!name.value.match(/^[A-Za-z\s]{2,50}$/)) {
                name.nextElementSibling.style.display = "block";
                valid = false;
            } else {
                name.nextElementSibling.style.display = "none";
            }

            // Email validation
            if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                email.nextElementSibling.style.display = "block";
                valid = false;
            } else {
                email.nextElementSibling.style.display = "none";
            }

            // Password validation
            if (!password.value.match(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/)) {
                password.nextElementSibling.style.display = "block";
                valid = false;
            } else {
                password.nextElementSibling.style.display = "none";
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>