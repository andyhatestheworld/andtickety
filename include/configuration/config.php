<?php

$site_name = 'andtickety';
$site_description = 'Your all-in-one ticketing solution for events, conferences, and more. Simplify ticket management with our user-friendly platform.';

$site_url = 'https://domain.com';


$currency = 'RON';
$currency_symbol = 'lei';


// Stripe > Secret key
$secret_key_stripe = 'sk_test_x';
// Stripe > Search > Webhooks > Add Destination > 'checkout.session.completed' > Webhook endpoint > Signing secret
$endpoint_secret_stripe = 'whsec_y';


$mail_name       = $site_name;
$mail_host       = 'mail.domain.com';
$mail_username   = 'no-reply@domain.com';
$mail_password   = 'password';
$mail_smtpSecure = 'ssl';
$mail_port       = 465;

?>
