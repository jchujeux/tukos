define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready",  "dojo/on", "dojo/mouse", "tukos/TukosTab",  "dijit/registry", "dijit/Dialog", "dijit/Menu", "dijit/MenuItem", 
         "dijit/PopupMenuItem", "tukos/utils", "tukos/PageManager", "tukos/_ViewCustomMixin", "dojo/json", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, ready, on, mouse, TukosTab, registry, Dialog, Menu, MenuItem, PopupMenuItem, utils, Pmg, _ViewCustomMixin, JSON, messages ){
    return declare([ _ViewCustomMixin], {

    	constructor: function(args){
    		this.container = args.container;
    	},
    	
    	mouseDownCallback: function(evt){
            var self = this, pane = this.currentPane();;
            if (mouse.isRight(evt) && (evt.target.checked || (evt.target.checked !== false && pane.isObjectPane() && pane.title === registry.getEnclosingWidget(evt.target).label))){
                if (this.contextMenu){
                    this.contextMenu.destroyRecursive();
                }
                this.contextMenu = new Menu({targetNodeIds:[evt.target]});
                this.contextMenu.addChild(new MenuItem({label: messages.refresh, onClick: function(evt){self.refresh('tab', []);}}));
                this.contextMenu.addChild(new MenuItem({label: messages.customization, onClick: lang.hitch(this, this.openCustomDialog)}));
            }
        },

        refresh: function(action, data, keepOptions, currentPane){
            var currentPane = currentPane || this.currentPane(), theForm = currentPane.form, theFormContent = currentPane.formContent, changesToRestore = (keepOptions ? theForm.keepChanges(keepOptions) : {});
    		var title = currentPane.get('title');
            currentPane.set('title', Pmg.loading(title, true));
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
	            	if (theFormContent.viewMode === 'edit'){
	                    var id = lang.hitch(theForm, theForm.valueOf)('id');
	                    if (id){
	                    	query.id = id;
	                    }
	                }
                }
                if (theFormContent.viewMode === 'overview' && currentPane.isAccordion()){
                	query.title = title;
                }
            	return Pmg.serverDialog({object: theFormContent.object, view: theFormContent.viewMode, mode: theFormContent.paneMode, action: action, query: query}, {data: data}, false).then(
                    function(response){
                        currentPane.refresh(response.formContent);
                        ready(function(){
                            (currentPane.form || currentPane).restoreChanges(changesToRestore, keepOptions);
                            Pmg.setFeedback(response['feedback'], messages.tabRefreshed);
                            currentPane.resize();
                            currentPane.set('title', response.title || title);
                        });
                        return response;
                    },
                    function(error){
                    	currentPane.set('title', title);
                    }
                );
            }
            if (keepOptions || !theForm){
                return refreshAction();
            }else{
                return theForm.checkChangesDialog(lang.hitch(this, refreshAction));
            }
        },

        currentPane: function(){
            return this.container.selectedChildWidget;
        }
    }); 
});
