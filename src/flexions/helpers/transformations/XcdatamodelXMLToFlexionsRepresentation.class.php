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


require_once FLEXIONS_ROOT_DIR . 'flexions/helpers/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/helpers/languages/Objective-c/ObjCGeneric.functions.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/helpers/languages/Objective-c/ObjectiveCHelper.class.php';
class XCDDataXMLToFlexionsRepresentation {
	
	/**
	 *
	 * @param string $descriptorFilePath        	
	 * @throws Exception
	 * @return ProjectRepresentation
	 */
	function projectRepresentationFromXcodeModel($descriptorFilePath, $nativePrefix = "") {
		fLog ( "Invoking XCDDataXMLToFlexionsRepresentation.projectRepresentationFromXcodeModel()" . cr () . cr (), true );
		
		$r = new ProjectRepresentation ();
		$r->classPrefix = $nativePrefix;
		$r->entities = array ();
		
		$dom = new DomDocument ();
		$pth = realpath ( $descriptorFilePath );
		$dom->load ( $pth );
		$entities = $dom->getElementsByTagName ( 'entity' );
		
		foreach ( $entities as $entity ) {
			
			/* @var DOMNode $entity */
			
			$entityR = new EntityRepresentation ();
			if ($entity->hasAttribute ( "representedClassName" )) {
				$entityR->name = $entity->getAttribute ( "representedClassName" );
				fLog ( 'Parsing : ' . $entityR->name . cr (), true );
			} else if ($entity->hasAttribute ( "name" )) {
				$entityR->name = $entity->getAttribute ( "name" );
			} else {
				throw new Exception ( 'entity with no representedClassName and no name' );
			}
			
			// We parse the properties
			$attributes = $entity->getElementsByTagName ( 'attribute' );
			$entityR->type = "object"; // Entities are objects
			if ($entity->hasAttribute ( "parentEntity" )) {
				$entityR->instanceOf = $nativePrefix . $entity->getAttribute ( "parentEntity" );
			} else {
				// We donnot qualifiy the instance
				// a requalification can be done according to the situation in
			// the template
			}
			
			$entityUserInfos = $entity->getElementsByTagName ( "userInfo" );
			foreach ( $entityUserInfos as $entityUserInfo ) {
				$userInfoEntries = $entityUserInfo->getElementsByTagName ( "entry" );
				foreach ( $userInfoEntries as $userInfoEntry ) {
					
					// $entity->generateCollectionClass?
					// We generate a collection class if :
					// 1- there is a relation 1-n to
					// 2- the entity embedds an explicit generation directive :
					// Sample :
					// <entity name="Datum" representedClassName="Datum"
					// syncable="YES">
					// <attribute name="key" attributeType="String"
					// syncable="YES"/>
					// <userInfo> <entry key="generate"
					// value="collection"/></userInfo>
					if ($userInfoEntry->hasAttribute ( "key" ) && rtrim ( $userInfoEntry->getAttribute ( "key" ) ) == "generate" && ($userInfoEntry->hasAttribute ( "value" ) && rtrim ( $userInfoEntry->getAttribute ( "value" ) ) == "collection")) {
						$entityR->generateCollectionClass = true;
					}
					if ($userInfoEntry->hasAttribute ( "key" ) && rtrim ( $userInfoEntry->getAttribute ( "key" ) ) == "parent" && $userInfoEntry->hasAttribute ( "value" )) {
						$entityR->instanceOf =$userInfoEntry->getAttribute ( "value" );
					}
				}
			}
			
			foreach ( $attributes as $attribute ) {
				$property = new PropertyRepresentation ();
				if ($attribute->hasAttribute ( "name" )) {
					$property->name = $attribute->getAttribute ( "name" );
				} else {
					throw new Exception ( 'property with no name' );
				}
				
				if ($attribute->hasAttribute ( "attributeType" )) {
					$property->type = $attribute->getAttribute ( "attributeType" );
				} else {
					$property->type = ObjectiveCHelper::UNDEFINED_TYPE;
				}
				
				$userInfos = $attribute->getElementsByTagName ( 'entry' );
				foreach ( $userInfos as $userInfo ) {
						if ($userInfo->hasAttribute ( "key" ) && rtrim ( $userInfo->getAttribute ( "key" ) ) == "type" && rtrim ( $userInfo->hasAttribute ( "value" ) )) {
							/*
							 * The user can define a specific type within the
							 * user info Conventionnally we use a "type" as a
							 * key. <attribute name="myDictionary"
							 * optional="YES" attributeType="String"
							 * syncable="YES"> <userInfo> <entry key="type"
							 * value="dictionary"/> </userInfo> </attribute>
							 */
							$propertyType = $userInfo->getAttribute ( "value" );
							$property->type = $propertyType;
						}
				}
				
			
				if ($attribute->hasAttribute ( "defaultValueString" )){
					$property->default = $attribute->getAttribute ( "defaultValueString" );
				}
					// Add the property to the entity
				$entityR->properties [$property->name] = $property;
			}
			
			// We parse the relationships
			$relationships = $entity->getElementsByTagName ( 'relationship' );
			foreach ( $relationships as $relationship ) {
				$property = new PropertyRepresentation ();
				if ($relationship->hasAttribute ( "name" )) {
					$property->name = $relationship->getAttribute ( "name" );
				} else {
					throw new Exception ( 'property with no name' );
				}
			
				$tooMany = false;
				if ($relationship->hasAttribute ( "toMany" )) {
					$tooMany = ($relationship->getAttribute ( "toMany" ) == "YES");
				}
				
				if ($relationship->hasAttribute ( "destinationEntity" )) {
					$destinationEntity = $relationship->getAttribute ( "destinationEntity" );
					if ($tooMany == true) {
						$property->type = "object";
						$property->instanceOf = getCollectionClassName ( $nativePrefix, $destinationEntity );
						$property->isGeneratedType = true;
					} else {
						$property->type = "object";
						$property->instanceOf = $nativePrefix . ucfirst ( $destinationEntity );
						$property->isGeneratedType = true;
					}
				} else {
					$property->type = ObjectiveCHelper::UNDEFINED_TYPE;
				}
				
				// Add the property to the entity
				$entityR->properties [$property->name] = $property;
			}
			
			$r->entities [$entityR->name] = $entityR;
		}
		
		fLog ( "" . cr (), true );
		return $r;
	}
}

?>