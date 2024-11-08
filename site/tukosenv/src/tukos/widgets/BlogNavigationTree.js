define (["dojo/_base/declare", "tukos/store/ActionRequest", "dijit/Tree", "dijit/tree/ObjectStoreModel", "tukos/utils", "tukos/evalutils", "tukos/PageManager"], 
    function(declare, ActionRequest, Tree, ObjectStoreModel, utils, eutils, Pmg){
    return declare([Tree], {
        constructor: function(args){
        	var myStore = args.serverStore = new ActionRequest(args.storeArgs);
        	myStore.tree = this;
        	myStore.getChildren = function(item){
        		var tree = this.tree, model = tree.model,
        			queryResult = model.childrenCache[item.id] || this.query({parentid: item.id}, {params: {type: item.object}});
        		queryResult.then(function(children){
        			children.forEach(function(child){
    					child.children = parseInt(child.children);
     				});
    				return children;
        		});
        		return queryResult;
        	}
        	args.model = new ObjectStoreModel(
        		{store: myStore, root: {id: 0, object: 'contexts', children: true}, mayHaveChildren: this.mayHaveChildren, getLabel: this.getLabel, labelType: 'html'}
        	)
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
        mayHaveChildren: function(item){
            return item.children > 0 || typeof item.children == 'contexts';
        },
        getLabel: function(item){
            return item.object === 'contexts'
				? item.children > 0 ? item.name + '(' + item.children + ')' : item.name
				: '<span style="display:inline-block;white-space:normal;margin-right:40px;">' + item.name + ((this.colInLabel === false || !item[this.colInLabel]) ? '' :  '(' + item[this.colInLabel || 'id'] + ')') + '</span>';
        }
    }); 
});
