<?php
// admin/members.php
require_once 'auth_guard.php';
require_once '../config/database.php';
require_once 'admin_nav.php';

$pdo = getPDO();

$search = trim($_GET['q'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("
        SELECT m.*, COUNT(i.id) as nb_billets
        FROM membres m
        LEFT JOIN inscriptions i ON i.membre_id = m.id
        WHERE m.nom_prenom LIKE ? OR m.telephone LIKE ?
        GROUP BY m.id ORDER BY m.created_at DESC
    ");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT m.*, COUNT(i.id) as nb_billets
        FROM membres m
        LEFT JOIN inscriptions i ON i.membre_id = m.id
        GROUP BY m.id ORDER BY m.created_at DESC
    ");
}
$membres = $stmt->fetchAll();
?>
<div class="container">
  <h1>Membres (<?= count($membres) ?>)</h1>

  <div class="card" style="padding:14px">
    <form method="GET" style="display:flex;gap:10px">
      <input type="text" name="q" placeholder="Rechercher par nom ou téléphone"
             value="<?= htmlspecialchars($search) ?>" style="margin-bottom:0">
      <button type="submit" class="btn">Chercher</button>
      <?php if ($search): ?><a href="members.php" class="btn btn-secondary">Effacer</a><?php endif; ?>
    </form>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr><th>#</th><th>Nom et prénom</th><th>Téléphone</th><th>Billets</th><th>Inscrit le</th></tr>
      </thead>
      <tbody>
        <?php foreach ($membres as $m): ?>
        <tr>
          <td><?= $m['id'] ?></td>
          <td><?= htmlspecialchars($m['nom_prenom']) ?></td>
          <td><?= htmlspecialchars($m['telephone']) ?></td>
          <td><span class="badge badge-yellow"><?= $m['nb_billets'] ?></span></td>
          <td><?= date('d/m/Y', strtotime($m['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($membres)): ?>
          <tr><td colspan="5" style="text-align:center;color:#666">Aucun membre trouvé.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body></html>
