<?php
require_once __DIR__ . '/../include/configuration/config.php';

if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
    header("Location: $site_url"); 
    exit();
}

$sessionId = $_GET['session_id'];
$tickets = [];
$error_msg = "";

try {
    $db_path = __DIR__ . '/../include/configuration/database/site.db';
    
    if (!file_exists($db_path)) {
        $db_path = __DIR__ . '/../include/database/site.db'; 
        if (!file_exists($db_path)) throw new Exception("Baza de date nu a fost găsită.");
    }

    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT * FROM tickets WHERE session_id = :sid");
    $stmt->execute([':sid' => $sessionId]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_msg = "Eroare la citirea biletelor: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Order Confirmed</title>
	<link rel="stylesheet" href="../assets/stripe.css">
</head>

<body>

<div class="container">
    <?php if (empty($tickets)): ?>
        <div class="info-box">
            <strong style="font-size: 1.2rem;">Processing...</strong><br>
            <span style="opacity: 0.8;">The payment is confirmed. The system is generating the unique codes.</span>
        </div>
        <button onclick="location.reload()" class="btn">Reload Page</button>
    <?php else: ?>
        
        <?php foreach ($tickets as $ticket): ?>
            <div class="ticket-card">
                <div class="ticket-content">
                    <div class="ticket-header">
                        <div class="event-name"><?= htmlspecialchars($ticket['event_name']) ?></div>
                        <span class="category-badge"><?= htmlspecialchars($ticket['category']) ?></span>
                    </div>

                    <div class="ticket-body">
                        <div class="qr-placeholder">
                            <!-- VARIANTA CU API -->
                            <!-- <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=<?= urlencode($ticket['code']) ?>&bgcolor=ffffff" alt="QR Code" title="<?= htmlspecialchars($ticket['code']) ?>"> -->
                            
                            <!-- VARIANTA LOCALA -->
                            <img src="generate_qr.php?code=<?= urlencode($ticket['code']) ?>" alt="QR Code" title="<?= htmlspecialchars($ticket['code']) ?>">
                       
                        </div>

                        <span class="code-label">ACCESS CODE:</span>
                        <div class="ticket-code"><?= htmlspecialchars($ticket['code']) ?></div>
                    </div>
                </div>

                <div class="ticket-footer">
                    <span><?= date('d.m.Y', strtotime($ticket['buy_date'])) ?></span>
                    <span>
                        STATUS: 
                        <?php if ($ticket['used'] == 1): ?>
                            <span class="status-used">USED</span>
                        <?php else: ?>
                            <span class="status-active">UNUSED</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include '../include/footer.php'; ?>
</body>
</html>