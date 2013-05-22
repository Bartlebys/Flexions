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


-(id)initInDefaultRegistry{
    self=[self init];
    if(self){
<?php 
while ( $d->iterateOnProperties () === true ) {
	$property = $d->getProperty ();
	if ($property->instanceOf != null) {
		$instanceOf = $property->instanceOf;
		$pos = strpos ( $instanceOf, COLLECTION_OF );
		if ($pos >= 0) {
			$propertyNameLocal=$property->name;
			echoindent ( "self.$propertyNameLocal=[[$instanceOf alloc] initInDefaultRegistry];\n", 2 );
		}
	}
}?>   
    }
    return self;
}

- (<?php echo getCurrentClassNameFragment($d,$f->prefix);?> *)localized{
    [self localize];
    return self;
}


+ (<?php echo getCurrentClassNameFragment($d,$f->prefix);?>*)instanceFromDictionary:(NSDictionary *)aDictionary{
	<?php echo getCurrentClassNameFragment($d,$f->prefix);?>*instance = nil;
	NSInteger wtuinstID=[[aDictionary objectForKey:__uinstID__] integerValue];
    if(wtuinstID>0){
        return (<?php echo getCurrentClassNameFragment($d,$f->prefix);?>*)[[wattMAPI defaultRegistry] objectWithUinstID:wtuinstID];
    }
	if([aDictionary objectForKey:__className__] && [aDictionary objectForKey:__properties__]){
		Class theClass=NSClassFromString([aDictionary objectForKey:__className__]);
		id unCasted= [[theClass alloc] init];
		[unCasted setAttributesFromDictionary:aDictionary];
		instance=(<?php echo getCurrentClassNameFragment($d,$f->prefix);?>*)unCasted;
	}
	return instance;
}


- (void)setAttributesFromDictionary:(NSDictionary *)aDictionary{
	if (![aDictionary isKindOfClass:[NSDictionary class]]) {
		return;
	}
    if([aDictionary objectForKey:__className__] && [aDictionary objectForKey:__properties__]){
        id properties=[aDictionary objectForKey:__properties__];
        NSString *selfClassName=NSStringFromClass([self class]);
        if (![selfClassName isEqualToString:[aDictionary objectForKey:__className__]]) {
             [NSException raise:@"WTMAttributesException" format:@"selfClassName %@ is not a %@ ",selfClassName,[aDictionary objectForKey:__className__]];
        }
        if([properties isKindOfClass:[NSDictionary class]]){
            for (NSString *key in properties) {
                id value=[properties objectForKey:key];
                if(value)
                    [self setValue:value forKey:key];
            }
        }else{
            [NSException raise:@"WTMAttributesException" format:@"properties is not a NSDictionary"];
        }
    }else{
        [self setValuesForKeysWithDictionary:aDictionary];
    }
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
			echoIndent("[super setValue:value forKey:key];\n",2);	
			echoIndent("}\n",1);
		}
	}
?>
}

/*
// @todo implement the default values? 
- (void)setNilValueForKey:(NSString *)theKey{
    if ([theKey isEqualToString:@"age"]) {
        [self setValue:[NSNumber numberWithFloat:0.0] forKey:@"age"];
    } else
        [super setNilValueForKey:theKey];
}

//@todo implement the validation process
-(BOOL)validateName:(id *)ioValue error:(NSError * __autoreleasing *)outError{
 
    // The name must not be nil, and must be at least two characters long.
    if ((*ioValue == nil) || ([(NSString *)*ioValue length] < 2)) {
        if (outError != NULL) {
            NSString *errorString = NSLocalizedString(
                    @"A Person's name must be at least two characters long",
                    @"validation: Person, too short name error");
            NSDictionary *userInfoDict = @{ NSLocalizedDescriptionKey : errorString };
            *outError = [[NSError alloc] initWithDomain:@"PERSON_ERROR_DOMAIN"
                                                    code:1//PERSON_INVALID_NAME_CODE
                                                userInfo:userInfoDict];
        }
        return NO;
    }
    return YES;
}
*/


- (NSDictionary*)dictionaryRepresentation{
	NSMutableDictionary *wrapper = [NSMutableDictionary dictionary];
    NSMutableDictionary *dictionary=[NSMutableDictionary dictionary];
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
	[wrapper setObject:NSStringFromClass([self class]) forKey:__className__];
    [wrapper setObject:dictionary forKey:__properties__];
    [wrapper setObject:[NSNumber numberWithInteger:self.uinstID] forKey:__uinstID__];
    return wrapper;
}

-(NSString*)description{
	NSMutableString *s=[NSMutableString string];
<?php
	while ( $d ->iterateOnProperties() === true ) {
		$property = $d->getProperty();
	    $type=$languageHelper->nativeTypeForProperty($property,$allowScalars);
	    $name=$property->name;
	    $s=$languageHelper->objectFromExpression("self.$name", $type);
	    echoIndent("[s appendFormat:@\"$name : %@\\n\",$s];\n",1);
	}
 ?>
	return s;
}

@end
<?php ////////////   GENERATION ENDS HERE   ////////// ?>