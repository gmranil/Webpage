<?php
// php/email_config.php

// Gmail SMTP beállítások (APP PASSWORD kell!)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);            // TLS
define('SMTP_SECURE', 'tls');        // 'tls' 587-hez, 'ssl' 465-höz

define('SMTP_USERNAME', 'gmranil@gmail.com');  // teljes Gmail cím
define('SMTP_PASSWORD', 'facy ptjy nlcv pjfe');   // NEM a sima jelszó!

define('SMTP_FROM_EMAIL', 'SAJAT_GMAIL_CIM@gmail.com');
define('SMTP_FROM_NAME', 'L2 Savior');

define('SMTP_DEBUG', 2); // 0 = nincs debug, 2 = részletes (fejlesztéshez)
