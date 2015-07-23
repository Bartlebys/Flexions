<?php


/**
 * Class FlexionsTypes
 * Enumerates the "primitive" type of Flexions as const
 */
class FlexionsTypes{
    // Basic types
    const STRING='string';
    const INTEGER='integer';
    const BOOLEAN='boolean';
    const FLOAT='float';
    const DOUBLE='double';
    //
    const BYTE='byte';
    const DATETIME='dateTime';
    const URL='url';

    const FILE='file';

    // Generic type
    const DICTIONARY='';
    //
    const OBJECT='object'; // Used to reference an $instanceOf
    const COLLECTION='collection';// Used to reference a collection of $instanceOf that can be any FlexionsType
    const ENUM='enum';// Used to reference a enumeration of $instanceOf that can be any FlexionsType
    //
    const NOT_SUPPORTED='Not_Supported';
}