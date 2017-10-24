<?php echo (GenerativeHelperForSwift::defaultHeader($f,$d).cr()); ?>
<?php echo ($importDirectives) ?>

// We try to reduce the usage of Objc Dynamic and the Objc() name mangling to prevent intermodule conflicts
// We still use NSObject for ManagedModel to be able to use Cocoa Bindings, KVO, KVC.
// But we want to be able to drop that dependencies in future versions of Bartleby's stack.
//
// #TODO
// THIS IMPLEMENTION IS CURRENTLY NON OPTIMIZED
// (WE WILL USE INDEXED CLOSURES NOT TO NEED TO COMPARE THE TYPENAME)
//
// So we prefer to use Flexions to generate facades and wait for native dynamism implementations.
//
// Dynamics is a must for example:
//  - to handle trigger from Server Sent Event (to deserialize the payload)
//  - to deal with operation provisionning
//
// Everywhere else we use Generic approaches.
// We currently support dynamics for Managed And UnmanagedModel
//
// Deserialization Usage sample :
// ` if let deserializedTimedText = try d.deserialize(typeName: TimedText.typeName(), data: data, document: nil) as? TimedText{
//      ...
// }`
open <?php echo ($isIncludeInBartlebysCommons ? 'class BartlebysDynamics:Dynamics' : 'class '.$typeName.':BartlebysDynamics');?>{

    public <?php echo $overrideString ?>init(){
        <?php echo $superInitInvocation ?>
    }

    /// Deserializes dynamically an entity based on its Class name.
    ///
    /// - Parameters:
    ///   - typeName: the typeName
    ///   - data: the encoded data
    ///   - document: the document to register In the instance (if set to nil the instance will not be registred
    /// - Returns: the dynamic instance that you cast..?
    open <?php echo $overrideString ?>func deserialize(typeName:String,data:Data,document:BartlebyDocument?)throws->Any{

        var instance : Decodable!

        if let document = document{
            defer{
                if let managedModel = instance as? ManagedModel{
                    if (managedModel is BartlebyCollection) || (managedModel is BartlebyOperation){
                        // Add the document reference
                        managedModel.referentDocument=document
                    }else{
                        // Add the collection reference
                        // Calls the Bartleby.register(self)
                        managedModel.collection=document.collectionByName(managedModel.d_collectionName)
                    }
                }
            }
        }//FLEXIONS_TAG_001
        <?php echo $superDeserializeInvocation ?>
    }



    /// This is a Dyamic Factory
    /// e.g: d.newInstanceOf(TimedText.typeName())
    /// - Parameter typeName: the class name
    /// - Returns: the new instance
    open <?php echo $overrideString ?>func newInstanceOf(_ typeName:String)throws->Any{//FLEXIONS_TAG_002
        <?php echo $superNewInstanceInvocation ?>
    }

<?php if ($isIncludeInBartlebysCommons) {
    include __DIR__ . '/dynamics.base.block';
} ?>

}