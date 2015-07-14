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
            case FlexionsTypes::_STRING:
                return 'NString';
            case FlexionsTypes::_INTEGER:
                return 'NSInteger';
            case FlexionsTypes::_BOOLEAN:
                return 'Bool';
            case FlexionsTypes::_OBJECT:
                return 'object';// Pseudo type (we need an instanceOf)
            case FlexionsTypes::_ARRAY:
                return 'NSArray';
            case FlexionsTypes::_COLLECTION:
                return 'collection';//Pseudo type (we need an instanceOf)
            case FlexionsTypes::_FLOAT:
                return 'Float';
            case FlexionsTypes::_DOUBLE:
                return 'Double';
            case FlexionsTypes::_BYTE:
                return 'Byte';
            case FlexionsTypes::_DATETIME:
                return 'NSDate';
            case FlexionsTypes::_URL:
                return 'NSURL';
            case FlexionsTypes::_ANY:
                return 'id';
        }
        return FlexionsTypes::_NOT_SUPPORTED.'->'.$flexionsType;
    }



}