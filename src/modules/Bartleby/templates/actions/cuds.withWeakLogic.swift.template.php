<?php

/*
 * SWIFT 2.X template
 * This weak logic template is compliant with Bartleby 1.0 approach.
 * It allows to update easily very complex templates.
 * It is not logic less but the logic should be as weak as possible
 */
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $flexed Flexed */
/* @var $actionRepresentation ActionRepresentation*/
/* @var $hypotypose Hypotypose */

if (isset( $f,$d,$h)) {
    /* @var $f Flexed */
    /* @var $d ActionRepresentation*/
    /* @var $h Hypotypose */


    // We use explicit name.
    $flexed=$f;
    $actionRepresentation=$d;
    $actionRepresentation->class=$actionRepresentation->class;
    $hypotypose=$h;

    $flexed->fileName = $actionRepresentation->class . '.swift';
    $flexed->package = 'xOS/endpoints/';

}else{
    return NULL;
}

/////////////////
// EXCLUSIONS
/////////////////

// Should this Action be excluded ?

$exclusionName = str_replace($h->classPrefix, '', $d->class);
if (isset($excludeActionsWith)) {
    foreach ($excludeActionsWith as $exclusionString) {
        if (strpos($exclusionName, $exclusionString) !== false) {
            return NULL; // We return null
        }
    }
}


// This template cannot be used for GET Methods
if ($actionRepresentation->httpMethod==='GET'){
    return NULL;
}

// We want also to exclude by query

if (!(strpos($d->class,'ByQuery')===false)){
    return NULL;
}

/////////////////////////
// VARIABLES COMPUTATION
/////////////////////////

// Compute ALL the Variables you need in the template

$httpMethod=$actionRepresentation->httpMethod;
$pluralizedName=lcfirst($actionRepresentation->collectionName);
$singularName=lcfirst(Pluralization::singularize($pluralizedName));
$baseClassName=ucfirst($actionRepresentation->class);
$ucfSingularName=ucfirst($singularName);
$ucfPluralizedName=ucfirst($pluralizedName);

$actionString=NULL;
$localAction=NULL;

if ($httpMethod=="POST"){
    $actionString='creation';
    $localAction='upsert';
}elseif ($httpMethod=="PUT"){
    $actionString='update';
    $localAction='upsert';
}elseif ($httpMethod=="PATCH"){
    $actionString='update';
    $localAction='upsert';
}elseif ($httpMethod=="DELETE"){
    $actionString=NULL;
    $localAction=NULL;
}else{
    $actionString='NO_FOUND';
    $localAction='NO_FOUND';
}

$firstParameterName=NULL;
$firstParameterTypeString=NULL;
$varName=NULL;
$executeArgumentSerializationBlock=NULL;

