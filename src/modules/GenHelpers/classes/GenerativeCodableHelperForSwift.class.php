<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 31/07/2017
 * Time: 11:20
 */
require_once 'GenerativeHelperForSwift.class.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';


class GenerativeCodableHelperForSwift extends GenerativeHelperForSwift {

    /**
     * @param $d ActionRepresentation || EntityRepresentation
     * @param $increment integer
     */
    static function echoBodyOfInitFromDecoder($d, $increment) {

        // Codable support for entities and parameters classes.
        // $d may be ActionRepresentation or EntityRepresentation
        $isEntity = ($d instanceof EntityRepresentation);
        $codingKeysName = $d->name.'CodingKeys';

        echoIndent('let values = try decoder.container(keyedBy: '.$codingKeysName.'.self)',$increment);

        while ($isEntity ? $d->iterateOnProperties() : $d->iterateOnParameters() === true) {
            $property = $isEntity ? $d->getProperty() : $d->getParameter();
            if ($isEntity == true && $property->isSerializable == false) {
                continue;
            }
            if (!isset($property->customSerializationMapping)) {
                GenerativeCodableHelperForSwift::_echoPropertyForInitFromDecoder($property, $increment);
            } else {
                // RECURSIVE CALL FOR CUSTOMSERIALIZATION
                foreach ($property->customSerializationMapping as $property) {
                   GenerativeCodableHelperForSwift::_echoPropertyForInitFromDecoder($property, $increment);
                }
            }
        }
    }

