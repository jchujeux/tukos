/*
 *  Provides a Tree widget field which data store is args.storeData, callable from ObjectPane.js
 *   
 */
define (["dojo/_base/declare", "dijit/tree/ObjectStoreModel", "dijit/Tree", "dijit/tree/dndSource", "tukos/PageManager", "dojo/domReady!"], 
    function(declare, ObjectStoreModel, Tree, dndSource, Pmg){
    return declare(Tree, {
        constructor: function(args){
            if (args.storeArgs && (args.storeArgs.action || args.storeArgs.target)){
                args.storeArgs.object = args.storeArgs.object || args.object;
            }
            var myStore = Pmg.store(args.storeArgs);
            myStore.getChildren = function(object, options){
                return this.query({parentid: object.id});
            }
            var theQuery = new Object;
            theQuery[myStore.idProperty] = args.root;
            args.model = new ObjectStoreModel({store: myStore,
                                               query: theQuery,
                                               mayHaveChildren: function(object){
                                                    return (object.hasChildren != undefined ? object.hasChildren : myStore.getChildren(object).length > 0);
					                           }
            });
        },
        getLabel: function(item){
            return item.name + '(' + item.id + ')';
        }
    }); 
});
