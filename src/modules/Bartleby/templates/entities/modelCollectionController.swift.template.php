<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    // We determine the file name.
    $f->fileName = ucfirst(Pluralization::pluralize($d->name)).'CollectionController.swift';
    // And its package.
    $f->package = 'iOS/swift/collectionControllers/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation
import ObjectMapper

// MARK: A collection contromller for <?php echo ucfirst($d->name)?>

// This controller implements data automation features.
// it uses KVO , KVC , dynamic invocation, oS X cocoa bindings, etc ...
@objc(<? echo ucfirst(Pluralization::pluralize($d->name)).'CollectionController'?>) class <? echo ucfirst(Pluralization::pluralize($d->name)).'CollectionController'?> : <?php echo GenerativeHelperForSwift::defaultBaseClass(); ?>,CollectibleCollection{

    weak var undoManager:NSUndoManager?

#if os(OSX)
    // When using cocoa bindings with an array controller
    // You can set the arrayController for a seamless integration
    weak var arrayController:NSArrayController?
    // And also a tableview
    weak var tableView: NSTableView?
#endif

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
        return Project.collectionName
    }

    override var d_collectionName:String{
        return Project.collectionName
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

    // MARK: Facilities ?

    func add(object:<?php echo ucfirst($d->name)?>){
        self.items.append(object)

        /*
        if let undoManager = self.undoManager{
        // Has an edit occurred already in this event?
        if undo.groupingLevel > 0 {
        // Close the last group
        undo.endUndoGrouping()
        // Open a new group
        undo.beginUndoGrouping()
        }
        }
        // Create the object
        let employee = arrayController.newObject() as! Employee

        // Add it to the array controller's content array
        arrayController.addObject(employee)

        // Re-sort (in case the use has sorted a column)
        arrayController.rearrangeObjects()

        // Get the sorted array
        let sortedEmployees = arrayController.arrangedObjects as! [Employee]

        // Find the object just added
        let row = sortedEmployees.indexOf(employee)!

        // Begin the edit in the first column
        print("starting edit of \(employee) in row \(row)")
        tableView.editColumn(0, row: row, withEvent: nil, select: true)
        */
    }

    /*
    // MARK: - Actions

    @IBAction func addEmployee(sender: NSButton) {
    let windowController = windowControllers[0]
    let window = windowController.window!

    let endedEditing = window.makeFirstResponder(window)
    if !endedEditing {
    print("Unable to end editing")
    return
    }

    let undo: NSUndoManager = undoManager!

    // Has an edit occurred already in this event?
    if undo.groupingLevel > 0 {
    // Close the last group
    undo.endUndoGrouping()
    // Open a new group
    undo.beginUndoGrouping()
    }

    // Create the object
    let employee = arrayController.newObject() as! Employee

    // Add it to the array controller's content array
    arrayController.addObject(employee)

    // Re-sort (in case the use has sorted a column)
    arrayController.rearrangeObjects()

    // Get the sorted array
    let sortedEmployees = arrayController.arrangedObjects as! [Employee]

    // Find the object just added
    let row = sortedEmployees.indexOf(employee)!

    // Begin the edit in the first column
    print("starting edit of \(employee) in row \(row)")
    tableView.editColumn(0, row: row, withEvent: nil, select: true)
    }

    // MARK: - Accessors

    func insertObject(employee: Employee, inEmployeesAtIndex index: Int) {
    print("adding \(employee) to the employees array")

    // Add the inverse of this operation to the undo stack
    let undo: NSUndoManager = undoManager!
    undo.prepareWithInvocationTarget(self).removeObjectFromEmployeesAtIndex(employees.count)
    if !undo.undoing {
    undo.setActionName("Add Person")
    }

    employees.append(employee)
    }

    func removeObjectFromEmployeesAtIndex(index: Int) {
    let employee: Employee = employees[index]

    print("removing \(employee) from the employees array")

    // Add the inverse of this operation to the undo stack
    let undo: NSUndoManager = undoManager!
    undo.prepareWithInvocationTarget(self).insertObject(employee, inEmployeesAtIndex: index)
    if !undo.undoing {
    undo.setActionName("Remove Person")
    }

    // Remove the employee from the array
    employees.removeAtIndex(index)
    }
    */

    // MARK: - Key Value Observing

    private var KVOContext: Int = 0

    func startObserving(item: <?php echo ucfirst($d->name)?>) {
        item.addObserver(self, forKeyPath: "name", options: .Old, context: &KVOContext)
        item.addObserver(self, forKeyPath: "informativeString", options: .Old, context: &KVOContext)
    }

    func stopObserving(item: <?php echo ucfirst($d->name)?>) {
        item.removeObserver(self, forKeyPath: "name", context: &KVOContext)
        item.removeObserver(self, forKeyPath: "informativeString", context: &KVOContext)
    }

    override func observeValueForKeyPath(keyPath: String?, ofObject object: AnyObject?, change: [String : AnyObject]?, context: UnsafeMutablePointer<Void>) {
        guard context == &KVOContext else {
        // If the context does not match, this message
        // must be intended for our superclass.
        super.observeValueForKeyPath(keyPath, ofObject: object, change: change, context: context)
            return
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

    }



}
