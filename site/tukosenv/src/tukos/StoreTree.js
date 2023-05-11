define (["dojo/_base/declare", "dojo/aspect", "dijit/tree/ObjectStoreModel", "dijit/Tree", "dojo/store/Memory", "dijit/tree/dndSource", "tukos/PageManager", "tukos/utils", "tukos/evalutils", "dojo/domReady!"], 
    function(declare, aspect, ObjectStoreModel, Tree, Memory, dndSource, Pmg, utils, eutils){
    return declare(Tree, {
        constructor: function(args){
            var storeArgs = args.storeArgs;
            if (storeArgs && (storeArgs.action || storeArgs.target)){
                storeArgs.object = storeArgs.object || args.object;
            }
            var myStore = Pmg.store(storeArgs), parentProperty = args.parentProperty || 'parentid';
            myStore.getChildren = function(object){
                return this.query(utils.newObj([[parentProperty, object.id]]));
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
            args.model = new ObjectStoreModel({store: myStore, query: utils.newObj([[myStore.idProperty, args.root]]), labelType: 'html', mayHaveChildren: function(object){
                    return (object.hasChildren != undefined ? object.hasChildren : myStore.getChildren(object).length > 0);
               }
            });
            if (storeArgs.object && storeArgs.data){
            	var dataStore = this.dataStore = new Memory({data: storeArgs.data}), root = args.root, parentDataProperty = args.parentDataProperty ||parentProperty; 
            	args.model.getRoot = function(onItem){
            		onItem(dataStore.get(root));
            	}
            	this.initializeChildrenCache(root, dataStore, parentDataProperty, args.model);
            }
            if (!args.noDnd){
				args.dndController = dndSource;
			}
        },
   		postCreate: function(){
            this.inherited(arguments);
            if (this.onClickAction){
	            this.onClickFunction = eutils.eval(this.onClickAction, 'item');
	            this.on('click', function(item, treeNode, event){
	                this.onClickFunction(item, treeNode, event);
	            });
            }
        },
        initializeChildrenCache : function(rootId, dataStore, parentDataProperty, model){
        	var self = this, children =  dataStore.query(utils.newObj([[parentDataProperty, rootId]]));
        	if (children.length > 0){
        			model.childrenCache[rootId] = children;
        			children.forEach(function(item){
            			var id = item[dataStore.idProperty], children = dataStore.query(utils.newObj([[parentDataProperty, id]]));
            			if (children.length > 0){
            				model.childrenCache[id] = children;
            				self.initializeChildrenCache(id, dataStore, parentDataProperty, model);
            			}
         			});
        	}
        },
        getLabel: function(item){
            return  '<span style="display:inline-block;white-space:normal;margin-right:40px;">' + item.name + ((this.colInLabel === false || !item[this.colInLabel]) ? '' :  '(' + item[this.colInLabel || 'id'] + ')') + '</span>';
        },
	    update : function() {
	      //this.model.store.clearOnClose = true;
	      //this.model.store.close();
			if (this.rootNode){
				delete this._itemNodesMap;
				this._itemNodesMap = {};
				this.rootNode.state = "UNCHECKED";
				delete this.model.root.children;
				this.model.root.children = null;
				this.rootNode.destroyRecursive();
			}
			this.model.constructor(this.model)
			this.postMixInProperties();
			this._load();
	    }
    }); 
});
