<?php

/*
 Created by Benoit Pereira da Silva on 20/04/2013.
Copyright (c) 2013  http://www.pereira-da-silva.com

This file is part of Flexions

Flexions is free software: you can redistribute it and/or modify
it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Flexions is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU LESSER GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
along with Flexions  If not, see <http://www.gnu.org/licenses/>
*/

class  PropertyRepresentation {

    /**
     * @var string unique Name of the property
     */
    public $name;

    /**
     * @var string  Documentation  of the property
     */
    public $description;

    /**
     * @var string Type  const enumerated in FlexTypes
     */
    public $type;

    /**
     * @var  string When the type is an _OBJECT or a _COLLECTION or an _ENUM, you can specify its class
     */
    public $instanceOf;


    /**
     * When $type is _ENUM you can enumerate the values in an array or an associative array
     * @var array
     */
    public $enumerations = array();

    /**
     * @var bool set to true if the type is generated (allow to to discriminate primitive from generated types)
     */
    public $isGeneratedType = false;


    /**
     * @var bool defines if the class is external to the generative package.
     */
    public $isExternal = false;

    /**
     * @var  bool Whether or not the property is required
     */
    public $required;

    /**
     * @var  mixed Default value to use if no value is supplied
     */
    public $default;

    /**
     * @var   When the type is a string, you can specify the regex pattern that a value must match
     */
    public $pattern;


    /**
     * @var bool set to false if the property is not extractible in a sub-graph copy operation
     */
    public $isExtractible = true;


    /**
     * @var array an associative array to pass specific metadata
     */
    public $metadata=array();

}

?>