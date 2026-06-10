<?php
// admin/tickets.php
require_once 'auth_guard.php';
require_once '../config/database.php';
require_once 'admin_nav.php';

$pdo = getPDO();

$filter = $_GET['filter'] ?? 'all'; // all | used | unused

$where = match($filter) {
    'used'   => 'WHERE i.utilise = 1',
    'unused' => 'WHERE i.utilise = 0',
    default  => ''
};

$tickets = $pdo->query("
    SELECT i.*, m.nom_prenom, m.telephone, e.titre, e.date_event
    FROM inscriptions i
    JOIN membres m ON m.id = i.membre_id
    JOIN evenements e ON e.id = i.evenement_id
    $where
    ORDER BY i.date_achat DESC
")->fetchAll();
?>
<div class="container">
  <h1>Billets vendus (<?= count($tickets) ?>)</h1>

  <div style="margin-bottom:14px;display:flex;gap:8px">
    <a href="tickets.php?filter=all"    class="btn btn-sm <?= $filter==='all'    ? '' : 'btn-secondary' ?>">Tous</a>
    <a href="tickets.php?filter=unused" class="btn btn-sm <?= $filter==='unused' ? '' : 'btn-secondary' ?>">Non scannés</a>
    <a href="tickets.php?filter=used"   class="btn btn-sm <?= $filter==='used'   ? '' : 'btn-secondary' ?>">Scannés</a>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr><th>Code</th><th>Membre</th><th>Téléphone</th><th>Événement</th><th>Achat</th><th>Statut</th></tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
        <tr>
          <td><code style="font-size:11px"><?= htmlspecialchars($t['ticket_code']) ?></code></td>
          <td><?= htmlspecialchars($t['nom_prenom']) ?></td>
          <td><?= htmlspecialchars($t['telephone']) ?></td>
          <td><?= htmlspecialchars($t['titre']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($t['date_achat'])) ?></td>
          <td>
            <?php if ($t['utilise']): ?>
              <span class="badge badge-green">Scanné</span>
            <?php else: ?>
              <span class="badge badge-yellow">En attente</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($tickets)): ?>
          <tr><td colspan="6" style="text-align:center;color:#666">Aucun billet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body></html>
