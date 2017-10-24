<?php

require_once FLEXIONS_ROOT_DIR . '/flexions/representations/IFlexionsLanguageMapping.php';
require_once FLEXIONS_ROOT_DIR . '/flexions/representations/FlexionsTypes.php';

class FlexionsSwiftLang implements IFlexionsLanguageMapping {


    /**
     * @param  $flexionsType
     * @return String the native type
     */
    static function nativeTypeFor($flexionsType) {
        switch ($flexionsType) {
            case FlexionsTypes::STRING:
                return 'String';
            case FlexionsTypes::INTEGER:
                return 'Int';
            case FlexionsTypes::BOOLEAN:
                return 'Bool';
            case FlexionsTypes::OBJECT:
                return 'Object';//Pseudo type (the instanceOf type will apply)
            case FlexionsTypes::COLLECTION:
                return 'Collection';//Pseudo type (the instanceOf type will apply)
            case FlexionsTypes::ENUM:
                return 'Emum';//Pseudo type (the instanceOf type will apply)
            case FlexionsTypes::FILE:
                return 'URL';
            case FlexionsTypes::FLOAT:
                return 'Float';
            case FlexionsTypes::DOUBLE:
                return 'Double';
            case FlexionsTypes::BYTE:
                return 'UInt8';
            case FlexionsTypes::DATETIME:
                return 'Date';
            case FlexionsTypes::URL:
                return 'URL';
            case FlexionsTypes::DICTIONARY:
                // Dictionaries are not codable
                // The general dictionary type has been removed to support swift 4 encodable
                // You should use for example KeyedData  in Bartleby to obtain the same benefits
                // Or [String:Data] that is an excellent codable container.
                // Before swift 4 [String:Any] was supported by dynamic serialization process
                return '[String:Any]';
            case FlexionsTypes::DATA:
                return 'Data';
        }
        return FlexionsTypes::NOT_SUPPORTED;
    }

}