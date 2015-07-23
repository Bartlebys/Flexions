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

require_once FLEXIONS_ROOT_DIR . 'flexions/core/Flexed.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_MODULES_DIR . 'watt-Objective-c/ObjectiveCHelper.class.php';




/**
 *
 * @var $f Flexed
 * @var $d \stdClass
 *     
 */


/**
 * Returns a class name fragment
 *
 * @param string $d
 * @param string $classesPrefix
 * @return string
 */
function getCurrentClassNameFragment($d,$classesPrefix="") {
	if (! $d)
		return '$d should be set in getCurrentClassFragment( )';
	if (property_exists ( $d, 'name' )) {
		return $classesPrefix.$d->name ;
	} else {
		return 'UNDEFINDED-CLASS-FRAGMENT';
	}
}


/**
 * Get action documentation
 *
 * @param string $d
 * @return string
 */
function getDocumentation($d) {
	if (! $d)
		return '$d should be set in getDocumentation( )';
	if (property_exists ( $d, 'description' ))
		return "\n/*\n" . $d->description . "\n*/\n";
	else
		return '';
}

/**
 * Returns the comment "header"
 *
 * @param Flexed $flexed        	
 * @return string
 */
function getCommentHeader($flexed) {
	return "//  ".$flexed->fileName."
//  ".$flexed->projectName."
//
//  Generated by Flexions  
//  Copyright (c) ".  $flexed->year." ". $flexed->company." All rights reserved.\n \n";
}


/**
 * 
 * @param string $prefix
 * @param string $baseClassName
 * @return string
 */
function getCollectionClassName($prefix,$baseClassName ){
	return ucfirst($prefix).COLLECTION_OF.$baseClassName;
}

/**
 * 
 * @param string $collectionClassName
 * @return string
 */
function getClassNameFromCollectionClassName($collectionClassName){
	return str_replace(COLLECTION_OF, "", $collectionClassName) ;
	
}


