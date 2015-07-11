<?php


if (!defined('SWAGGER_VERSION')) {

    define('SWAGGER_VERSION', 'swagger');
    define('SWAGGER_HOST', 'host');
    define('SWAGGER_BASE_PATH', 'basePath');
    define('SWAGGER_TAGS', 'tags');
    define('SWAGGER_SCHEMES', 'schemes');
    define('SWAGGER_SECURITY_DEFINITIONS', 'securityDefinitions');
    define('SWAGGER_DEFINITIONS', 'definitions');
    define('SWAGGER_EXTERNAL_DOCS', 'externalDocs');

    define('SWAGGER_TYPE','type');
    define('SWAGGER_OBJECT','object');
    define('SWAGGER_PROPERTIES','properties');
    define('SWAGGER_DESCRIPTION','description');
    define('SWAGGER_FORMAT','format');
}

require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_MODULES_DIR . 'SwaggerToFlexions/ISwaggerDelegate.php';

class SwaggerToFlexionsRepresentations {

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
                                    if (array_key_exists(SWAGGER_TYPE, $propertyValue)) {
                                        $swaggerType = $propertyValue[SWAGGER_TYPE];
                                        $propertyR->type = $swaggerType;
                                    }
                                    if (array_key_exists(SWAGGER_FORMAT, $propertyValue)) {
                                        $swaggerFormat = $propertyValue[SWAGGER_FORMAT];
                                        // @todo do we want more precise casting int32,...
                                    }
                                    // string Type of variable (string, number, integer, boolean, object, array, float, null, any).

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

        } else {
            throw new Exception('Unsupported swagger version' . $json[SWAGGER_VERSION]);
        }

        return $r;

    }

}

?>