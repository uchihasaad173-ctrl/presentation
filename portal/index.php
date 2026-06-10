<?php
// portal/index.php  — Liste des événements publics
require_once '../config/database.php';
require_once '../includes/header.php';

$pdo = getPDO();
$events = $pdo->query("SELECT * FROM evenements WHERE date_event >= NOW() ORDER BY date_event ASC")->fetchAll();
?>
<nav>
  <span style="color:#e5a800;font-weight:bold;font-size:18px">🎵 Club DanDana</span>
  <a href="index.php">Événements</a>
  <a href="my_tickets.php">Mes billets</a>
</nav>

<div class="container" style="margin-top:24px">
  <h1>Événements à venir</h1>

  <?php if (empty($events)): ?>
    <div class="alert alert-info">Aucun événement à venir pour le moment.</div>
  <?php endif; ?>

  <?php foreach ($events as $e): ?>
  <div class="card">
    <h2 style="font-size:18px;margin-bottom:8px"><?= htmlspecialchars($e['titre']) ?></h2>
    <p style="color:#aaa;margin-bottom:10px"><?= htmlspecialchars($e['description']) ?></p>
    <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:13px;color:#ccc;margin-bottom:14px">
      <span>📅 <?= date('d/m/Y à H:i', strtotime($e['date_event'])) ?></span>
      <span>📍 <?= htmlspecialchars($e['lieu']) ?></span>
      <span>💰 <?= number_format($e['prix'], 2) ?> MAD</span>
      <span>🎟 <?= $e['places_disponibles'] ?> places restantes</span>
    </div>
    <?php if ($e['places_disponibles'] > 0): ?>
      <a href="buy_ticket.php?event_id=<?= $e['id'] ?>" class="btn">Acheter un billet</a>
    <?php else: ?>
      <span class="badge badge-red">Complet</span>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
