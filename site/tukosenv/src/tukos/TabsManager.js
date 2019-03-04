
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom", "dojo/ready",  "dojo/on", "dojo/mouse", "tukos/_PanesManager", "tukos/TukosTab",  "dijit/registry", "dijit/Dialog", "dijit/Menu", "dijit/MenuItem", 
         "dijit/PopupMenuItem", "tukos/utils", "tukos/PageManager", "dojo/json"], 
    function(declare, lang, dom, ready, on, mouse, _PanesManager, TukosTab, registry, Dialog, Menu, MenuItem, PopupMenuItem, utils, Pmg, JSON){
    return declare([_PanesManager], {
        constructor: function(args){
            this.container = args.container;
            var self = this;
            var unloadAction = function(){
                var openedTabs = self.container.getChildren(),
                    changedTabs = [];
                for (var i in openedTabs){
                    var theTab = openedTabs[i];
                    if (theTab.form && theTab.form.hasChanged()){
                        changedTabs.push(theTab.get('title'));
                    }
                }
                if (!utils.empty(changedTabs)){
                    var theMessage = 'The following tabs have changed and were not saved: \n' + changedTabs.join(', ');
                    var theDialog = new Dialog({title: 'Unsaved changes', content: theMessage});
                    theDialog.show();
                    return theMessage;
                }                
            }
            window.onbeforeunload = unloadAction;
            on(this.container.tablist.domNode, '.tabLabel:mousedown', lang.hitch(this, self.mouseDownCallback));
            ready(lang.hitch(this, function(){
                this.container.watch("selectedChildWidget", function(name, oldTab, newTab){
                    var form = newTab.form;
                    if (form){
                        form.setUserContextPaths();
                    }
                });
            }));
        },

        create: function(args){
            var theNewTab = new TukosTab(args), form = theNewTab.form;
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
            	target.query = lang.mixin({id: target.id}, target.query ||{});// legacy - changed to target on 2017-06-16 & need to support previous syntax in tukos/editor/plugins/TuksoLinkDialog
            }
            var openedTabs = this.container.getChildren();
            for (var i in openedTabs){
                if ((openedTabs[i].get('title').match(/(\d+)\)$/) || {} )[1] === target.query.id){
                    this.container.selectChild(openedTabs[i]);
                    return;
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
