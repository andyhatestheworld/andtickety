<?php
require_once __DIR__ . '/include/stripe-php-19.0.0/init.php';
require_once __DIR__ . '/include/configuration/config.php';


require_once __DIR__ . '/include/PHPMailer-7.0.1/src/PHPMailer.php';
require_once __DIR__ . '/include/PHPMailer-7.0.1/src/SMTP.php';
require_once __DIR__ . '/include/PHPMailer-7.0.1/src/Exception.php';
require_once __DIR__ . '/include/PHPMailer-7.0.1/language/phpmailer.lang-ro.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

\Stripe\Stripe::setApiKey($secret_key_stripe);
$endpoint_secret = $endpoint_secret_stripe;

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    http_response_code(400); exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400); exit();
}

if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;

    if ($session->payment_status == 'paid') {
        
        $sessionId = $session->id;
        
        try {
            $line_items = \Stripe\Checkout\Session::allLineItems($sessionId, [
                'limit' => 100,
                'expand' => ['data.price.product'],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            file_put_contents(__DIR__ . '/logs/stripe_error.log', date('Y-m-d H:i:s') . " | Stripe Error: " . $e->getMessage() . "\n", FILE_APPEND);
            http_response_code(500); exit();
        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/logs/stripe_error.log', date('Y-m-d H:i:s') . " | General Error: " . $e->getMessage() . "\n", FILE_APPEND);
            http_response_code(500); exit();
        }

        $customerEmail = $session->customer_details->email ?? 'Email necunoscut';
        $customerName = $session->customer_details->name ?? 'Client';
        
        $payedAmount = $session->amount_total / 100;

        $eventId = $session->metadata->event_id ?? null;

        $json_file = __DIR__ . '/include/event_info.json';
        $events_data = json_decode(file_get_contents($json_file), true);
        
        $current_event = null;
        foreach ($events_data as $ev) {
            if ($ev['id'] === $eventId) {
                $current_event = $ev;
                break;
            }
        }

        $eventNameDb = $current_event ? $current_event['event_name'] : ($session->metadata->event_name ?? 'Eveniment');

        try {
            $db_path = __DIR__ . '/include/database/site.db';
            $db = new PDO('sqlite:' . $db_path);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/logs/db_error.log', date('Y-m-d H:i:s') . " | DB Connection Error: " . $e->getMessage() . "\n", FILE_APPEND);
            http_response_code(500); exit();
        }

        $stmt = $db->prepare("INSERT INTO tickets (session_id, event_name, category, code, buy_date, expire_date, used) VALUES (:session_id, :event_name, :category, :code, :buy_date, :expire_date, :used)");
        $stmt2 = $db->prepare("INSERT INTO customers (session_id, email, name, payed, tickets_count) VALUES (:session_id, :email, :name, :payed, :tickets_count)");

        $buyDate = date('Y-m-d H:i:s');
        $generatedCodesLog = []; 


        foreach ($line_items->data as $item) {
            $qty = $item->quantity;
            $product_obj = $item->price->product;
            $categoryKey = $product_obj->metadata->ticket_type ?? null;
            
            $categoryNameDb = 'Necunoscut';
            
            if ($categoryKey && isset($current_event['tickets'][$categoryKey])) {
                $categoryNameDb = $current_event['tickets'][$categoryKey]['name'];
            } else {
                $categoryNameDb = $product_obj->name;
            }

            for ($i = 0; $i < $qty; $i++) {
                $uniqueCode = bin2hex(random_bytes(32)); 

                $stmt->execute([
                    ':session_id'  => $sessionId,
                    ':event_name'  => $eventNameDb,
                    ':category'    => $categoryNameDb,
                    ':code'        => $uniqueCode,
                    ':buy_date'    => $buyDate,
                    ':expire_date' => 0,    
                    ':used'        => 0     
                ]);

                $generatedCodesLog[] = "$categoryNameDb";
            }
        }

        $stmt2->execute([
            ':session_id'       => $sessionId,
            ':email'            => $customerEmail,
            ':name'             => $customerName,
            ':payed'            => number_format($payedAmount,2),
            ':tickets_count'    => count($generatedCodesLog)
        ]);

        $downloadLink = $site_url . '/actions/succes_stripe.php?session_id=' . $sessionId;
        $contactLink = $site_url . '/index.php?page=contact';

        $mail = new PHPMailer(true);
        $mail->setLanguage('ro', __DIR__ . '/include/PHPMailer-7.0.1/language/');

        try {
            $mail->isSMTP();
            $mail->Host       = $mail_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $mail_username;
            $mail->Password   = $mail_password;
            $mail->SMTPSecure = $mail_smtpSecure; 
            $mail->Port       = $mail_port;
            
            $mail->setFrom($mail_username, $mail_name);
            $mail->addAddress($customerEmail);

            $mail->isHTML(true);
            $mail->Subject = "Your tickets for: $eventNameDb";
            
            $bodyContent = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Hello, $customerName!</h2>
                    <p>Thank you for your purchase. Your payment has been successfully confirmed.</p>
                    <p><strong>Event:</strong> $eventNameDb</p>
                    <hr>
                    <h3>Your tickets have been generated! (".count($generatedCodesLog)." tickets)</h3>
                   
                    <br>
                    <a href='$downloadLink' style='background-color: #ff1e8a; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>ACCESS TICKETS HERE</a>
                    <br><br>
                    <p><small>Direct link: <a href='$downloadLink'>$downloadLink</a></small></p>
                    <hr>
                    <p>$site_name</p>
                </div>
            ";

            $mail->Body = $bodyContent;
            $mail->send();

        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/logs/mail_error.log', date('Y-m-d H:i:s')." | Mail error: ".$mail->ErrorInfo."\n", FILE_APPEND);
        }
    }
}

http_response_code(200);
?>