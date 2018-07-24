<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 31/07/2017
 * Time: 11:20
 */
require_once 'GenerativeHelperForSwift.class.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';


class GenerativeNSecureCodingHelperForSwift extends GenerativeHelperForSwift {

    /**
     * @param $d ActionRepresentation || EntityRepresentation
     * @param $increment integer
     */
    static function echoBodyOfInitWithCoder($d, $increment) {

        // NSCoding support for entities and parameters classes.
        // $d may be ActionRepresentation or EntityRepresentation
        $isEntity = ($d instanceof EntityRepresentation);

        while ($isEntity ? $d->iterateOnProperties() : $d->iterateOnParameters() === true) {
            $property = $isEntity ? $d->getProperty() : $d->getParameter();
            if ($isEntity == true && $property->isSerializable == false) {
                continue;
            }
            if (!isset($property->customSerializationMapping)) {
                GenerativeNSecureCodingHelperForSwift::_echoPropertyForIniWithCoder($property, $increment);
            } else {
                // RECURSIVE CALL FOR CUSTOMSERIALIZATION
                foreach ($property->customSerializationMapping as $property) {
                    GenerativeNSecureCodingHelperForSwift::_echoPropertyForIniWithCoder($property, $increment);
                }
            }
        }
    }

    /**
     * @param $property PropertyRepresentation
     * @param $increment integer
     */
    static private function _echoPropertyForIniWithCoder($property, $increment) {
        $name = $property->name;
        $flexionsType = $property->type;
        $asString = $property->required ? 'as!' : 'as?';
        $nativeType = FlexionsSwiftLang::nativeTypeFor($flexionsType);
        if ($property->isSerializable == false) {
            return;
        }
        switch ($flexionsType) {
            case FlexionsTypes::STRING:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::INTEGER:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::BOOLEAN:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::OBJECT:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::COLLECTION:
                $instanceOf = FlexionsSwiftLang::nativeTypeFor($property->instanceOf);
                if ($instanceOf == FlexionsTypes::NOT_SUPPORTED) {
                    $instanceOf = $property->instanceOf;
                }
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType) . $asString . ' [' . ucfirst($instanceOf) . ']', $increment);
                break;
            case FlexionsTypes::ENUM:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::FILE:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType) . $asString . ' ' . $nativeType . '', $increment);
                break;
            case FlexionsTypes::DICTIONARY:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType) . $asString . ' ' . $nativeType . '', $increment);
                break;
            case FlexionsTypes::FLOAT:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::DOUBLE:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::BYTE:
                echoIndent('var ref' . ucfirst($name) . '=1;', $increment);
                // ??
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType) . '("' . $name . '",&ref' . ucfirst($name) . ')', $increment);
                break;
            case FlexionsTypes::DATETIME:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::URL:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
                break;
            case FlexionsTypes::DATA:
                echoIndent('self.' . $name . '=' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $name, $flexionsType), $increment);
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
        $isNotOptionnal = $property->required;
        switch ($flexionsType) {
            case FlexionsTypes::STRING:
                if ($isNotOptionnal) {
                    return 'String(describing: decoder.decodeObject(of: NSString.self, forKey: "' . $keyName . '")! as NSString)';
                } else {
                    return 'String(describing: decoder.decodeObject(of: NSString.self, forKey:"' . $keyName . '") as NSString?)';
                }
            case FlexionsTypes::INTEGER:
                return 'decoder.decodeInteger(forKey:"' . $keyName . '") ';
            case FlexionsTypes::BOOLEAN:
                return 'decoder.decodeBool(forKey:"' . $keyName . '") ';
            case FlexionsTypes::OBJECT:
                //return 'decodeObjectForKey("'.$keyName.'") ';
                $instanceOf = $property->instanceOf;
                /*
                  if (strpos($instanceOf,'Alias')!==false){
                      $instanceOf="Alias";
                  }
                */
                return 'decoder.decodeObject(of:' . $instanceOf . '.self, forKey: "' . $keyName . '")' . ($isNotOptionnal ? '! ' : ' ');
            case FlexionsTypes::COLLECTION:
                if ($property->instanceOf == FlexionsTypes::STRING) {
                    return 'decoder.decodeObject(of: [NSArray.classForCoder(),NSString.self], forKey: "' . $keyName . '")' . ($isNotOptionnal ? '! ' : ' ');
                } else if ($property->instanceOf == FlexionsTypes::INTEGER || $property->instanceOf == FlexionsTypes::DOUBLE || $property->instanceOf == FlexionsTypes::FLOAT) {
                    return 'decoder.decodeObject(of: [NSArray.classForCoder(),NSNumber.self], forKey: "' . $keyName . '")' . ($isNotOptionnal ? '! ' : ' ');
                } else if ($property->instanceOf == FlexionsTypes::DICTIONARY) {
                    return 'decoder.decodeObject(of: [NSArray.classForCoder(),NSDictionary.classForCoder()], forKey: "' . $keyName . '")' . ($isNotOptionnal ? '! ' : ' ');
                } else {
                    return 'decoder.decodeObject(of: [NSArray.classForCoder(),' . $property->instanceOf . '.classForCoder()], forKey: "' . $keyName . '")' . ($isNotOptionnal ? '! ' : ' ');
                }

            case FlexionsTypes::ENUM:
                // .$asString.' '. ucfirst($property->enumPreciseType)
                // User.Status(rawValue:String(describing: decoder.decodeObject(NSString.self, forKey: "status")! as NSString))!
                return $property->enumPreciseType . '(rawValue:' . GenerativeNSecureCodingHelperForSwift::_decodingFunctionFor($property, $keyName, $property->instanceOf) . ')' . ($isNotOptionnal ? '! ' : ' ');
            case FlexionsTypes::DICTIONARY;
                return 'decoder.decodeObject(of: [NSDictionary.classForCoder(),NSString.classForCoder(),NSNumber.classForCoder(),NSObject.classForCoder(),NSSet.classForCoder()], forKey: "' . $keyName . '")';
            case FlexionsTypes::FLOAT:
                return 'decoder.decodeFloat(forKey:"' . $keyName . '") ';
            case FlexionsTypes::DOUBLE:
                return 'decoder.decodeDouble(forKey:"' . $keyName . '") ';
            case FlexionsTypes::BYTE:
                return 'decoder.decodeBytes-(forKey:"' . $keyName . '") ';

            // NOTE NSDate, NSURL, NSData requires to use <OBJC_TYPE>.self
            // This may change soon

            case FlexionsTypes::DATETIME:
                if ($isNotOptionnal) {
                    return 'decoder.decodeObject(of: NSDate.self , forKey: "' . $keyName . '")! as Date';
                } else {
                    return 'decoder.decodeObject(of: NSDate.self , forKey:"' . $keyName . '") as Date?';
                }
            case FlexionsTypes::URL :
                if ($isNotOptionnal) {
                    return 'decoder.decodeObject(of: NSURL.self, forKey: "' . $keyName . '")! as URL';
                } else {
                    return 'decoder.decodeObject(of: NSURL.self, forKey:"' . $keyName . '") as URL?';
                }
            case FlexionsTypes::FILE :
                if ($isNotOptionnal) {
                    return 'decoder.decodeObject(of: NSURL.self, forKey: "' . $keyName . '")! as URL';
                } else {
                    return 'decoder.decodeObject(of: NSURL.self, forKey:"' . $keyName . '") as URL?';
                }
            case FlexionsTypes::DATA:
                if ($isNotOptionnal) {
                    //return '//NOT IMPLEMETED - decodeObject DATA';
                    return 'decoder.decodeObject(of: NSData.self, forKey: "' . $keyName . '")! as Data';
                } else {
                    return 'decoder.decodeObject(of: NSData.self, forKey:"' . $keyName . '") as Data?';
                }
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
    static function echoBodyOfEncodeToCoder($d, $increment) {

        // NSCoding support for entities and parameters classes.
        // $d may be ActionRepresentation or EntityRepresentation
        $isEntity = ($d instanceof EntityRepresentation);

        while ($isEntity ? $d->iterateOnProperties() : $d->iterateOnParameters() === true) {

            /* @var $property PropertyRepresentation */
            $property = $isEntity ? $d->getProperty() : $d->getParameter();
            if ($isEntity == true && $property->isSerializable == false) {
                continue;
            }
            if (!isset($property->customSerializationMapping)) {
                GenerativeNSecureCodingHelperForSwift::_echoPropertyForEncodeWithCoder($property, $increment);
            } else {
                // RECURSIVE CALL FOR CUSTOMSERIALIZATION
                foreach ($property->customSerializationMapping as $property) {
                    GenerativeNSecureCodingHelperForSwift::_echoPropertyForEncodeWithCoder($property, $increment);
                }
            }
        }
    }

    /**
     * @param $property PropertyRepresentation
     * @param $increment integer
     */
    static private function _echoPropertyForEncodeWithCoder($property, $increment) {
        $name = $property->name;
        $incrementPlusOne = $increment + 1;
        $securizedName = $name;

        $shouldUseIfString = $property->required;

        // We may enclose the encoding within a it let ... { ... } expression
        // to unwrap optionnals.

        if ($shouldUseIfString) {
            $securizedName = str_replace(".", "_", $name);
            $currentIncrement = $incrementPlusOne;
            echoIndent('if let ' . $securizedName . ' = self.' . $name . ' {', $increment);
        } else {
            $currentIncrement = $increment;
        }
        $encodingFunction = NULL;
        if (!isset($property->type)) {
            echoIndent('//' . $name . ' HAS NOT BEEN GENERATED (GENERATIVE TEMPLATE NEEDS TO BE AMENDED)', $increment);
            return;
        }
        if ($property->type === FlexionsTypes::NOT_SUPPORTED) {
            echoIndent('//' . $name . 'is not supported', $increment);
            return;
        }

        $keyToEncode = $securizedName;
        if ($property->type === FlexionsTypes::ENUM && ($property->instanceOf === FlexionsTypes::STRING || $property->instanceOf === FlexionsTypes::INTEGER)) {
            //Casting is required
            $keyToEncode = $securizedName . '.rawValue ';
        }

        if (!$shouldUseIfString) {
            $keyToEncode = 'self.' . $keyToEncode;
        }

        echoIndent('coder.' . GenerativeNSecureCodingHelperForSwift::_encodingFunctionFor($property->type, $property->instanceOf) . '(' . $keyToEncode . ',forKey:"' . $name . '")', $currentIncrement);
        if ($shouldUseIfString) {
            echoIndent('}', $increment);
        }
    }


    /**
     * @param $flexionsType
     * @param string $instanceOf
     * @return string
     */
    private static function _encodingFunctionFor($flexionsType, $instanceOf = 'UNDEFINED') {
        switch ($flexionsType) {
            case FlexionsTypes::STRING:
                return 'encode';
            case FlexionsTypes::INTEGER:
                return 'encode';
            case FlexionsTypes::BOOLEAN:
                return 'encode';
            case FlexionsTypes::OBJECT:
                return 'encode';
            case FlexionsTypes::COLLECTION:
                return 'encode';
            case FlexionsTypes::ENUM;
                // We have 3 levels :
                // When the type is an ENUM, you can specify its precise type
                // Swift enum can be typed. We want to be able to cast the enums.
                // E.g : property status type=enum, instanceOf=string , enumPreciseType=User.status
                return GenerativeNSecureCodingHelperForSwift::_encodingFunctionFor($instanceOf);
            case FlexionsTypes::FILE:
                return 'encode';
            case FlexionsTypes::DICTIONARY:
                return 'encode';
            case FlexionsTypes::FLOAT:
                return 'encode';
            case FlexionsTypes::DOUBLE:
                return 'encode';
            case FlexionsTypes::BYTE:
                return 'encode';
            case FlexionsTypes::DATETIME:
                return 'encode';
            case FlexionsTypes::URL:
                return 'encode';
            case FlexionsTypes::DATA:
                return 'encode';
            case FlexionsTypes::NOT_SUPPORTED:
                return FlexionsTypes::NOT_SUPPORTED;
            default :
                return FlexionsTypes::VOID;
        }
    }
}