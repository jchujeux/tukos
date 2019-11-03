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
                	var paneId = currentPane.id, panesConfig = Pmg.getCustom().panesConfig, paneConfig;
                	panesConfig.some(function(config){
                		if (config.name === paneId){
                			paneConfig = config;
                			return true;
                		}
                	});
                	query = {id: paneConfig.id};
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
                        currentPane.refresh(response.formContent);
                        ready(function(){
                            (currentPane.form || currentPane).restoreChanges(changesToRestore, keepOptions);
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
                return this.checkChangesDialog(theForm, lang.hitch(this, refreshAction));
            }
        },
        checkChangesDialog: function(form, action){
            if (!form.userHasChanged()){
                return action();
            }else{
                return Pmg.confirmForgetChanges().then(
                    function(){return action()},
                    function(){Pmg.setFeedback(Pmg.message('actionCancelled'));}
                );
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
        }
    }); 
});
