e<?php

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
$sf->fileName =$collectionClassName.".h";
$className=getClassNameFromCollectionClassName($collectionClassName);

?><?php ////////////   GENERATION STARTS HERE   ////////// ?>
<?php if($f->license!=null) include $f->license;?>
<?php echo getCommentHeader($sf);?>

<?php echo "#import \"$className.h\"";?> 

@interface <?php echo $collectionClassName;?>:NSObject {
}

+ (<?php echo $collectionClassName;?> *)instanceFromDictionary:(NSDictionary *)aDictionary;
- (void)setAttributesFromDictionary:(NSDictionary *)aDictionary;
- (NSDictionary *)dictionaryRepresentation;

- (NSUInteger)count;
- (<?php echo $className;?> *)objectAtIndex:(NSUInteger)index;
- (void)addObject:(<?php echo $className;?>*)anObject;
- (void)insertObject:(<?php echo $className;?>*)anObject atIndex:(NSUInteger)index;
- (void)removeLastObject;
- (void)removeObjectAtIndex:(NSUInteger)index;
- (void)replaceObjectAtIndex:(NSUInteger)index withObject:(<?php echo $className;?>*)anObject;

@end
<?php ////////////   GENERATION ENDS HERE   ////////// ?>