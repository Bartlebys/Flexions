<?php

if (isset ( $f )) {
    $f->fileName = '.htaccess';
    $f->package = "php/api/";
}

echo('<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule v1/(.*)$ bootStrap.php?request=$1 [QSA,NC,L]
</IfModule>');