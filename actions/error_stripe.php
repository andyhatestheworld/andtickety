<?php
require_once __DIR__ . '/../include/configuration/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Payment Failed</title>
	<link rel="stylesheet" href="../assets/stripe.css">
</head>

<body>
    <div class="container">
        
        <div class="error-header">
            <h1>Payment Failed</h1>
        </div>

        <div class="info-box">
            <strong style="font-size: 1.2rem;">What happened?</strong><br>
            <span style="opacity: 0.8;">Possible reasons:</span>
            <ul style="opacity: 0.8;">
                <li>Insufficient funds</li>
                <li>The bank declined the transaction</li>
                <li>Incorrect card details entered</li>
                <li>Session expired</li>
            </ul>
            <span style="opacity: 0.8;">Please try again.</span>
        </div>

        <div>
            <a href="<?= $site_url ?>" class="btn">Try again</a>
        </div>

    </div>

   <?php include '../include/footer.php'; ?>

</body>

</html>