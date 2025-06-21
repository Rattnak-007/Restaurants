<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Restaurant Order System</title>
  <link rel="stylesheet" href="Assets/css/style.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
  </style>
</head>

<body>
  <div class="login-container">
    <h3><i class="fas fa-user-lock"></i> Login</h3>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger">
        <?php echo htmlspecialchars($_GET['error']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success">
        Registration successful! Please login.
      </div>
    <?php endif; ?>

    <form action="login_process.php" method="POST" novalidate>
      <div class="form-group">
        <label for="email"><i class="fas fa-envelope"></i> Email</label>
        <input type="email" id="email" name="email" required />
        <div class="invalid-feedback">Please enter a valid email address</div>
      </div>

      <div class="form-group">
        <label for="password"><i class="fas fa-key"></i> Password</label>
        <input type="password" id="password" name="password" required />
        <div class="invalid-feedback">Please enter your password</div>
      </div>

      <div class="form-check">
        <input type="checkbox" id="remember_me" name="remember_me" />
        <label for="remember_me"><i class="fas fa-cookie"></i> Remember me</label>
      </div>
      <button type="submit"><i class="fas fa-sign-in-alt"></i> Login</button>
    </form>

    <div class="text-center mt-3">
      <p>
        <i class="fas fa-user-plus"></i> Don't have an account?
        <a href="register.php">Register here</a>
      </p>
    </div>
  </div>

  <script>
    // Simple client-side validation
    document.querySelector("form").addEventListener("submit", function(e) {
      const email = document.getElementById("email");
      const password = document.getElementById("password");
      let valid = true;

      if (!email.checkValidity()) {
        email.nextElementSibling.style.display = "block";
        valid = false;
      } else {
        email.nextElementSibling.style.display = "none";
      }

      if (!password.checkValidity()) {
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