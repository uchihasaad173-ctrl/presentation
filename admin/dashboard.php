<?php
// admin/dashboard.php
require_once 'auth_guard.php';
require_once '../config/database.php';
require_once 'admin_nav.php';

$pdo = getPDO();

$stats = [
    'membres'      => $pdo->query("SELECT COUNT(*) FROM membres")->fetchColumn(),
    'evenements'   => $pdo->query("SELECT COUNT(*) FROM evenements")->fetchColumn(),
    'billets'      => $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn(),
    'utilises'     => $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE utilise = 1")->fetchColumn(),
    'notifications'=> $pdo->query("SELECT COUNT(*) FROM notifications WHERE lu = 0")->fetchColumn(),
    'places_total' => $pdo->query("SELECT SUM(places_disponibles) FROM evenements")->fetchColumn() ?? 0,
];

$recent_tickets = $pdo->query("
    SELECT i.ticket_code, i.date_achat, i.utilise, m.nom_prenom, e.titre
    FROM inscriptions i
    JOIN membres m ON m.id = i.membre_id
    JOIN evenements e ON e.id = i.evenement_id
    ORDER BY i.date_achat DESC LIMIT 10
")->fetchAll();

$upcoming = $pdo->query("
    SELECT * FROM evenements WHERE date_event >= NOW() ORDER BY date_event ASC LIMIT 5
")->fetchAll();
?>
<div class="container">
  <h1>Dashboard</h1>
  <p style="color:#888;font-size:13px;margin-bottom:20px">
    Connecté en tant que <strong style="color:#eee"><?= htmlspecialchars($_SESSION['admin_user']) ?></strong>
    — <?= date('d/m/Y H:i') ?>
  </p>

  <div class="stat-grid">
    <div class="stat"><div class="num"><?= $stats['membres'] ?></div><div class="lbl">Membres</div></div>
    <div class="stat"><div class="num"><?= $stats['evenements'] ?></div><div class="lbl">Événements</div></div>
    <div class="stat"><div class="num"><?= $stats['billets'] ?></div><div class="lbl">Billets vendus</div></div>
    <div class="stat"><div class="num"><?= $stats['utilises'] ?></div><div class="lbl">Entrées scannées</div></div>
    <div class="stat"><div class="num" style="color:<?= $stats['notifications'] ? '#e74c3c' : '#2ecc71' ?>">
      <?= $stats['notifications'] ?></div><div class="lbl">Notifs non lues</div></div>
    <div class="stat"><div class="num"><?= $stats['places_total'] ?></div><div class="lbl">Places restantes</div></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;flex-wrap:wrap">
    <div class="card">
      <h2 style="font-size:15px">Prochains événements</h2>
      <?php if (empty($upcoming)): ?>
        <p style="color:#666;font-size:13px">Aucun événement à venir.</p>
      <?php else: foreach ($upcoming as $e): ?>
        <div style="border-bottom:1px solid #2a2a2a;padding:8px 0">
          <strong style="font-size:13px"><?= htmlspecialchars($e['titre']) ?></strong><br>
          <span style="font-size:12px;color:#888">
            <?= date('d/m/Y', strtotime($e['date_event'])) ?> — <?= $e['places_disponibles'] ?> places
          </span>
        </div>
      <?php endforeach; endif; ?>
      <a href="events.php" class="btn btn-sm" style="margin-top:12px">Gérer les événements</a>
    </div>

    <div class="card">
      <h2 style="font-size:15px">Derniers billets</h2>
      <?php foreach ($recent_tickets as $t): ?>
        <div style="border-bottom:1px solid #2a2a2a;padding:8px 0;font-size:12px">
          <strong><?= htmlspecialchars($t['nom_prenom']) ?></strong> — <?= htmlspecialchars($t['titre']) ?><br>
          <span style="color:#888"><?= htmlspecialchars($t['ticket_code']) ?></span>
          <?php if ($t['utilise']): ?>
            <span class="badge badge-green" style="float:right">Scanné</span>
          <?php else: ?>
            <span class="badge badge-yellow" style="float:right">En attente</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <a href="tickets.php" class="btn btn-sm" style="margin-top:12px">Tous les billets</a>
    </div>
  </div>
</div>
</body></html>
