
     // MARK: -  Entities factories


    /// The Collectible factory
    ///
    /// - Parameters:
    ///   - uid: the predefined UID
    ///   - commit: should we commit the entity ?
    ///   - isUndoable: is that creation undoable  ?
    /// - Returns: the collectible Instance
    open func newManagedModel<T:Collectible>(predefinedUID uid: UID = Default.NO_UID, commit:Bool = true, isUndoable:Bool = true)->T{

        // User factory relies on as a special Method
        if T.typeName()=="User"{
            return self._newUser(commit:commit,isUndoable: isUndoable, predefinedUID: uid ) as! T
        }
        // Generated UnaManaged and ManagedModel are supported
        // We prefer to crash if some tries to inject another collectible
        var instance = try! self.dynamics.newInstanceOf(T.typeName()) as! T
        if uid != Default.NO_UID{
            instance.UID = uid
        }
        self.register(instance: &instance, commit: commit, isUndoable: isUndoable)
        return  instance
    }



    ///  You can register a instance that has been created out of the Document
    ///
    /// - Parameters:
    ///   - instance: the instance
    ///   - commit: should we commit the entity ?
    ///   - isUndoable:  is that creation undoable  ?
    open func register<T:Collectible>(instance:inout T,commit:Bool=true, isUndoable:Bool=true){
        instance.quietChanges{
            if instance.UID == Default.NO_UID{
                instance.UID = Bartleby.createUID()
            }

            // Do we have a collection ?
            if let collection=self.collectionByName(instance.d_collectionName){
                collection.add(instance, commit: false, isUndoable:isUndoable)
            }

            // Set up the creator
            instance.creatorUID = self.metadata.currentUserUID

            // We defer the commit to the next synchronization loop
            // to allow post instantiation modification
            if commit{
                instance.needsToBeCommitted()
            }
        }
        self.didCreate(instance)
    }



    /// The user factory
    ///
    /// - Parameters:
    ///   - commit: should we commit the user?
    ///   - isUndoable:  is its creation undoable?
    ///   - uid: the forced UID
    /// - Returns: the created user
    internal func _newUser(commit: Bool = true, isUndoable:Bool , predefinedUID uid: UID) -> User {
        let user=User()
        user.quietChanges {
            user._id = uid == Default.NO_UID ? Bartleby.createUID() : uid
            user.password=Bartleby.randomStringWithLength(8,signs:Bartleby.configuration.PASSWORD_CHAR_CART)
            if let creator=self.metadata.currentUser {
                user.creatorUID = creator.UID
            }else{
                // Autopoiesis.
                user.creatorUID = user.UID
            }
            user.spaceUID = self.metadata.spaceUID
            self.users.add(user, commit:false,isUndoable:isUndoable )
        }
        if commit{
            user.needsToBeCommitted()
        }
        self.didCreate(user)
        return user
    }

    /// Called just after Factory Method
    /// Override this method in your document instance
    /// to perform instance customization
    ///
    /// - Parameter instance: the fresh instance
    open func didCreate(_ instance:Collectible){

    }

    /// Called just before to Erase a Collectible
    /// Related object are cleaned by the Relational logic
    /// But you may want to clean up or perform something before Erasure.
    /// Override this method in your document instance
    /// to perform associated cleaning before erasure
    ///
    /// - Parameter instance: the fresh instance
    open func willErase(_ instance:Collectible){
        if let o = instance as? Box {
            self.bsfs.unMount(boxUID: o.UID, completed: { (completed) in })
        }else if let _ = instance as? Node{
            // Cancel any pending operation
        }else if let o = instance as? Block {
            self.bsfs.deleteBlockFile(o)
        }
    }