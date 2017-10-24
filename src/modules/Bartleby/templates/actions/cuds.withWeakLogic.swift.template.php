<?php echo GenerativeHelperForSwift::defaultHeader($flexed,$actionRepresentation); ?>

import Foundation
#if !USE_EMBEDDED_MODULES
<?php echo $includeBlock ?>
#endif

@objc public class <?php echo$baseClassName ?> : <?php echo GenerativeHelperForSwift::getBaseClass($actionRepresentation); ?>,BartlebyOperation{

    // Universal type support
    override open class func typeName() -> String {
        return "<?php echo $baseClassName ?>"
    }

    override open class var collectionName:String{ return "embeddedInPushOperations" }

    override open var d_collectionName:String{ return "embeddedInPushOperations" }

    fileprivate var _payload:Data?

    required public init() {
        super.init()
    }

<?php echo $exposedBlock?>
<?php echo $mappableBlock?>
<?php echo $secureCodingBlock?>
<?php echo $codableBlock?>

    /**
    Creates the operation and proceeds to commit

    - parameter <?php echo$subjectName ?>: the instance
    - parameter document:     the document
    */
    static func commit(_ <?php echo$subjectName ?>:<?php echo$subjectStringType ?>, <?php echo$registrySyntagm ?> document:BartlebyDocument){
        // The operation instance is serialized in a pushOperation
        // That's why we donnot use the document factory to create this instance.
        let operationInstance = <?php echo$baseClassName ?>()
        operationInstance.UID = Bartleby.createUID()
        operationInstance.referentDocument = document
        let context=Context(code:<?php echo crc32($baseClassName.'.commit') ?>, caller: "\(operationInstance.runTimeTypeName()).commit")
        do{
            operationInstance._payload = try JSON.encoder.encode(<?php echo$subjectName ?>.self)
            let ic:ManagedPushOperations = try document.getCollection()
            // Create the pushOperation
            let pushOperation:PushOperation = document.newManagedModel(commit: false, isUndoable: false)
            pushOperation.quietChanges{
                pushOperation.commandUID = operationInstance.UID
                pushOperation.collection = ic
                pushOperation.counter += 1
                pushOperation.status = PushOperation.Status.pending
                pushOperation.creationDate = Date()<?php echo $operationIdentificationBlock ?>
                pushOperation.creatorUID = document.metadata.currentUserUID
                operationInstance.creatorUID = document.metadata.currentUserUID
                <?php echo $operationCommitBlock.cr()?>
            }
            pushOperation.operationName = <?php echo$baseClassName ?>.typeName()
            pushOperation.serialized = operationInstance.serialize()
        }catch{
            document.dispatchAdaptiveMessage(context,
                                             title: "Structural Error",
                                             body: "Operation collection is missing in \(operationInstance.runTimeTypeName())",
                onSelectedIndex: { (selectedIndex) -> () in
            })
            glog("\(error)", file: #file, function: #function, line: #line, category: Default.LOG_WARNING, decorative: false)
        }
    }


    open func push(sucessHandler success:@escaping (_ context:HTTPContext)->(),
        failureHandler failure:@escaping (_ context:HTTPContext)->()){
            do{
                let <?php echo$subjectName ?> = <?php echo $payloadDeserialization.cr(); ?>
                // The unitary operation are not always idempotent
                // so we do not want to push multiple times unintensionnaly.
                // Check BartlebyDocument+Operations.swift to understand Operation status
                let pushOperation = try self._getOperation()
                // Provision the operation
                if  pushOperation.canBePushed(){
                    pushOperation.status=PushOperation.Status.inProgress
                    type(of: self).execute(<?php echo"$subjectName,
                        $registrySyntagm:self.documentUID,".cr() ?>
                        sucessHandler: { (context: HTTPContext) -> () in
                            pushOperation.counter=pushOperation.counter+1
                            pushOperation.status=PushOperation.Status.completed
                            pushOperation.responseData = try? JSON.encoder.encode(context)
                            pushOperation.lastInvocationDate=Date()
                            let completion=Completion.successStateFromHTTPContext(context)
                            completion.setResult(context)
                            pushOperation.completionState=completion
                            success(context)
                        },
                        failureHandler: {(context: HTTPContext) -> () in
                            pushOperation.counter=pushOperation.counter+1
                            pushOperation.status=PushOperation.Status.completed
                            pushOperation.responseData = try? JSON.encoder.encode(context)
                            pushOperation.lastInvocationDate=Date()
                            let completion=Completion.failureStateFromHTTPContext(context)
                            completion.setResult(context)
                            pushOperation.completionState=completion
                            failure(context)
                        }
                    )
                }else{
                    self.referentDocument?.log("<?php echo$baseClassName ?> can't be pushed \(pushOperation.status)", file: #file, function: #function, line: #line, category: Default.LOG_FAULT, decorative: false)
                }
            }catch{
                let context = HTTPContext( code:3 ,
                caller: "<?php echo$baseClassName ?>.execute",
                relatedURL:nil,
                httpStatusCode:StatusOfCompletion.undefined.rawValue)
                context.message="\(error)"
                failure(context)
                self.referentDocument?.log("\(error)", file: #file, function: #function, line: #line, category: Default.LOG_WARNING, decorative: false)
            }

    }

    internal func _getOperation()throws->PushOperation{
        if let document = Bartleby.sharedInstance.getDocumentByUID(self.documentUID) {
            if let idx=document.pushOperations.index(where: { $0.commandUID==self.UID }){
                return document.pushOperations[idx]
            }
            throw BartlebyOperationError.operationNotFound(UID:"<?php echo$baseClassName ?>: \(self.UID)")
        }
        throw BartlebyOperationError.documentNotFound(documentUID:"<?php echo$baseClassName ?>: \(self.documentUID)")
    }

    <?php

    if ($shouldImplementExecuteBlock) {
        include __DIR__ . '/cuds.withWeakLogicExecute.swift.block.php';
    }else{
        include __DIR__ . '/cuds.withWeakLogicExecutePlaceHolder.swift.block.php';
    }


    ?>
}
<?php /*<- END OF TEMPLATE */?>