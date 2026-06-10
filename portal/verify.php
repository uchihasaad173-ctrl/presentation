<?php
// portal/verify.php
// Can be called:
//  - GET verify.php?code=TKT-XXX          → human-readable page
//  - POST verify.php  body: code=TKT-XXX  → JSON response (used by scanner.php)

require_once '../config/database.php';

$is_ajax = ($_SERVER['REQUEST_METHOD'] === 'POST')
        || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || isset($_GET['json']);

$code = trim($_POST['code'] ?? $_GET['code'] ?? '');

if (!$code) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Code manquant']);
        exit;
    }
    require_once '../includes/header.php';
    echo '<div class="container"><div class="alert alert-error">Aucun code fourni.</div></div>';
    require_once '../includes/footer.php';
    exit;
}

$pdo = getPDO();

$stmt = $pdo->prepare("
    SELECT i.*, m.nom_prenom, m.telephone, e.titre, e.date_event, e.lieu
    FROM inscriptions i
    JOIN membres m ON m.id = i.membre_id
    JOIN evenements e ON e.id = i.evenement_id
    WHERE i.ticket_code = ?
");
$stmt->execute([$code]);
$ticket = $stmt->fetch();

$result = [];

if (!$ticket) {
    $result = ['status' => 'invalid', 'message' => '❌ Ticket introuvable'];
} elseif ($ticket['utilise']) {
    $result = [
        'status'  => 'used',
        'message' => '⚠️ Ticket déjà utilisé',
        'nom'     => $ticket['nom_prenom'],
        'event'   => $ticket['titre'],
    ];
} else {
    // Mark as used
    $pdo->prepare("UPDATE inscriptions SET utilise = 1 WHERE ticket_code = ?")->execute([$code]);
    $result = [
        'status'  => 'valid',
        'message' => '✅ Ticket valide — entrée autorisée',
        'nom'     => $ticket['nom_prenom'],
        'tel'     => $ticket['telephone'],
        'event'   => $ticket['titre'],
        'date'    => date('d/m/Y à H:i', strtotime($ticket['date_event'])),
        'lieu'    => $ticket['lieu'],
    ];
}

if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Human-readable page
require_once '../includes/header.php';

$color_map = ['valid' => 'alert-success', 'used' => 'alert-error', 'invalid' => 'alert-error'];
$cls = $color_map[$result['status']] ?? 'alert-info';
?>
<nav><a href="index.php">← Événements</a></nav>
<div class="container" style="margin-top:24px">
  <h1>Vérification de billet</h1>
  <div class="card">
    <div class="alert <?= $cls ?>" style="font-size:16px;font-weight:bold"><?= htmlspecialchars($result['message']) ?></div>
    <p><strong>Code :</strong> <?= htmlspecialchars($code) ?></p>
    <?php if (isset($result['nom'])): ?>
      <p><strong>Nom :</strong> <?= htmlspecialchars($result['nom']) ?></p>
      <p><strong>Événement :</strong> <?= htmlspecialchars($result['event']) ?></p>
      <?php if (isset($result['date'])): ?>
        <p><strong>Date :</strong> <?= htmlspecialchars($result['date']) ?></p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($result['lieu']) ?></p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
