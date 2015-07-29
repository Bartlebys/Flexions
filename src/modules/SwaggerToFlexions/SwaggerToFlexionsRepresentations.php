<?php

require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_MODULES_DIR . 'SwaggerToFlexions/ISwaggerDelegate.php';

if (!defined('SWAGGER_VERSION')) {
    define('SWAGGER_VERSION', 'swagger');
    define('SWAGGER_INFO', 'info');
    define('SWAGGER_TITLE', 'title');
    define('SWAGGER_HOST', 'host');
    define('SWAGGER_BASE_PATH', 'basePath');
    define('SWAGGER_TAGS', 'tags');
    define('SWAGGER_SCHEMES', 'schemes');
    define('SWAGGER_PATHS', 'paths');
    define('SWAGGER_SECURITY_DEFINITIONS', 'securityDefinitions');
    define('SWAGGER_DEFINITIONS', 'definitions');
    define('SWAGGER_EXTERNAL_DOCS', 'externalDocs');
    define('SWAGGER_TYPE', 'type');
    define('SWAGGER_ENUM', 'enum');
    define('SWAGGER_OBJECT', 'object');
    define('SWAGGER_PROPERTIES', 'properties');
    define('SWAGGER_DESCRIPTION', 'description');
    define('SWAGGER_FORMAT', 'format');
    define('SWAGGER_ITEMS', 'items');
    define('SWAGGER_REF', '$ref');
    define ('SWAGGER_ALL_OF','allOf');// Inheritance and composition
    define('SWAGGER_OPERATION_ID', 'operationId');
    define('SWAGGER_PARAMETERS', 'parameters');
    define('SWAGGER_NAME', 'name');
    define('SWAGGER_SCHEMA', 'schema');
    define('SWAGGER_REQUIRED', 'required');
    define('SWAGGER_RESPONSES', 'responses');
    define('SWAGGER_OAUTH_AUTHORIZATION_URL','authorizationUrl');
    define('SWAGGER_OAUTH_SCOPES','scopes');
    define('SWAGGER_IN','in');
    define('SWAGGER_SECURITY','security');
    //define ('SWAGGER_HEADERS','headers');
}




/**
 * We support partially SWAGGER 2.0 but
 * enough to modelize and generate APIS and Entities with Flexions.
 *
 * IMPORTANT to support login and logout generation you must include the signature in the path
 * And add a security key that maps to the security definitions.
 *
 * SWAGGER complete specs are available at :
 * https://github.com/swagger-api/swagger-spec/blob/master/versions/2.0.md
 *
 * For example we do not implement :
 * - "object" + "additionalProperties" (Swagger usage of additionalProperties is not compliant with http://json-schema.org/example2.html )
 * - "consumes" and "produces" (as we generate both client and servers the generation template can decide to use JSON or XML or anything else)
 * - Resolution of $ref (we extract the entity from the reference)
 *
 * We prefer to use a fully typed approach so you should define (in definitions) and $ref an entity as much as possible.
 *
 * Class SwaggerToFlexionsRepresentations
 */
class SwaggerToFlexionsRepresentations {


