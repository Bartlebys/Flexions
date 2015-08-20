<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */


if (isset ( $f )) {
    $f->fileName = 'GeneratedConfiguration.php';
    $f->package = 'php/generated/'.$h->majorVersionPathSegmentString();
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

// SHARED CONFIGURATION BETWEEN THE API & MAIN PAGES

namespace Bartleby;

use Bartleby\Core\RoutesAliases;
use Bartleby\Core\Stages;
use Bartleby\Mongo\MongoConfiguration;

require_once BARTLEBY_ROOT_FOLDER . '/mongo/MongoConfiguration.php';
require_once BARTLEBY_ROOT_FOLDER . '/core/RoutesAliases.php';

class GeneratedConfiguration extends MongoConfiguration {

    /**
     * @param $baseDirectory
     * @param $bartlebysRootDirectory
     */
    public function __construct($executionDirectory,$bartlebyRootDirectory){
        parent::__construct($executionDirectory,$bartlebyRootDirectory);

        $this->_STAGE=Stages::DEVELOPMENT;
        $this->_VERSION='<?php echo $d->apiVersion; ?>';
        $this->_SALT='<?php echo uniqid('bartleby-'); ?>';

        // MONGO DB
        $this->_MONGO_DB_NAME='<?php echo ucfirst($f->projectName); ?>';

        // APN
        $this->_APN_PASS_PHRASE='donkeys_also_can_be_good_citizens';
        $this->_APN_PORT=2195;

    }

    protected function _getPagesRouteAliases () {
        $mapping = array(
        );
        return new RoutesAliases($mapping);
    }

    protected function _getEndPointsRouteAliases () {
        $mapping = array(
            '/user/login' => 'Auth',// Will use the HTTP method (POST,GET,PUT,DELETE)
            '/user/logout' => array('Auth','DELETE'), // Will call explicitly DELETE
<?php
$history=array();
/* @var $d ProjectRepresentation */
/* @var $action ActionRepresentation */
foreach ($d->actions as $action ) {
    $path=$action->path;
    $path=ltrim($path,'/');
    $classNameWithoutPrefix=ucfirst(substr($action->class,strlen($d->classPrefix)));
    $string= '\'/'.lcfirst($path).'\'=>\''.$classNameWithoutPrefix.'\',';
    if(!in_array($string,$history)){
        $history[]=$string;
        echoIndent($string.cr(),3);
    }
}
?>
        );
        return new RoutesAliases($mapping);
    }
}
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>