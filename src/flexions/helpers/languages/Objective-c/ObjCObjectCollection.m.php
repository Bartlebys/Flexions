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
}

- (instancetype)initInRegistry:(WattRegistry*)registry{
    self=[super initInRegistry:registry];
    if(self){
        _collection=[NSMutableArray array];
    }
    return self;
}


- (NSString*)description{
    if([self isAnAlias])
        return [super aliasDescription];
	NSMutableString *s=[NSMutableString string];
    [s appendFormat:@"Collection of %@\n",@"<?php echo $className;?>"];
    [s appendFormat:@"With of %i members\n",[_collection count]];
	return s;
}

- (void)enumerateObjectsUsingBlock:(void (^)(<?php echo $className;?> *obj, NSUInteger idx, BOOL *stop))block{
	 NSUInteger idx = 0;
    BOOL stop = NO;
    for( <?php echo $className;?>* obj in _collection ){
        block(obj, idx++, &stop);
        if( stop )
            break;
    }
}

- (<?php echo $collectionClassName;?>*)filteredCollectionUsingPredicate:(NSPredicate *)predicate withRegistry:(WattRegistry *)registry{
	return (<?php echo $collectionClassName;?>*)[super filteredCollectionUsingPredicate:predicate withRegistry:registry];
}

- (NSUInteger)count{
    return [_collection count];
}
- (<?php echo $className;?> *)objectAtIndex:(NSUInteger)index{
	return (<?php echo $className;?>*)[super  objectAtIndex:index];
}

- (<?php echo $className;?> *)lastObject{
    return  (<?php echo $className;?>*)[super lastObject];
}

- (<?php echo $className;?> *)firstObjectCommonWithArray:(NSArray*)array{
    return (<?php echo $className;?>*)[super firstObjectCommonWithArray:array];
}

- (void)addObject:(<?php echo $className;?>*)anObject{
 	[super addObject:anObject];
}

- (void)insertObject:(<?php echo $className;?>*)anObject atIndex:(NSUInteger)index{
	[super insertObject:anObject atIndex:index];
}

- (void)removeLastObject{
	[super removeLastObject];
}

- (void)removeObjectAtIndex:(NSUInteger)index{
    [super removeObjectAtIndex:index];
}

- (void)replaceObjectAtIndex:(NSUInteger)index withObject:(<?php echo $className;?>*)anObject{
    [super replaceObjectAtIndex:index withObject:anObject];
}


@end
<?php ////////////   GENERATION ENDS HERE   ////////// ?>