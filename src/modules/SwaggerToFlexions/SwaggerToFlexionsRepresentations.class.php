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

    define('SWAGGER_REF', '$ref');
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
            $r->baseUrl = $json[SWAGGER_HOST];
            $r->apiVersion = $json[SWAGGER_BASE_PATH];

            // Extract the entities from definitions :
            if (array_key_exists(SWAGGER_DEFINITIONS, $json)) {
                $definitions = $json[SWAGGER_DEFINITIONS];
                foreach ($definitions as $entityName => $descriptor) {
                    $e = new EntityRepresentation();
                    $e->name = $entityName;
                    if (array_key_exists(SWAGGER_TYPE, $descriptor)) {
                        $entityType = $descriptor[SWAGGER_TYPE];
                        if ($entityType === SWAGGER_OBJECT) {
                            if (array_key_exists(SWAGGER_PROPERTIES, $descriptor)) {
                                $properties = $descriptor[SWAGGER_PROPERTIES];
                                foreach ($properties as $propertyName => $propertyValue) {
                                    // type, format, description
                                    $propertyR = new PropertyRepresentation();
                                    $propertyR->name = $propertyName;
                                    if (is_array($propertyValue)) {
                                        if (array_key_exists(SWAGGER_REF, $propertyValue)) {
                                            $ref = $propertyValue[SWAGGER_REF];
                                            // @todo dirty implementation the ref should be inspected (and could be external)
                                            $components = explode('/', $ref);
                                            $instanceOf = end($components);
                                            $propertyR->type = 'object';
                                            $propertyR->instanceOf = $nativePrefix . ucfirst($instanceOf); // We add the prefix
                                            $propertyR->isGeneratedType = true;
                                        }

                                        if (array_key_exists(SWAGGER_TYPE, $propertyValue)) {
                                            $swaggerType = $propertyValue[SWAGGER_TYPE];
                                            $propertyR->type = $swaggerType;
                                        }

                                        if (array_key_exists(SWAGGER_FORMAT, $propertyValue)) {
                                            $swaggerFormat = $propertyValue[SWAGGER_FORMAT];
                                            // @todo we may want more precise casting int32,...
                                            // Type mapping
                                            //https://github.com/swagger-api/swagger-core/wiki/Datatypes
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
                    $className=$this->_classNameForPath($path);
                    $action=new ActionRepresentation();
                    $action->class=$className;
                    $r->actions[]=$action;

                }
            }


        } else {
            throw new Exception('Unsupported swagger version' . $json[SWAGGER_VERSION]);
        }

        return $r;

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