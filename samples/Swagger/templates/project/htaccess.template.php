<?php

require_once FLEXIONS_SOURCE_DIR.'/SharedSwagger.php';
/* @var $f Flexed */
/* @var $h Hypotypose */

if (isset ( $f )) {
    $f->fileName = '.htaccess';
    $f->package = "php/api/";
}

/* TEMPLATES STARTS HERE -> */?><IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule v<?php echo($h->majorVersionString())?>/(.*)$ bootStrap.php?request=$1 [QSA,NC,L]
</IfModule>
<?php /*<- END OF TEMPLATE */?>