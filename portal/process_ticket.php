<?php
// portal/process_ticket.php
require_once '../config/database.php';
require_once '../includes/qr_helper.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['buy_data'])) {
    header('Location: index.php');
    exit;
}

$data     = $_SESSION['buy_data'];
$nom      = $data['nom_prenom'];
$tel      = $data['telephone'];
$event_id = (int)$data['event_id'];

unset($_SESSION['buy_data']); // one-time use

$pdo = getPDO();

// --- 1. Lock & re-check availability ---
$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ? FOR UPDATE");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event || $event['places_disponibles'] < 1) {
        $pdo->rollBack();
        header('Location: index.php?error=complet');
        exit;
    }

    // --- 2. Find or create member ---
    $stmt = $pdo->prepare("SELECT * FROM membres WHERE telephone = ?");
    $stmt->execute([$tel]);
    $membre = $stmt->fetch();

    $is_new_member = false;

    if (!$membre) {
        $is_new_member = true;
        $stmt = $pdo->prepare("INSERT INTO membres (nom_prenom, telephone) VALUES (?, ?)");
        $stmt->execute([$nom, $tel]);
        $membre_id = (int)$pdo->lastInsertId();
    } else {
        $membre_id = (int)$membre['id'];
    }

    // --- 3. Check if already registered for this event ---
    $stmt = $pdo->prepare("SELECT id FROM inscriptions WHERE membre_id = ? AND evenement_id = ?");
    $stmt->execute([$membre_id, $event_id]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        header('Location: my_tickets.php?error=already_registered');
        exit;
    }

    // --- 4. Generate unique ticket code ---
    do {
        $ticket_code = 'TKT-' . strtoupper(bin2hex(random_bytes(8)));
        $check = $pdo->prepare("SELECT id FROM inscriptions WHERE ticket_code = ?");
        $check->execute([$ticket_code]);
    } while ($check->fetch());

    // --- 5. Insert inscription ---
    $stmt = $pdo->prepare(
        "INSERT INTO inscriptions (membre_id, evenement_id, ticket_code) VALUES (?, ?, ?)"
    );
    $stmt->execute([$membre_id, $event_id, $ticket_code]);
    $inscription_id = (int)$pdo->lastInsertId();

    // --- 6. Decrement available places ---
    $pdo->prepare("UPDATE evenements SET places_disponibles = places_disponibles - 1 WHERE id = ?")
        ->execute([$event_id]);

    // --- 7. Notification if new member ---
    if ($is_new_member) {
        $msg = "Nouveau non-membre : {$nom} ({$tel}) a acheté un billet pour « {$event['titre']} » [code: {$ticket_code}]";
        $pdo->prepare("INSERT INTO notifications (message) VALUES (?)")->execute([$msg]);
    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    die('Erreur lors du traitement : ' . htmlspecialchars($e->getMessage()));
}

// --- 8. Build verify URL for QR ---
$base_url    = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$verify_url  = $base_url . dirname($_SERVER['PHP_SELF'], 2) . '/portal/verify.php?code=' . urlencode($ticket_code);

require_once '../includes/header.php';
?>
<nav>
  <a href="index.php">← Événements</a>
  <a href="my_tickets.php">Mes billets</a>
</nav>

<div class="container" style="margin-top:24px">
  <div class="alert alert-success">✅ Billet confirmé ! Conservez ce ticket précieusement.</div>

  <div class="card" style="text-align:center">
    <h2 style="margin-bottom:6px"><?= htmlspecialchars($event['titre']) ?></h2>
    <p style="color:#aaa;font-size:13px;margin-bottom:16px">
      <?= date('d/m/Y à H:i', strtotime($event['date_event'])) ?> — <?= htmlspecialchars($event['lieu']) ?>
    </p>

    <?= qrCodeImg($verify_url, 220) ?>

    <p style="margin-top:14px;font-size:13px;color:#aaa">Code billet</p>
    <p style="font-size:20px;font-weight:bold;letter-spacing:3px;color:#e5a800;margin-bottom:16px">
      <?= htmlspecialchars($ticket_code) ?>
    </p>

    <p style="font-size:12px;color:#666;margin-bottom:20px">
      Nom : <?= htmlspecialchars($nom) ?> &nbsp;|&nbsp; Tél : <?= htmlspecialchars($tel) ?>
    </p>

    <a href="<?= htmlspecialchars(qrCodeUrl($verify_url, 300)) ?>" download="billet-dandana.png"
       class="btn" style="margin-right:10px">⬇ Télécharger le QR</a>
    <a href="my_tickets.php?tel=<?= urlencode($tel) ?>" class="btn" style="background:#333;color:#eee">
      Tous mes billets
    </a>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
