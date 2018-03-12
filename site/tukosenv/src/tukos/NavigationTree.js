/*
 *  Provides a Tree widget field which data store is args.storeData, callable from ObjectPane.js
 *   
 */
define (["dojo/_base/declare", "dojo/aspect", "dijit/Tree", "tukos/PageManager", "tukos/_WidgetsMixin", "tukos/widgets/LazyWidgetPane", "dojo/json"], 
    function(declare, aspect, Tree, Pmg, _TukosWidgetsMixin, LazyWidgetPane, JSON){
    return declare([LazyWidgetPane, _TukosWidgetsMixin], {

        postCreate: function(){
            this.inherited(arguments);

            this.widgetClass = Tree;
            this.widgetArgs.storeArgs.mayHaveChildren = this.mayHaveChildren;
            this.widgetArgs.storeArgs.getChildren = this.getChildrenNodes;
            this.widgetArgs.storeArgs.getRoot = this.getRoot;
            this.widgetArgs.storeArgs.getLabel = this.getLabel;
            this.widgetArgs.model =  Pmg.store(this.widgetArgs.storeArgs);

            var self = this;
            aspect.after(this, "lazyCreate", function(){
                self.theWidget.onClick = function(item){
                    if (item.type == 'item'){
                        Pmg.tabs.request({object: item.object, view: 'edit', action: 'tab', query: {id: item.id}});
                    }else{
                        Pmg.setFeedback('No click action available on object folders');
                    }
                };
            });
        },

        mayHaveChildren: function(item){
            return item.children > 0 || typeof item.children == 'object';
        },

        getChildrenNodes: function(parentItem, onComplete, onError){// can't name it getChildren or conflicts with _widgetBase.getChildren
            if (parentItem.type === 'root'){
                var query = {parentid: parentItem.id};
                var options = {params: {get: ['objects']}};

            }else if (parentItem.type === 'object'){
                var query = {parentid: parentItem.parentid};
                var options = {params: {get: ['items'], object: parentItem.object}};
            }else{
                var query = {parentid: parentItem.id};
                var options = {params: {get: ['items', 'objects'], object: parentItem.object}};
            }
            this.query(query, options).then(function(children){
                var length = children.length;
                for (var i = 0; i < length; i++){
                    if (children[i].parentid){// is an object node
                        children[i].type = 'object';
                        children[i].id = children[i].object + children[i] .parentid;
                        children[i].name = children[i].object + '(' + children[i].children + ' items)';
                    }else{ // is an item node
                        children[i].type = 'item';
                        children[i].name = children[i].name + '(' + children[i].id + ')';
                    }
                } 
                parentItem.children = children;
                onComplete(children);
            }, onError);
        },
        
        getRoot: function(onItem, onError){
            onItem({id: 0, type: 'root', children: true});
        },
               
        getLabel: function(item){
            return item.name;
        },

        showItem: function(item){ // given an item id & object
            var self = this;
            Pmg.serverDialog({object: 'navigation', view: 'pane', action: 'get', query: {id: item.id, object:  item.object, params:{get: 'getPaths'}}}).then(
                function(response){
                    //console.log(response);
                    self.lazyCreate();
                    var thePaths = response.paths;
                    self.theWidget.set('paths', thePaths).then(
                        function(){
                            var theNode = self.theWidget.get('selectedNode');
                            self.theWidget.focusNode(theNode);
                        }
                    );
                    //console.log('hello world!');
                }
            );
        }
    }); 
});
