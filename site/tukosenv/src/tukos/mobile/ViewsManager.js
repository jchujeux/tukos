define (["dojo/_base/declare", "dojo/_base/lang", "tukos/_PanesManager", "tukos/mobile/TukosView", "tukos/PageManager"], 
    function(declare, lang, _PanesManager, TukosView, Pmg){
    return declare(_PanesManager, {
        constructor: function(args){
        	var self = this, created, selected, descriptions = this.viewsDescription;
        	this.container.on(".mblView:contextmenu", lang.hitch(this, this.contextMenuCallback));
        	this.container.selectChild = function(target, transitionDir, transition){
        		if (this.selectedChildWidget !== target){
            		this.selectedChildWidget.performTransition(target.id, transitionDir || 1, transition || "slide");
            		this.selectedChildWidget = target;        			
        		}else{
        			Pmg.beep();
        		}
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
        	this.selectPane(this._navigationView || (this._navigationView = this.create({title: Pmg.message('NavigationView'), navigationContent: Pmg.get('menuBarDescription')})), -1, 'slidev');
        }
    }); 
});
