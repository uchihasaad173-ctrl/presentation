<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Club DanDana</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:Arial,sans-serif;background:#111;color:#eee;min-height:100vh}
  .container{max-width:900px;margin:0 auto;padding:20px}
  nav{background:#1a1a1a;padding:12px 20px;display:flex;gap:16px;align-items:center}
  nav a{color:#e5a800;text-decoration:none;font-weight:bold}
  nav a:hover{text-decoration:underline}
  h1,h2{color:#e5a800;margin-bottom:16px}
  .card{background:#1e1e1e;border:1px solid #333;border-radius:8px;padding:20px;margin-bottom:16px}
  .btn{display:inline-block;padding:10px 20px;background:#e5a800;color:#111;border:none;border-radius:6px;cursor:pointer;font-weight:bold;text-decoration:none;font-size:14px}
  .btn:hover{background:#ffbf00}
  .btn-danger{background:#c0392b;color:#fff}
  .btn-danger:hover{background:#e74c3c}
  .btn-sm{padding:6px 12px;font-size:12px}
  .alert{padding:12px 16px;border-radius:6px;margin-bottom:16px}
  .alert-success{background:#1a3a1a;border:1px solid #27ae60;color:#2ecc71}
  .alert-error{background:#3a1a1a;border:1px solid #c0392b;color:#e74c3c}
  .alert-info{background:#1a2a3a;border:1px solid #2980b9;color:#3498db}
  input,select,textarea{width:100%;padding:10px;background:#2a2a2a;border:1px solid #444;color:#eee;border-radius:6px;margin-bottom:12px;font-size:14px}
  label{display:block;margin-bottom:4px;color:#aaa;font-size:13px}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th{background:#2a2a2a;padding:10px;text-align:left;color:#e5a800}
  td{padding:10px;border-bottom:1px solid #2a2a2a}
  tr:hover td{background:#1a1a1a}
  .badge{display:inline-block;padding:3px 8px;border-radius:10px;font-size:11px;font-weight:bold}
  .badge-green{background:#1a3a1a;color:#2ecc71}
  .badge-red{background:#3a1a1a;color:#e74c3c}
  .badge-yellow{background:#3a2a00;color:#e5a800}
</style>
</head>
<body>
