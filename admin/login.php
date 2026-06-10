<?php
// admin/login.php
require_once '../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Identifiants incorrects.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — Club DanDana</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#0d0d0d;color:#eee;font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}
  .box{background:#1a1a1a;border:1px solid #333;border-radius:10px;padding:36px;width:100%;max-width:380px}
  h1{color:#e5a800;text-align:center;margin-bottom:24px}
  label{display:block;margin-bottom:4px;color:#aaa;font-size:13px}
  input{width:100%;padding:10px;background:#2a2a2a;border:1px solid #444;color:#eee;border-radius:6px;margin-bottom:14px}
  .btn{width:100%;padding:12px;background:#e5a800;color:#111;border:none;border-radius:6px;font-weight:bold;cursor:pointer;font-size:15px}
  .btn:hover{background:#ffbf00}
  .error{background:#3a1a1a;border:1px solid #c0392b;color:#e74c3c;padding:10px;border-radius:6px;margin-bottom:14px;font-size:13px}
</style>
</head>
<body>
<div class="box">
  <h1>🎵 DanDana Admin</h1>
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <label>Nom d'utilisateur</label>
    <input type="text" name="username" autocomplete="username" required>
    <label>Mot de passe</label>
    <input type="password" name="password" autocomplete="current-password" required>
    <button type="submit" class="btn">Se connecter</button>
  </form>
</div>
</body>
</html>
