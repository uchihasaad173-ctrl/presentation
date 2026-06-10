<?php
// portal/my_tickets.php
require_once '../config/database.php';
require_once '../includes/qr_helper.php';
require_once '../includes/header.php';

$pdo = getPDO();
$tel     = trim($_GET['tel'] ?? '');
$tickets = [];
$membre  = null;
$error   = '';

$already_registered = isset($_GET['error']) && $_GET['error'] === 'already_registered';

if ($tel) {
    if (!preg_match('/^0[5-7][0-9]{8}$/', $tel)) {
        $error = 'Numéro invalide.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM membres WHERE telephone = ?");
        $stmt->execute([$tel]);
        $membre = $stmt->fetch();

        if ($membre) {
            $stmt = $pdo->prepare("
                SELECT i.*, e.titre, e.date_event, e.lieu, e.prix
                FROM inscriptions i
                JOIN evenements e ON e.id = i.evenement_id
                WHERE i.membre_id = ?
                ORDER BY i.date_achat DESC
            ");
            $stmt->execute([$membre['id']]);
            $tickets = $stmt->fetchAll();
        } else {
            $error = 'Aucun compte trouvé pour ce numéro.';
        }
    }
}

$base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$verify_base = $base_url . dirname($_SERVER['PHP_SELF'], 2) . '/portal/verify.php?code=';
?>
<nav>
  <a href="index.php">← Événements</a>
  <a href="my_tickets.php">Mes billets</a>
</nav>

<div class="container" style="margin-top:24px">
  <h1>Mes billets</h1>

  <?php if ($already_registered): ?>
    <div class="alert alert-info">Vous êtes déjà inscrit à cet événement.</div>
  <?php endif; ?>

  <div class="card">
    <form method="GET">
      <label>Entrez votre numéro de téléphone pour retrouver vos billets</label>
      <div style="display:flex;gap:10px">
        <input type="text" name="tel" placeholder="0612345678"
               value="<?= htmlspecialchars($tel) ?>" style="margin-bottom:0">
        <button type="submit" class="btn">Rechercher</button>
      </div>
    </form>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php elseif ($membre && empty($tickets)): ?>
    <div class="alert alert-info">Aucun billet acheté pour le moment.</div>
  <?php elseif ($tickets): ?>
    <p style="margin-bottom:16px;color:#aaa">
      Bonjour <strong style="color:#eee"><?= htmlspecialchars($membre['nom_prenom']) ?></strong>,
      voici vos billets :
    </p>
    <?php foreach ($tickets as $t):
        $verify_url = $verify_base . urlencode($t['ticket_code']);
    ?>
    <div class="card" style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">
      <div style="flex:0 0 auto">
        <?= qrCodeImg($verify_url, 120) ?>
      </div>
      <div style="flex:1">
        <strong style="color:#e5a800"><?= htmlspecialchars($t['titre']) ?></strong><br>
        <span style="font-size:12px;color:#aaa">
          📅 <?= date('d/m/Y à H:i', strtotime($t['date_event'])) ?><br>
          📍 <?= htmlspecialchars($t['lieu']) ?><br>
          🎟 Code : <code style="color:#fff"><?= htmlspecialchars($t['ticket_code']) ?></code><br>
          📆 Acheté le <?= date('d/m/Y', strtotime($t['date_achat'])) ?>
        </span>
      </div>
      <div>
        <?php if ($t['utilise']): ?>
          <span class="badge badge-red">Scanné</span>
        <?php else: ?>
          <span class="badge badge-green">Valide</span>
        <?php endif; ?>
        <br><br>
        <a href="<?= htmlspecialchars(qrCodeUrl($verify_url, 300)) ?>"
           download="billet-<?= $t['ticket_code'] ?>.png" class="btn btn-sm">⬇ QR</a>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
