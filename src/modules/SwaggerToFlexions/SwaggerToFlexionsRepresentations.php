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
    define('SWAGGER_OPERATION_ID', 'operationId');
    define('SWAGGER_PARAMETERS', 'parameters');
    define('SWAGGER_NAME', 'name');
    define('SWAGGER_SCHEMA', 'schema');
    define('SWAGGER_REQUIRED', 'required');
    define('SWAGGER_RESPONSES', 'responses');
    //define ('SWAGGER_HEADERS','headers');
}




/**
 * We support partially SWAGGER 2.0 but enough to modelize and generate APIS and Entities with Flexions.
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

            // #2 Extract the entities EntityRepresentation
            //from definitions :
            if (array_key_exists(SWAGGER_DEFINITIONS, $json)) {
                $definitions = $json[SWAGGER_DEFINITIONS];
                foreach ($definitions as $entityName => $descriptor) {
                    $e = new EntityRepresentation();
                    $e->name = $nativePrefix . ucfirst($entityName);
                    if (array_key_exists(SWAGGER_TYPE, $descriptor)) {
                        $entityType = $descriptor[SWAGGER_TYPE];
                        if ($entityType === SWAGGER_OBJECT) {
                            if (array_key_exists(SWAGGER_PROPERTIES, $descriptor)) {
                                $properties = $descriptor[SWAGGER_PROPERTIES];
                                foreach ($properties as $propertyName => $propertyValue) {
                                    $e->properties[] = $this->_extractPropertyFrom($propertyName, $propertyValue, $nativePrefix);
                                }
                            }
                        }
                    } else {
                        // Entity is not an object
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
                                $property = $this->_extractPropertyFrom("$name", $response, $nativePrefix);
                                $action->responses[] = $property;
                            }
                        }


                        $r->actions[] = $action;
                    }
                }
            }

        } else {
            throw new Exception('Unsupported swagger version' . $json[SWAGGER_VERSION]);
        }

        return $r;
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
            // @todo resolve really refs.
            // Its it a single reference.
            if (!isset($propertyR->type)) {
                $propertyR->type = FlexionsTypes::OBJECT;
            }
            $ref = $subDictionary[SWAGGER_REF];
            $components = explode('/', $ref);
            $instanceOf = end($components);
            $propertyR->instanceOf = $nativePrefix . ucfirst($instanceOf); // We add the prefix
            $propertyR->isGeneratedType = true;

        } else {
            if (($propertyR->type == FlexionsTypes::COLLECTION) || ($propertyR->type == FlexionsTypes::ENUM)) {
                $propertyR->instanceOf = $this->_swaggerTypeToFlexions($swaggerType, $swaggerFormat);
            } else {
                $propertyR->type = $this->_swaggerTypeToFlexions($swaggerType, $swaggerFormat);
            }
        }
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