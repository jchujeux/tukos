
define (["dojo/_base/declare", "dojo/_base/lang", "tukos/_PanesManager", "tukos/mobile/TukosView", "tukos/PageManager"], 
    function(declare, lang, _PanesManager, TukosView, Pmg){
    return declare(_PanesManager, {
        constructor: function(args){
        	var self = this, created, selected, descriptions = this.viewsDescription;
        	this.container.on(".mblView:contextmenu", lang.hitch(this, this.contextMenuCallback));
        	this.container.selectChild = function(target, transition){
        		self.container.selectedChildWidget.performTransition(target.id, transition || 1, "slide");
        		self.container.selectedChildWidget = target;
        		
        	}
        	for (var i in descriptions){
        		created = this.create(descriptions[i]);
        		if (descriptions[i].selected){
        			selected = created;
        		}
        		this.container.selectedChildWidget = selected ? selected : this.container.getChildren()[0];
        	}
        },
        create: function(args){
            var theNewTab = new TukosView(lang.mixin(args, {mobileViews: this}));
        	this.container.addChild(theNewTab);
        	return theNewTab;
        },
        request: function(urlArgs){
            var self = this;
        	return Pmg.serverDialog(urlArgs, {}, false).then(
                function(response){
                    var theNewView = self.create(response);
                    if (response.focusOnOpen){
                    	self.container.selectChild(theNewView);
                    }
                    return response;
                }
            );
        },
        gotoTab: function(target){
            var openedViews = this.container.getChildren();
            for (var i in openedViews){
                if ((openedViews[i].get('title').match(/(\d+)\)$/) || {} )[1] === target.query.id){
                    this.container.selectChild(openedViews[i]);
                    return;
                }
            }
            target.action = target.action || 'Tab';
            this.request(target);
        }, 
        currentPaneNode: function(){
        	return this.currentPane().heading.domNode;
        },
        navigationView: function(){
        	if (this._navigationView){
        		this.container.selectChild(this._navigationMenu);
        	}else{
        		var self = this;
        		require(["tukos/mobile/NavigationView"], function(NavigationView){
        			self.container.addChild(self._navigationView = new NavigationView(Pmg.getItem('menuBarDescription')));
        			self.container.selectChild(self._navigationView);
        		});
        	}
        }
    }); 
});
