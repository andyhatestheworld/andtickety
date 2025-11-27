<?php
require_once __DIR__ . '/../include/phpqrcode/qrlib.php';

if (isset($_GET['code'])) {
    QRcode::png($_GET['code'], false, QR_ECLEVEL_L, 10, 1); 
} else {
    header("HTTP/1.0 404 Not Found");
}
?>