<?php
/**
 *
 * This is a Block template (not a full template)
 * That can be used to generate a Swift 4++ Codable block in an entity.
 * $blockRepresentation must be set.
 *
 *  Usage sample : Codable
 *
 * $codableBlock='';
 * if($modelsShouldConformToCodable) {
 *   We define the context for the block
 *   Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>false,'decodableblockEndContent'=>'','encodableblockEndContent'=>'']);
 *      $codableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Codable.swift.block.php');
 * }
 *
 */

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';


///////////////////////
// LOCAL REQUIREMENTS
///////////////////////

/* @var $blockRepresentation ActionRepresentation || EntityRepresentation */
/* @var $decodableblockEndContent string */
/* @var $encodableblockEndContent string */
/* @var $isBaseObject boolean */

$blockRepresentation=Registry::instance()->valueForKey('blockRepresentation');
$outSideBartleby=Registry::instance()->valueForKey('outSideBartleby');
$decodableblockEndContent=Registry::instance()->valueForKey('decodableblockEndContent');
$encodableblockEndContent=Registry::instance()->valueForKey('encodableblockEndContent');
$isBaseObject=Registry::Instance()->valueForKey('isBaseObject');
$entityName=$blockRepresentation->concernedType();

if (!isset($blockRepresentation)){
    return NULL;
}

if(!isset($isBaseObject)){
    $isBaseObject=false;
}

////////////////////////
// VARIABLES DEFINITION
////////////////////////

$inheritancePrefix = ('override ');


$name = $blockRepresentation->name;
$codingKeysDeclaration = 'String,CodingKey';
$codingKeysName= 'CodingKeys';

////////////////////////
// BLOCK TEMPLATE LOGIC
////////////////////////

?>

    // MARK: - Codable


    public enum <?php echo($codingKeysName)?>: <?php echo $codingKeysDeclaration ?>{
<?php
// Codable support for entities and parameters classes.
// $d may be ActionRepresentation or EntityRepresentation
$isEntity=($blockRepresentation instanceof EntityRepresentation);
while ($isEntity?$blockRepresentation->iterateOnProperties():$blockRepresentation->iterateOnParameters() === true) {
    /* @var $property PropertyRepresentation */
    $property = $isEntity?$blockRepresentation->getProperty():$blockRepresentation->getParameter();
    $name = $property->name;
    if (isset($property->codingKey)){
        echoIndent('case '.$name.' = "'.$property->codingKey.'"' ,2);
    }else{
        echoIndent('case '.$name,2);
    }

}
if ($entityName == 'ManagedModel') {
    echoIndent('case typeName',2);
}
?>
    }

    required public init(from decoder: Decoder) throws{
<?php if(!$isBaseObject){echoIndent('try super.init(from: decoder)',2);}else{echoIndent('super.init()',2);} ?>
        <?php if($outSideBartleby){echo('//');}?>try self.quietThrowingChanges {
<?php GenerativeCodableHelperForSwift::echoBodyOfInitFromDecoder($blockRepresentation, 3);
if (isset($decodableblockEndContent)){
    echo $decodableblockEndContent;
}
?>
        <?php if($outSideBartleby){echo('//');}?>}
    }

    <?php echo $inheritancePrefix?>open func encode(to encoder: Encoder) throws {
<?php if(!$isBaseObject){echoIndent('try super.encode(to:encoder)',2);}?>
<?php GenerativeCodableHelperForSwift::echoBodyOfEncodeToEncoder($blockRepresentation, 2);
if (isset($encodableblockEndContent)){
    echo $encodableblockEndContent;
}

?>
    }

<?php // End of Block ?>