<?php
include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */


if (isset ( $f )) {
    $f->fileName = 'BartlebyCommonsConfiguration.php';
    $f->package = 'php/_generated/';
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

// SHARED CONFIGURATION BETWEEN THE API & MAIN PAGES

namespace Bartleby;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoConfiguration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/RoutesAliases.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterEntityPasswordRemover.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterCollectionOfEntityPasswordsRemover.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterHookByClosure.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/KeyPath.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Stages.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Mode.php';


use Bartleby\Core\Mode;
use Bartleby\Core\RoutesAliases;
use Bartleby\Core\Stages;
use Bartleby\Mongo\MongoConfiguration;
use Bartleby\Core\KeyPath;
use Bartleby\Filters\FilterCollectionOfEntityPasswordsRemover;
use Bartleby\Filters\FilterEntityPasswordRemover;
use Bartleby\Filters\FilterHookByClosure;

use \MongoClient;
use \MongoCursorException;
use \MongoDB;


class BartlebyCommonsConfiguration extends MongoConfiguration {


    /**
    * The constructor
    * @param string $executionDirectory
    * @param string $bartlebyRootDirectory
    * @param $runMode
    */
    public function __construct($executionDirectory,$bartlebyRootDirectory,$runMode = Mode::API){
        parent::__construct($executionDirectory,$bartlebyRootDirectory,$runMode);
        $this->_configureFilters();
        $this->_configurePermissions();
        $this->_configuresFixedPaths();
    }

    private function  _configuresFixedPaths(){
        // We force the resolution
        // So You can Overload the standard path and define a fixed One
        // To to so you can call `definePath`:
        // $this->definePath("ClassName", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/ClassName.php');`

        // (!) IMPORTANT
        // If you put files in the Overloads folder that extends an existing class.
        // The nameSpace of the Overload must be post fixed with \Overloads
        // Check UpdateUser for a sample.

        // Update user(s) overload for security purposes.
        $this->definePath("UpdateUser", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/UpdateUser.php');
        $this->definePath("UpdateUsers", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/UpdateUsers.php');

        // Proceed to file cleaning when a logical block is deleted
        $this->definePath("DeleteBlock", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/DeleteBlock.php');
        $this->definePath("DeleteBlocks", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/DeleteBlocks.php');

    }

    private function _configureFilters(){

        /// NEVER DISCLOSE THE PASSWORDS!
        /// We transmit crypted images of the original password
        /// We store salted derived digests on the server
        /// We remove the password on Read Operations.

        $filterReadUser=new FilterEntityPasswordRemover();
        $filterReadUser->passwordKeyPath='password';
        $this->addFilterOut('ReadUserById->call',$filterReadUser);

        $filterReadUsers=new FilterCollectionOfEntityPasswordsRemover();
        $filterReadUsers->passwordKeyPath='password';// Each entity has directly a "password" key
        $filterReadUsers->iterableCollectionKeyPath=NULL;// the response is a collection.
        $this->addFilterOut('ReadUsersByIds->call',$filterReadUsers);

        // Salt the passwords on Create and Update
        // The transmitted password is already crypted client side before transmission.
        // But we salt the password server side.

        $data=NULL;// Dummy data for the IDE

        $filterCreateUser=new FilterHookByClosure();
        $filterCreateUser->closure=function($data) {
            $password=KeyPath::valueForKeyPath($data,"user.password");
            if (isset($password)){
                // let's salt the password
                KeyPath::setValueByReferenceForKeyPath($data,"user.password",$this->salt($password));
            }
            return $data;
        };
        $this->addFilterIn('CreateUser->call',$filterCreateUser);

        $filterCreateUsers=new FilterHookByClosure();
        $filterCreateUsers->closure=function($data) {
            $password=KeyPath::valueForKeyPath($data,"user.password");
            if (isset($password)){
                // let's salt the password
                KeyPath::setValueByReferenceForKeyPath($data,"user.password",$this->salt($password));
            }
            return $data;
        };
        $this->addFilterIn('CreateUsers->call',$filterCreateUsers);

        $filterUpdateUser=new FilterHookByClosure();
        $filterUpdateUser->closure=function($data) {
            $password=KeyPath::valueForKeyPath($data,"user.password");
            if (isset($password)){
                // let's salt the password
                KeyPath::setValueByReferenceForKeyPath($data,"user.password",$this->salt($password));
            }
            return $data;
        };
        $this->addFilterIn('UpdateUser->call',$filterUpdateUser);

        $filterUpdateUsers=new FilterHookByClosure();
        $filterUpdateUsers->closure=function($data) {
            $password=KeyPath::valueForKeyPath($data,"user.password");
            if (isset($password)){
                // let's salt the password
                KeyPath::setValueByReferenceForKeyPath($data,"user.password",$this->salt($password));
            }
            return $data;
        };
        $this->addFilterIn('UpdateUsers->call',$filterUpdateUsers);
    }


    /**
    * Configure the permissions
    * By default we provide a good level of security
    */
    private function _configurePermissions(){

        $this->_permissionsRules = array(


            'NotFound->GET'=> array('level'=> PERMISSION_NO_RESTRICTION),
            'Reachable->GET'=> array('level'=> PERMISSION_NO_RESTRICTION),
            'Reachable->verify'=> array('level'=> PERMISSION_BY_IDENTIFICATION),
            'Auth->POST' => array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'LoginUser#spaceUID'),// (!) do not change
            'Auth->DELETE' => array('level'  => PERMISSION_NO_RESTRICTION), // (!)
            //SSE Time
            'SSETime->GET'=> array('level'=> PERMISSION_NO_RESTRICTION),
            // ProtectedRun
            'ProtectedRun->GET'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            // Documents stats
            'Stats->GET'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            // The configuration infos endpoint
            'Infos->GET'=>array('level' => PERMISSION_NO_RESTRICTION),
            'EntityExistsById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),

            // USERS

            'ReadUserById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'CreateUser->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'CreateUser#spaceUID'),

            'UpdateUser->call'=>array(
				'level' => PERMISSION_RESTRICTED_BY_QUERIES,
					ARRAY_OF_QUERIES =>array(
						"hasBeenCreatedByCurrentUser"=>array(
							SELECT_COLLECTION_NAME=>'users',
							WHERE_VALUE_OF_ENTITY_KEY=>'_id',
							EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

							COMPARE_WITH_OPERATOR=>'==',
							RESULT_ENTITY_KEY=>'creatorUID',
							AND_CURRENT_USERID=>true
						),
						"isCurrentUser"=>array(
							SELECT_COLLECTION_NAME=>'users',
							WHERE_VALUE_OF_ENTITY_KEY=>'_id',
							EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

							COMPARE_WITH_OPERATOR=>'==',
							RESULT_ENTITY_KEY=>'_id',
							AND_CURRENT_USERID=>true
					)
                )
            ),

            'DeleteUser->call'=>array(
                'level' => PERMISSION_RESTRICTED_BY_QUERIES,
                    ARRAY_OF_QUERIES =>array(
                        "hasBeenCreatedByCurrentUser"=>array(
							SELECT_COLLECTION_NAME=>'users',
							WHERE_VALUE_OF_ENTITY_KEY=>'_id',
							EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'userId',

							COMPARE_WITH_OPERATOR=>'==',
							RESULT_ENTITY_KEY=>'creatorUID',
							AND_CURRENT_USERID=>true
						),
                        "isCurrentUser"=>array(
							SELECT_COLLECTION_NAME=>'users',
							WHERE_VALUE_OF_ENTITY_KEY=>'_id',
							EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'userId',

							COMPARE_WITH_OPERATOR=>'==',
							RESULT_ENTITY_KEY=>'_id',
							AND_CURRENT_USERID=>true
						)
                	)
    ),

            'CreateUsers->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'ReadUsersByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'UpdateUsers->call'=>array('level' => PERMISSION_IS_BLOCKED),
            'DeleteUsers->call'=>array('level' => PERMISSION_IS_BLOCKED),
            'ReadUsersByQuery->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),

            // Special method used for Identities synchronization
            'PatchUser->POST'=>array(
                'level' => PERMISSION_RESTRICTED_BY_QUERIES,
                    ARRAY_OF_QUERIES =>array(
                        "hasBeenCreatedByCurrentUser"=>array(
                            SELECT_COLLECTION_NAME=>'users',
                            WHERE_VALUE_OF_ENTITY_KEY=>'_id',
                            EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

                            COMPARE_WITH_OPERATOR=>'==',
                            RESULT_ENTITY_KEY=>'creatorUID',
                            AND_CURRENT_USERID=>true
                        ),
                        "isCurrentUser"=>array(
                            SELECT_COLLECTION_NAME=>'users',
                            WHERE_VALUE_OF_ENTITY_KEY=>'_id',
                            EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

                            COMPARE_WITH_OPERATOR=>'==',
                            RESULT_ENTITY_KEY=>'_id',
                            AND_CURRENT_USERID=>true
                        )
                    )
            ),

            // Locker

			/*
				1# A distant locker can created and verifyed using a signed request (PERMISSION_BY_TOKEN)
     			2# A Locker can be "Created Updated Deleted" only by its creator. Locker.creatorUID
     			3# A locker cannot be read distantly but only verifyed
     			4# On successful verification the locker is returned with its cake :)
			*/

			'VerifyLocker->POST' => array('level' => PERMISSION_BY_IDENTIFICATION),
			'CreateLocker->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'CreateLockers->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
			'UpdateLocker->call'=>array(
				'level' => PERMISSION_RESTRICTED_BY_QUERIES,
				ARRAY_OF_QUERIES =>array(
					"hasBeenCreatedByCurrentUser"=>array(
						SELECT_COLLECTION_NAME=>'lockers',
						WHERE_VALUE_OF_ENTITY_KEY=>'_id',
						EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

						COMPARE_WITH_OPERATOR=>'==',
						RESULT_ENTITY_KEY=>'creatorUID',
						AND_CURRENT_USERID=>true
					)
				)
			),
			'DeleteLocker->call'=>array(
				'level' => PERMISSION_RESTRICTED_BY_QUERIES,
				ARRAY_OF_QUERIES =>array(
					"hasBeenCreatedByCurrentUser"=>array(
						SELECT_COLLECTION_NAME=>'lockers',
						WHERE_VALUE_OF_ENTITY_KEY=>'_id',
						EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'lockerId',

						COMPARE_WITH_OPERATOR=>'==',
						RESULT_ENTITY_KEY=>'creatorUID',
						AND_CURRENT_USERID=>true
					)
				)
			),
			'ReadLockerById->call'=>array('level' => PERMISSION_IS_BLOCKED),
			'ReadLockersByIds->call'=>array('level' => PERMISSION_IS_BLOCKED),
			'UpdateLockers->call'=>array('level' => PERMISSION_IS_BLOCKED),
			'DeleteLockers->call'=>array('level' => PERMISSION_IS_BLOCKED),
			'ReadLockersByQuery->call'=>array('level' => PERMISSION_IS_BLOCKED),

            // TRIGGERS
            // Deletion of triggers is impossible.

            'SSETriggers->GET'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'TriggersAfterIndex->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'TriggerForIndexes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'TriggersByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),


            // Import export special Endpoints

            //'Import->GET'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            //'Export->GET'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY)

            'Import->GET'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),// TEMP THE ACL SHOULD BE REQUALIFIED (!)
            'Export->GET'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),// TEMP THE ACL SHOULD BE REQUALIFIED (!)

            // This is used to confirm the users owns really that phone number.
            'RelayActivationCode->POST' => array('level' => PERMISSION_BY_IDENTIFICATION),

            // The Base ACL relies on Identification
            // But there is a business logic
            // We Will verify the  consistency of the current user ID  VS the Locker User UID
            // uses user PhoneNumber to send the locker "activation" code. to be used by the verification
            'GetActivationCode->GET' => array('level' => PERMISSION_BY_IDENTIFICATION),


            // BSFS ACL TO Be refined

            'UploadBlock->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'DownloadBlock->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),

            'ReadBlockById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'CreateBlock->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'UpdateBlock->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'DeleteBlock->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'CreateBlocks->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'ReadBlocksByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'UpdateBlocks->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'DeleteBlocks->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'ReadBlocksByQuery->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'ReadBoxById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'CreateBox->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'UpdateBox->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'DeleteBox->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'CreateBoxes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'ReadBoxesByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'UpdateBoxes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'DeleteBoxes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'ReadBoxesByQuery->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'ReadNodeById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'CreateNode->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'UpdateNode->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'DeleteNode->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'CreateNodes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'ReadNodesByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'UpdateNodes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'DeleteNodes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION),
            'ReadNodesByQuery->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION)


<?php
echoIndent("/*",2);
$permissionHistory=array();
/* @var $d ProjectRepresentation */
/* @var $action ActionRepresentation */


while ($d->iterateOnActions() ) {

    $action=$d->getAction();
    $shouldBeExcluded=false;
    foreach ($h->excludePath as $pathToExclude ) {
        if(strpos($action->class.'.php',$pathToExclude)!==false){
            $shouldBeExcluded=true;
        }
    }
    if (isset($excludeActionsWith)) {
        foreach ($excludeActionsWith as $actionTobeExcluded ) {
            if (strpos($action->class, $actionTobeExcluded) !== false) {
                $shouldBeExcluded = true;
            }
        }
    }

    if($shouldBeExcluded==true){
        continue;
    }

    $path=$action->path;
    $path=ltrim($path,'/');
    $classNameWithoutPrefix=ucfirst(substr($action->class,strlen($d->classPrefix)));


    //$string= "'".$classNameWithoutPrefix."->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'$classNameWithoutPrefix#rUID')";
    $string= "'".$classNameWithoutPrefix."->call'=>array('level' => PERMISSION_BY_IDENTIFICATION_AND_ACTIVATION)";

    if(!$d->lastAction()){
        $string.=',';
    }
    if(!in_array($string,$permissionHistory)){
        $permissionHistory[]=$string;
        echoIndent($string,3);
    }
}
echoIndent("*/",2);
?>      );
    }

    /**
    * Setups a returns the commons Routes aliases
    * @return RoutesAliases
    */
    protected function _getEndPointsRouteAliases () {
        $mapping = array(
            'POST:/user/login' => array('Auth','POST'),
            'POST:/user/logout' => array('Auth','DELETE'), // Will call explicitly DELETE (equivalent to explicit call of DELETE login)
            'GET:/verify/credentials' => array('Reachable','verify'),
            'POST:/locker/verify' => array('VerifyLocker','POST'),
            'GET:/{spaceUID}/triggers/after/{lastIndex}' => array('TriggersAfterIndex','call'),// Multi route test
            'GET:/triggers/after/{lastIndex}' => array('TriggersAfterIndex','call'),
            'GET:/triggers/'=> array('TriggerForIndexes','call'), // Triggers for indexes
            'GET:/export' => array('Export','GET'),
            'GET:/exists/{id}' => array('EntityExistsById','call'),
            'GET:/block/{id}' => array('DownloadBlock','call'),
            'POST:/block/{id}' => array('UploadBlock','call'),
            'POST:/patchUser/'=>array('PatchUser','POST'),
            'POST:/relay/'=>array('RelayActivationCode','POST'),
            'GET:/activationCode/'=>array('GetActivationCode','GET'),
            // --
<?php
$history=array();
/* @var $d ProjectRepresentation */
/* @var $action ActionRepresentation */

while ($d->iterateOnActions() ) {

    $action=$d->getAction();
    $shouldBeExcluded=false;
    foreach ($h->excludePath as $pathToExclude ) {
        if(strpos($action->class.'.php',$pathToExclude)!==false){
            $shouldBeExcluded=true;
        }
    }
    if (isset($excludeActionsWith)) {
        foreach ($excludeActionsWith as $actionTobeExcluded ) {
            if (strpos($action->class, $actionTobeExcluded) !== false) {
                $shouldBeExcluded = true;
            }
        }
    }

    if($shouldBeExcluded==true){
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
        echoIndent($string,3);
    }
}
?>
        );
        return new RoutesAliases($mapping);
    }


    /**
    * Returns the collection name list
    * @return array
    */
    public function getCollectionsNameList(){
        $list=parent::getCollectionsNameList();
        $list [] = "triggers";
<?php
/* @var $d ProjectRepresentation */
/* @var $entity EntityRepresentation */
foreach ($d->entities as $entity ) {
    $name=$entity->name;
    if(isset($prefix)){
        $name=str_replace($prefix,'',$name);
    }
    $shouldBeExcluded=false;
    if ($entity->isUnManagedModel()){
        $shouldBeExcluded=true;
    }
    if (isset($excludeActionsWith)) {
        foreach ($excludeActionsWith as $actionTobeExcluded ) {
            if (strpos(strtolower($name), strtolower($actionTobeExcluded)) !== false) {
                $shouldBeExcluded = true;
            }
        }
    }
    if ($shouldBeExcluded==true){
        continue;
    }
    $pluralized=lcfirst(Pluralization::pluralize($name));
    echoIndent('$list [] = "'.$pluralized.'";',2);
}
    ?>
        return $list;
    }

}
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>