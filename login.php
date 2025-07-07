<?php
require_once 'config.php';

if (isLoggedIn()) {
    // Already logged in — redirect by role
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $user = loginUser($username, $password);
        if ($user) {
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login – NeuroAid</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    html { scroll-behavior: smooth; }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f6f9f9;
    }
    header {
      background-color: white;
      border-bottom: 1px solid #ccc;
    }
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 15px 30px 5px 30px;
    }
    .top-bar-left {
      display: flex;
      align-items: center;
    }
    .top-bar-left img.logo {
      height: 40px;
      margin-right: 10px;
    }
    .top-bar-left h1 {
      color: #1D4C43;
      font-size: 32px;
      margin: 0;
      font-weight: 800;
    }
    .contact {
      font-size: 14px;
      color: #1D4C43;
      text-align: right;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
    }
    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 30px 20px 30px;
      border-top: 1px solid #eee;
    }
    .nav-links {
      display: flex;
      gap: 80px;
      margin-left: 100px;
    }
    nav a {
      text-decoration: none;
      color: #1D4C43;
      font-weight: bold;
      font-size: 17px;
      transition: color 0.3s, background-color 0.3s;
      padding: 6px 10px;
      border-radius: 5px;
    }
    nav a:hover {
      color: white;
      background-color: #1D4C43;
    }
    .btn-login {
      font-weight: bold;
      font-size: 17px;
      border: 2px solid #1D4C43;
      background-color: white;
      color: #1D4C43;
      border-radius: 5px;
      text-decoration: none;
      padding: 6px 10px;
    }
    .btn-login:hover {
      background-color: #1D4C43;
      color: white;
    }
    .nav-login {
      display: flex;
      margin-right: 60px;
    }

    .login-container {
      max-width: 500px;
      margin: 2rem auto;
      padding: 2rem;
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .circle-icon {
      display: flex;
      justify-content: center;
      margin-top: -40px;
    }
    .circle-icon div {
      width: 50px;
      height: 50px;
      background-color: #1D4C43;
      border-radius: 50%;
    }
    .login-container h2 {
      text-align: center;
      color: #1D4C43;
      margin-bottom: 1.5rem;
      font-size: 24px;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    input {
      width: 95%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      transition: border 0.3s;
    }
    input:focus {
      border-color: #1D4C43;
      outline: none;
      box-shadow: 0 0 0 3px rgba(29, 76, 67, 0.2);
    }

    .password-toggle {
      position: relative;
    }
    .password-toggle i {
      position: absolute;
      top: 12px;
      right: 15px;
      cursor: pointer;
      color: #888;
    }

    .btn {
      background: #1D4C43;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      width: 100%;
    }
    .btn:hover {
      background: #14332c;
    }

    .form-footer {
      text-align: center;
      margin-top: 1.5rem;
      color: #666;
      font-size: 14px;
    }
    .form-footer a {
      color: #1D4C43;
      text-decoration: none;
    }
    .form-footer a:hover {
      text-decoration: underline;
    }

    .forgot {
      text-align: right;
      margin-top: -10px;
      margin-bottom: 15px;
    }
    .forgot a {
      font-size: 14px;
      color: #1D4C43;
      text-decoration: none;
    }

    .alert {
      padding: 15px;
      margin-bottom: 1.5rem;
      border-radius: 5px;
      font-weight: 500;
      text-align: center;
    }
    .error {
      background-color: #fdecea;
      color: #e74c3c;
      border: 1px solid #f5c2c7;
    }

    footer {
      background-color: #1D4C43;
      padding: 15px 20px;
      color: white;
      text-align: center;
      font-size: 14px;
      margin-top: 60px;
    }
    footer a {
      color: white;
      text-decoration: none;
      margin: 0 15px;
    }
    footer a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .login-container {
        padding: 1.5rem;
        margin: 1rem;
      }
      .nav-links {
        gap: 30px;
        margin-left: 30px;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="top-bar">
    <div class="top-bar-left">
      <img src="image/logo.png" alt="Logo" class="logo">
      <h1><span style="color: #1D4C43; font-weight: 600;">Neuro</span><span style="color: black; font-weight: 600;">Aid</span></h1>
    </div>
    <div class="contact">
      <span style="display: flex; align-items: center;"><img src="image/phone-icon.png" alt="Phone" style="height: 16px; margin-right: 6px;">+1 (800) 777-NEUR (6387)</span><br>
      <small>Call us for any question</small>
    </div>
  </div>
  <nav>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="about_us.php">About Us</a>
      <a href="services.php">Services</a>
      <a href="blogs.php">Blogs</a>
      <a href="team.php">Team</a>
    </div>
    <div class="nav-login">
      <a href="login.php" class="btn-login">Login</a>
    </div>
  </nav>
</header>

<div class="login-container">
    <div class="circle-icon"><div></div></div>
    <h2>Sign in</h2>

    <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <input type="text" id="username" name="username" placeholder="Email or mobile phone number" required>
        </div>
        <div class="form-group password-toggle">
            <input type="password" id="password" name="password" placeholder="Your password" required>
            <i class="fas fa-eye-slash" id="togglePassword"></i>
        </div>

        <div class="forgot"><a href="#">Forgot your password</a></div>

        <button type="submit" class="btn">Login</button>

        <div class="form-footer">
            <p>By continuing, you agree to the <a href="#">Terms of use</a> and <a href="#">Privacy Policy</a>.</p>
            <p><a href="register.php">Create an Account</a></p>
        </div>
    </form>
</div>

<footer>
  <a href="#">Help Center</a>
  <a href="#">Terms of Service</a>
  <a href="#">Privacy Policy</a>
  <span>@NeuroAid</span>
</footer>

<script>
document.getElementById("togglePassword").addEventListener("click", function () {
    const password = document.getElementById("password");
    const type = password.type === "password" ? "text" : "password";
    password.type = type;
    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
});
</script>

</body>
</html>