    /**
     * @param $property PropertyRepresentation
     * @param $increment integer
     */
    static private function _echoPropertyForInitFromDecoder($property, $increment) {
        $name = $property->name;
        $flexionsType = $property->type;
        $nativeType = FlexionsSwiftLang::nativeTypeFor($flexionsType);
        if ($property->isSerializable == false) {
            return;
        }
        if ($property->mutability == Mutability::IS_CONSTANT) {
            return;
        }
        switch ($flexionsType) {
            case FlexionsTypes::STRING:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::INTEGER:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::BOOLEAN:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::OBJECT:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::COLLECTION:
                $instanceOf = FlexionsSwiftLang::nativeTypeFor($property->instanceOf);
                if ($instanceOf == FlexionsTypes::NOT_SUPPORTED) {
                    $instanceOf = $property->instanceOf;
                }
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::ENUM:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::FILE:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType) , $increment);
                break;
            case FlexionsTypes::DICTIONARY:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType) , $increment);
                break;
            case FlexionsTypes::FLOAT:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::DOUBLE:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::BYTE:
                echoIndent('var ref' . ucfirst($name) . '=1;', $increment);
                // ??
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType) . '("' . $name . '",&ref' . ucfirst($name) . ')', $increment);
                break;
            case FlexionsTypes::DATETIME:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::URL:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::DATA:
                echoIndent('self.' . $name . ' = ' . GenerativeCodableHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::NOT_SUPPORTED:
                echoIndent('//' . 'self.' . $name . 'is not supported', $increment);
                break;
            default :
                echoIndent('//TODO:' . $name . ' HAS NOT BEEN GENERATED (GENERATIVE TEMPLATE NEEDS TO BE AMENDED)', $increment);
                break;

        }
    }

    /**
     * @param $property PropertyRepresentation
     * @param $keyName string
     * @param $flexionsType string
     * @return string
     */
    private static function _decodingFunctionFor($property, $keyName, $flexionsType) {
        $isNotOptionnal = ($property->required || $property->default != NULL);
        $qualifiedType = FlexionsSwiftLang::nativeTypeFor($property->instanceOf);
        if ($qualifiedType == FlexionsTypes::VOID || $qualifiedType == FlexionsTypes::NOT_SUPPORTED ){
            $qualifiedType = ucfirst($property->instanceOf);
        }
        switch ($flexionsType) {
            case FlexionsTypes::STRING:
                if ($property->isCryptable){
                    if ($isNotOptionnal){
                        return "try self.decodeCryptedString(codingKey: .$keyName, from: values)";
                    }else{
                        return "try self.decodeCryptedStringIfPresent(codingKey: .$keyName, from: values)";
                    }
                }else{
                    return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(String.self,forKey:.'.$keyName.')';
                }
            case FlexionsTypes::INTEGER:
                return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(Int.self,forKey:.'.$keyName.')';
            case FlexionsTypes::BOOLEAN:
                return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(Bool.self,forKey:.'.$keyName.')';
            case FlexionsTypes::OBJECT:
                return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'('.$qualifiedType.'.self,forKey:.'.$keyName.')';
            case FlexionsTypes::COLLECTION:

                return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(['.$qualifiedType.'].self,forKey:.'.$keyName.')';
            case FlexionsTypes::ENUM:
                return $property->emumPreciseType.'(rawValue: try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).
                    '('.FlexionsSwiftLang::nativeTypeFor($property->instanceOf).'.self,forKey:.'.$keyName.')) ?? '.$property->default;
            case FlexionsTypes::DICTIONARY;
                return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'([String:Any].self,forKey:.'.$keyName.')';
            case FlexionsTypes::FLOAT:
                return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(Float.self,forKey:.'.$keyName.')';
            case FlexionsTypes::DOUBLE:
                return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(Double.self,forKey:.'.$keyName.')';
            case FlexionsTypes::BYTE:
                return FlexionsTypes::NOT_SUPPORTED;

            // NOTE NSDate, NSURL, NSData requires to use <OBJC_TYPE>.self
            // This may change soon

            case FlexionsTypes::DATETIME:
                return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(Date.self,forKey:.'.$keyName.')';
            case FlexionsTypes::URL :
                 return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(URL.self,forKey:.'.$keyName.')';
            case FlexionsTypes::FILE :
                return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(URL.self,forKey:.'.$keyName.')';
            case FlexionsTypes::DATA:
              return 'try values.'.GenerativeCodableHelperForSwift::_fDecodeFor($property).'(Data.self,forKey:.'.$keyName.')';
            case FlexionsTypes::NOT_SUPPORTED:
                return FlexionsTypes::NOT_SUPPORTED;
                break;
            default :
                return FlexionsTypes::VOID;
                break;
        }
    }


    /**
     * @param $d ActionRepresentation || EntityRepresentation
     * @param $increment integer
     */
    static function echoBodyOfEncodeToEncoder($d, $increment) {

        // NSCoding support for entities and parameters classes.
        // $d may be ActionRepresentation or EntityRepresentation
        $isEntity = ($d instanceof EntityRepresentation);
        $codingKeysName = $d->name.'CodingKeys';

        echoIndent('var container = encoder.container(keyedBy: '.$codingKeysName.'.self)',$increment);

        while ($isEntity ? $d->iterateOnProperties() : $d->iterateOnParameters() === true) {

            /* @var $property PropertyRepresentation */
            $property = $isEntity ? $d->getProperty() : $d->getParameter();
            if ($isEntity == true && $property->isSerializable == false) {
                continue;
            }
            if (!isset($property->customSerializationMapping)) {
                GenerativeCodableHelperForSwift::_echoPropertyForEncodeToEncoder($property, $increment);
            } else {
                // RECURSIVE CALL FOR CUSTOMSERIALIZATION
                foreach ($property->customSerializationMapping as $property) {
                    GenerativeCodableHelperForSwift::_echoPropertyForEncodeToEncoder($property, $increment);
                }
            }
        }
    }

    /**
     * @param $property PropertyRepresentation
     * @param $increment integer
     */
    static private function _echoPropertyForEncodeToEncoder($property, $increment) {
        $isNotOptionnal = ($property->required || $property->default != NULL);
        $name = $property->name;
        $currentIncrement = $increment;
        $encodingFunction = NULL;
        if (!isset($property->type)) {
            echoIndent('//' . $name . ' HAS NOT BEEN GENERATED (GENERATIVE TEMPLATE NEEDS TO BE AMENDED)', $increment);
            return;
        }
        if ($property->type === FlexionsTypes::NOT_SUPPORTED) {
            echoIndent('//' . $name . 'is not supported', $increment);
            return;
        }
        $keyToEncode = $name;
        if ($property->type === FlexionsTypes::ENUM && ($property->instanceOf === FlexionsTypes::STRING || $property->instanceOf === FlexionsTypes::INTEGER)) {
            //Casting is required
            $keyToEncode = $name . '.rawValue ';
        }

        $keyToEncode = 'self.' . $keyToEncode;

        if ($property->type == FlexionsTypes::STRING && $property->isCryptable){
            if ($isNotOptionnal){
                echoIndent("try self.encodeCryptedString(value: self.$name, codingKey: .$name, container: &container)", $currentIncrement);
            }else{
                echoIndent("try self.encodeCryptedStringIfPresent(value: self.$name, codingKey: .$name, container: &container)", $currentIncrement);
            }

        }else{
            echoIndent('try container.' . GenerativeCodableHelperForSwift::_fencodeFor($property) . '(' . $keyToEncode . ',forKey:.' . $name . ')', $currentIncrement);
        }


    }


    private static function _fDecodeFor($property) {
        $required = ($property->required === true || $property->default !== NULL);
        if ($required){
            return "decode";
        }else{
            return "decodeIfPresent";
        }
    }



    private static function _fencodeFor($property) {
        $required = ($property->required === true || $property->default !== NULL);
        if ($required){
            return "encode";
        }else{
            return "encodeIfPresent";
        }
    }
}