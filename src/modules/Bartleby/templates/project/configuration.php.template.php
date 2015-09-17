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

require_once BARTLEBY_ROOT_FOLDER . 'mongo/MongoConfiguration.php';
require_once BARTLEBY_ROOT_FOLDER . 'core/RoutesAliases.php';

use Bartleby\Core\RoutesAliases;
use Bartleby\Core\Stages;
use Bartleby\Mongo\MongoConfiguration;


class GeneratedConfiguration extends MongoConfiguration {

    /**
     * @param $baseDirectory
     * @param $bartlebysRootDirectory
     */
    public function __construct($executionDirectory,$bartlebyRootDirectory){
        parent::__construct($executionDirectory,$bartlebyRootDirectory);

        $this->_STAGE=Stages::DEVELOPMENT;
        $this->_VERSION='<?php echo $d->apiVersion; ?>';
        $this->_SALT='SALT TO BE SET';

        // MONGO DB
        $this->_MONGO_DB_NAME='<?php echo ucfirst($f->projectName); ?>';

        // APN
        $this->_APN_PASS_PHRASE='donkeys_also_can_be_good_citizens';
        $this->_APN_PORT=2195;

        $this->_configurePermissions();

    }

    private function _configurePermissions(){

        $this->_permissionsRules = array(
        'Reachable->GET'=> array('level'=> PERMISSION_NO_RESTRICTION),
        'Auth->GET' => array('level' => PERMISSION_NO_RESTRICTION),// (!) do not change
        'Auth->DELETE' => array('level' => PERMISSION_NO_RESTRICTION), // (!) do not change  to allow logout even if there is no valid cookie.
        'SSETime->GET'=> array('level'=> PERMISSION_IDENTIFIED_BY_COOKIE),
<?php
$permissionHistory=array();
/* @var $d ProjectRepresentation */
/* @var $action ActionRepresentation */
while ($d->iterateOnActions() ) {
    $action=$d->getAction();
    $shouldBeExlcuded=false;
    foreach ($h->excludePath as $pathToExclude ) {
        if(strpos($action->class.'.php',$pathToExclude)!==false){
            $shouldBeExlcuded=true;
        }
    }
    if($shouldBeExlcuded==true){
        continue;
    }

    $path=$action->path;
    $path=ltrim($path,'/');
    $classNameWithoutPrefix=ucfirst(substr($action->class,strlen($d->classPrefix)));


    $string= "'".$classNameWithoutPrefix."->call'=>array('level' => PERMISSION_IDENTIFIED_BY_COOKIE)";
    if(!$d->lastAction()){
        $string.=',';
    }
    if(!in_array($string,$permissionHistory)){
        $permissionHistory[]=$string;
        echoIndentCR($string,2);
    }
}
?>      );
    }


    protected function _getPagesRouteAliases () {
        $mapping = array(
        );
        return new RoutesAliases($mapping);
    }

/*
    In your Configuration you can override the aliases.

    protected function _getPagesRouteAliases () {
        $routes=parent::_getEndPointsRouteAliases();
        $mapping = array(
        ''=>'Start',
        'time'=>'Time',
        '*' => 'NotFound'
        );
        $routes->addAliasesToMapping($mapping);
    return $routes;
    }

    protected function _getEndPointsRouteAliases () {
        $routes=parent::_getEndPointsRouteAliases();
        $mapping = array(
        'POST:/user/{userId}/comments'=>array('CommentsByUser','POST_method_for_demo'),
        'DELETE:/user/{userId}/comments'=>array('CommentsByUser','DELETE'),
        'time'=>'SSETime' // A server sent event sample
        );
        $routes->addAliasesToMapping($mapping);
        return $routes;
    }


*/

    protected function _getEndPointsRouteAliases () {
        $mapping = array(
            '/user/login' => 'Auth',// Will can use any the HTTP method (GET)
            '/user/logout' => array('Auth','DELETE'), // Will call explicitly DELETE (equivalent to explicit call of DELETE login)
<?php
$history=array();
/* @var $d ProjectRepresentation */
/* @var $action ActionRepresentation */

while ($d->iterateOnActions() ) {
    $action=$d->getAction();
    $shouldBeExlcuded=false;
    foreach ($h->excludePath as $pathToExclude ) {
        if(strpos($action->class.'.php',$pathToExclude)!==false){
            $shouldBeExlcuded=true;
        }
    }
    if($shouldBeExlcuded==true){
        continue;
    }
    $path=$action->path;
    $path=ltrim($path,'/');
    $classNameWithoutPrefix=ucfirst(substr($action->class,strlen($d->classPrefix)));
    $string= '\''.$action->httpMethod.':/'.lcfirst($path).'\'=>array(\''.$classNameWithoutPrefix.'\',\'call\')';
    if(!$d->lastAction()){
        $string.=',';
    }
    if(!in_array($string,$history)){
        $history[]=$string;
        echoIndentCR($string,3);
    }
}
?>
        );
        return new RoutesAliases($mapping);
    }
}
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>