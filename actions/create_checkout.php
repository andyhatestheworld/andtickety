<?php
require_once __DIR__ . '/../include/stripe-php-19.0.0/init.php';
require_once __DIR__ . '/../include/configuration/config.php';

\Stripe\Stripe::setApiKey($secret_key_stripe);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $site_url);
    exit;
}

if (!isset($_POST['event_id']) || !isset($_POST['tickets'])) {
    header("Location: " . $site_url);
    exit;
}

$eventId = $_POST['event_id'];
$submittedTickets = $_POST['tickets'];

$json_file = __DIR__ . '/../include/event_info.json';

if (!file_exists($json_file)) {
    die('Eroare internă: Nu găsesc baza de date cu evenimente.');
}

$json_content = file_get_contents($json_file);
$events_data = json_decode($json_content, true);

$event = null;
foreach ($events_data as $e) {
    if ($e['id'] === $eventId) {
        $event = $e;
        break;
    }
}

if (!$event) {
    header("Location: " . $site_url);
    exit;
}

$line_items = [];
$metadata_tickets = [];

foreach ($submittedTickets as $category_key => $user_input) {
    $qty = intval($user_input['qty']);

    if ($qty > 0) {
        if (!isset($event['tickets'][$category_key])) {
            continue;
        }

        $ticket_info = $event['tickets'][$category_key];
        $real_price_ron = $ticket_info['price'];
        $real_name = $ticket_info['name'];

        $limit_per_category = $event['max_tickets_per_category'];

        if ($qty > $limit_per_category) {
            $qty = $limit_per_category;
        }

        $line_items[] = [
            'price_data' => [
                'currency' => $currency,
                'product_data' => [
                    'name' => $event['event_name'] . ' - ' . $real_name,
                    'metadata' => [
                        'event_id' => $eventId,
                        'ticket_type' => $category_key
                    ]
                ],
                'unit_amount' => $real_price_ron * 100,
            ],
            'quantity' => $qty,
        ];

        $metadata_tickets[] = "$qty x $real_name";
    }
}

if (empty($line_items)) {
    header("Location: " . $site_url);
    exit;
}

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        
        'success_url' => $site_url . '/actions/succes_stripe.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => $site_url . '/actions/error_stripe.php',
        
        'metadata' => [
            'event_id' => $eventId,
            'event_name' => $event['event_name'],
            'summary' => implode(', ', $metadata_tickets)
        ]
    ]);

    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
} catch (Error $e) {
    http_response_code(500);
    echo "Eroare Stripe: " . $e->getMessage();
}
?>