    /**
     * @param $descriptorFilePath
     * @param string $nativePrefix
     * @param ISwaggerDelegate|null $delegate
     * @param array $signInSignature
     * @param array $signOutSignature
     * @return ProjectRepresentation|void
     * @throws Exception
     */
    function projectRepresentationFromSwaggerJson($descriptorFilePath, $nativePrefix = "", ISwaggerDelegate $delegate=null,array $signInSignature=array(),array $signOutSignature=array()) {

        if (!isset($delegate)) {
            fLog("projectRepresentationFromSwaggerJson.projectRepresentationFromSwaggerJson() module requires an ISwaggerDelegate", true);
            return;
        }

        fLog("Invoking projectRepresentationFromSwaggerJson.projectRepresentationFromSwaggerJson() on " . $descriptorFilePath . cr() . cr(), true);

        // #1 Create the ProjectRepresentation

        $r = new ProjectRepresentation ();
        $r->classPrefix = $nativePrefix;
        $r->name = 'NO_NAME';
        $s = file_get_contents($descriptorFilePath);
        $json = json_decode($s, true);
        $r->metadata = $json;// We store the raw descriptor as a metadata

        if (array_key_exists(SWAGGER_INFO, $json)) {
            if (array_key_exists(SWAGGER_INFO, $json)) {
                $nameOfProject = $json[SWAGGER_INFO][SWAGGER_TITLE];
                $nameOfProject = str_replace(' ', '_', $nameOfProject);
                $r->name = $nameOfProject;
            }
        }

        if ($json[SWAGGER_VERSION] = '2.0') {

            $r->baseUrl = $json[SWAGGER_SCHEMES][0] . '://' . $json[SWAGGER_HOST] . $json[SWAGGER_BASE_PATH];
            $r->apiVersion = rtrim($json[SWAGGER_BASE_PATH], '/');


            // Store a reference of PermissionRepresentation to be cloned in each action.
            if (array_key_exists(SWAGGER_SECURITY_DEFINITIONS, $json)) {
                $securityDefinitions = $json[SWAGGER_SECURITY_DEFINITIONS];
                foreach ($securityDefinitions as $name => $descriptor) {
                   if(array_key_exists(SWAGGER_TYPE, $descriptor)){
                       $type=strtolower($descriptor[SWAGGER_TYPE]);
                       /*@var $p PermissionRepresentation */
                       $p = null;
                       if($type=="oauth2"){
                           $p=new PermissionRepresentationOauth();
                           $p->setPermissionName($name);
                           if(array_key_exists(SWAGGER_OAUTH_AUTHORIZATION_URL, $descriptor)){
                             $p->authorizationUrl=$descriptor[SWAGGER_OAUTH_AUTHORIZATION_URL];
                           }
                           if(array_key_exists(SWAGGER_OAUTH_SCOPES, $descriptor)){
                               $scopes=$descriptor[SWAGGER_OAUTH_SCOPES];
                               foreach ($scopes as $scopeName=>$description) {
                                   if(isset($scopeName) && isset($description)){
                                       $p->addScope(array($scopeName=>$description));
                                   }else{
                                       throw new Exception('Invalid scope' . json_encode($scopes),90);
                                   }
                                }
                           }
                       }
                       if($type=="apikey"){
                           $p=new PermissionRepresentationWithAccessRights();
                           $p->setPermissionName($name);
                           if(array_key_exists(SWAGGER_IN, $descriptor)){
                               $in=strtolower($descriptor[SWAGGER_IN]);
                               if($in=='header'){
                                   $p->setLocation(PermissionLocation::IN_HEADERS);
                               }else{
                                   $p->setLocation(PermissionLocation::IN_PARAMETERS);
                               }
                           }

                       }
                       if(isset ($p)){
                           $this->_permissionsByName[$name] = $p;
                       }else{
                           throw new Exception('Unsupported PermissionRepresentation type :' . $type,100);
                       }

                    }else{
                       throw new Exception('Malformed security definition name:' . $name.' descriptor as a json:'.json_encode($descriptor),101);
                   }


                }
            }



            // #2 Extract the entities EntityRepresentation
            //from definitions :
            if (array_key_exists(SWAGGER_DEFINITIONS, $json)) {
                $definitions = $json[SWAGGER_DEFINITIONS];
                foreach ($definitions as $entityName => $descriptor) {
                    $e = new EntityRepresentation();
                    $e->name = $nativePrefix . ucfirst($entityName);

                    $properties=array();
                    if (array_key_exists(SWAGGER_TYPE, $descriptor)) {
                        $entityType = $descriptor[SWAGGER_TYPE];
                        if ($entityType === SWAGGER_OBJECT) {
                            if (array_key_exists(SWAGGER_PROPERTIES, $descriptor)) {
                                $properties = $descriptor[SWAGGER_PROPERTIES];
                            }
                        }
                    } else {
                        // Entity is not a simple object
                        if(array_key_exists(SWAGGER_ALL_OF,$descriptor)){
                            $allOF=$descriptor[SWAGGER_ALL_OF];

                            $refs=array();
                            foreach ($allOF as $currentItem) {
                                if (is_array($currentItem)){
                                    if(array_key_exists(SWAGGER_REF, $currentItem)){
                                        $parentRef=$currentItem[SWAGGER_REF];
                                        $refs[]=$parentRef;
                                    }
                                    if(array_key_exists(SWAGGER_PROPERTIES, $currentItem)){
                                        $properties=$currentItem[SWAGGER_PROPERTIES];
                                    }
                                }
                            }
                            if(count($refs)==1){
                                // Inheritance support
                                $e->instanceOf=$this->typeFromRef($parentRef,$nativePrefix);
                            }else if( count($refs)>1){
                                // @todo composition
                                // Requires $ref resolution
                            }

                            if(array_key_exists(SWAGGER_PROPERTIES,$allOF)){
                                $properties=$allOF[SWAGGER_PROPERTIES];
                            }
                        }
                    }
                    // Parse the properties
                    foreach ($properties as $propertyName => $propertyValue) {
                        $e->properties[] = $this->_extractPropertyFrom($propertyName, $propertyValue, $nativePrefix);
                    }
                    $r->entities[] = $e;
                }
            }

            //#3 Extract the actions ActionRepresentation
            if (array_key_exists(SWAGGER_PATHS, $json)) {
                $paths = $json[SWAGGER_PATHS];
                foreach ($paths as $path => $pathDescriptor) {

                    foreach ($pathDescriptor as $method => $methodPathDescriptor) {
                        $className = '';
                        if (array_key_exists(SWAGGER_OPERATION_ID, $methodPathDescriptor)) {
                            $className = $nativePrefix . ucfirst($methodPathDescriptor[SWAGGER_OPERATION_ID]);
                        } else {
                            $className = $nativePrefix . $this->_classNameForPath($path);
                        }
                        $action = new ActionRepresentation();
                        $action->class = $className;
                        $action->path = $path;
                        $action->httpMethod = strtoupper($method);

                        if (array_key_exists(SWAGGER_PARAMETERS, $methodPathDescriptor)) {
                            $parameters = $methodPathDescriptor[SWAGGER_PARAMETERS];
                            foreach ($parameters as $parameter) {
                                if (array_key_exists(SWAGGER_NAME, $parameter)) {
                                    $property = $this->_extractPropertyFrom($parameter[SWAGGER_NAME], $parameter, $nativePrefix);
                                    $action->parameters[] = $property;
                                }
                            }
                        }

                        if (array_key_exists(SWAGGER_RESPONSES, $methodPathDescriptor)) {
                            $responses = $methodPathDescriptor[SWAGGER_RESPONSES];
                            foreach ($responses as $name => $response) {
                                if ($name=="default"){
                                    $name="200";// We consider default as a succes.
                                }
                                $property = $this->_extractPropertyFrom("$name", $response, $nativePrefix);
                                $action->responses[] = $property;
                            }
                        }

                        // security

                        if (array_key_exists(SWAGGER_SECURITY, $methodPathDescriptor)) {
                            $security = $methodPathDescriptor[SWAGGER_SECURITY];
                            foreach ($security as $collection) {
                                foreach ($collection as $securityItemName=>$securityItem) {
                                    // The security context is extracted using the action name semantics.


                                    $actionPath=strtolower($action->class);
                                    $actionPath=str_replace('_','',$actionPath);
                                    $actionPath=str_replace('-','',$actionPath);

                                    $containsSignInSignature=false;
                                    foreach ($signInSignature as $signature ) {
                                        if((strpos($actionPath,$signature)!==false)){
                                            $containsSignInSignature=true;
                                        }
                                    }

                                    $containsSignOutSignature=false;
                                    foreach ($signOutSignature as $signature ) {
                                        if((strpos($actionPath,$signature)!==false)){
                                            $containsSignOutSignature=true;
                                        }
                                    }

                                    if($containsSignInSignature==true){
                                        $action->security=$this->getContextPermissionByName($securityItemName,RelationToPermission::PROVIDES);
                                    }else if($containsSignOutSignature==true){
                                        $action->security=$this->getContextPermissionByName($securityItemName,RelationToPermission::DISCARDS);
                                    }else{
                                        // By default we consider that the security is required.
                                        $action->security=$this->getContextPermissionByName($securityItemName,RelationToPermission::REQUIRES);
                                    }
                                }
                            }
                        }


                        $r->actions[] = $action;
                    }
                }
            }

        } else {
            throw new Exception('Unsupported swagger version' . $json[SWAGGER_VERSION],0);
        }

        return $r;
    }



