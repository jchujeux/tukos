
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom", "dojo/ready", "tukos/_PanesManager", "tukos/TukosTab",  "dijit/registry", "dijit/Dialog", "tukos/utils", "tukos/PageManager", "dojo/json"], 
    function(declare, lang, dom, ready, _PanesManager, TukosTab, registry, Dialog, utils, Pmg, JSON){
    return declare([_PanesManager], {
        constructor: function(args){
            var self = this, descriptions = this.tabsDescription, created, selected;
            var unloadAction = function(){
                var openedTabs = self.container.getChildren(), changedTabs = {widgets: [], customization: []}, tabChange, theMessage;
                for (var i in openedTabs){
                    var theTab = openedTabs[i];
                    if (theTab.form && (tabChange = theTab.form.userHasChanged())){
                        if (tabChange.widgets){
                        	changedTabs.widgets.push(theTab.get('title'));
                        }
                        if (tabChange.customization){
                        	changedTabs.customization.push(theTab.get('title'));
                        }
                    }
                }
                if (!utils.empty(changedTabs.widgets)){
                	theMessage = Pmg.message('tabsvalueschangednotsaved') + ': <br>' + changedTabs.widgets.join(', ');
                }
                if (!utils.empty(changedTabs.customization)){
                	theMessage = (theMessage ? theMessage + '<br>' : '') + Pmg.message('tabscustomizationchangednotsaved') + ': <br>' + changedTabs.customization.join(', ');
                }
                if (theMessage){
                	var theDialog = new Dialog({title: Pmg.message('Unsaved changes'), content: theMessage});
                	theDialog.show();
                	return theMessage;
                }
            }
            window.onbeforeunload = unloadAction;
            this.container.on(".dijitTab:contextmenu", lang.hitch(this, this.contextMenuCallback));
        	for (var i in descriptions){
        		created = this.create(descriptions[i]);
        		if (descriptions[i].selected){
        			selected = created;
        		}
        	}
            ready(lang.hitch(this, function(){
                this.container.watch("selectedChildWidget", function(name, oldTab, newTab){
                    var form = newTab.form;
                    if (form){
                        form.setUserContextPaths();
                    }
                });
                if (selected){
                	self.container.selectChild(selected);
                }
            }));
        },
        create: function(args){
            var theNewTab = new TukosTab(args);
            ready(lang.hitch(this, function(){
                this.container.addChild(theNewTab);
                Pmg.setFeedback(args.feedback, Pmg.message('tabCreated'));
                theNewTab.resize();
            }));
            return theNewTab;
        },
        
        objectPane: function(objectName, viewMode){
            var openedTabs = this.container.getChildren(), objectPane;
            openedTabs.some(function(tab){
            	var form = tab.form;
            	if (form.object === objectName && form.viewMode === viewMode){
            		objectPane = form;
            		return true;
            	}
            });
            return objectPane;
        },

        request: function(urlArgs){
            var self = this, tukosHeaderLoading = dom.byId('tukosHeaderLoading');            
    		tukosHeaderLoading.innerHTML = Pmg.loading('', true);
            return Pmg.serverDialog(urlArgs, {}, false).then(
                function(response){
                    var theNewTab = self.create(response);
                    if (response.focusOnOpen){
                        ready(function(){
                            self.container.selectChild(theNewTab);
                            Pmg.setFeedback(response['feedback'], Pmg.message('Ok'));
                            tukosHeaderLoading.innerHTML = '';
                        });
                    }
                    return response;
                }
            );
        },
        gotoTab: function(target){
            if (target.table){// here due to 'table' hard-coded in editor text with links for text prior to modification request 34230
                target.object = target.table;
            }
            if (target.id){
            	target.query = lang.mixin({id: target.id}, target.query ||{});// legacy - changed to target on 2017-06-16 & need to support previous syntax in tukos/editor/plugins/TukosLinkDialog
            }
			if ((target.query || {}).id){
				var openedTabs = this.container.getChildren(), id = target.query.id, name = target.query.name;
	            for (var i in openedTabs){
	                var tab = openedTabs[i];
	                if ((id && tab.contentId == id) || (name && tab.contentName === name) || (id && (tab.get('title').match(/(\d+)\)$/) || {} )[1] === id)){
	                    this.container.selectChild(tab);
	                    return;
	                }
	            }
			}
            target.action = target.action || 'Tab';
            this.request(target);
        }, 
        setCurrentTabTitle: function(newTitle){ 
            this.container.selectedChildWidget.set('title', newTitle);
        },

        currentPaneNode: function(){
        	return this.currentPane().controlButton.containerNode;
        }
    }); 
});
