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
<?php 
if($markAsDynamic==true){
	// We generate the dynamic
	echoIndent("\n",0);
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
		$name=$property->name;
		echoIndent("@dynamic $name;\n",0);
	}
}else{
	// We generate the synthesize
	echoIndent("\n",0);
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
		$name=$property->name;
		echoIndent("@synthesize $name=_$name;\n",0);
	}
}
?>

<?php if(isset($protocols) &&  (strpos($protocols,"WattCopying")!==false) ) { ?>

#pragma  mark WattCopying

- (instancetype)wattCopyInRegistry:(WattRegistry*)registry{
    <?php echo getCurrentClassNameFragment($d,$f->prefix);?> *instance=[self copy];
    [registry addObject:instance];
    return instance;
}


// NSCopying
- (id)copyWithZone:(NSZone *)zone{
    <?php echo getCurrentClassNameFragment($d,$f->prefix);?> *instance=[[[super class] allocWithZone:zone] init];
    <?php echoIndent("instance->_registry=nil; // We want to furnish a registry free copy\n",1);?>
	<?php echoIndent("// we do not provide an _uinstID\n",1);	?>
   	<?php while ( $d ->iterateOnProperties() === true ) {
   				/* @var $property PropertyRepresentation */
  	 			$property = $d->getProperty();
		  		$name=$property->name;
		  		$ivar="_".$name;
		  		$nativeType=$languageHelper->nativeTypeForProperty($property);
		  		if($languageHelper->isScalar($nativeType)){
					echoIndent("instance->".$ivar."=".$ivar.";\n",2);
				}else{
					echoIndent("instance->".$ivar."=[".$ivar." copyWithZone:zone];\n",2);
				}
   	}?>
    return instance;
}

#pragma mark -

<?php } ?>

- (void)setValue:(id)value forKey:(NSString *)key {
<?php
	if(count($d->properties)==0){
		echoIndent("[super setValue:value forKey:key];\n",1);
	}else{
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
					echoIndent("[super setValue:value forKey:key];\n",2);	
				echoIndent("}\n",1);
			}
		}
}
?>
}

<?php  
	// We generate the setters && getters with aliases support
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
		$name=$property->name;
		if($property->isGeneratedType){
				$ivarName="_".$name;
				
				// Getter 
				echoIndent("- (". $property->instanceOf."*)$name{\n",0);
					echoIndent("if([$ivarName isAnAlias]){\n",1);
						echoIndent("id o=[_registry objectWithUinstID:$ivarName.uinstID];\n",2);
						echoIndent("if(o){\n",2);
						echoIndent("$ivarName=o;\n",3);
						echoIndent("}\n",2);
					echoIndent("}\n",1);
					/*
					$instanceOf = $property->instanceOf;
					$pos = strpos ( $instanceOf, COLLECTION_OF );
					if ($pos >= 0) {
						echoIndent("if(!$ivarName){\n",1);
							echoindent ( "$ivarName=[[$instanceOf alloc] initInRegistry:_registry];\n", 2 );
						echoIndent("}\n",1);
					}
					*/
					echoIndent("return $ivarName;\n",1);
				echoIndent("}\n",0);
				echoIndent("\n",0);
				echoIndent("\n",0);
				
				// Auto Getter (that create an instance if nil) 
				echoIndent("- (".$property->instanceOf."*)".$name."_auto{\n",0);
					echoIndent("$ivarName=[self $name];\n",1);
					echoIndent("if(!$ivarName){\n",1);
						echoIndent("$ivarName=[[".$property->instanceOf." alloc] initInRegistry:_registry];\n",2);
					echoIndent("}\n",1);
					echoIndent("return $ivarName;\n",1);
				echoIndent("}\n",0);
				echoIndent("\n",0);
				
				// Setter 
				echoIndent("- (void)set".ucfirst($name).":(".$property->instanceOf."*)$name{\n",0);
					echoIndent("$ivarName=$name;\n",1);
				echoIndent("}\n",0);
				echoIndent("\n",0);
		}
	}
?>

- (NSDictionary *)dictionaryRepresentationWithChildren:(BOOL)includeChildren{
	NSMutableDictionary *wrapper = [NSMutableDictionary dictionary];
	[wrapper setObject:NSStringFromClass([self class]) forKey:__className__];
    [wrapper setObject:[self dictionaryOfPropertiesWithChildren:includeChildren] forKey:__properties__];
    [wrapper setObject:[NSNumber numberWithInteger:self.uinstID] forKey:__uinstID__];
    return wrapper;
}

- (NSMutableDictionary*)dictionaryOfPropertiesWithChildren:(BOOL)includeChildren{
    NSMutableDictionary *dictionary=[super dictionaryOfPropertiesWithChildren:includeChildren];
<?php
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
	    $type=$languageHelper->nativeTypeForProperty($property,$allowScalars);
	    $name=$property->name;
	    $s=$languageHelper->objectFromExpression("self.$name", $type);
	 	if($property->isGeneratedType==true){
			echoIndent("if(self.$name){\n",1);
				echoIndent("if(includeChildren){\n",2);
					echoIndent("[dictionary setValue:[$s dictionaryRepresentationWithChildren:includeChildren] forKey:@\"$name\"];\n",3);
				echoIndent("}else{\n",2);
					echoIndent("[dictionary setValue:[$s aliasDictionaryRepresentation] forKey:@\"$name\"];\n",3);
				echoIndent("}\n",2);
			echoIndent("}\n",1);
		}else{
			echoIndent("[dictionary setValue:$s forKey:@\"$name\"];\n",1);
		}
	}
 ?>
    return dictionary;
}


- (NSString*)description{
    if([self isAnAlias])
        return [super aliasDescription];
    NSMutableString *s=[NSMutableString stringWithString:[super description]];
	[s appendFormat:@"Instance of %@ (%i) :\n",@"<?php echo getCurrentClassNameFragment($d,$f->prefix);?> ",self.uinstID];
<?php
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
	    $type=$languageHelper->nativeTypeForProperty($property,$allowScalars);
	    $name=$property->name;
	    if($property->isGeneratedType==true){
			$s="NSStringFromClass([self.$name class])";
		}else{
			$s=$languageHelper->objectFromExpression("self.$name", $type);
		}
	    echoIndent("[s appendFormat:@\"$name : %@\\n\",$s];\n",1);
	}
 ?>
	return s;
}

@end
<?php ////////////   GENERATION ENDS HERE   ////////// ?>