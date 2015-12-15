<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ($f)) {
    // We determine the file name.
    $f->fileName = ucfirst(Pluralization::pluralize($d->name)) . 'CollectionController.swift';
    // And its package.
    $f->package = 'xOS/collectionControllers/';
}

// Exclusion -

//Collection controllers are related to actions.

$exclusion = array();
$exclusionName = str_replace($h->classPrefix, '', $d->name);

$includeCollectionController = false;
if (isset($xOSIncludeCollectionControllerForEntityNamed)) {
    foreach ($xOSIncludeCollectionControllerForEntityNamed as $inclusion) {
        if (strpos($exclusionName, $inclusion) !== false) {
            $includeCollectionController = true;
        }

    }
    if (!$includeCollectionController) {
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

$collectionControllerClass=ucfirst(Pluralization::pluralize($d->name)).'CollectionController';

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation
import ObjectMapper

// MARK: A  collection controller for <?php echo ucfirst($d->name)?>

// This controller implements data automation features.
// it uses KVO , KVC , dynamic invocation, oS X cocoa bindings,...
// It should be used on documents and not very large collections as it is computationnally intensive
@objc(<?php echo $collectionControllerClass ?>) class <?php echo $collectionControllerClass ?> : JAbstractCollectibleCollection{

    required init() {
        super.init()
    }

    deinit{
        for item in items {
            stopObserving(item)
        }
    }

    dynamic var items:[<?php echo ucfirst($d->name)?>]=[<?php echo ucfirst($d->name)?>](){
        willSet {
            for item in items {
                stopObserving(item)
            }
        }
        didSet {
            for item in items {
                startObserving(item)
            }
        }
    }


    // MARK: Identifiable

    override class var collectionName:String{
        return <?php echo ucfirst($d->name)?>.collectionName
    }

    override var d_collectionName:String{
        return <?php echo ucfirst($d->name)?>.collectionName
    }


    // MARK: Mappable

    required init?(_ map: Map) {
        super.init(map)
        mapping(map)
    }


    override func mapping(map: Map) {
        super.mapping(map)
        items <- map["items"]
    }

    // MARK: Add

    override func add(item:Collectible){
        if let item=item as? <?php echo ucfirst($d->name)?>{
            // print("adding \(item) to the items array")

            if let undoManager = self.undoManager{
                // Has an edit occurred already in this event?
                if undoManager.groupingLevel > 0 {
                    // Close the last group
                    undoManager.endUndoGrouping()
                    // Open a new group
                    undoManager.beginUndoGrouping()
                }
            }

            // Add the inverse of this operation to the undo stack
            if let undoManager: NSUndoManager = undoManager {
                   // undoManager.prepareWithInvocationTarget(self).removeObjectFromItemsAtIndex(items.count)
                if !undoManager.undoing {
                    undoManager.setActionName("Add <?php echo ucfirst($d->name)?>")
                }
            }

            if let arrayController = self.arrayController{
                // Add it to the array controller's content array
                arrayController.addObject(item)

                // Re-sort (in case the use has sorted a column)
                arrayController.rearrangeObjects()

                // Get the sorted array
                let sorted = arrayController.arrangedObjects as! [<?php echo ucfirst($d->name)?>]

                if let tableView = self.tableView{
                    // Find the object just added
                    let row = sorted.indexOf(item)!
                    // Begin the edit in the first column
                    //print("starting edit of \(object) in row \(row)")
                    tableView.editColumn(0, row: row, withEvent: nil, select: true)
                }

            }else{
                // Add directly to the collection
                 self.items.append(item)
            }
        }else{
            print("casting error")
        }
    }

    // MARK: Insert

    override func insertObject(item: Collectible, inItemsAtIndex index: Int) {
        if let item=item as? <?php echo ucfirst($d->name)?>{
            print("inserting \(item) to the items array")

            // Add the inverse of this operation to the undo stack
            if let undoManager: NSUndoManager = undoManager {
                //undoManager.prepareWithInvocationTarget(self).removeObjectFromItemsAtIndex(items.count)
                if !undoManager.undoing {
                    undoManager.setActionName("Add <?php echo ucfirst($d->name)?>")
                }
            }
            self.items.insert(item, atIndex: index)
        }else{
            print("casting error")
        }
    }


    // MARK: Remove

    override func removeObjectFromItemsAtIndex(index: Int) {
        if let item: <?php echo ucfirst($d->name)?> = items[index] {
            print("removing \(item) from the items array")

            // Add the inverse of this operation to the undo stack
            if let undoManager: NSUndoManager = undoManager {
               // undoManager.prepareWithInvocationTarget(self).insertObject(item, inItemsAtIndex: index)
                if !undoManager.undoing {
                    undoManager.setActionName("Remove <?php echo ucfirst($d->name)?>")
                }
            }
            // Remove the item from the array
            items.removeAtIndex(index)
        }
    }

    override func removeObject(item: Collectible)->Bool{
        var index=0
        for storedItem in items{
            if item.UDID==storedItem.UDID{
                self.removeObjectFromItemsAtIndex(index)
                return true
            }
            index++
        }
        return false
    }

    override func removeObjectWithID(id:String)->Bool{
        var index=0
        for storedItem in items{
            if id==storedItem.UDID{
                self.removeObjectFromItemsAtIndex(index)
                return true
            }
            index++
        }
        return false
    }


    // MARK: - Key Value Observing

    private var KVOContext: Int = 0

    func startObserving(item: <?php echo ucfirst($d->name)?>) {
<?php
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    echoIndentCR('item.addObserver(self, forKeyPath: "'.$name.'", options: .Old, context: &KVOContext)',2);
} ?>
    }

    func stopObserving(item: <?php echo ucfirst($d->name)?>) {
<?php
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    echoIndentCR('item.removeObserver(self, forKeyPath: "'.$name.'", context: &KVOContext)',2);
} ?>
    }

    override func observeValueForKeyPath(keyPath: String?, ofObject object: AnyObject?, change: [String : AnyObject]?, context: UnsafeMutablePointer<Void>) {
        guard context == &KVOContext else {
        // If the context does not match, this message
        // must be intended for our superclass.
        super.observeValueForKeyPath(keyPath, ofObject: object, change: change, context: context)
            return
        }
        if let o = object as? JObject{
            o.hasChanged=true
        }

        if let undoManager = self.undoManager{

            if let keyPath = keyPath, object = object, change = change {
                var oldValue: AnyObject? = change[NSKeyValueChangeOldKey]
                 if oldValue is NSNull {
                    oldValue = nil
                }

                print("oldValue=\(oldValue)")
                undoManager.prepareWithInvocationTarget(object).setValue(oldValue, forKeyPath: keyPath)
            }
        }

        // Sort descriptors support
        if let keyPath = keyPath {
            if let arrayController = self.arrayController{
                for sortDescriptor:NSSortDescriptor in arrayController.sortDescriptors{
                    if sortDescriptor.key==keyPath {
                        // Re-sort
                        arrayController.rearrangeObjects()
                    }
                }
            }
        }
    }
}
