# Contact form server

The form on sedovi.nl posts to `https://sedovi.net/contact.php`, which runs on Strato webhosting (GitHub Pages can't run code). Jekyll skips this folder, so none of it is published on sedovi.nl.

- `public/` is the web root of sedovi.net (webspace folder `/form/public`; Strato serves sedovi.net from there). It holds only `contact.php`.
- `private/` sits next to it, outside the web root: PHPMailer 7.2.0 (LGPL, see `phpmailer/LICENSE`), `config.php` and the `ratelimit/` folder the script creates.
- `config.php` holds the mailbox password and exists only on the server. Copy `config.example.php` and fill it in there.

The script sends through smtp.strato.de:465 as developer@sedovi.nl, so the message is DKIM-signed for sedovi.nl and passes its `p=reject` DMARC policy. The visitor's address goes in Reply-To. Limits: 5 messages per IP per hour, 100 per day in total.

Local test: run `public/` with `php -S` and point `smtp_host`/`smtp_port` in a test config at Mailpit (port 1025).
