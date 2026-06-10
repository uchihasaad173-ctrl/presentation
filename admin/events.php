<?php
// admin/events.php
require_once 'auth_guard.php';
require_once '../config/database.php';
require_once 'admin_nav.php';

$pdo = getPDO();
$msg = $err = '';

// --- DELETE ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM evenements WHERE id = ?")->execute([$id]);
    header('Location: events.php?msg=deleted');
    exit;
}

// --- EDIT form data ---
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

// --- CREATE / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre  = trim($_POST['titre'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $date   = trim($_POST['date_event'] ?? '');
    $lieu   = trim($_POST['lieu'] ?? '');
    $prix   = (float)($_POST['prix'] ?? 0);
    $places = (int)($_POST['places_disponibles'] ?? 0);
    $id     = (int)($_POST['id'] ?? 0);

    if (!$titre || !$date || !$lieu) {
        $err = 'Titre, date et lieu sont requis.';
    } elseif ($id) {
        $pdo->prepare("UPDATE evenements SET titre=?,description=?,date_event=?,lieu=?,prix=?,places_disponibles=? WHERE id=?")
            ->execute([$titre, $desc, $date, $lieu, $prix, $places, $id]);
        header('Location: events.php?msg=updated');
        exit;
    } else {
        $pdo->prepare("INSERT INTO evenements (titre,description,date_event,lieu,prix,places_disponibles) VALUES(?,?,?,?,?,?)")
            ->execute([$titre, $desc, $date, $lieu, $prix, $places]);
        header('Location: events.php?msg=created');
        exit;
    }
}

$events = $pdo->query("SELECT * FROM evenements ORDER BY date_event DESC")->fetchAll();

$msg_map = ['created' => 'Événement créé.', 'updated' => 'Événement modifié.', 'deleted' => 'Événement supprimé.'];
if (isset($_GET['msg'], $msg_map[$_GET['msg']])) $msg = $msg_map[$_GET['msg']];
?>
<div class="container">
  <h1>Gestion des événements</h1>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <!-- Form -->
  <div class="card">
    <h2 style="font-size:15px"><?= $edit ? 'Modifier l\'événement' : 'Nouvel événement' ?></h2>
    <form method="POST">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label>Titre *</label>
          <input type="text" name="titre" required value="<?= htmlspecialchars($edit['titre'] ?? '') ?>">
        </div>
        <div>
          <label>Lieu *</label>
          <input type="text" name="lieu" required value="<?= htmlspecialchars($edit['lieu'] ?? '') ?>">
        </div>
        <div>
          <label>Date et heure *</label>
          <input type="datetime-local" name="date_event" required
                 value="<?= $edit ? date('Y-m-d\TH:i', strtotime($edit['date_event'])) : '' ?>">
        </div>
        <div>
          <label>Prix (MAD)</label>
          <input type="number" name="prix" min="0" step="0.01" value="<?= $edit['prix'] ?? 0 ?>">
        </div>
        <div>
          <label>Places disponibles</label>
          <input type="number" name="places_disponibles" min="0"
                 value="<?= $edit['places_disponibles'] ?? 0 ?>">
        </div>
      </div>
      <label>Description</label>
      <textarea name="description" rows="3"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
      <button type="submit" class="btn"><?= $edit ? 'Enregistrer' : 'Créer' ?></button>
      <?php if ($edit): ?>
        <a href="events.php" class="btn btn-secondary" style="margin-left:8px">Annuler</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Table -->
  <div class="card">
    <table>
      <thead>
        <tr><th>Titre</th><th>Date</th><th>Lieu</th><th>Prix</th><th>Places</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($events as $e): ?>
        <tr>
          <td><?= htmlspecialchars($e['titre']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($e['date_event'])) ?></td>
          <td><?= htmlspecialchars($e['lieu']) ?></td>
          <td><?= number_format($e['prix'], 2) ?> MAD</td>
          <td><?= $e['places_disponibles'] ?></td>
          <td>
            <a href="events.php?edit=<?= $e['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
            <a href="events.php?delete=<?= $e['id'] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('Supprimer cet événement ?')">Supprimer</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body></html>
