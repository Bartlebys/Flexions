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
    $hypotypose=$h;

    $flexed->fileName = "TMP".$actionRepresentation->class . '.swift';
    $flexed->package = 'xOS/endpoints/';

}else{
    return NULL;
}

// Should this Action be excluded ?

$exclusionName = str_replace($h->classPrefix, '', $d->class);
if (isset($excludeActionsWith)) {
    foreach ($excludeActionsWith as $exclusionString) {
        if (strpos($exclusionName, $exclusionString) !== false) {
            return NULL; // We return null
        }
    }
}

// Compute ALL the Variables you need in the template

$httpMethod=$actionRepresentation->httpMethod;
$singularName=lcfirst($actionRepresentation->class);
$pluralizedName=lcfirst(Pluralization::pluralize($actionRepresentation->class));
$ucfSingularName=ucfirst($singularName);
$ucfPluralizedName=ucfirst($pluralizedName);

$successP = $d->getSuccessResponse();
$successTypeString = '';
if ($successP->type == FlexionsTypes::COLLECTION) {
    $successTypeString = '['.$successP->instanceOf.']';
} else if ($successP->type == FlexionsTypes::OBJECT) {
    $successTypeString = ucfirst($successP->instanceOf);
} else if ($successP->type == FlexionsTypes::DICTIONARY) {
    $successTypeString = 'Dictionary<String, AnyObject>';
}else {
    $nativeType = FlexionsSwiftLang::nativeTypeFor($successP->type);
    if($nativeType==FlexionsTypes::NOT_SUPPORTED){
        $successTypeString='';
    }else{
        $successTypeString=$nativeType;
    }
}


// This template cannot be used for GET Methods
if ($httpMethod=="GET"){
    return NULL;
}


/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($flexed,$actionRepresentation); ?>
import Foundation
import Alamofire
import ObjectMapper

@objc(CreateSampleParameters) class CreateSampleParameters : JObject {

    var sample:Sample?

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
        sample <- map["sample"]
    }
}


@objc(CreateSample) class CreateSample : JObject{

    private var _parameters:CreateSampleParameters

    private var _documentUDID:String

    private var _rUDID:String

    private var _observableViaUDID:String?

    private var _invocation:Invocation=Invocation()

    required convenience init(){
        self.init(within:"",parameters:CreateSampleParameters(),rUDID:"",observableViaUDID:nil)
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
        // (!) Do not serialize the invocation
        // In this case the invocation will hold this instance in its dictionary.
    }

    /**
    This is the designated constructor.

    - parameter documentUDID:      The document UDID in wich you want to perform
    - parameter parameters:        The call parameters
    - parameter rUDID:             The releated UDID is the "domain" in wich the user is authenticated
    - parameter observableViaUDID: If you want to support distributed execution this action will be propagated with this UDID

    */
    init (within documentUDID:String, parameters:CreateSampleParameters,rUDID:String,observableViaUDID:String?) {
        self._parameters=parameters
        self._documentUDID=documentUDID
        self._observableViaUDID=observableViaUDID
        self._rUDID=rUDID
        super.init()
    }

    func commit(){
        // This first test should not be necessary (with BARTLEBY 1.0)
        if let sample=self._parameters.sample{

            let context=Context(code:0, caller: "CreateSample.commit")

            if let registry = Bartleby.sharedInstance.getRegistryByUDID(self._documentUDID) {

                // Upsert locally
                if registry.upsert(sample){

                // Prepare the invocation
                self._invocation.counter=0
                self._invocation.status=Invocation.Status.Pending

                // Provision the invocation.
                do{
                let ic:InvocationsCollectionController = try registry.getCollection()
                ic.add(self._invocation)
                }catch{

                Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                title: "Structural Error",
                body: "Invocation collection is missing",
                onSelectedIndex: { (selectedIndex) -> () in
                })

                }
                // The status will mark Invocation.hasChanged as true
                self._invocation.data=self.dictionaryRepresentation()


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
        // The unitary operation are not always idempotent
        // so we do not want to push multiple times unintensionnaly.
        if  self._invocation.status==Invocation.Status.Pending ||
            self._invocation.status==Invocation.Status.Unsucessful {
            // We try to execute
            self._callAPI(self._documentUDID,
                parameters: self._parameters,
                sucessHandler: { () -> () in
                    self._parameters.sample?.hasBeenDistributed=true
                    // In any case we must mark the invocation has successful.
                    self._invocation.counter=self._invocation.counter!+1
                    self._invocation.status=Invocation.Status.Successful
                },
                    failureHandler: {(result: HTTPContext) -> () in
                    self._invocation.counter=self._invocation.counter!+1
                    self._invocation.status=Invocation.Status.Unsucessful
                    self._invocation.failureMessage="\(result)"
                }
            )
        }else{
            // This registry is not available there is nothing to todo.
            let context=Context(code:0, caller: "CreateSample.push")
            Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                title: NSLocalizedString("Push error", comment: "Push error"),
                body: "\(NSLocalizedString("Attempt to push an invocation with status ==",comment:"Attempt to push an invocation with status =="))\(self._invocation.status))",
                onSelectedIndex: { (selectedIndex) -> () in
                })

        }
    }

    private func _callAPI(rUDID:String,
            parameters:CreateSampleParameters,
            sucessHandler success:()->(),
            failureHandler failure:(context:HTTPContext)->()){
                self._invocation.status=Invocation.Status.InProgress
                let pathURL=Configuration.baseUrl.URLByAppendingPathComponent("/sample")
                let dictionary:Dictionary<String, AnyObject>?=Mapper().toJSON(parameters)
                let urlRequest=HTTPManager.mutableRequestWithToken(relatedUDID:rUDID,withActionName:"CreateSample" ,forMethod:Method.POST, and: pathURL)
                let r:Request=request(ParameterEncoding.JSON.encode(urlRequest, parameters: dictionary).0)
                r.responseString{ response in

                    let request=response.request
                    let result=response.result
                    let response=response.response

                    // Bartleby consignation

                    let context = HTTPContext( code: 1000,
                        caller: "CreateSamples._callApi",
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
                            body: NSLocalizedString("creation of samples",
                            comment: "creation of samples failure description"),
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
                                body: NSLocalizedString("creation of samples",
                                comment: "creation of samples failure description"),
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