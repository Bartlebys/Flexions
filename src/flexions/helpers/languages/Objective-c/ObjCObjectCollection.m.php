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
 * THIS TEMPLATE IS A SUB-TEMPLATE
 * IT RELIES ON  $sf
 * 	When using this template  you must define :  $collectionClassName;
 */
require_once FLEXIONS_ROOT_DIR.'flexions/helpers/languages/Objective-c/ObjCGeneric.functions.php';

/* @var $sf Flexed */
/* @var string $collectionClassName */

if(!isset($collectionClassName)){
	throw new Exception('$baseClassName is not set');
}
$sf->fileName =$collectionClassName.".m";
$className=getClassNameFromCollectionClassName($collectionClassName);


?><?php ////////////   GENERATION STARTS HERE   ////////// ?>
<?php if($f->license!=null) include $f->license;?>
<?php echo getCommentHeader($f);?>

<?php echo "#import \"$collectionClassName.h\"";?> 

@implementation <?php echo $collectionClassName;?>{
	NSMutableArray* _collection;
}

-(id)init{
    self=[super init];
    if(self){
        _collection=[NSMutableArray array];
    }
    return self;
}

+ (<?php echo $collectionClassName;?>*)instanceFromDictionary:(NSDictionary *)aDictionary{
<?php echoindent( "$collectionClassName* instance = [[$collectionClassName alloc] init];\n",1);?>
	[instance setAttributesFromDictionary:aDictionary];
	return instance;
}


- (void)setAttributesFromDictionary:(NSDictionary *)aDictionary{
	if (![aDictionary isKindOfClass:[NSDictionary class]]) {
		return;
	}
	_collection=[NSMutableArray array];
    NSArray *a=[aDictionary objectForKey:@"collection"];
    for (NSDictionary*objectDictionary in a) {
        <?php echo $className;?>*o=[<?php echo $className;?> instanceFromDictionary:objectDictionary];
        [_collection addObject:o];
    }
}


- (NSDictionary*)dictionaryRepresentation{
	NSMutableDictionary *dictionary = [NSMutableDictionary dictionary];
    NSMutableArray *array=[NSMutableArray array];
    for (<?php echo $className;?> *o in _collection) {
        NSDictionary*oDictionary=[o dictionaryRepresentation];
        [array addObject:oDictionary];
    }
    [dictionary setValue:array forKey:@"collection"];
	return dictionary;
}


-(NSString*)description{
	NSMutableString *s=[NSMutableString string];
    [s appendFormat:@"Collection of %@\n",@"<?php echo $className;?>"];
    [s appendFormat:@"With of %i members\n",[_collection count]];
	return s;
}


- (NSUInteger)count{
    return [_collection count];
}
- (<?php echo $className;?> *)objectAtIndex:(NSUInteger)index{
	return [_collection objectAtIndex:index];
}


- (void)addObject:(<?php echo $className;?>*)anObject{
 	[_collection addObject:anObject];
}

- (void)insertObject:(<?php echo $className;?>*)anObject atIndex:(NSUInteger)index{
	[_collection insertObject:anObject atIndex:index];
}

- (void)removeLastObject{
	[_collection removeLastObject];
}

- (void)removeObjectAtIndex:(NSUInteger)index{
    [_collection removeObjectAtIndex:index];
}

- (void)replaceObjectAtIndex:(NSUInteger)index withObject:(<?php echo $className;?>*)anObject{
    [_collection replaceObjectAtIndex:index withObject:anObject];
}


@end
<?php ////////////   GENERATION ENDS HERE   ////////// ?>