while($actionRepresentation->iterateOnParameters()){
    /*@var $parameter PropertyRepresentation*/
    $parameter=$actionRepresentation->getParameter();
    // We use the first parameter.
    if (!isset($varName,$firstParameterName,$firstParameterTypeString)){
        if ($parameter->type == FlexionsTypes::COLLECTION){
            $firstParameterName=$parameter->name;
            if($httpMethod!='DELETE'){
                $firstParameterTypeString='['.$ucfSingularName.']';
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, AnyObject>()
                var collection=[Dictionary<String, AnyObject>]()

                for $singularName in $pluralizedName{
                    let serializedInstance=Mapper<$ucfSingularName>().toJSON($singularName)
                    collection.append(serializedInstance)
                }
                parameters[\"$pluralizedName\"]=collection".cr();
            }else{
                $actionString='deleteByIds';
                $localAction='deleteByIds';
                $firstParameterTypeString='[String]';
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, AnyObject>()
                parameters[\"ids\"]=ids".cr();
            }
            $varName=$pluralizedName;
        }else{
            $firstParameterName=$parameter->name;
            if($httpMethod!='DELETE'){
                $firstParameterTypeString=$ucfSingularName;
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, AnyObject>()
                parameters[\"$singularName\"]=Mapper<$firstParameterTypeString>().toJSON($firstParameterName)".cr();
            }else{
                $actionString='deleteById';
                $localAction='deleteById';
                $firstParameterTypeString='String';
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, AnyObject>()
                parameters[\"".$singularName."Id\"]=".$singularName."Id".cr();
            }
            $varName=$singularName;
        }
    }
}


/////////////////////////
// TEMPLATE
/////////////////////////

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($flexed,$actionRepresentation); ?>

import Foundation
import Alamofire
import ObjectMapper

@objc(<?php echo$baseClassName ?>) class <?php echo$baseClassName ?> : JObject{

    private var _<?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString.cr() ?>

    private var _dID:String

    private var _oID:String?

    private var _operation:Operation=Operation()

    required convenience init(){
        self.init(<?php echo$firstParameterTypeString ?>(), withinDomain:Default.NO_UDID,observableVia:Default.NOT_OBSERVABLE)
    }

    required convenience init?(_ map: Map) {
        self.init()
        self.mapping(map)
    }

    override func mapping(map: Map) {
        super.mapping(map)
        self._dID <- map["_dID"]
        self._<?php echo$firstParameterName ?> <- map["_<?php echo$firstParameterName ?>"]
        self._oID <- map["_oID"]
        // (!) Do not serialize the operation
        // The operation will serialize this instance in its data dictionary.
    }

    /**
    This is the designated constructor.

    - parameter <?php echo$firstParameterName ?>: the <?php echo$firstParameterName ?> concerned the operation
    - parameter dID:The domain UDID
    - parameter oID: If you want to support distributed execution this action will be propagated to subscribers by this UDID

    */
    init (_ <?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?>=<?php echo$firstParameterTypeString."()" ?>, withinDomain dID:String,observableVia oID:String=Default.NOT_OBSERVABLE) {
        self._<?php echo$firstParameterName ?>=<?php echo$firstParameterName.cr() ?>
        self._dID=dID
        self._oID=oID
        super.init()
    }

    /**
    Creates the operation and proceeds to commit

    - parameter <?php echo$firstParameterName ?>: the instance
    - parameter dID:     the domain UDID
    - parameter oID:     the observavle UDID
    */
    static func commit(<?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?>, withinDomain dID:String,observableVia oID:String){
        let operationInstance=<?php echo$baseClassName ?>(<?php echo$firstParameterName ?>,withinDomain:dID,observableVia:oID)
        operationInstance.commit()
    }

    func commit(){

        let context=Context(code:<?php echo crc32($baseClassName.'.commit') ?>, caller: "<?php echo$baseClassName ?>.commit")

        if let registry = Bartleby.sharedInstance.getRegistryByUDID(self._dID) {
            // <?php echo$localAction ?> locally
            <?php if ($httpMethod!="DELETE") {
                echo("if registry.$localAction(self._$firstParameterName){");
            } else {
                echo("if registry.$localAction(self._$firstParameterName, fromCollectionWithName:\"$actionRepresentation->collectionName\"){"); }
            ?>
                // Prepare the operation
                self._operation.counter=0
                self._operation.status=Operation.Status.Pending
                self._operation.baseUrl=Configuration.baseUrl

                // Provision the operation.
                do{
                    let ic:OperationsCollectionController = try registry.getCollection()
                    ic.add(self._operation)
                }catch{
                    Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                    title: "Structural Error",
                    body: "Operation collection is missing",
                    onSelectedIndex: { (selectedIndex) -> () in
                    })
                }
                // The status will mark Operation.hasChanged as true
                self._operation.data=self.dictionaryRepresentation()
            }else{
                // Its is a Local Failure.
                Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                    title:NSLocalizedString("Dynamic Error", comment: "Dynamic Error"),
                    body: NSLocalizedString("Local operation has failed", comment: "Local operation has failed"),
                    onSelectedIndex: { (selectedIndex) -> () in
                })
            }

        }else{
            // This registry is not available there is nothing to todo.
            Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                title: NSLocalizedString("Structural error", comment: "Structural error"),
                body: NSLocalizedString("Registry is missing", comment: "Registry is missing"),
                onSelectedIndex: { (selectedIndex) -> () in
            })
        }
    }

    func push(sucessHandler success:()->(),
        failureHandler failure:(context:HTTPContext)->()){
        if let <?php if($httpMethod=="POST"){echo("registry");}else{echo("_");} ?> = Bartleby.sharedInstance.getRegistryByUDID(self._dID) {
            // The unitary operation are not always idempotent
            // so we do not want to push multiple times unintensionnaly.
            if  self._operation.status==Operation.Status.Pending ||
                self._operation.status==Operation.Status.Unsucessful {
                // We try to execute
                self._operation.status=Operation.Status.InProgress
                <?php echo$baseClassName ?>.execute(<?php echo"self._$firstParameterName,
                    withinDomain:self._dID,".cr() ?>
                    sucessHandler: { () -> () in
                        <?php if ($httpMethod=="POST") {
                            echo("registry.markAsDistributed(self._$firstParameterName)".cr());
                        } else {
                            echo(cr());
                        }
                        ?>
                        self._operation.counter=self._operation.counter!+1
                        self._operation.status=Operation.Status.Successful
                        success()
                    },
                    failureHandler: {(result: HTTPContext) -> () in
                        self._operation.counter=self._operation.counter!+1
                        self._operation.status=Operation.Status.Unsucessful
                        self._operation.failureMessage="\(result)"
                        failure(context:result)
                    }
                )
            }else{
                // This registry is not available there is nothing to todo.
                let context=Context(code:<?php echo crc32($baseClassName.'.push') ?>, caller: "<?php echo$baseClassName ?>.push")
                Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                    title: NSLocalizedString("Push error", comment: "Push error"),
                    body: "\(NSLocalizedString("Attempt to push an operation with status ==",comment:"Attempt to push an operation with status =="))\(self._operation.status))",
                    onSelectedIndex: { (selectedIndex) -> () in
                })
            }
        }
    }

    static func execute(<?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?>,
            withinDomain dID:String,
            sucessHandler success:()->(),
            failureHandler failure:(context:HTTPContext)->()){
                let pathURL=Configuration.baseUrl.URLByAppendingPathComponent("/<?php echo$varName ?>")<?php echo $executeArgumentSerializationBlock?>
                let urlRequest=HTTPManager.mutableRequestWithToken(domainID:dID,withActionName:"<?php echo$baseClassName ?>" ,forMethod:Method.<?php echo$httpMethod?>, and: pathURL)
                let r:Request=request(ParameterEncoding.JSON.encode(urlRequest, parameters: parameters).0)
                r.responseString{ response in

                    let request=response.request
                    let result=response.result
                    let response=response.response

                    // Bartleby consignation

                    let context = HTTPContext( code: <?php echo crc32($baseClassName.'.execute') ?>,
                        caller: "<?php echo$baseClassName ?>.execute",
                        relatedURL:request?.URL,
                        httpStatusCode: response?.statusCode ?? 0,
                        response: response )

                    // React according to the situation
                    var reactions = Array<Bartleby.Reaction> ()
                    reactions.append(Bartleby.Reaction.Track(result: nil, context: context)) // Tracking

                    if result.isFailure {
                        let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
                            context: context,
                            title: NSLocalizedString("Unsuccessfull attempt",
                            comment: "Unsuccessfull attempt"),
                            body: NSLocalizedString("<?php echo$actionString ?>  of <?php echo$varName ?>",
                            comment: "<?php echo$actionString ?> of <?php echo$varName ?> failure description"),
                            trigger:{ (selectedIndex) -> () in
                            print("Post presentation message selectedIndex:\(selectedIndex)")
                        })
                        reactions.append(failureReaction)
                        failure(context:context)
                    }else{
                        if let statusCode=response?.statusCode {
                            if 200...299 ~= statusCode {
                                success()
                            }else{
                                // Bartlby does not currenlty discriminate status codes 100 & 101
                                // and treats any status code >= 300 the same way
                                // because we consider that failures differentiations could be done by the caller.
                                let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
                                    context: context,
                                    title: NSLocalizedString("Unsuccessfull attempt",
                                    comment: "Unsuccessfull attempt"),
                                    body: NSLocalizedString("<?php echo$actionString ?> of <?php echo$varName ?>",
                                    comment: "<?php echo$actionString ?> of <?php echo$varName ?> failure description"),
                                    trigger:{ (selectedIndex) -> () in
                                    print("Post presentation message selectedIndex:\(selectedIndex)")
                                })
                                reactions.append(failureReaction)
                                failure(context:context)
                            }
                        }
                     }
                    //Let's react according to the context.
                    Bartleby.sharedInstance.perform(reactions, forContext: context)
                }
            }
}
<?php /*<- END OF TEMPLATE */?>