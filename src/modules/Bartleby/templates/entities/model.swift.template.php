<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation
#if !USE_EMBEDDED_MODULES
<?php if (!$hideImportDirectives) { echoIndent($includeBlock,1); } ?>
#endif

// MARK: <?php echo $d->description?>

@objc open class <?php echo ucfirst($d->name)?> : <?php echo GenerativeHelperForSwift::getBaseClass($d); ?>{

    // Universal type support
    <?php echo $inheritancePrefix ?>open class func typeName() -> String {
        return "<?php echo ucfirst($d->name)?>"
    }
<?php

while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;

    echo(cr());

    if($property->description!=''){
        echoIndent('//' .$property->description. cr(), 1);
    }
    // Infer consistant semantics.
    if($property->method==Method::IS_CLASS){
        // we can't currently serialize Static members.
        $property->isSerializable=false;
    }

    // Dynamism, method, scope, optionality and mutability support

    $dynanic=($property->isDynamic ? '@objc dynamic ':'');
    $method=($property->method==Method::IS_CLASS ? 'static ' : '' );
    $scope='';
    if($property->scope==Scope::IS_PRIVATE){
        $scope='private ';
    }else if ($property->scope==Scope::IS_PROTECTED){
        $scope='internal ';
    }else{
       $scope='open '; // We could may be switch to public?
    }
    $mutable = ($property->mutability == Mutability::IS_VARIABLE ? 'var ' : 'let ');
    $prefix = $dynanic . $method . $scope . $mutable;
    $optionalSuffix = ($property->required === true ? "" : "?");

    //Generate the property line

    if($property->type==FlexionsTypes::ENUM){
        $enumTypeName=ucfirst($name);
        echoIndent('public enum ' .$enumTypeName.':'.ucfirst(FlexionsSwiftLang::nativeTypeFor($property->instanceOf)). '{', 1);
        foreach ($property->enumerations as $element) {
            if($property->instanceOf==FlexionsTypes::STRING){
                echoIndent('case ' .lcfirst($element).' = "'.$element.'"', 2);
            }elseif ($property->instanceOf==FlexionsTypes::INTEGER){
                echoIndent('case ' .lcfirst($element), 2);
            } else{
                echoIndent('case ' .lcfirst($element).' = '.$element, 2);
            }
        }
        echoIndent('}', 1);
        echoIndent($prefix . $name . ':' . $enumTypeName . $optionalSuffix . _propertyValueString($property), 1);
    }else if($property->type==FlexionsTypes::COLLECTION){
        $instanceOf=FlexionsSwiftLang::nativeTypeFor($property->instanceOf);
        if ($instanceOf==FlexionsTypes::NOT_SUPPORTED){
            $instanceOf=$property->instanceOf;
        }
        echoIndent($prefix . $name . ':[' . ucfirst($instanceOf) . ']' . $optionalSuffix . _propertyValueString($property), 1);
    }else if($property->type==FlexionsTypes::OBJECT){
        echoIndent($prefix . $name . ':' . ucfirst($property->instanceOf) . $optionalSuffix . _propertyValueString($property), 1);
    }else{
        $nativeType=FlexionsSwiftLang::nativeTypeFor($property->type);
        if(strpos($nativeType,FlexionsTypes::NOT_SUPPORTED)===false){
            echoIndent($prefix . $name . ':' . $nativeType . $optionalSuffix . _propertyValueString($property), 1);
        }else{
            echoIndent($prefix. $name .':Not_Supported = Not_Supported()//'. ucfirst($property->type), 1);
        }
    }
}
?>

<?php echo $baseObjectBlock ?>
<?php echo $mappableBlock ?>
<?php echo $secureCodingBlock ?>
<?php echo $codableBlock ?>
<?php echo $exposedBlock ?>

    // MARK: - Initializable
   <?php echo $inversedInheritancePrefix?> required public init() {
        <?php echo $superInit ?>
    }

    // MARK: - UniversalType
    <?php echo $inheritancePrefix?> open class var collectionName:String{
        return "<?php echo lcfirst(Pluralization::pluralize($d->name)) ?>"
    }

    <?php echo $inheritancePrefix?> open var d_collectionName:String{
        return <?php echo ucfirst($d->name)?>.collectionName
    }
}