<?php
// Copy to config.php on the server only. Never commit config.php.
return [
    'smtp_user' => 'developer@sedovi.nl',   // Strato mailbox that sends the message
    'smtp_pass' => 'PASSWORD',              // that mailbox's password
    'to'        => 'developer@sedovi.nl',   // where messages arrive
    'salt'      => 'RANDOM',                // any long random string, used to hash IP addresses
];
