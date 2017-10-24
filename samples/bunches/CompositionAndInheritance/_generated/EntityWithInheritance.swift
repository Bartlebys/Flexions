//
//  EntityWithInheritance.swift
//  Samples
//
// THIS FILE AS BEEN GENERATED BY BARTLEBYFLEXIONS for Benoit Pereira da Silva https://pereira-da-silva.com/contact
// DO NOT MODIFY THIS FILE YOUR MODIFICATIONS WOULD BE ERASED ON NEXT GENERATION!
//
// Copyright (c) 2016  Bartleby's https://bartlebys.org   All rights reserved.
//
import Foundation
#if !USE_EMBEDDED_MODULES
import Alamofire
import ObjectMapper
import BartlebyKit
#endif

// MARK: An entity that uses Inheritance
@objc(EntityWithInheritance) open class EntityWithInheritance : BaseContext{

    // Universal type support
    override open class func typeName() -> String {
        return "EntityWithInheritance"
    }


	dynamic open var creationDate:Date? {	 
	    didSet { 
	       if creationDate != oldValue {
	            self.provisionChanges(forKey: "creationDate",oldValue: oldValue,newValue: creationDate) 
	       } 
	    }
	}


	dynamic open var color:String? {	 
	    didSet { 
	       if color != oldValue {
	            self.provisionChanges(forKey: "color",oldValue: oldValue,newValue: color) 
	       } 
	    }
	}


	dynamic open var icon:String? {	 
	    didSet { 
	       if icon != oldValue {
	            self.provisionChanges(forKey: "icon",oldValue: oldValue,newValue: icon) 
	       } 
	    }
	}


    // MARK: - Exposed (Bartleby's KVC like generative implementation)

    /// Return all the exposed instance variables keys. (Exposed == public and modifiable).
    override open var exposedKeys:[String] {
        var exposed=super.exposedKeys
        exposed.append(contentsOf:["creationDate","color","icon"])
        return exposed
    }


    /// Set the value of the given key
    ///
    /// - parameter value: the value
    /// - parameter key:   the key
    ///
    /// - throws: throws JObjectExpositionError when the key is not exposed
    override open func setExposedValue(_ value:Any?, forKey key: String) throws {
        switch key {

            case "creationDate":
                if let casted=value as? Date{
                    self.creationDate=casted
                }
            case "color":
                if let casted=value as? String{
                    self.color=casted
                }
            case "icon":
                if let casted=value as? String{
                    self.icon=casted
                }
            default:
                try super.setExposedValue(value, forKey: key)
        }
    }


    /// Returns the value of an exposed key.
    ///
    /// - parameter key: the key
    ///
    /// - throws: throws JObjectExpositionError when the key is not exposed
    ///
    /// - returns: returns the value
    override open func getExposedValueForKey(_ key:String) throws -> Any?{
        switch key {

            case "creationDate":
               return self.creationDate
            case "color":
               return self.color
            case "icon":
               return self.icon
            default:
                return try super.getExposedValueForKey(key)
        }
    }
    // MARK: - Mappable

    required public init?(map: Map) {
        super.init(map:map)
    }

    override open func mapping(map: Map) {
        super.mapping(map: map)
        self.silentGroupedChanges {
			self.creationDate <- ( map["creationDate"], ISO8601DateTransform() )
			self.color <- ( map["color"] )
			self.icon <- ( map["icon"] )
        }
    }


    // MARK: - NSSecureCoding

    required public init?(coder decoder: NSCoder) {
        super.init(coder: decoder)
        self.silentGroupedChanges {
			self.creationDate=decoder.decodeObject(of: NSDate.self , forKey:"creationDate") as Date?
			self.color=String(describing: decoder.decodeObject(of: NSString.self, forKey:"color") as NSString?)
			self.icon=String(describing: decoder.decodeObject(of: NSString.self, forKey:"icon") as NSString?)
        }
    }

    override open func encode(with coder: NSCoder) {
        super.encode(with:coder)
		if let creationDate = self.creationDate {
			coder.encode(creationDate,forKey:"creationDate")
		}
		if let color = self.color {
			coder.encode(color,forKey:"color")
		}
		if let icon = self.icon {
			coder.encode(icon,forKey:"icon")
		}
    }

    override open class var supportsSecureCoding:Bool{
        return true
    }


    required public init() {
        super.init()
    }

    // MARK: Identifiable

    override open class var collectionName:String{
        return "entityWithInheritances"
    }

    override open var d_collectionName:String{
        return EntityWithInheritance.collectionName
    }


}

