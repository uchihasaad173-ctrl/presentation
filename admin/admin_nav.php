<?php
// admin/admin_nav.php
require_once '../config/database.php';
$pdo_nav = getPDO();
$unread = (int)$pdo_nav->query("SELECT COUNT(*) FROM notifications WHERE lu = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — Club DanDana</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:Arial,sans-serif;background:#0d0d0d;color:#eee;min-height:100vh}
  nav{background:#111;padding:12px 20px;display:flex;gap:4px;align-items:center;flex-wrap:wrap;border-bottom:2px solid #e5a800}
  nav .brand{color:#e5a800;font-weight:bold;font-size:16px;margin-right:10px}
  nav a{color:#ccc;text-decoration:none;padding:6px 12px;border-radius:4px;font-size:13px}
  nav a:hover,nav a.active{background:#e5a800;color:#111;font-weight:bold}
  nav .logout{margin-left:auto;color:#c0392b}
  .notif-badge{background:#e5a800;color:#111;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:bold;margin-left:4px}
  .container{max-width:1100px;margin:0 auto;padding:24px}
  h1,h2{color:#e5a800;margin-bottom:16px}
  .card{background:#1a1a1a;border:1px solid #2a2a2a;border-radius:8px;padding:20px;margin-bottom:16px}
  .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px}
  .stat{background:#1a1a1a;border:1px solid #2a2a2a;border-radius:8px;padding:18px;text-align:center}
  .stat .num{font-size:32px;font-weight:bold;color:#e5a800}
  .stat .lbl{font-size:12px;color:#888;margin-top:4px}
  .btn{display:inline-block;padding:8px 16px;background:#e5a800;color:#111;border:none;border-radius:6px;cursor:pointer;font-weight:bold;text-decoration:none;font-size:13px}
  .btn:hover{background:#ffbf00}
  .btn-danger{background:#c0392b;color:#fff}
  .btn-danger:hover{background:#e74c3c}
  .btn-sm{padding:5px 10px;font-size:11px}
  .btn-secondary{background:#333;color:#eee}
  .btn-secondary:hover{background:#444}
  .alert{padding:11px 15px;border-radius:6px;margin-bottom:14px;font-size:13px}
  .alert-success{background:#1a3a1a;border:1px solid #27ae60;color:#2ecc71}
  .alert-error{background:#3a1a1a;border:1px solid #c0392b;color:#e74c3c}
  .alert-info{background:#1a2a3a;border:1px solid #2980b9;color:#3498db}
  input,select,textarea{width:100%;padding:9px;background:#2a2a2a;border:1px solid #444;color:#eee;border-radius:6px;margin-bottom:10px;font-size:13px}
  label{display:block;margin-bottom:3px;color:#999;font-size:12px}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th{background:#1e1e1e;padding:9px;text-align:left;color:#e5a800;border-bottom:1px solid #333}
  td{padding:9px;border-bottom:1px solid #1e1e1e}
  tr:hover td{background:#161616}
  .badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:bold}
  .badge-green{background:#1a3a1a;color:#2ecc71}
  .badge-red{background:#3a1a1a;color:#e74c3c}
  .badge-yellow{background:#3a2a00;color:#e5a800}
</style>
</head>
<body>
<nav>
  <span class="brand">🎵 DanDana Admin</span>
  <a href="dashboard.php">Dashboard</a>
  <a href="events.php">Événements</a>
  <a href="members.php">Membres</a>
  <a href="tickets.php">Billets</a>
  <a href="scanner.php">Scanner</a>
  <a href="notifications.php">
    Notifications
    <?php if ($unread > 0): ?><span class="notif-badge"><?= $unread ?></span><?php endif; ?>
  </a>
  <a href="logout.php" class="logout" style="margin-left:auto">Déconnexion</a>
</nav>
