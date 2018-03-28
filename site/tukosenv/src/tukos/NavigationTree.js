define (["dojo/_base/declare", "dojo/_base/lang", "dojo/aspect", "dojo/window", "dojo/Deferred", "tukos/store/ActionRequest", "dijit/Tree",
         "dijit/tree/ObjectStoreModel", "dijit/tree/dndSource", "tukos/PageManager", "tukos/_WidgetsMixin", "tukos/utils", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, aspect, window, Deferred, ActionRequest, Tree, ObjectStoreModel, dndSource, Pmg, _TukosWidgetsMixin, utils, messages){
    return declare([Tree, _TukosWidgetsMixin], {

        constructor: function(args){
        	var myStore = args.serverStore = new ActionRequest(args.storeArgs);
        	myStore.tree = this;
        	myStore.getChildren = function(object){
        		var tree = this.tree, model = tree.model, parentType = object.type, parentid = parentType === 'object' ? object.parentid : object.id, contextPathId,
        			queryResult = model.childrenCache[object.id] || this.query(
        								(contextPathId = tree.form.valueOf('contextid')) ? {parentid: parentid, contextpathid: contextPathId}: {parentid: parentid}, 
        								{params: parentType === 'root' ? {get: ['objects']} : (parentType === 'object' ? {get: ['items'], object: object.object} : {get: ['items', 'objects'], object: object.object})}
        							);
        		queryResult.then(function(children){
    				var insertedChildren = model.changesCache.insertedChildren;
        			children.forEach(function(child){
    					child.children = parseInt(child.children);
    					child = child.parentid 
    						? lang.mixin(child, {type: 'object', id: child.object + '@' + child.parentid, name: child.object})
    						: lang.mixin(child, {type: 'item', name: child.name + '(' + child.id + ')'});
     				});
    				if (insertedChildren[object.id]){
    					utils.forEach(insertedChildren[object.id], function(child){
    						children.unshift(child);
    					});
    					lang.hitch(model, "onChange", object)();
    				}
    				return children;
        			});
        		return queryResult;
        	}

        	myStore.put = function(obj, options){
        		var changedId, model = this.tree.model, oldParent = options.oldParent, oldParentId = oldParent.id, newParent = options.parent, newParentId = newParent.id, id = obj.id, childrenCache = model.childrenCache,
        			oldChildrenCache = childrenCache[oldParentId], newChildrenCache = childrenCache[newParentId], changesCache = model.changesCache, changedIds = changesCache.changedIds, removedChildren = changesCache.removedChildren,
        			insertedChildren = changesCache.insertedChildren;
        		if (changedId = changedIds[id]){
    				delete insertedChildren[oldParentId][id];
        			if (newParentId === changedId.initialParentId){
        				delete changedIds[id];
        				delete removedChildren[newParentId];
        			}else{
        				changedId.currentParentId = newParentId;
        				insertedChildren[newParentId] = utils.newObj([[id, obj]]);
        			}
        		}else{
        			changedId = changedIds[id] = {initialParentId: oldParentId, currentParentId: newParentId};
           			removedChildren[oldParentId] = insertedChildren[newParentId] = utils.newObj([[id, obj]]);
        		}
				oldParent.children +=-1;
				newParent.children += 1;
       			if (oldChildrenCache){
       				oldChildrenCache.then(function(children){
       					var index = children.indexOf(obj);
       					children.splice(index, 1);  
       					return children;
       				});
       				oldChildrenCache.then(lang.hitch(model, "onChildrenChange", oldParent));
       				lang.hitch(model, "onChange", oldParent)();
       			}
       			if (newChildrenCache){
       				newChildrenCache.then(function(children){
       					children.unshift(obj);  
       					return children;
       				});
       				newChildrenCache.then(lang.hitch(model, "onChildrenChange", newParent));
       				lang.hitch(model, "onChange", newParent)();
       			}
       			lang.hitch(model, "onChange", obj)();
        		return obj;//not used anyway
        	}
        	args.model = new ObjectStoreModel(
        		{store: myStore, root: {id: 0, type: 'root', children: true}, mayHaveChildren: this.mayHaveChildren, getLabel: this.getLabel, labelType: 'html', changesCache: {changedIds: {}, removedChildren: {}, insertedChildren: {}}}
        	)
            args.dndController = dndSource;
        },

    	postCreate: function(){
            this.inherited(arguments);
            this.on('click', function(item){
                if (item.type == 'item'){
                    Pmg.tabs.request({object: item.object, view: 'edit', mode: 'tab', action: 'tab', query: {id: item.id}});
                }else{
                    Pmg.setFeedback('No click action available on object folders');
                }
            });
        },

        mayHaveChildren: function(item){
            return item.children > 0 || typeof item.children == 'object';
        },

        getLabel: function(item){
            var content = item.children > 0 ? item.name + '(' + item.children + ' ' + messages.children + ')' : item.name, changesCache = this.model.changesCache, id = item.id;
        	if (typeof item.canEdit === "undefined" || item.canEdit){
                return changesCache.changedIds[id] || !utils.empty(changesCache.insertedChildren[id]) || !utils.empty(changesCache.removedChildren[id]) ? '<i>' + content + '</i>' : content;       		
        	}else{
        		return '<span style="color: Gray;">' + content + '</span>';
        	}
        },
        
        checkAcceptance: function(source, nodes){
        	var canEdit = true;
        	nodes.some(function(node){
        		var canEditItem = dijit.registry.byId(node.id).item.canEdit;
        		if (typeof canEditItem !== "undefined" && !canEditItem){
        			canEdit = false;
        			return true;
        		}
        	});
        	return canEdit;
        },
        
        navigationPath: function(itemsPath){
        	var parentId = 0, parentObject = '', navigationPath = [];
        	itemsPath.forEach(function(item){
        		if (parentObject !== item.object){
        			navigationPath.push(item.object + '@' + parentId);
        		}
        		navigationPath.push(item);
        		parentId = item.id;
        		parentObject = item.object;
        	});
        	return navigationPath;
        },
        
        showItem: function(item){
            var self = this;
            Pmg.serverDialog({object: 'navigation', view: 'pane', 'mode': 'accordion', action: 'get', query: {id: item.id, object:  item.object, params:{get: 'getPath'}}}).then(
                function(response){
                    self.set('paths', [self.navigationPath(response.path)]).then(
                        function(){
                            window.scrollIntoView(self.get('selectedNode').domNode);
                        }
                    );
                }
            );
        },
        reset: function(){
        	var model = this.model, root = model.root;
        	this.dndController.selectNone();
        	this._itemNodesMap = {};
        	this.rootNode.state = "UNCHECKED";
        	this.rootNode.destroyRecursive();
        	model.changesCache = {changedIds: {}, removedChildren: {}, insertedChildren: {}};
        	model.childrenCache = {};
        	this.tree._load();
        },
        save: function(){
        	var changesToSend = {}, index, parentid;
        	utils.forEach(this.model.changesCache.changedIds, function(change, id){
        		//changesToSend[id] = (index = (parentid = change.currentParentId).indexOf('@')) > -1 ? parentid.substring(index + 1) : parentid;
        		var currentParentId = change.currentParentId;
        		changesToSend[id] = typeof currentParentId === 'string' && (index = (parentid = currentParentId).indexOf('@')) > -1 ? parentid.substring(index + 1) : currentParentId;
        	});
            Pmg.serverDialog({object: 'navigation', view: 'pane', mode: 'accordion', action: 'save', query: {}}, {data: changesToSend}).then(
                lang.hitch(this, function(response){
                    this.reset();
                }
            ));
        }
    }); 
});
