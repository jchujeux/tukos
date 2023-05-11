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
    		if (!Pmg.isRestrictedUser()){
    			mutils.buildContextMenu(this.container, {type: 'DynamicMenu', atts: {targetNodeIds: [this.container.domNode]}, items: []});
			}
    	},
    	contextMenuCallback: function(evt){
			evt.preventDefault();            
			var self = this, pane = this.currentPane();
            if (evt.target.checked || (evt.target.checked !== false && pane.isObjectPane() && pane.title === registry.getEnclosingWidget(evt.target).label)){
                mutils.setContextMenuItems(this.container, [
					{atts: {label: Pmg.message('refresh'), onClick: function(evt){self.refresh('Tab', []);}}}, 
					{atts: {label: Pmg.message('customization'), onClick: lang.hitch(this, this.openCustomDialog), tukosTooltip:{label: '', onClickLink: {label: Pmg.message('help'), name: 'customization' + pane.formContent.viewMode + 'TukosTooltip', object: 'tukoslib'}}}}]);
            }else{
                mutils.setContextMenuItems(this.container, []);
            }
        },
        refresh: function(action, data, keepOptions, currentPane){
            var currentPane = currentPane || this.currentPane(), theForm = currentPane.form, theFormContent = currentPane.formContent, changesToRestore = (keepOptions ? theForm.keepChanges(keepOptions) : null);
            var refreshAction = function(){
                var query = {};
                if (!theForm){
                	//var paneId = currentPane.id, panesConfig = Pmg.getCustom('panesConfig'), paneConfig;
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
						currentPane.serverFormContent = lang.clone(response.formContent);
						if (changesToRestore && changesToRestore.customization){
							response.formContent = utils.mergeRecursive(response.formContent, changesToRestore.customization);
						}
						currentPane.refresh(response.formContent);
	                    ready(function(){
	                        currentPane.resize();
							if (changesToRestore){
								utils.waitUntil(
									function(){
										return currentPane.form && currentPane.form.markIfChanged;
									}, 
									function(){
										(currentPane.form || currentPane).restoreChanges(changesToRestore, keepOptions);
	                            		Pmg.setFeedback(response['feedback'], Pmg.message('refreshed'));
	                            		//currentPane.resize();
									}, 
									100);
	                            //setTimeout(function(){(currentPane.form || currentPane).restoreChanges(changesToRestore, keepOptions);}, 1000);// due to similar setTimeout in ObjectPane affecting markIfChanged
							}else{
	                        	Pmg.setFeedback(response['feedback'], Pmg.message('refreshed'));
							}
	                    });
                        return response;
                    }
                );
            }
            if (keepOptions || !theForm){
                return refreshAction();
            }else{
                return theForm.checkChangesDialog(refreshAction);
            }
        },
        localRefresh: function(keepOptions, currentPane){
            var currentPane = currentPane || this.currentPane(), theForm = currentPane.form, theFormContent = lang.clone(currentPane.serverFormContent), changesToRestore = (keepOptions ? theForm.keepChanges(keepOptions) : null);
			if (currentPane.inLocalRefresh){
				Pmg.addFeedback(Pmg.message('actionnotcompletedwait'));
				return false;
			}
			currentPane.inLocalRefresh = true;
			if (changesToRestore && changesToRestore.customization){
				theFormContent = utils.mergeRecursive(theFormContent, changesToRestore.customization);
			}
			currentPane.refresh(theFormContent);
            ready(function(){
            	const title = currentPane.get('title');
				currentPane.resize();
            	currentPane.set('title', Pmg.loading(title));
				Pmg.setFeedback(Pmg.message('refreshing'));
				if (changesToRestore.widgets){
					utils.waitUntil(
						function(){
							return currentPane.form && currentPane.form.markIfChanged;
						}, 
						function(){
							//currentPane.resize();
							(currentPane.form || currentPane).restoreChanges(changesToRestore, keepOptions);
                			currentPane.set('title', title);
							currentPane.inLocalRefresh = false;
							ready(function(){
								currentPane.resize();
                				ready(function(){
									Pmg.setFeedback(Pmg.message('refreshed'));
								});
							});
						}, 
						100);
				}else{
					setTimeout(function(){
                		currentPane.set('title', title);
						currentPane.inLocalRefresh = false;
                		Pmg.setFeedback(Pmg.message('refreshed'));
					}, 0);
				}
            });
            return true;
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
