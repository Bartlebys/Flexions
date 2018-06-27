<?php
include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation*/
/* @var $h Hypotypose */


if (isset( $f,$d,$h)) {
    // We use explicit name (!)
    // And reserve $f , $d , $h possibly for blocks

    /* @var $flexed Flexed*/
    /* @var $entityRepresentation EntityRepresentation*/
    /* @var $hypotypose Hypotypose*/

    $flexed=$f;
    $entityRepresentation=$d;
    $hypotypose=$h;

    // We determine the file name.
    $f->fileName = 'Managed'.ucfirst(Pluralization::pluralize($d->name)).'.swift';
    // And its package.
    $f->package = 'xOS/managedCollections/';

}else{
    return NULL;
}

/////////////////////
// EXCLUSIONS
/////////////////////

// Value Object Are Excluded
if ($d->isUnManagedModel()){
    return NULL;
}

//Collection controllers are related to actions.

$exclusion = array();
$exclusionName = str_replace($h->classPrefix, '', $entityRepresentation->name);

$includeManagedCollection = false;
if (isset($xOSIncludeManagedCollectionForEntityNamed)) {
    foreach ($xOSIncludeManagedCollectionForEntityNamed as $inclusion) {
        if (strpos($exclusionName, $inclusion) !== false) {
            $includeManagedCollection = true;
        }

    }
    if (!$includeManagedCollection) {
        if (isset($excludeActionsWith)) {
            $exclusion = $excludeActionsWith;
        }
        foreach ($exclusion as $exclusionString) {
            if (strpos($exclusionName, $exclusionString) !== false) {
                return NULL; // We return null
            }
        }
    }
}

/////////////////////////
// VARIABLES COMPUTATION
/////////////////////////


$entityName=$entityRepresentation->name;
$pluralizedEntityName=lcfirst(Pluralization::pluralize($entityName));
$ucfPluralizedEntityName=ucfirst(Pluralization::pluralize($entityName));
$usesUrdMode=$d->usesUrdMode()==true;
$managedCollectionClass='Managed'.ucfirst(Pluralization::pluralize($entityName));


$deletionRoutineBlock='';
if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()) {
    if ($entityRepresentation->groupedOnCommit()) {
        $deletionRoutineBlock.='
        if self._deleted.count > 0 {
            var toBeDeleted'.$ucfPluralizedEntityName.'=['.ucfirst($entityName).']()
            for itemUID in self._deleted{
                if let o:'.ucfirst($entityName).' = try? Bartleby.registredObjectByUID(itemUID){
                    toBeDeleted'.$ucfPluralizedEntityName.'.append(o)
                }
            }
            if toBeDeleted'.$ucfPluralizedEntityName.'.count > 0 {
                Delete'.$ucfPluralizedEntityName.'.commit(toBeDeleted'.$ucfPluralizedEntityName.', from: self.referentDocument!)
                Bartleby.unRegister(toBeDeleted'.$ucfPluralizedEntityName.')
            }
            self._deleted.removeAll()
        }';
    }else{
        $deletionRoutineBlock.='
        if self._deleted.count > 0 {
            var toBeDeletedItems=['.ucfirst($entityName).']()
            for itemUID in self._deleted{
                if let o:'.ucfirst($entityName).' = try? Bartleby.registredObjectByUID(itemUID){
                    toBeDeletedItems.append(o)
                }
            }
            for '.lcfirst($entityName).' in toBeDeletedItems{
                Delete'.ucfirst($entityName).'.commit('.lcfirst($entityName).', from: self.referentDocument!)
                Bartleby.unRegister('.lcfirst($entityName).')
            }
            self._deleted.removeAll()
        }';
    }
}


$operationRoutineBlock='';

