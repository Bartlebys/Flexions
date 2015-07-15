<?php


if (!defined('SWAGGER_VERSION')) {

    define('SWAGGER_VERSION', 'swagger');
    define('SWAGGER_HOST', 'host');
    define('SWAGGER_BASE_PATH', 'basePath');
    define('SWAGGER_TAGS', 'tags');
    define('SWAGGER_SCHEMES', 'schemes');
    define('SWAGGER_PATHS', 'paths');
    define('SWAGGER_SECURITY_DEFINITIONS', 'securityDefinitions');
    define('SWAGGER_DEFINITIONS', 'definitions');
    define('SWAGGER_EXTERNAL_DOCS', 'externalDocs');

    define('SWAGGER_TYPE', 'type');
    define('SWAGGER_OBJECT', 'object');
    define('SWAGGER_PROPERTIES', 'properties');
    define('SWAGGER_DESCRIPTION', 'description');
    define('SWAGGER_FORMAT', 'format');
    define('SWAGGER_ITEMS', 'items');

    define('SWAGGER_REF', '$ref');

    define('SWAGGER_OPERATION_ID','operationId');
}

require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_MODULES_DIR . 'SwaggerToFlexions/ISwaggerDelegate.php';

/**
 * Class SwaggerToFlexionsRepresentations
 */
class SwaggerToFlexionsRepresentations {