    private $_permissionsByName=array();


    /**
     * Returns the context for a given permission name
     * @param $name
     * @param string $relationToPermission
     * @throws exception
     * @return SecurityContextRepresentation
     */
    private function getContextPermissionByName($name,$relationToPermission=RelationToPermission::REQUIRES){

        $rtp=new RelationToPermission();
        if(!$rtp->isValid($relationToPermission)){
            throw new exception("invalid RelationToPermission the relation is not present in the enumeration : ".$relationToPermission,10);
        }

        if (array_key_exists($name,$this->_permissionsByName)) {
            $permission=$this->_permissionsByName[$name];
            $cloned=clone $permission;// We clone the permission
            $context=new SecurityContextRepresentation();
            $context->setPermission($cloned);
            $context->setRelation($relationToPermission);
            return $context;
        }else{
            throw new Exception('Permission with name : '.$name.' does not exists :',11);
        }
    }



    /**
     * @param string $propertyName
     * @param $propertyValue
     * @param string $nativePrefix
     * @return PropertyRepresentation
     */
    private function _extractPropertyFrom($propertyName, $propertyValue, $nativePrefix) {
        // type, format, description
        $propertyR = new PropertyRepresentation();
        $propertyR->name = $propertyName;
        if (is_array($propertyValue)) {

            if (array_key_exists(SWAGGER_SCHEMA, $propertyValue)) {
                // Seen in parameters.
                $this->_parsePropertyType($propertyR, $propertyValue[SWAGGER_SCHEMA], $nativePrefix);
            } else {
                // Most common
                $this->_parsePropertyType($propertyR, $propertyValue, $nativePrefix);
            }

            if (array_key_exists(SWAGGER_DESCRIPTION, $propertyValue)) {
                $propertyR->description = $propertyValue[SWAGGER_DESCRIPTION];
            }
            if (array_key_exists(SWAGGER_REQUIRED, $propertyValue)) {
                $propertyR->required = $propertyValue[SWAGGER_REQUIRED];
            }
        }
        return $propertyR;
    }


