define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready",  "dojo/on",  "dijit/registry", "dijit/Dialog", 
         "tukos/utils", "tukos/menuUtils", "tukos/PageManager", "tukos/_ViewCustomMixin", "dojo/json"], 
  function(declare, lang, ready, on, registry, Dialog, utils, mutils, Pmg, _ViewCustomMixin, JSON ){
    return declare([ _ViewCustomMixin], {
    	constructor: function(args){
    		declare.safeMixin(this, args);
            var defaultTabMenu = registry.byId(this.container.id + '_tablist_Menu');
            if (defaultTabMenu){
            	defaultTabMenu.destroyRecursive();
            }
    		mutils.buildContextMenu(this.container, {type: 'DynamicMenu', atts: {targetNodeIds: [this.container.domNode]}, items: []});
    	},
    	contextMenuCallback: function(evt){
			evt.preventDefault();            
			var self = this, pane = this.currentPane();
            if (evt.target.checked || (evt.target.checked !== false && pane.isObjectPane() && pane.title === registry.getEnclosingWidget(evt.target).label)){
                mutils.setContextMenuItems(this.container, [{atts: {label: Pmg.message('refresh'), onClick: function(evt){self.refresh('Tab', []);}}}, {atts: {label: Pmg.message('customization'), onClick: lang.hitch(this, this.openCustomDialog)}}]);
            }else{
                mutils.setContextMenuItems(this.container, []);
            }
        },
        refresh: function(action, data, keepOptions, currentPane){
            var currentPane = currentPane || this.currentPane(), theForm = currentPane.form, theFormContent = currentPane.formContent, changesToRestore = (keepOptions ? theForm.keepChanges(keepOptions) : {});
            var refreshAction = function(){
                var query = {};
                if (!theForm){
                	var paneId = currentPane.id, panesConfig = Pmg.getCustom('panesConfig'), paneConfig;
                	if (currentPane.associatedtukosid){
						query.id = currentPane.associatedtukosid;
					}
                }else{
	            	if (theFormContent.viewMode === 'Edit'){
	                    var id = lang.hitch(theForm, theForm.valueOf)('id');
	                    if (id){
	                    	query.id = id;
	                    }
	                }
                }
                if (theFormContent.viewMode === 'Overview' && currentPane.isAccordion()){
                	query.title = currentPane.get('title');
                }
            	return Pmg.serverDialog({object: theFormContent.object, view: theFormContent.viewMode, mode: theFormContent.paneMode, action: action, query: query}, {data: data}, {widget: currentPane, att: 'title', defaultFeedback: false}).then(
                    function(response){
						currentPane.set('title', response.title);
						currentPane.refresh(response.formContent);
                        ready(function(){
                            setTimeout(function(){(currentPane.form || currentPane).restoreChanges(changesToRestore, keepOptions);}, 0);// due to similar setTimeout in ObjectPane affecting markIfChanged
                            Pmg.setFeedback(response['feedback'], Pmg.message('refreshed'));
                            currentPane.resize();
                        });
                        return response;
                    }
                );
            }
            if (keepOptions || !theForm){
                return refreshAction();
            }else{
                //return this.checkChangesDialog(theForm, lang.hitch(this, refreshAction));
                return theForm.checkChangesDialog(refreshAction);
            }
        },
        currentPane: function(){
            return this.container.selectedChildWidget;
        },
        firstPane: function(){
        	return this.container.getChildren()[0];
        },
        lastPane: function(){
        	return this.container.getChildren().slice(-1)[0];
        },
        selectPane: function(pane, transitionDir, transition){
        	this.container.selectChild(pane, transitionDir, transition);
        },
        gotoTab: function(target){
            var id, name, storeAtts;
			if (target.table){// here due to 'table' hard-coded in editor text with links for text prior to modification request 34230
                target.object = target.table;
            }
            if (target.id){
            	target.query = lang.mixin({id: target.id}, target.query ||{});// legacy - changed to target on 2017-06-16 & need to support previous syntax in tukos/editor/plugins/TukosLinkDialog
            }
			if (target.view === "Overview" || (id = (target.query || {}).id) || (name = (target.query || {}).name) || (storeAtts = (target.query || {}).storeatts)){
				var openedTabs = this.container.getChildren();
	            if (!name && typeof storeAtts === 'string'){
					name = ((JSON.parse(storeAtts).where || {}).name || [])[1];
				}
				for (var i in openedTabs){
	                var tab = openedTabs[i];
					if ((target.view === "Overview" && (tab.formContent || {}).viewMode === "Overview" && tab.formContent.object === target.object) || (id && tab.contentId == id) || (name && tab.contentName === name) || (id && (tab.get('title').match(/(\d+)\)$/) || {} )[1] === id)){
	                    this.container.selectChild(tab);
	                    return;
	                }
	            }
			}
            target.action = target.action || 'Tab';
            this.request(target);
        }
    }); 
});
