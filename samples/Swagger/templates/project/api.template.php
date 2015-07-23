<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */
/* @var $f Flexed */
/* @var $d ProjectRepresentation */

require_once FLEXIONS_SOURCE_DIR.'/SharedSwagger.php';

if (isset ( $f )) {
    $f->fileName = 'Api.class.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString();
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>
<?php

/* @var $action ActionRepresentation */
foreach ($d->actions as $action ) {
    echo('require_once "/endpoints/'.$action->class.'.php";'.cr());
}?>

    // Path : <?php echo $action->path.cr(); ?>
    class Api {
        function run(){
            $result=array("message"=>"OK");
            return json_encode($result,400);
        }
    }

<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>