if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()){
    if ($entityRepresentation->groupedOnCommit()){
        if($usesUrdMode){
            $operationRoutineBlock='if changed'.$ucfPluralizedEntityName. '.count > 0 {
    Upsert'.$ucfPluralizedEntityName. '.commit(changed'.$ucfPluralizedEntityName. ',in:self.referentDocument!)
}';
        }else{
// We need to distinguish distributed from non distributed entities
            $operationRoutineBlock='let tobeUpdated = changed'.$ucfPluralizedEntityName. '.filter { $0.commitCounter > 0  }
let toBeCreated = changed'.$ucfPluralizedEntityName. '.filter { $0.commitCounter == 0 }
if toBeCreated.count > 0 {
    Create'. $ucfPluralizedEntityName. '.commit(toBeCreated, in:self.referentDocument!)
}
if tobeUpdated.count > 0 {
    Update'.$ucfPluralizedEntityName. '.commit(tobeUpdated, in:self.referentDocument!)
}';
        }

    }else{
        if($usesUrdMode){
            $operationRoutineBlock='Upsert'. ucfirst($entityName) . '.commit(changed, in:self.referentDocument!)';
        }else{
            // We need to distinguish distributed from non distributed entities
            $operationRoutineBlock='if changed.commitCounter > 0 {
    Update'. ucfirst($entityName). '.commit(changed, in:self.referentDocument!)
}else{
    Create'. ucfirst($entityName). '.commit(changed, in:self.referentDocument!)
}';
        }
    }
}

$operationRoutineBlock=cr().stringIndent($operationRoutineBlock,($entityRepresentation->groupedOnCommit() ? 3:4));
$wrappedOperationRoutineBlock='';
if ($entityRepresentation->groupedOnCommit()){
    $wrappedOperationRoutineBlock=$operationRoutineBlock;
}else{
    $wrappedOperationRoutineBlock='
            for changed in changed'.$ucfPluralizedEntityName. '{'.$operationRoutineBlock.'
            }';
}
$commitBlock='';


if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()){
        $commitBlock='
    /// Commit all the staged changes and planned deletions.
    open func commitChanges(){
        if self._staged.count>0{
            var changed'.$ucfPluralizedEntityName. '=['.ucfirst($entityName).']()
            for itemUID in self._staged{
                if let o:'.ucfirst($entityName).' = try? Bartleby.registredObjectByUID(itemUID){
                    changed'.$ucfPluralizedEntityName. '.append(o)
                }
            }'.$wrappedOperationRoutineBlock.'
            self.hasBeenCommitted()
            self._staged.removeAll()
        }
     '.$deletionRoutineBlock.'
    }';

}else{
    $commitBlock="

    /// Commit is ignored because
    /// Distant persistency is not allowed for $entityName
    open func commitChanges(){
    }
    
";
}

$commitBlock=$commitBlock.cr();


// Include block
include  dirname(__DIR__).'/blocks/BarltebysSimpleIncludeBlock.swift.php';

/////////////////////
// TEMPLATE
/////////////////////


?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$entityRepresentation); ?>

import Foundation
#if os(OSX)
import AppKit
#endif
#if !USE_EMBEDDED_MODULES
<?php echo $includeBlock ?>
#endif

// MARK: - Notification

public extension Notification.Name {
    public struct <?php echo ucfirst($pluralizedEntityName); ?> {
        /// Posted when the selected <?php echo lcfirst($pluralizedEntityName); ?> changed
        public static let selectionChanged = Notification.Name(rawValue: "org.bartlebys.notification.<?php echo ucfirst($pluralizedEntityName); ?>.selected<?php echo ucfirst($pluralizedEntityName); ?>Changed")
    }
}


// MARK: A  collection controller of "<?php echo lcfirst($pluralizedEntityName); ?>"

// This controller implements data automation features.

@objc open class <?php echo $managedCollectionClass ?> : <?php echo GenerativeHelperForSwift::getBaseClass($f,$entityRepresentation); ?>,IterableCollectibleCollection{

    open var collectedType:Collectible.Type { return <?php echo ucfirst($entityName)?>.self }

    // Staged "<?php echo lcfirst($pluralizedEntityName); ?>" identifiers (used to determine what should be committed on the next loop)
    fileprivate var _staged=[String]()

    // Store the  "<?php echo lcfirst($pluralizedEntityName); ?>" identifiers to be deleted on the next loop
    fileprivate var _deleted=[String]()

    // Ordered UIDS
    fileprivate var _UIDS=[String]()

