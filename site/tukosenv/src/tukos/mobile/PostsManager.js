define (["dojo/_base/declare", "dojo/_base/lang", "tukos/mobile/TukosView", "tukos/PageManager"], 
    function(declare, lang, TukosView, Pmg){
    return declare(null, {
        constructor: function(args){
    		declare.safeMixin(this, args);
        	var created, selected, descriptions = this.viewsDescription;
        	this.container.selectChild = function(target, transitionDir, transition){
        		if (this.selectedChildWidget !== target){
            		if (transitionDir !== false){
            			this.selectedChildWidget.performTransition(target.id, transitionDir, transition || "slide");
					}
            		this.selectedChildWidget = target;
        		}else{
        			Pmg.beep();
        		}
				target.set('style', {height: 'auto'});
				Pmg.tabs.pageWidget.bottomContainer.set('style', {display: target.form.hideBottomContainer ? 'none' : 'block'});
        	}
			if (descriptions.length === 0){
				//this.navigationView();
			}else{
				for (var i in descriptions){
	        		created = this.create(descriptions[i]);
	        		if (descriptions[i].selected){
	        			selected = created;
	        		}
	        		this.container.selectedChildWidget = selected ? selected : this.container.getChildren()[0];
	        	}
			}
        },
        create: function(args){
            var theNewTab = new TukosView(lang.mixin(args, {mobileViews: this}));
        	this.container.addChild(theNewTab);
        	return theNewTab;
        },
        request: function(urlArgs){
            var self = this, container = this.container;
        	return Pmg.serverDialog(urlArgs, {}, false).then(
                function(response){
                    var theNewView = self.create(response);
                    //if (response.focusOnOpen){
                    	container.selectChild(theNewView);
                    //}
					//dojo.ready(function(){
						container.previousButton.set('style', {display: Pmg.mobileViews.isFirstPane() ? 'none' : 'block'});
						container.nextButton.set('style', {display: Pmg.mobileViews.isLastPane() ? 'none' : 'block'});
	                    window.scrollTo(0,0);;
					//});
                    return response;
                }
            );
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
		selectPreviousPane: function(){
			var self = this, panes = this.container.getChildren(), currentPane = this.currentPane();
			if (currentPane !== panes[0]){
				panes.forEach(function(pane, i){
					if (pane === currentPane){
						self.selectPane(panes[i-1], -1);
					}
				});
			}
		},
		selectNextPane: function(){
			var self = this, panes = this.container.getChildren(), currentPane = this.currentPane();
			if (currentPane !== panes.slice(-1)[0]){
				panes.forEach(function(pane, i){
					if (pane === currentPane){
						self.selectPane(panes[i+1], 1);
					}
				});
			}
		},
		isFirstPane: function(){
			return this.currentPane() === this.firstPane();
		},
		isLastPane: function(){
			return this.currentPane() === this.lastPane();
		},
        currentPaneNode: function(){
        	return this.currentPane().heading.domNode;
        },
        gotoTab: function(target, silent){
            var id, name, storeAtts;
			if (target.view === "Overview" || (id = (target.query || {}).id) || (name = (target.query || {}).name) || (storeAtts = (target.query || {}).storeatts)){
				var openedTabs = this.container.getChildren();
	            if (!name && typeof storeAtts === 'string'){
					name = ((JSON.parse(storeAtts).where || {}).name || [])[1];
				}
				for (var i in openedTabs){
	                var tab = openedTabs[i];
					if ((target.view === "Overview" && (tab.formContent || {}).viewMode === "Overview" && tab.formContent.object === target.object) || (id && tab.contentId == id) || (name && tab.contentName === name) || (id && (tab.get('title').match(/(\d+)\)$/) || {} )[1] === id)){
	                    if (!silent || tab !== this.container.selectedChildWidget){
							this.container.selectChild(tab);
						}
	                    //window.scrollTo(0,0);
	                    return;
	                }
	            }
			}
            target.action = target.action || 'Tab';
            return this.request(target);
        }
    }); 
});