    /**
     * @param $descriptorFilePath
     * @param string $nativePrefix
     * @param ISwaggerDelegate $delegate
     * @return ProjectRepresentation|void
     * @throws Exception
     */
    function projectRepresentationFromSwaggerJson($descriptorFilePath, $nativePrefix = "", ISwaggerDelegate $delegate) {

        if (!isset($delegate)) {
            fLog("projectRepresentationFromSwaggerJson.projectRepresentationFromSwaggerJson() module requires an ISwaggerDelegate", true);
            return;
        }

        fLog("Invoking projectRepresentationFromSwaggerJson.projectRepresentationFromSwaggerJson() on " . $descriptorFilePath . cr() . cr(), true);
        $r = new ProjectRepresentation ();
        $r->classPrefix = $nativePrefix;

        $s = file_get_contents($descriptorFilePath);
        $json = json_decode($s, true);
        $r->metadata = $json;// We store the raw descriptor as a metadata


        if ($json[SWAGGER_VERSION] = '2.0') {
            $r->baseUrl = $json[SWAGGER_SCHEMES][0].'://'.$json[SWAGGER_HOST].$json[SWAGGER_BASE_PATH];
            $r->apiVersion = rtrim($json[SWAGGER_BASE_PATH],'/');

            // Extract the entities from definitions :
            if (array_key_exists(SWAGGER_DEFINITIONS, $json)) {
                $definitions = $json[SWAGGER_DEFINITIONS];
                foreach ($definitions as $entityName => $descriptor) {
                    $e = new EntityRepresentation();
                    $e->name = $nativePrefix.ucfirst($entityName);
                    if (array_key_exists(SWAGGER_TYPE, $descriptor)) {
                        $entityType = $descriptor[SWAGGER_TYPE];
                        if ($entityType === SWAGGER_OBJECT) {
                            if (array_key_exists(SWAGGER_PROPERTIES, $descriptor)) {
                                $properties = $descriptor[SWAGGER_PROPERTIES];
                                foreach ($properties as $propertyName => $propertyValue) {
                                    // type, format, description
                                    $propertyR = new PropertyRepresentation();
                                    $propertyR->name = $propertyName;
                                    // @todo  implementation the $ref should be inspected (and could be external)
                                    if (is_array($propertyValue)) {
                                        if (array_key_exists(SWAGGER_REF, $propertyValue)) {
                                            // Its it a single reference.
                                            $ref = $propertyValue[SWAGGER_REF];
                                            $components = explode('/', $ref);
                                            $instanceOf = end($components);
                                            $propertyR->type = FlexionsTypes::_OBJECT;
                                            $propertyR->instanceOf = $nativePrefix . ucfirst($instanceOf); // We add the prefix
                                            $propertyR->isGeneratedType = true;
                                        }else{
                                            $swaggerType=null;
                                            if (array_key_exists(SWAGGER_TYPE, $propertyValue)) {
                                                $swaggerType = $propertyValue[SWAGGER_TYPE];
                                                $propertyR->metadata['SWAGGER_TYPE']=$swaggerType;
                                            }
                                            $swaggerFormat=null;
                                            if (array_key_exists(SWAGGER_FORMAT, $propertyValue)) {
                                                $swaggerFormat = $propertyValue[SWAGGER_FORMAT];
                                                $propertyR->metadata['SWAGGER_FORMAT']=$swaggerFormat;
                                            }
                                            $isACollection=false;
                                            if (array_key_exists(SWAGGER_ITEMS, $propertyValue)) {
                                                if (array_key_exists(SWAGGER_REF, $propertyValue[SWAGGER_ITEMS])) {
                                                    // It is a collection of items.
                                                    $isACollection=true;
                                                    $ref = $propertyValue[SWAGGER_ITEMS][SWAGGER_REF];
                                                    $components = explode('/', $ref);
                                                    $instanceOf = end($components);
                                                    $propertyR->type = FlexionsTypes::_COLLECTION;
                                                    $propertyR->instanceOf = $nativePrefix . ucfirst($instanceOf); // We add the prefix
                                                    $propertyR->isGeneratedType = true;
                                                }else if (array_key_exists(SWAGGER_TYPE, $propertyValue[SWAGGER_ITEMS])) {
                                                    // It may be a supported type;
                                                    $isACollection=true;
                                                    $propertyR->type = FlexionsTypes::_COLLECTION;
                                                    $propertyR->instanceOf = $propertyValue[SWAGGER_ITEMS][SWAGGER_TYPE];
                                                    $propertyR->isGeneratedType = false;
                                                }
                                            }
                                            if($isACollection==false){
                                                $propertyR->type = $this->_swaggerTypeToFlexions($swaggerType,$swaggerFormat);
                                            }
                                        }
                                        if (array_key_exists(SWAGGER_DESCRIPTION, $propertyValue)) {
                                            $propertyR->description = $propertyValue[SWAGGER_DESCRIPTION];
                                        }
                                    }
                                    $e->properties[] = $propertyR;
                                }
                            }
                        } else {
                            // Entity is not an object
                        }
                    }
                    $r->entities[] = $e;
                }
            }

            if (array_key_exists(SWAGGER_PATHS, $json)) {
                $paths = $json[SWAGGER_PATHS];
                foreach ($paths as $path => $descriptor) {
                    foreach ( $descriptor as $method => $methodDescriptor) {
                        $className='';
                        if (array_key_exists(SWAGGER_OPERATION_ID, $methodDescriptor)) {
                            $className=$nativePrefix.ucfirst($methodDescriptor[SWAGGER_OPERATION_ID]);
                        }else{
                            $className=$nativePrefix.$this->_classNameForPath($path);
                        }
                        $action=new ActionRepresentation();
                        $action->class=$className;
                        $action->path=$path;
                        $action->httpMethod=strtoupper($method);
                        $r->actions[]=$action;
                    }


                }
            }


        } else {
            throw new Exception('Unsupported swagger version' . $json[SWAGGER_VERSION]);
        }

        return $r;
    }


    private function _swaggerTypeToFlexions($type,$format){
        $type=strtolower($type);
        if($type=='string'){
            return FlexionsTypes::_STRING;
        }
        if($type=='integer'){
            return FlexionsTypes::_INTEGER;
        }
        if($type=='long'){
            return FlexionsTypes::_INTEGER;
        }
        if($type=='float'){
            return FlexionsTypes::_FLOAT;
        }
        if($type=='double'){
            return FlexionsTypes::_DOUBLE;
        }
        if($type=='byte'){
            return FlexionsTypes::_BYTE;
        }
        if($type=='boolean'){
            return FlexionsTypes::_BOOLEAN;
        }
        if($type=='date' || $type=='dateTime'){
            return FlexionsTypes::_DATETIME;
        }
        return FlexionsTypes::_NOT_SUPPORTED;
    }



    /**
     * @param String $path
     * @return string
     */
    protected function _classNameForPath($path){
        $components=explode('/',$path);
        $className='';
        foreach ($components as $component ) {
            preg_match('#\{(.*?)\}#', $component, $match);

            if(is_null($match) || count($match)==0 ){
                $className.=ucfirst($component);
            }else{
                $cp=$match[1];
                $className.='With'.ucfirst($cp);
            }
        }
        return $className;
    }


}

?>