    // The "<?php echo lcfirst($pluralizedEntityName); ?>" list (computed by _rebuildFromStorage and on operations)
    @objc fileprivate dynamic var _items=[<?php echo ucfirst($entityName)?>]()  {
        didSet {
            if !self.wantsQuietChanges && _items != oldValue {
                self.provisionChanges(forKey: "_items",oldValue: oldValue,newValue: _items)
            }
        }
    }

    // The underlining "<?php echo lcfirst($pluralizedEntityName); ?>" storage (serialized)
    // We cannot use the `Collected` generic type for _items and set `@objc dynamic` at the same time
    // `@objc dynamic` is required to be able to use KVO and `CocoaBindings`
    // May be we will stop using KVO and Cocoa Bindings in the future when Apple will give use alternative dynamic approach.
    // Refer to Apple documentation for more explanation.
    // https://developer.apple.com/library/content/documentation/Swift/Conceptual/BuildingCocoaApps/AdoptingCocoaDesignPatterns.html
    // So we use a strongly typed `<?php echo ucfirst($entityName)?> for the storage
    // While the API deals with `Collectible` instances.
    fileprivate var _storage=[String:<?php echo ucfirst($entityName)?>]()


    fileprivate func _rebuildFromStorage(){
        self._UIDS=[String]()
        self._items=[<?php echo ucfirst($entityName)?>]()
        for (UID,item) in self._storage{
            self._UIDS.append(UID)
            self._items.append(item)
        }
    }

    /// Marks that a collectible instance should be committed.
    ///
    /// - Parameter item: the collectible instance
    open func stage(_ item: Collectible){
        if !self._staged.contains(item.UID){
            self._staged.append(item.UID)
        }
        // When operation off line The staging may have already occur in previous session.
        // So we need to mark shouldBeSaved even if the element is already staged
        self.shouldBeSaved = true
        self.referentDocument?.hasChanged()
    }

    /// Replace the items in the proxy (advanced feature)
    ///
    /// - Parameter items: the collectible item
    /// - Returns: N/A
    open func replaceProxyData(_ items:[Collectible]){
        if let collection = items as? [<?php echo ucfirst($entityName)?>]{
            self._items = [<?php echo ucfirst($entityName)?>]()
            self.append(collection, commit: false, isUndoable: false)
        }
    }

    /// Returns the collected items
    /// You should not normally use this method directly
    /// We use this to offer better performances during collection proxy deserialization phase
    /// This method may be removed in next versions
    /// - Returns: the collected items
    open func getItems()->[Collectible]{
        return self._items
    }

    // Used to determine if the wrapper should be saved.
    open var shouldBeSaved:Bool=false

    // Universal type support
    override open class func typeName() -> String {
        return "<?php echo $managedCollectionClass ?>"
    }

    open var spaceUID:String { return self.referentDocument?.spaceUID ?? Default.NO_UID }

    /// Init with prefetched content
    ///
    /// - parameter items: itels
    ///
    /// - returns: the instance
    required public init(items:[<?php echo ucfirst($entityName)?>], within document:BartlebyDocument) {
        super.init()
        self.referentDocument = document
        for item in items{
            let UID=item.UID
            self._UIDS.append(UID)
            self._storage[UID]=item
            self._items=items
        }
    }

    required public init() {
        super.init()
    }

