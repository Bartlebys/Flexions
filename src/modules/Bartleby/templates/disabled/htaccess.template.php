<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $h Hypotypose */

if (isset ( $f )) {
    $f->fileName = '.htaccess';
    $f->package = "php/api/";
}

/* TEMPLATES STARTS HERE -> */?><IfModule mod_rewrite.c>

    RewriteEngine On

    # Routes to API
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/v1/(.*)$ api/go.php?request=$1 [QSA,NC,L]

    # Routes to Pages
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule (.*)$ pages/go.php?request=$1 [QSA,NC,L]

</IfModule>
<?php /*<- END OF TEMPLATE */?>