    /**
     * Sub parsing method used to factorize parsing (as swagger is not fully regular)
     *
     * @param PropertyRepresentation $propertyR
     * @param $dictionary
     * @param $nativePrefix
     */
    private function _parsePropertyType(PropertyRepresentation $propertyR, $dictionary, $nativePrefix) {

        $subDictionary = $dictionary;
        if (array_key_exists(SWAGGER_ITEMS, $dictionary)) {
            $subDictionary = $dictionary[SWAGGER_ITEMS];
            $propertyR->type = FlexionsTypes::COLLECTION;
        }

        if (array_key_exists(SWAGGER_ENUM, $subDictionary)) {
            $propertyR->type = FlexionsTypes::ENUM;
            $enums = $subDictionary[SWAGGER_ENUM];
            foreach ($enums as $enumerableElement) {
                $propertyR->enumerations[] = $enumerableElement;
            }
        }

        $swaggerType = null;
        if (array_key_exists(SWAGGER_TYPE, $subDictionary)) {
            $swaggerType = $subDictionary[SWAGGER_TYPE];
            $propertyR->metadata['SWAGGER_TYPE'] = $swaggerType;
        }

        $swaggerFormat = null;
        if (array_key_exists(SWAGGER_FORMAT, $subDictionary)) {
            $swaggerFormat = $subDictionary[SWAGGER_FORMAT];
            $propertyR->metadata['SWAGGER_FORMAT'] = $swaggerFormat;
        }

        if (array_key_exists(SWAGGER_REF, $subDictionary)) {
            $ref = $subDictionary[SWAGGER_REF];
            // Its it a single reference.
            if (!isset($propertyR->type)) {
                $propertyR->type = FlexionsTypes::OBJECT;
            }
            $propertyR->instanceOf = $this->typeFromRef($ref,$nativePrefix);
            $propertyR->isGeneratedType = true;

        } else {


            if (($propertyR->type == FlexionsTypes::COLLECTION) || ($propertyR->type == FlexionsTypes::ENUM)) {
                $propertyR->instanceOf = $this->_swaggerTypeToFlexions($swaggerType, $swaggerFormat);
            } else if (($propertyR->type == FlexionsTypes::OBJECT)&&(isset($propertyR->instanceOf))){
                $propertyR->type=$propertyR->instanceOf;
            }else{
                $propertyR->type = $this->_swaggerTypeToFlexions($swaggerType, $swaggerFormat);
            }
        }
    }



    private  function typeFromRef($ref,$nativePrefix){
        // @todo resolve really refs ?
        $components = explode('/', $ref);
        $instanceOf = end($components);
        $type=$nativePrefix . ucfirst($instanceOf); // We add the prefix
        return $type;
    }


    /**
     * @param $type
     * @param $format
     * @return string
     */
    private function _swaggerTypeToFlexions($type, $format) {
        $type = strtolower($type);
        if ($type == 'string') {
            return FlexionsTypes::STRING;
        }
        if ($type == 'integer') {
            return FlexionsTypes::INTEGER;
        }
        if ($type == 'long') {
            return FlexionsTypes::INTEGER;
        }
        if ($type == 'float') {
            return FlexionsTypes::FLOAT;
        }
        if ($type == 'double') {
            return FlexionsTypes::DOUBLE;
        }
        if ($type == 'byte') {
            return FlexionsTypes::BYTE;
        }
        if ($type == 'boolean') {
            return FlexionsTypes::BOOLEAN;
        }
        if ($type == 'file') {
            return FlexionsTypes::FILE;
        }

        if ($type == 'date' || $type == 'dateTime') {
            return FlexionsTypes::DATETIME;
        }
        // NOT USED ACTUALLY
        //case FlexionsTypes::_DICTIONARY:


        return FlexionsTypes::NOT_SUPPORTED;
    }

    /**
     * @param String $path
     * @return string
     */
    protected function _classNameForPath($path) {
        $components = explode('/', $path);
        $className = '';
        foreach ($components as $component) {
            preg_match('#\{(.*?)\}#', $component, $match);

            if (is_null($match) || count($match) == 0) {
                $className .= ucfirst($component);
            } else {
                $cp = $match[1];
                $className .= 'With' . ucfirst($cp);
            }
        }
        return $className;
    }
}

?>
}