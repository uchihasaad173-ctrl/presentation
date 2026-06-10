<?php
// admin/notifications.php
require_once 'auth_guard.php';
require_once '../config/database.php';
require_once 'admin_nav.php';

$pdo = getPDO();

// Mark all as read when visiting
$pdo->exec("UPDATE notifications SET lu = 1 WHERE lu = 0");

$notifs = $pdo->query("SELECT * FROM notifications ORDER BY date_notification DESC")->fetchAll();
?>
<div class="container">
  <h1>Notifications</h1>

  <?php if (empty($notifs)): ?>
    <div class="alert alert-info">Aucune notification.</div>
  <?php else: ?>
    <div class="card">
      <table>
        <thead>
          <tr><th>Message</th><th>Date</th><th>Lu</th></tr>
        </thead>
        <tbody>
          <?php foreach ($notifs as $n): ?>
          <tr>
            <td><?= htmlspecialchars($n['message']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($n['date_notification'])) ?></td>
            <td><?= $n['lu'] ? '<span class="badge badge-green">Lu</span>' : '<span class="badge badge-yellow">Nouveau</span>' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
</body></html>
