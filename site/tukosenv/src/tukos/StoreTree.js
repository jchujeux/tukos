/*
 *  Provides a Tree widget field which data store is args.storeData, callable from ObjectPane.js
 *   
 */
define (["dojo/_base/declare", "dojo/aspect", "dijit/tree/ObjectStoreModel", "dijit/Tree", "dijit/tree/dndSource", "tukos/PageManager", "tukos/utils", "dojo/domReady!"], 
    function(declare, aspect, ObjectStoreModel, Tree, dndSource, Pmg, utils){
    return declare(Tree, {
        constructor: function(args){
            if (args.storeArgs && (args.storeArgs.action || args.storeArgs.target)){
                args.storeArgs.object = args.storeArgs.object || args.object;
            }
            var myStore = Pmg.store(args.storeArgs);
            myStore.getChildren = function(object, options){
                return this.query({parentid: object.id});
            }
            aspect.around(myStore, "put", function(originalPut){
                // To support DnD, the store must support put(child, {parent: parent}).
                // Since our store is relational, that just amounts to setting child.parent
                // to the parent's id.
                return function(obj, options){
                    if(options && options.parent){
                        obj.parentid = options.parent.id;
                    }
                    return originalPut.call(myStore, obj, options);
                }
            });
            args.model = new ObjectStoreModel({store: myStore, query: utils.newObj([[myStore.idProperty, args.root]]),
                                               mayHaveChildren: function(object){
                                                    return (object.hasChildren != undefined ? object.hasChildren : myStore.getChildren(object).length > 0);
					                           }
            });
            args.dndController = dndSource;
        },
        getLabel: function(item){
            return item.name + '(' + item.id + ')';
        }
    }); 
});
