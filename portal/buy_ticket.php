<?php
// portal/buy_ticket.php
require_once '../config/database.php';
require_once '../includes/header.php';

$pdo = getPDO();
$event_id = (int)($_GET['event_id'] ?? 0);

if (!$event_id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo '<div class="container"><div class="alert alert-error">Événement introuvable.</div></div>';
    require_once '../includes/footer.php';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom_prenom'] ?? '');
    $tel    = trim($_POST['telephone'] ?? '');

    if (!$nom || !$tel) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!preg_match('/^0[5-7][0-9]{8}$/', $tel)) {
        $error = 'Numéro de téléphone invalide (format marocain attendu).';
    } elseif ($event['places_disponibles'] < 1) {
        $error = 'Plus de places disponibles.';
    } else {
        // Redirect to process (POST-redirect-GET)
        $_SESSION['buy_data'] = ['nom_prenom' => $nom, 'telephone' => $tel, 'event_id' => $event_id];
        header('Location: process_ticket.php');
        exit;
    }
}
?>
<nav>
  <a href="index.php">← Retour aux événements</a>
</nav>

<div class="container" style="margin-top:24px">
  <h1>Acheter un billet</h1>

  <div class="card" style="margin-bottom:20px">
    <strong style="color:#e5a800"><?= htmlspecialchars($event['titre']) ?></strong><br>
    <span style="font-size:13px;color:#aaa">
      📅 <?= date('d/m/Y à H:i', strtotime($event['date_event'])) ?> &nbsp;
      📍 <?= htmlspecialchars($event['lieu']) ?> &nbsp;
      💰 <?= number_format($event['prix'], 2) ?> MAD
    </span>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="POST">
      <label>Nom et prénom</label>
      <input type="text" name="nom_prenom" placeholder="Ex: Yassine El Amrani"
             value="<?= htmlspecialchars($_POST['nom_prenom'] ?? '') ?>" required>

      <label>Numéro de téléphone</label>
      <input type="text" name="telephone" placeholder="Ex: 0612345678"
             value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" required>

      <p style="font-size:12px;color:#777;margin-bottom:14px">
        Si vous êtes déjà membre, votre compte sera retrouvé automatiquement.
        Sinon, un compte sera créé.
      </p>

      <button type="submit" class="btn">Confirmer l'achat</button>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
