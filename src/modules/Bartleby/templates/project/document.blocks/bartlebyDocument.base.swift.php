<?php

//////////////////////
// EXCLUSIONS
//////////////////////

if (!isset($configurator)){
    return '$configurator required in '.__FILE__;
}

//////////////////////////
// VARIABLES DEFINITIONS
//////////////////////////

//////////////////////////
// BLOCK
//////////////////////////

?>
    //MARK: - Initializers

    #if os(OSX)

    required public override init() {
        super.init()
        self._configure()
    }

    #else


    public override init(fileURL url: URL) {
        super.init(fileURL: url as URL)
        self._configure()
    }

    #endif


    // Perform cleanUp when closing a document
    public func cleanUp(){
        syncOnMain{

            self.send(DocumentStates.cleanUp)

            // Transition off line
            self.online=false

            // Boxes
            if self.metadata.cleanupBoxesWhenClosingDocument{
                self.bsfs.unMountAllBoxes()
            }

            // Security scoped urls
            self.releaseAllSecurizedURLS()

            // Unregister the instances.
            for (_ , collection) in self._collections{
                collection.superIterate({ o in
                    Bartleby.unRegister(o)
                })
            }
        }
    }

    // The document shared Serializer
    open lazy var serializer:Serializer = JSONSerializer(document: self)

    // This deserializer is replaced by your `AppDynamics` in app contexts.
    open var dynamics:Dynamics = BartlebysDynamics()

    // Keep a reference to the document file Wrapper
    open var documentFileWrapper:FileWrapper = FileWrapper(directoryWithFileWrappers:[:])

    // The Document Metadata
    @objc dynamic open var metadata = DocumentMetadata()

    // Bartleby's Synchronized File System for this document.
    public var bsfs:BSFS{
        if self._bsfs == nil{
            self._bsfs=BSFS(in:self)
        }
        return self._bsfs!
    }

    internal var _bsfs: BSFS?

    // Hook the triggers
    public var triggerHooks = [TriggerHook]()

    // Triggered Data is used to store data before data integration
    internal var _triggeredDataBuffer:[Trigger] = [Trigger]()

    // An in memory flag to distinguish dotBart import case
    open var dotBart = false

    /// The underlining storage hashed by collection name
    internal var _collections = [String:BartlebyCollection]()

    /// We store the URL of the active security bookmarks
    internal var _activeSecurityBookmarks = [String:URL]()

    // Reachability Manager
    internal var _reachabilityManager:NetworkReachabilityManager?

    // MARK: URI

    // The online flag is driving the "connection" process
    // It connects to the SSE and starts the pushLoop
    open var online:Bool = false{
        willSet{
            // Transition on line
            if newValue == true && online == false{
                self._connectToSSE()
            }
            // Transition off line
            if newValue == false && online == true{
                self.log("SSE is transitioning offline",file:#file,function:#function,line:#line,category: "SSE")
                self._closeSSE()
            }
            if newValue == online{
                self.log("Neutral online var setting",file:#file,function:#function,line:#line,category: "SSE")
            }
        }
        didSet{
            self.metadata.online = online
            self.startPushLoopIfNecessary()
        }
    }

    // MARK: - Synchronization

    // SSE server sent event source
    internal var _sse:EventSource?

    // The EventSource URL for Server Sent Events
    @objc open dynamic lazy var sseURL:URL = URL(string: self.baseURL.absoluteString+"/SSETriggers?spaceUID=\(self.spaceUID)&observationUID=\(self.UID)&lastIndex=\(self.metadata.lastIntegratedTriggerIndex)&runUID=\(Bartleby.runUID)&showDetails=false")!

    open var synchronizationHandlers:Handlers = Handlers.withoutCompletion()

    internal var _timer: Timer?

    // MARK: - Metrics

    @objc open dynamic var metrics = [Metrics]()

    // MARK: - Logs

    open var enableLog: Bool = true

    open var printLogsToTheConsole: Bool = true

    open var logs=[LogEntry]()

    open var logsObservers=[LogEntriesObserver]()

    // MARK: - Consignation

    /// The display duration of volatile messages
    public static let VOLATILE_DISPLAY_DURATION: Double = 3

    // MARK:  Simple stack management

    open var trackingIsEnabled: Bool = false

    open var glogTrackedEntries: Bool = false

    open var trackingStack = [(result:Any?, context:Consignable)]()

    // MARK: - BSFS: BoxDelegate

    /// BSFS sends to BoxDelegate
    /// The delegate invokes proceed asynchronously giving the time to perform required actions
    ///
    /// - Parameter node: the node that will be moved or copied
    open func moveIsReady(node:Node,to destinationPath:String,proceed:()->()){
        // If necessary we can wait
        proceed()
    }


    /// BSFS sends to BoxDelegate
    /// The delegate invokes proceed asynchronously giving the time to perform required actions
    ///
    /// - Parameter node: the node that will be moved or copied
    open func copyIsReady(node:Node,to destinationPath:String,proceed:()->()){
        // If necessary we can wait
        proceed()
    }

    /// BSFS sends to BoxDelegate
    /// The delegate invokes proceed asynchronously giving the time to perform required actions
    ///
    /// - Parameter node: the node that will be Updated
    open func deletionIsReady(node:Node,proceed:()->()){
        // If necessary we can wait
        proceed()
    }

    /// BSFS sends to BoxDelegate
    /// The delegate invokes proceed asynchronously giving the time to perform required actions
    ///
    /// - Parameter node: the node that will be Created or Updated
    open func nodeIsReady(node: Node, proceed: () -> ()) {
        proceed()
    }

    /// Should we allow the replacement of content node
    ///
    /// - Parameters:
    ///   - node: the node
    ///   - path: the path
    ///   - accessor: the accessor
    /// - Returns: true if allowed
    open func allowReplaceContent(of node:Node, withContentAt path:String, by accessor:NodeAccessor)->Bool{
        return false // Return false by default
    }

    // MARK: - Document Messages Listeners

    fileprivate var _messageListeners=[MessageListener]()

    open func send<T:StateMessage>(_ message:T){
        for listener in self._messageListeners{
            listener.handle(message: message)
        }
    }

    open func addDocumentMessagesListener(_ listener:MessageListener){
        if !self._messageListeners.contains(where: { (l) -> Bool in
             return listener.UID == l.UID
        }){
            self._messageListeners.append(listener)
        }
    }

    open func removeDocumentMessagesListener(_ listener:MessageListener){
        if let idx = self._messageListeners.index(where: { (l) -> Bool in
            return listener.UID == l.UID
        }){
            self._messageListeners.remove(at: idx)
        }
    }