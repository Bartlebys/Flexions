<?php

require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/IFlexionsLanguageMapping.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsTypes.php';

/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 14/07/15
 * Time: 16:26
 */
class FlexionsSwiftLang implements IFlexionsLanguageMapping {

    /**
     * @param  $flexionsType
     * @return String the native type
     */
    static function nativeTypeFor($flexionsType){
        switch ($flexionsType) {
            case FlexionsTypes::_STRING:
                return 'String';
            case FlexionsTypes::_INTEGER:
                return 'Int';
            case FlexionsTypes::_BOOLEAN:
                return 'Bool';
            case FlexionsTypes::_OBJECT:
                return 'Object';// Pseudo type (we need an instancOf)
            case FlexionsTypes::_COLLECTION:
                return 'Collection';//Pseudo type (we need an instancOf)
            case FlexionsTypes::_FLOAT:
                return 'Float';
            case FlexionsTypes::_DOUBLE:
                return 'Double';
            case FlexionsTypes::_BYTE:
                return 'UInt8';
            case FlexionsTypes::_DATETIME:
                return 'NSDate';
            case FlexionsTypes::_URL:
                return 'NSURL';
        }
        return FlexionsTypes::_NOT_SUPPORTED.'//'.$flexionsType;
    }



}