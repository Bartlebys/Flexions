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

/**
 * 	When using this template you must define :	$f->prefix, $allowScalars 
 *	And you can inject : 	$markAsDynamic==true to inject a @dynamic tag (for core data)
 */

require_once FLEXIONS_ROOT_DIR.'flexions/helpers/languages/Objective-c/ObjCGeneric.functions.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation */
/* @var $languageHelper ObjectiveCHelper */
/* @var $maskedImports string */

$f->fileName = getCurrentClassNameFragment($d,$f->prefix).'.m';
$languageHelper=new ObjectiveCHelper();

?><?php ////////////   GENERATION STARTS HERE   ////////// ?>
<?php if($f->license!=null) include $f->license;?>
<?php echoIndent( getCommentHeader($f),0);?>
<?php echoIndent('#import "'.getCurrentClassNameFragment($d,$f->prefix).'.h"',0);?> 
<?php 
while ( $d ->iterateOnProperties() === true ) {
	$property = $d->getProperty();
	if($property->instanceOf!=null){
		$instanceOf=$property->instanceOf;
		echoIndent("#import \"$instanceOf.h\"\n",0);
	}
}
?>

@implementation <?php echo getCurrentClassNameFragment($d,$f->prefix);?> 
<?php if($markAsDynamic==true){
	echoIndent("\n",0);
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
		$name=$property->name;
		echoIndent("@dynamic $name;\n",0);
	}
}
?>

+ (<?php echo getCurrentClassNameFragment($d,$f->prefix);?>*)instanceFromDictionary:(NSDictionary *)aDictionary{
<?php echoIndent( getCurrentClassNameFragment($d,$f->prefix)."*instance = [[".getCurrentClassNameFragment($d,$f->prefix)." alloc] init];\n",1);?>
	[instance setAttributesFromDictionary:aDictionary];
	return instance;
}

- (void)setAttributesFromDictionary:(NSDictionary *)aDictionary{
	if (![aDictionary isKindOfClass:[NSDictionary class]]) {
		return;
	}
	[self setValuesForKeysWithDictionary:aDictionary];
}

- (void)setValue:(id)value forKey:(NSString *)key {
<?php
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
	    $name=$property->name;
	    $valueString=$languageHelper->valueStringForProperty("value",$property);
	    if($d->firstProperty()){
	    	echoIndent("if ([key isEqualToString:@\"$name\"]){\n",1);
	    	echoIndent("[super setValue:$valueString forKey:@\"$name\"];\n",2);
	    }else{
			echoIndent("} else if ([key isEqualToString:@\"$name\"]) {\n",1);
			echoIndent("[super setValue:$valueString forKey:@\"$name\"];\n",2);
		}
		if ($d->lastProperty()){
			echoIndent("} else {\n",1);
			echoIndent("[super setValue:value forUndefinedKey:key];\n",2);	
			echoIndent("}\n",1);
		}
	}
?>
}

- (NSDictionary*)dictionaryRepresentation{
	NSMutableDictionary *dictionary = [NSMutableDictionary dictionary];
<?php
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
	    $type=$languageHelper->nativeTypeForProperty($property,$allowScalars);
	    $name=$property->name;
	    $s=$languageHelper->objectFromExpression("self.$name", $type);
	 	if($property->isGeneratedType==true){
			echoIndent("[dictionary setValue:[$s dictionaryRepresentation] forKey:@\"$name\"];\n",1);
		}else{
			echoIndent("[dictionary setValue:$s forKey:@\"$name\"];\n",1);
		}
	}
 ?>
	return dictionary;
}

-(NSString*)description{
	NSMutableString *s=[NSMutableString string];
<?php
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
	    $type=$languageHelper->nativeTypeForProperty($property,$allowScalars);
	    $name=$property->name;
	    $s=$languageHelper->objectFromExpression("self.$name", $type);
	    echoIndent("[s appendFormat:@\"$name : %@\",$s];\n",1);
	}
 ?>
	return s;
}

@end
<?php ////////////   GENERATION ENDS HERE   ////////// ?>