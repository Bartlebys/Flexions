<?php


/**
 * Class FlexionsTypes
 * Enumerates the "primitive" type of Flexions as const
 */
class FlexionsTypes{
    const _STRING='string';
    const _INTEGER='integer';
    const _BOOLEAN='boolean';
    const _OBJECT='object'; // Used to reference an $instanceOf
    const _COLLECTION='collection';// Used to reference a collection of $instanceOf that can be a FlexionsType
    const _FLOAT='float';
    const _DOUBLE='double';
    const _BYTE='byte';
    const _DATETIME='dateTime';
    const _URL='url';
    const _NOT_SUPPORTED='Not_Supported';
}