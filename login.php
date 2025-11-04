<?php
session_start();
require_once "db_connect.php";

// ✅ Redirect if already logged in
if (isset($_SESSION["username"])) {
  header("Location: index.php");
  exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);

  $query = "SELECT * FROM users WHERE username = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

 if ($result->num_rows > 0) {
  $user = $result->fetch_assoc();

  // Direct comparison since password is stored in plain text
  if ($password === $user["password"]) {
    $_SESSION["username"] = $username;
    header("Location: index.php");
    exit;
  } else {
    $error = "Invalid password.";
  }
} else {
  $error = "No such user found.";
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BlueMetrics | Login</title>
  <style> 
    body { 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
      background: linear-gradient(135deg, #0284c7, #0ea5e9); 
      display: flex; justify-content: center; align-items: center; 
      height: 100vh; margin: 0; color: #333; 
    } 
    .login-container { 
      background: white; 
      padding: 2rem; 
      border-radius: 1rem; 
      width: 100%; max-width: 350px; 
      box-shadow: 0 8px 20px rgba(0,0,0,0.15); 
      text-align: center; 
      animation: fadeIn 0.6s ease; 
    } 
    @keyframes fadeIn { 
      from { opacity: 0; transform: translateY(-10px); } 
      to { opacity: 1; transform: translateY(0); } 
    } 
    h2 { color: #0369a1; margin-bottom: 1.5rem; } 
    input[type="text"], input[type="password"] { 
      width: 100%; padding: 10px; margin-bottom: 1rem; 
      border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; 
    } 
    .password-field { position: relative; } 
    .toggle-password { 
      position: absolute; right: 10px; top: 9px; 
      cursor: pointer; color: #555; font-size: 1rem; 
    } 
    .form-options { 
      display: flex; justify-content: space-between; 
      align-items: center; margin-bottom: 1rem; font-size: 0.9rem; 
    } 
    .form-options a { color: #0284c7; text-decoration: none; } 
    button { 
      background: #0284c7; color: white; border: none; 
      padding: 10px; width: 100%; border-radius: 6px; 
      font-size: 1rem; cursor: pointer; transition: background 0.3s ease; 
    } 
    button:hover { background: #0369a1; } 
    .error { 
      color: red; font-size: 0.9rem; margin-bottom: 1rem; 
      display: <?= $error ? 'block' : 'none' ?>; 
    } 
    .footer-text { margin-top: 1rem; font-size: 0.8rem; color: #666; } 
  </style>
</head>
<body>
  <div class="login-container">
    <h2>BlueMetrics Login</h2>
    <p class="text-sm text-gray-500 mb-3">Sign in to access the monitoring system</p>

    <div class="error"><?= htmlspecialchars($error) ?></div>

    <form method="POST" action="">
      <input type="text" name="username" placeholder="Username" required />

      <div class="password-field">
        <input type="password" name="password" id="password" placeholder="Password" required />
        <span class="toggle-password" onclick="togglePassword()">👁️</span>
      </div>

      <div class="form-options">
        <label><input type="checkbox" /> Remember me</label>
        <a href="#">Forgot Password?</a>
      </div>

      <button type="submit">Login</button>
    </form>

    <p class="footer-text">© 2025 BlueMetrics Systems</p>
  </div>

  <script>
    function togglePassword() {
      const password = document.getElementById("password");
      password.type = password.type === "password" ? "text" : "password";
    }
  </script>
</body>
</html>
