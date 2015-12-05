<?php

/*
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


if ($actionRepresentation->class=="DeleteUser"){
    $a='b';
}

// Compute ALL the Variables you need in the template

$httpMethod=$actionRepresentation->httpMethod;
$pluralizedName=lcfirst($actionRepresentation->collectionName);
$singularName=lcfirst(Pluralization::singularize($pluralizedName));
$baseClassName=ucfirst($actionRepresentation->class);
$ucfSingularName=ucfirst($singularName);
$ucfPluralizedName=ucfirst($pluralizedName);

$actionString='creation';
$localAction='upsert';

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
    $actionString='delete';
    $localAction='delete';
}else{
    $actionString='NO_FOUND';
    $localAction='NO_FOUND';
}

$firstParameterName=NULL;
$firstParameterTypeString=NULL;
$varName=NULL;
$successTypeString = NULL;

while($actionRepresentation->iterateOnParameters()){
    /*@var $parameter PropertyRepresentation*/
    $parameter=$actionRepresentation->getParameter();
    // We use the first parameter.
    if (!isset($varName,$successTypeString)){
        if ($parameter->type == FlexionsTypes::COLLECTION){
            $firstParameterName=$parameter->name;
            $firstParameterTypeString=($httpMethod!="DELETE")?'['.$ucfSingularName.']':"String";
            $varName=$pluralizedName;
            $successTypeString = '['.$ucfSingularName.']';
        }else{
            $firstParameterName=$parameter->name;
            $firstParameterTypeString=($httpMethod!="DELETE")?$ucfSingularName:"String";
            $varName=$singularName;
            $successTypeString =$ucfSingularName;
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

@objc(<?php echo $baseClassName ?>Parameters) class <?php echo $baseClassName ?>Parameters : JObject {

    var <?php echo $firstParameterName ?>:<?php echo $firstParameterTypeString ?>?

    required init(){
        super.init()
    }

    // MARK: Mappable

    required init?(_ map: Map) {
        super.init(map)
        mapping(map)
    }

    override func mapping(map: Map) {
        super.mapping(map)
        <?php echo$firstParameterName ?> <- map["<?php echo$firstParameterName ?>"]
    }
}


@objc(<?php echo$baseClassName ?>) class <?php echo$baseClassName ?> : JObject{

    private var _parameters:<?php echo$baseClassName ?>Parameters

    private var _documentUDID:String

    private var _rUDID:String

    private var _observableViaUDID:String?

    private var _operation:Operation=Operation()

    required convenience init(){
        self.init(within:"",parameters:<?php echo$baseClassName ?>Parameters(),rUDID:"",observableViaUDID:nil)
    }

    required convenience init?(_ map: Map) {
        self.init()
        self.mapping(map)
    }

    override func mapping(map: Map) {
        super.mapping(map)
        self._parameters <- map["_parameters"]
        self._documentUDID <- map["_documentUDID"]
        self._rUDID <- map["_rUDID"]
        self._observableViaUDID <- map["observableViaUDID"]
        // (!) Do not serialize the operation
        // In this case the operation will hold this instance in its dictionary.
    }

    /**
    This is the designated constructor.

    - parameter documentUDID:      The document UDID in wich you want to perform
    - parameter parameters:        The call parameters
    - parameter rUDID:             The releated UDID is the "domain" in wich the user is authenticated
    - parameter observableViaUDID: If you want to support distributed execution this action will be propagated with this UDID

    */
    init (within documentUDID:String, parameters:<?php echo$baseClassName ?>Parameters,rUDID:String,observableViaUDID:String?) {
        self._parameters=parameters
        self._documentUDID=documentUDID
        self._observableViaUDID=observableViaUDID
        self._rUDID=rUDID
        super.init()
    }

    func commit(){
        // This first test should not be necessary (with BARTLEBY 1.0)
        if let <?php echo$firstParameterName ?>=self._parameters.<?php echo$firstParameterName ?>{

            let context=Context(code:0, caller: "<?php echo$baseClassName ?>.commit")

            if let registry = Bartleby.sharedInstance.getRegistryByUDID(self._documentUDID) {
                // <?php echo$localAction ?> locally
                <?php if ($httpMethod!="DELETE") {
                    echo("if registry.$localAction($firstParameterName){");
                } else {
                    echo("if registry.$localAction($firstParameterName, fromCollectionWithName:\"$actionRepresentation->collectionName\"){"); }
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
        }else{
            // TEMP this situation should not occur with BARTLEBY 1.0
        }
    }

    func push(){
        if let <?php if($httpMethod=="POST"){echo("registry");}else{echo("_");} ?> = Bartleby.sharedInstance.getRegistryByUDID(self._documentUDID) {
            // The unitary operation are not always idempotent
            // so we do not want to push multiple times unintensionnaly.
            if  self._operation.status==Operation.Status.Pending ||
                self._operation.status==Operation.Status.Unsucessful {
                // We try to execute
                self._operation.status=Operation.Status.InProgress
                <?php echo$baseClassName ?>.execute(self._documentUDID,
                    parameters: self._parameters,
                    sucessHandler: { () -> () in
                        <?php if ($httpMethod=="POST") {
                            echo("registry.markAsDistributed(self._parameters.$firstParameterName)".cr());
                        } else {
                            echo(cr());
                        }
                        ?>
                        self._operation.counter=self._operation.counter!+1
                        self._operation.status=Operation.Status.Successful
                    },
                    failureHandler: {(result: HTTPContext) -> () in
                        self._operation.counter=self._operation.counter!+1
                        self._operation.status=Operation.Status.Unsucessful
                        self._operation.failureMessage="\(result)"
                    }
                )
            }else{
                // This registry is not available there is nothing to todo.
                let context=Context(code:0, caller: "<?php echo$baseClassName ?>.push")
                Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                    title: NSLocalizedString("Push error", comment: "Push error"),
                    body: "\(NSLocalizedString("Attempt to push an operation with status ==",comment:"Attempt to push an operation with status =="))\(self._operation.status))",
                    onSelectedIndex: { (selectedIndex) -> () in
                })
            }
        }
    }

    static func execute(rUDID:String,
            parameters:<?php echo$baseClassName ?>Parameters,
            sucessHandler success:()->(),
            failureHandler failure:(context:HTTPContext)->()){
                let pathURL=Configuration.baseUrl.URLByAppendingPathComponent("/<?php echo$varName ?>")
                let dictionary:Dictionary<String, AnyObject>?=Mapper().toJSON(parameters)
                let urlRequest=HTTPManager.mutableRequestWithToken(relatedUDID:rUDID,withActionName:"<?php echo$baseClassName ?>" ,forMethod:Method.<?php echo$httpMethod?>, and: pathURL)
                let r:Request=request(ParameterEncoding.JSON.encode(urlRequest, parameters: dictionary).0)
                r.responseString{ response in

                    let request=response.request
                    let result=response.result
                    let response=response.response

                    // Bartleby consignation

                    let context = HTTPContext( code: 1000,
                        caller: "<?php echo$baseClassName ?>._callApi",
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