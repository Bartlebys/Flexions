<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation*/

if (isset ( $f )) {
    $classNameWithoutPrefix=$h->ucFirstRemovePrefixFromString($d->class);
    $callDataClassName=$classNameWithoutPrefix.'CallData';
    $f->fileName = $classNameWithoutPrefix.'.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'generated/endpoints/';
}


// Exclusion

$shouldBeExcluded = false;
$exclusion = array();
$exclusionName = str_replace($h->classPrefix, '', $d->class);

if (isset($excludeActionsWith)) {
    $exclusion = $excludeActionsWith;
}
foreach ($exclusion as $exclusionString) {
    if (strpos($exclusionName, $exclusionString) !== false) {
        return NULL; // We return null
    }
}



/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'mongo/MongoCallDataRawWrapper.php';

use Bartleby\Mongo\MongoEndPoint;
use bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Core\JsonResponse;
use \MongoCollection;

class  <?php echo $callDataClassName; ?> extends MongoCallDataRawWrapper {
<?php
$name=null;
$parameterIsAcollection=false;
while ($d->iterateOnParameters() === true) {
    $parameter = $d->getParameter();
    $name=$parameter->name;
    $typeOfProp=$parameter->type;
    $o=FlexionsTypes::OBJECT;
    $c=FlexionsTypes::COLLECTION;
    $parameterIsAcollection=($typeOfProp===$c);
    if (($typeOfProp===$o)||($typeOfProp===$c)) {
        $typeOfProp = $h->ucFirstRemovePrefixFromString($parameter->instanceOf);
        if($typeOfProp==$c){
            $typeOfProp=' array of '.$typeOfProp;
        }
    }

    if($parameter->type==FlexionsTypes::ENUM){
        $enumTypeName=ucfirst($name);
        $typeOfProp=$parameter->instanceOf.' '.$typeOfProp;
        echoIndentCR('// Enumeration of possibles values of '.$name, 1);
        foreach ($parameter->enumerations as $element) {
            if($parameter->instanceOf==FlexionsTypes::STRING){
                echoIndentCR('const ' .$enumTypeName.'_'.ucfirst($element).' = "'.$element.'";' ,1);
            }else{
                echoIndentCR('const ' .$enumTypeName.'_'.ucfirst($element).' = '.$element.';', 1);
            }
        }
    }
    if(isset($parameter->description) && strlen($parameter->description)>1){
        echoIndentCR('/* '.$parameter->description.' */',1);
    }

    echoIndentCR('const '.$name.'=\''.$name.'\';',1);
}
?>
}

 class  <?php echo $classNameWithoutPrefix; ?> extends MongoEndPoint {
<?php


// We use the last and unique parameter for CRUD endpoints (ids based)
// If there is no parameters it means it is a generic Get endpoint based on request.

$lastParameterName=isset($name)?$name:'NO_PARAMETERS';




$parameterIsNotAcollection=(!$parameterIsAcollection);

$successP = $d->getSuccessResponse();
if ($successP->type == FlexionsTypes::COLLECTION) {
    $resultIsNotACollection = false;
}else{
    $resultIsNotACollection=true;
}

$isGenericGETEndpoint=(strpos($d->class,'ByQuery')!==false);
$isGETByIdsEndpoint=(strpos($d->class,'ByIds')!==false);
if($isGenericGETEndpoint==false && $isGETByIdsEndpoint==false){
    $isGETByIdEndpoint=true;
}else{
    $isGETByIdEndpoint=false;
}




if($d->httpMethod=='POST') {
    if ($d->urdMode==true){

    // URD MODE

        echo('
    function call('.$callDataClassName.' $parameters) {
        $db=$this->getDb();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';@
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true,
            "upsert" => true
        );
        '.
            (
            ($parameterIsNotAcollection===true)?
                '$obj=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
         if(!isset($obj) || count($parameters->getDictionary())==0){
          return new JsonResponse(\'Invalid void object\',406);
        }
        $q = array (\'_id\' =>$obj[\'_id\']);'
                :
                '$arrayOfObject=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(!isset($arrayOfObject) || (is_array($arrayOfObject) && count($arrayOfObject)<1) ){
            return new JsonResponse(\'Invalid void array\',406);
        }'
            )
            .'
        try {
            '.(($parameterIsNotAcollection===true)?
                '$r = $collection->update ($q, $obj,$options );
            if ($r[\'ok\']==1) {
                return new JsonResponse(VOID_RESPONSE,200);
            } else {
                return new JsonResponse($r,412);
            }'
                :
                'foreach ($arrayOfObject as $obj){
                $q = array (\'_id\' => $obj[\'_id\']);
                $r = $collection->update( $q, $obj,$options);
                if ($r[\'ok\']==1) {
                    if (array_key_exists(\'updatedExisting\', $r)) {
                        $existed = $r[\'updatedExisting\'];
                        if ($existed == false) {
                            return new JsonResponse($q,404);
                        }
                    }
                }else{
                    return new JsonResponse($q,412);
                }
             }
            return new JsonResponse(VOID_RESPONSE,200);'
            ).'

        } catch ( \Exception $e ) {
            return new JsonResponse( array (\'code\'=>$e->getCode(),
                                            \'message\'=>$e->getMessage(),
                                            \'file\'=>$e->getFile(),
                                            \'line\'=>$e->getLine(),
                                            \'trace\'=>$e->getTraceAsString()
                                            ),
                                            417
                                    );
        }
        return new JsonResponse(VOID_RESPONSE,200);
     }'
        );
    }else {

        // CRUD MODE

        echo('
    function call(' . $callDataClassName . ' $parameters) {
        $db=$this->getDb();
        /* @var \MongoCollection */
        $collection = $db->' . $d->collectionName . ';
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true
        );
        ' . (($parameterIsNotAcollection === true) ? '$obj=$parameters->getValueForKey(' . $callDataClassName . '::' . $lastParameterName . ');' : '$obj=$parameters->getValueForKey(' . $callDataClassName . '::' . $lastParameterName . ');') . '
        if(!isset($obj) || count($parameters->getDictionary())==0){
          return new JsonResponse(\'Void submission\',406);
        }
        try {
            ' . (($parameterIsNotAcollection === true) ? '$r = $collection->insert ( $obj,$options );' : '$r = $collection->batchInsert( $obj,$options );') . '
             if ($r[\'ok\']==1) {
                return new JsonResponse(VOID_RESPONSE,201);
            } else {
                return new JsonResponse($r,412);
            }
        } catch ( \Exception $e ) {
            return new JsonResponse( array (\'code\'=>$e->getCode(),
                                            \'message\'=>$e->getMessage(),
                                            \'file\'=>$e->getFile(),
                                            \'line\'=>$e->getLine(),
                                            \'trace\'=>$e->getTraceAsString()
                                            ),
                                            417
                                    );
        }
        return new JsonResponse(VOID_RESPONSE,200);
     }');
    }
}elseif ( $d->httpMethod=='GET' || $isGenericGETEndpoint===true ){


    echo('
     function call('.$callDataClassName.' $parameters) {
        $db=$this->getDb();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';'.cr());

    if ($isGETByIdEndpoint===true){
        //echo('// $isGETByIdEndpoint');
        echo(
'         $q = array (\'_id\' =>$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.'));
        if (isset($q)&& count($q)>0){
        }else{
            return new JsonResponse(\'Query is void\',412);
        }');
    }elseif ($isGETByIdsEndpoint===true){
        echo(
'        $ids=$parameters->getValueForKey('.$callDataClassName.'::ids);
        $f=$parameters->getValueForKey('.$callDataClassName.'::result_fields);
        if(isset ($ids) && count($ids)){
            $q = array( \'_id\'=>array( \'$in\' => $ids ));
        }else{
            return new JsonResponse(VOID_RESPONSE,204);
        }'
    );
    } elseif ($isGenericGETEndpoint===true){
        echo(
'      $q = $parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
       if(!isset($q)){
           return new JsonResponse(NULL,417);
       }
       $f=$parameters->getValueForKey('.$callDataClassName.'::result_fields);');
    }


    echo('
        try {'.
    (

        ($resultIsNotACollection===true)?

            // RESULT IS NOT A COLLECTION

            '
            $r = $collection->findOne($q);
            if (isset($r)) {
                return new JsonResponse($r,200);
            } else {
                return new JsonResponse(NULL,404);
            }'

            :
            // RESULT IS A COLLECTION

            '
           $r=array();
           if(isset($f)){
                $cursor = $collection->find( $q , $f );
           }else{
                $cursor = $collection->find($q);
           }
           // Sort ?
           $s=$parameters->getCastedDictionaryForKey('.$callDataClassName.'::sort);
           if (isset($s) && count($s)>0){
              $cursor=$cursor->sort($s);
           }
           if ($cursor->count ( TRUE ) > 0) {
			foreach ( $cursor as $obj ) {
				$r[] = $obj;
			}
		   }

            if (count($r)>0 ) {
                return new JsonResponse($r,200);
            } else {
                return new JsonResponse(NULL,404);
            }'
    ) .'
       } catch ( \Exception $e ) {
            return new JsonResponse( array (\'code\'=>$e->getCode(),
                                            \'message\'=>$e->getMessage(),
                                            \'file\'=>$e->getFile(),
                                            \'line\'=>$e->getLine(),
                                            \'trace\'=>$e->getTraceAsString()
                                            ),
                                            417
                                    );
        }
        return new JsonResponse(VOID_RESPONSE,200);
     }');



}elseif ($d->httpMethod=='PUT'){
    echo('
    function call('.$callDataClassName.' $parameters) {
        $db=$this->getDb();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true
        );
        '.
        (
        ($parameterIsNotAcollection===true)?
        '$obj=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
         if(!isset($obj) || count($parameters->getDictionary())==0){
          return new JsonResponse(\'Invalid void object\',406);
        }
        $q = array (\'_id\' =>$obj[\'_id\']);'
            :
            '$arrayOfObject=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(!isset($arrayOfObject) || (is_array($arrayOfObject) && count($arrayOfObject)<1) ){
            return new JsonResponse(\'Invalid void array\',406);
        }'
        )
        .'
        try {
            '.(($parameterIsNotAcollection===true)?
            '$r = $collection->update ($q, $obj,$options );
            if ($r[\'ok\']==1) {
              if(array_key_exists(\'updatedExisting\',$r)){
                    $existed=$r[\'updatedExisting\'];
                    if($existed==true){
                        return new JsonResponse(VOID_RESPONSE,200);
                    }else{
                        return new JsonResponse(NULL,404);
                    }
                }
                return new JsonResponse(VOID_RESPONSE,200);
            } else {
                return new JsonResponse($r,412);
            }'
            :
            'foreach ($arrayOfObject as $obj){
                $q = array (\'_id\' => $obj[\'_id\']);
                $r = $collection->update( $q, $obj,$options);
                if ($r[\'ok\']==1) {
                    if (array_key_exists(\'updatedExisting\', $r)) {
                        $existed = $r[\'updatedExisting\'];
                        if ($existed == false) {
                            return new JsonResponse($q,404);
                        }
                    }
                }else{
                    return new JsonResponse($q,412);
                }
             }
            return new JsonResponse(VOID_RESPONSE,200);'
        ).'

        } catch ( \Exception $e ) {
            return new JsonResponse( array (\'code\'=>$e->getCode(),
                                            \'message\'=>$e->getMessage(),
                                            \'file\'=>$e->getFile(),
                                            \'line\'=>$e->getLine(),
                                            \'trace\'=>$e->getTraceAsString()
                                            ),
                                            417
                                    );
        }
        return new JsonResponse(VOID_RESPONSE,200);
     }'
    );


}elseif ($d->httpMethod=='DELETE'){
    // DELETE
    echo('
    function call('.$callDataClassName.' $parameters) {
        $db=$this->getDb();
        /* @var \MongoCollection */
        $collection = $db->'.$d->collectionName.';
        // Default write policy
        $options = array (
            "w" => 1,
            "j" => true
        );
        '.
    (
        ($parameterIsNotAcollection===true)?

            '$q = array (\'_id\' =>$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.'));'

            :

            '$ids=$parameters->getValueForKey('.$callDataClassName.'::'.$lastParameterName.');
        if(isset ($ids) && count($ids)>0){
            $q = array( \'_id\' =>array( \'$in\' => $ids ));
        }else{
            return new JsonResponse(VOID_RESPONSE,204);
        }'

    )
        .'
        try {
            $r = $collection->remove ( $q,$options );
             if ($r[\'ok\']==1) {
                 if($r[\'n\']>=1){
                     return new JsonResponse(VOID_RESPONSE,200);
                 }else{
                     return new JsonResponse(NULL,404);
                 }
            } else {
                return new JsonResponse($r,412);
            }
        } catch ( \Exception $e ) {
            return new JsonResponse( array (\'code\'=>$e->getCode(),
                                            \'message\'=>$e->getMessage(),
                                            \'file\'=>$e->getFile(),
                                            \'line\'=>$e->getLine(),
                                            \'trace\'=>$e->getTraceAsString()
                                            ),
                                            417
                                    );
        }
        return new JsonResponse(VOID_RESPONSE,200);
     }'
    );
}else{
    echo('// STRANGE METHOD '.$d->httpMethod);
}
?>

 }

<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>