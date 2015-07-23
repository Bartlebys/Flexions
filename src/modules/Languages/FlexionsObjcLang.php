<?php

require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsTypes.php';

/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 14/07/15
 * Time: 16:26
 */
class FlexionsObjcLang implements IFlexionsLanguageMapping {

    /**
     * @param  $flexionsType
     * @return String the native type
     */
    static function nativeTypeFor($flexionsType){
        switch ($flexionsType) {
            case FlexionsTypes::STRING:
                return 'NString';
            case FlexionsTypes::INTEGER:
                return 'NSInteger';
            case FlexionsTypes::BOOLEAN:
                return 'Bool';
            case FlexionsTypes::OBJECT:
                return 'object';//Pseudo type (the instanceOf type will apply)
            case FlexionsTypes::COLLECTION:
                return 'collection';//Pseudo type (the instanceOf type will apply)
            case FlexionsTypes::ENUM:
                return 'Emum';//Pseudo type (the instanceOf type will apply)
            case FlexionsTypes::FILE:
                return 'NSURL';
            case FlexionsTypes::FLOAT:
                return 'Float';
            case FlexionsTypes::DOUBLE:
                return 'Double';
            case FlexionsTypes::BYTE:
                return 'Byte';
            case FlexionsTypes::DATETIME:
                return 'NSDate';
            case FlexionsTypes::URL:
                return 'NSURL';
            case FlexionsTypes::DICTIONARY:
                return 'NSDictionary';
        }
        return FlexionsTypes::NOT_SUPPORTED.'->'.$flexionsType;
    }



}