    // Should be called to propagate references (Collection, ReferentDocument, Owned relations)
    // And to propagate the selections
    open func propagate(){
        #if BARTLEBY_CORE_DEBUG
        if self.referentDocument == nil{
            glog("Document Reference is nil during Propagation on <?php echo $managedCollectionClass ?>", file: #file, function: #function, line: #line, category: Default.LOG_FAULT, decorative: false)
        }
        #endif

        let selectedUIDS = self._selectedUIDS
        var selected = [<?php echo ucfirst($entityName)?>]()
        for item in self{
            // Reference the collection
            item.collection=self
            // Re-build the own relation.
            item.ownedBy.forEach({ (ownerUID) in
                if let o = Bartleby.registredManagedModelByUID(ownerUID){
                    if !o.owns.contains(item.UID){
                        o.owns.append(item.UID)
                    }
                }else{
                    // If the owner is not already available defer the homologous ownership registration.
                    Bartleby.appendToDeferredOwnershipsList(item, ownerUID: ownerUID)
                }
            })
            if selectedUIDS.contains(item.UID){
                selected.append(item)
            }
        }
        self.selected<?php echo ucfirst($pluralizedEntityName); ?> = selected
    }

    open func generate() -> AnyIterator<<?php echo ucfirst($entityName)?>> {
        var nextIndex = -1
        let limit=self._storage.count-1
        return AnyIterator {
            nextIndex += 1
            if (nextIndex > limit) {
                return nil
            }
            let key=self._UIDS[nextIndex]
            return self._storage[key]
        }
    }


    open subscript(index: Int) -> <?php echo ucfirst($entityName)?> {
        let key=self._UIDS[index]
        return self._storage[key]!
    }

    open var startIndex:Int {
        return 0
    }

    open var endIndex:Int {
        return self._UIDS.count
    }

    /// Returns the position immediately after the given index.
    ///
    /// - Parameter i: A valid index of the collection. `i` must be less than
    ///   `endIndex`.
    /// - Returns: The index value immediately after `i`.
    open func index(after i: Int) -> Int {
        return i+1
    }


    open var count:Int {
        return self._storage.count
    }

    open func indexOf(element:@escaping(<?php echo ucfirst($entityName)?>) throws -> Bool) rethrows -> Int?{
        return self._getIndexOf(element as! Collectible)
    }

    open func item(at index:Int)->Collectible?{
        if index >= 0 && index < self._storage.count{
            return self[index]
        }else{
            self.referentDocument?.log("Index Error \(index)", file: #file, function: #function, line: #line, category: Default.LOG_WARNING, decorative: false)
        }
        return nil
    }

    fileprivate func _getIndexOf(_ item:Collectible)->Int?{
        return self._UIDS.index(of: item.UID)
    }

    /**
    An iterator that permit dynamic approaches.
    - parameter on: the closure
    */
    open func superIterate(_ on:@escaping(_ element: Collectible)->()){
        for UID in self._UIDS {
            let item=self._storage[UID]!
            on(item)
        }
    }

<?php echo $commitBlock ?>

    override open class var collectionName:String{
        return <?php echo ucfirst($entityName)?>.collectionName
    }

    override open var d_collectionName:String{
        return <?php echo ucfirst($entityName)?>.collectionName
    }

<?php if ($modelsShouldConformToExposed == true){

echo ('
    // MARK: - Exposed (Bartleby\'s KVC like generative implementation)

    /// Return all the exposed instance variables keys. (Exposed == public and modifiable).
    override open var exposedKeys:[String] {
        var exposed=super.exposedKeys
        exposed.append(contentsOf:["_storage","_staged"])
        return exposed
    }


    /// Set the value of the given key
    ///
    /// - parameter value: the value
    /// - parameter key:   the key
    ///
    /// - throws: throws an Exception when the key is not exposed
    override open func setExposedValue(_ value:Any?, forKey key: String) throws {
        switch key {
            case "_storage":
                if let casted=value as? [String:'.ucfirst($entityName).']{
                    self._storage=casted
                }
            case "_staged":
                if let casted=value as? [String]{
                    self._staged=casted
                }
            default:
                return try super.setExposedValue(value, forKey: key)
        }
    }


    /// Returns the value of an exposed key.
    ///
    /// - parameter key: the key
    ///
    /// - throws: throws Exception when the key is not exposed
    ///
    /// - returns: returns the value
    override open func getExposedValueForKey(_ key:String) throws -> Any?{
        switch key {
            case "_storage":
               return self._storage
            case "_staged":
               return self._staged
            default:
                return try super.getExposedValueForKey(key)
        }
    }
');
}
?>

<?php if ($modelsShouldConformToMappable == true){

echo ('
    // MARK: - Mappable

    required public init?(map: Map) {
        super.init(map:map)
    }

    override open func mapping(map: Map) {
        super.mapping(map: map)
        self.quietChanges {
			self._storage <- ( map["_storage"] )
			self._staged <- ( map["_staged"] )
            self._deleted <- ( map["_deleted"] )
            if map.mappingType == MappingType.fromJSON{
                self._rebuildFromStorage()
            }
        }
    }');
}
?>


<?php if ($modelsShouldConformToNSSecureCoding == true){

    echo ('
    // MARK: - Codable

    required public init?(coder decoder: NSCoder) {
        super.init(coder: decoder)
        self.quietChanges {
            self._storage=decoder.decodeObject(of: [NSDictionary.classForCoder(),NSString.self,<?php echo ucfirst($entityName)?>.classForCoder()], forKey: "_storage")! as! [String:<?php echo ucfirst($entityName)?>]
			self._staged=decoder.decodeObject(of: [NSArray.classForCoder(),NSString.self], forKey: "_staged")! as! [String]
            self._deleted=decoder.decodeObject(of: [NSArray.classForCoder(),NSString.self], forKey: "_deleted")! as! [String]
            self._rebuildFromStorage()
        }
    }

    override open func encode(with coder: NSCoder) {
        super.encode(with:coder)
		coder.encode(self._storage,forKey:"_storage")
		coder.encode(self._staged,forKey:"_staged")
    }

    override open class var supportsSecureCoding:Bool{
        return true
    }
');
}
?>

<?php if ($modelsShouldConformToCodable == true){

    echo ('
    
     // MARK: - Codable


    public enum CodingKeys: String,CodingKey{
		case _storage
		case _staged
		case _deleted
    }

    required public init(from decoder: Decoder) throws{
		try super.init(from: decoder)
        try self.quietThrowingChanges {
			let values = try decoder.container(keyedBy: CodingKeys.self)
			self._storage = try values.decode([String:'.ucfirst($entityName).'].self,forKey:._storage)
			self._staged = try values.decode([String].self,forKey:._staged)
			self._deleted = try values.decode([String].self,forKey:._deleted)
			self._rebuildFromStorage()
        }
    }

    override open func encode(to encoder: Encoder) throws {
		try super.encode(to:encoder)
		var container = encoder.container(keyedBy: CodingKeys.self)
		try container.encode(self._storage,forKey:._storage)
		try container.encode(self._staged,forKey:._staged)
		try container.encode(self._deleted,forKey:._deleted)
    }
    
');
}
?>


    // MARK: - Upsert


    /// Updates or creates an item
    ///
    /// - Parameters:
    ///   - item: the <?php echo ucfirst($entityName)?>
    ///   - commit: should we commit the `Upsertion`?
    /// - Returns: N/A
    open func upsert(_ item: Collectible, commit:Bool=true){
        do{
            if self._UIDS.contains(item.UID){
                // it is an update
                // we must patch it
                let currentInstance=_storage[item.UID]!
                if commit==false{
                    var catched:Error?
                    // When upserting from a trigger
                    // We do not want to produce Larsen effect on data.
                    // So we lock the auto commit observer before to merge
                    // And we unlock the autoCommit Observer after the merging.
                    currentInstance.doNotCommit {
                        do{
                            try currentInstance.mergeWith(item)
                        }catch{
                            catched=error
                        }
                    }
                    if catched != nil{
                        throw catched!
                    }
                }else{
                    try currentInstance.mergeWith(item)
                }
            }else{
                // It is a creation
                self.add(item, commit:commit,isUndoable:false)
            }
        }catch{
            self.referentDocument?.log("\(error)", file: #file, function: #function, line: #line, category: Default.LOG_DEFAULT, decorative: false)
        }
        self.shouldBeSaved = true
    }

    // MARK: Add

    /// Ads an <?php echo ucfirst($entityName)?>
    ///
    /// - Parameters:
    ///   - item: the <?php echo ucfirst($entityName)?>
    ///   - commit: should we commit the addition?
    ///   - isUndoable: is the addition reversible by the undo manager?
    /// - Returns: N/A
    open func add(_ item:Collectible, commit:Bool=true,isUndoable:Bool){
        self.insertObject(item, inItemsAtIndex: _storage.count, commit:commit,isUndoable:isUndoable)
    }


    /// Ads some items
    ///
    /// - Parameters:
    ///   - items: the collectible items to add
    ///   - commit: should we commit the additions?
    ///   - isUndoable: are the additions reversible by the undo manager?
    /// - Returns: N/A
    open func append(_ items:[Collectible],commit:Bool, isUndoable:Bool){
        if let items  = items as? [<?php echo ucfirst($entityName)?>] {
            self._items.append(contentsOf:items)
            for item in items{
                item.collection = self
                self._UIDS.append(item.UID)
                self._storage[item.UID]=item
            }
            #if os(OSX) && !USE_EMBEDDED_MODULES
            if let arrayController = self.arrayController{
                // Re-arrange (in case the user has sorted a column)
                arrayController.rearrangeObjects()
            }
            #endif
<?php if ($entityRepresentation->isUndoable()) {
    echo('  
            if isUndoable{
                // Add the inverse of this invocation to the undo stack
                if let undoManager: UndoManager = self.undoManager {
                    self.beginUndoGrouping()
                    undoManager.registerUndo(withTarget: self, handler: { (targetSelf) in
                        targetSelf.removeObjects(items, commit:commit)
                    })
                    if !undoManager.isUndoing {
                        undoManager.setActionName(NSLocalizedString("Add ' . ucfirst($entityName) . '", comment: "Add' . ucfirst($entityName) . ' undo action"))
                    }
                }
            }');
}
?>
<?php if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()){
    echo("
            if commit==true {
               ".($usesUrdMode?'Upsert':'Create').$ucfPluralizedEntityName.".commit(items, in:self.referentDocument!)
            }".cr());
}else{
    echo('
            // Commit is ignored because
            // Distant persistency is not allowed for '.$ucfPluralizedEntityName.'');
}
?>

            self.shouldBeSaved = true
        }
    }



    // MARK: Insert

    ///  Insert an item at a given index.
    ///
    /// - Parameters:
    ///   - item: the collectible item
    ///   - index: the index
    ///   - commit: should we commit the addition?
    ///   - isUndoable: is the addition reversible by the undo manager?
    /// - Returns: N/A
    open func insertObject(_ item: Collectible, inItemsAtIndex index: Int, commit:Bool=true,isUndoable:Bool) {
        if let item = item as? <?php echo ucfirst($entityName)?>{
            item.collection = self
            self._UIDS.insert(item.UID, at: index)
            self._items.insert(item, at:index)
            self._storage[item.UID]=item
<?php if ($entityRepresentation->isUndoable()) {
    echo('  
            if isUndoable{
                // Add the inverse of this invocation to the undo stack
                if let undoManager: UndoManager = self.undoManager {
                    self.beginUndoGrouping()
                    undoManager.registerUndo(withTarget: self, handler: { (targetSelf) in
                        targetSelf.removeObjectWithID(item.UID, commit:commit)
                    })
                    if !undoManager.isUndoing {
                        undoManager.setActionName(NSLocalizedString("Add ' . ucfirst($entityName) . '", comment: "Add' . ucfirst($entityName) . ' undo action"))
                    }
                }
            }
            ');
}
?>

            #if os(OSX) && !USE_EMBEDDED_MODULES
            if let arrayController = self.arrayController{
                // Re-arrange (in case the user has sorted a column)
                arrayController.rearrangeObjects()
            }
            #endif
<?php if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()){
    echo("
            if commit==true {
               ".($usesUrdMode?'Upsert':'Create').$entityName.".commit(item, in:self.referentDocument!)
            }".cr());
}else{
        echo('
            // Commit is ignored because
            // Distant persistency is not allowed for '.$entityName.'
            ');
}
?>
            self.shouldBeSaved = true
        }
    }




    // MARK: Remove

    /**
    Removes an object at a given index from the collection.

    - parameter index:  the index in the collection (not the ArrayController arranged object)
    - parameter commit: should we commit the removal?
    */
    open func removeObjectFromItemsAtIndex(_ index: Int, commit:Bool=true) {
        guard self._storage.count > index else {
            return
        }
        let item : <?php echo ucfirst($entityName)?> =  self[index]
<?php if ($entityRepresentation->isUndoable()) {
echo(
'
      // Add the inverse of this invocation to the undo stack
        if let undoManager: UndoManager = self.undoManager {
            self.beginUndoGrouping()
            // Add the inverse of this invocation to the undo stack
            let serializedData = item.serialize()
             undoManager.registerUndo(withTarget: self, handler: { (targetSelf) in
                targetSelf.addObjectFrom(serializedData)
             })
            if !undoManager.isUndoing {
                undoManager.setActionName(NSLocalizedString("Remove '.ucfirst($entityName).'", comment: "Remove '.ucfirst($entityName).' undo action"))
            }
        }
        ');
}
?>

        // Remove the item from the collection
        let UID=item.UID
        self._UIDS.remove(at: index)
        self._items.remove(at: index)
        self._storage.removeValue(forKey: UID)
        if let stagedIdx=self._staged.index(of: UID){
            self._staged.remove(at: stagedIdx)
        }
    <?php if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()) {
        echo('
        if commit==true{
           self._deleted.append(UID)
        }'.cr());
    }else{
        echo('
        // Commit is ignored because
        // Distant persistency is not allowed for '.$entityName.cr());
    }
    ?>

        #if os(OSX) && !USE_EMBEDDED_MODULES
            if let arrayController = self.arrayController{
                // Re-arrange (in case the user has sorted a column)
                arrayController.rearrangeObjects()
            }
        #endif

        try? item.erase()
        self.shouldBeSaved = true
    }

    /// Add an Object from an opaque serialized Data
    /// And registers the object into bartleby and its parent collection
    /// Used by the UndoManager.
    ///
    /// - Parameter data: the serialized Object
    open func addObjectFrom(_ data:Data){
        do{
            if let <?php echo(lcfirst($entityName))?>:<?php echo(ucfirst($entityName))?> = try self.referentDocument?.serializer.deserialize(data,register:true){
                if let owners = Bartleby.registredManagedModelByUIDs(<?php echo(lcfirst($entityName))?>.ownedBy){
                    for owner in owners{
                        // Re associate the relations.
                        if !owner.owns.contains(<?php echo(lcfirst($entityName))?>.UID){
                            owner.owns.append(<?php echo(lcfirst($entityName))?>.UID)
                        }
                    }
                }
                self.add(<?php echo(lcfirst($entityName))?>, commit: true, isUndoable:false)
            }
        }catch{
            self.referentDocument?.log("\(error)")
        }
    }


    open func removeObjects(_ items: [Collectible],commit:Bool=true){
        for item in items{
            self.removeObject(item,commit:commit)
        }
    }

    open func removeObject(_ item: Collectible, commit:Bool=true){
        if let instance=item as? <?php echo(ucfirst($entityName))?>{
            if let idx=self._getIndexOf(instance){
                self.removeObjectFromItemsAtIndex(idx, commit:commit)
            }
        }
    }

    open func removeObjectWithIDS(_ ids: [String],commit:Bool=true){
        for uid in ids{
            self.removeObjectWithID(uid,commit:commit)
        }
    }

    open func removeObjectWithID(_ id:String, commit:Bool=true){
        if let idx=self.index(where:{ return $0.UID==id } ){
            self.removeObjectFromItemsAtIndex(idx, commit:commit)
        }
    }

    // MARK: Filter

    /// Create a filtered copy of a collectible collection
    ///
    /// - Parameter isIncluded: the filtering closure
    /// - Returns: the filtered Collection
    open func filteredCopy(_ isIncluded: (Collectible)-> Bool) -> CollectibleCollection{
        let filteredCollection=<?php echo $managedCollectionClass ?>()
        for item in self._items{
            if isIncluded(item){
                filteredCollection._UIDS.append(item.UID)
                filteredCollection._storage[item.UID]=item
                filteredCollection._items.append(item)
            }
        }
        return filteredCollection
    }

    // MARK: - Selection management Facilities


#if os(OSX) && !USE_EMBEDDED_MODULES

    fileprivate var _KVOContext: Int = 0

    // We auto-configure most of the array controller.
    // And set up  indexes selection observation layer.
    open var arrayController:NSArrayController? {
        willSet{
        // Remove observer on previous array Controller
            arrayController?.removeObserver(self, forKeyPath: "selectionIndexes", context: &self._KVOContext)
        }
        didSet{
            //self.referentDocument?.setValue(self, forKey: "<?php echo lcfirst(Pluralization::pluralize($entityName)); ?>")
            arrayController?.objectClass=<?php echo ucfirst($entityName)?>.self
            arrayController?.entityName=<?php echo ucfirst($entityName)?>.className()
            arrayController?.bind(NSBindingName("content"), to: self, withKeyPath: "_items", options: nil)
            // Add observer
            arrayController?.addObserver(self, forKeyPath: "selectionIndexes", options: .new, context: &self._KVOContext)
            let indexesSet = NSMutableIndexSet()
            for instanceUID in self._selectedUIDS{
                if let idx = self._UIDS.index(of:instanceUID){
                    indexesSet.add(idx)
                }
            }
            arrayController?.setSelectionIndexes(indexesSet as IndexSet)

        }
    }

    // KVO on ArrayController selectionIndexes

    // Note :
    // If you use an ArrayController & Bartleby automation
    // to modify the current selection you should use the array controller
    // e.g: referentDocument.<?php echo lcfirst(Pluralization::pluralize($entityName)); ?>.arrayController?.setSelectedObjects(<?php echo lcfirst(Pluralization::pluralize($entityName)); ?>)
    // Do not use document.<?php echo lcfirst(Pluralization::pluralize($entityName)); ?>.selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?>=<?php echo lcfirst(Pluralization::pluralize($entityName)); ?>


    override open func observeValue(forKeyPath keyPath: String?, of object: Any?, change: [NSKeyValueChangeKey : Any]?, context: UnsafeMutableRawPointer?) {
        guard context == &_KVOContext else {
            // If the context does not match, this message
            // must be intended for our superclass.
            super.observeValue(forKeyPath: keyPath, of: object, change: change, context: context)
            return
        }
        if let keyPath = keyPath, let object = object {
            if keyPath=="selectionIndexes" &&  (object as? NSArrayController) == self.arrayController {
                if let items = self.arrayController?.selectedObjects as? [<?php echo ucfirst($entityName); ?>] {
                    self.selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?>=items
                }
            }
        }
    }


    deinit{
        self.arrayController?.removeObserver(self, forKeyPath: "selectionIndexes")
    }

#endif


    fileprivate var _selectedUIDS:[String]{
        set{
            syncOnMain {
                if let <?php echo lcfirst(Pluralization::pluralize($entityName)); ?> = self.selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?> {
                    let _selectedUIDS:[String]=<?php echo lcfirst(Pluralization::pluralize($entityName)); ?>.map({ (<?php echo lcfirst($entityName); ?>) -> String in
                        return <?php echo lcfirst($entityName); ?>.UID
                    })
                    self.referentDocument?.metadata.saveStateOf(_selectedUIDS, identified: self.selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?>UIDSKeys)
                }
            }
        }
        get{
            return syncOnMainAndReturn{ () -> [String] in
                return self.referentDocument?.metadata.getStateOf(identified: self.selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?>UIDSKeys) ?? [String]()
            }
        }
    }

    public let selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?>UIDSKeys="selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?>UIDSKeys"

    // Note :
    // If you use an ArrayController & Bartleby automation
    // to modify the current selection you should use the array controller
    // e.g: referentDocument.<?php echo lcfirst(Pluralization::pluralize($entityName)); ?>.arrayController?.setSelectedObjects(<?php echo lcfirst(Pluralization::pluralize($entityName)); ?>)
    @objc dynamic open var selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?>:[<?php echo ucfirst($entityName); ?>]?{
        didSet{
            syncOnMain {
                if let <?php echo lcfirst(Pluralization::pluralize($entityName)); ?> = selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?> {
                    let UIDS:[String]=<?php echo lcfirst(Pluralization::pluralize($entityName)); ?>.map({ (<?php echo lcfirst($entityName); ?>) -> String in
                        return <?php echo lcfirst($entityName); ?>.UID
                    })
                    self._selectedUIDS = UIDS
                }
                NotificationCenter.default.post(name:Notification.Name.<?php echo ucfirst(Pluralization::pluralize($entityName)); ?>.selectionChanged, object: nil)
            }
        }
    }

    // A facility
    open var firstSelected<?php echo ucfirst($entityName); ?>:<?php echo ucfirst($entityName); ?>? { return self.selected<?php echo ucfirst(Pluralization::pluralize($entityName)); ?>?.first }



}