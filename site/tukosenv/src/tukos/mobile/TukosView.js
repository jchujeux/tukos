define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dojox/mobile/View", "dojox/mobile/Heading", "dojox/mobile/ToolBarButton", "tukos/TabOnClick", "tukos/PageManager"], 
    function(declare, lang, ready, View, Heading, ToolBarButton, TabOnClick, Pmg){
    var paneModules = {objectPane: "tukos/mobile/ObjectPane", tukosPane: "tukos/TukosPane", navigationPane: "tukos/mobile/NavigationPane"};
	return declare(View, {
    	postCreate: function (){    
    		this.inherited(arguments);
    		this.widgetType = "TukosMobileView";
    		this.createPane();
        },
        refresh: function(viewPaneContent){
        	var previousView, nextView, self = this;
        	viewPaneContent.viewPane = this;
        	if (this.form){
            	this.form.destroyRecursive();
            	previousView = this.heading.previousViewButton.get('targetView');
            	nextView = this.heading.nextViewButton.get('targetView');
            	this.heading.destroyRecursive();
            	this.actionsHeading.destroyRecursive();
            }
        	this.addChild(this.heading = new Heading({label: this.title, fixed: 'top'}));
        	if (this.paneModuleType === 'objectPane'){
            	this.addChild(this.actionsHeading = new Heading({style: {display: 'none'}}));
            	(this.actionsHeadingToggle = new ToolBarButton({icon: "mblDomButtonWhitePlus", style: "float: right", onClick: lang.hitch(this, function(){
            		var heading = this.actionsHeading, toggle = this.actionsHeadingToggle, displayStatus = heading.get('style').display;
            		if (displayStatus === 'none'){
            			heading.set('style', {display: 'block'});
            			toggle.set('icon', 'mblDomButtonWhiteMinus');
            		}else{
            			heading.set('style', {display: 'none'});
            			toggle.set('icon', 'mblDomButtonWhitePlus');
            			
            		}
            		this.resize();
            	})})).placeAt(this.heading, 'first');
        	}else if(this.paneModuleType === 'navigationPane'){
            	(this.logout = new ToolBarButton({icon: "mblDomButtonWhiteCross", style: "float: right", onClick: function(){
            		location.replace(Pmg.get('pageUrl') + 'auth/logout',"_self");
            	}})).placeAt(this.heading, 'first');
        	}
        	previousView = previousView || this.mobileViews.lastPane() || this;
        	nextView = nextView || this.mobileViews.firstPane() || this;
        	if (previousView !== this){
        		previousView.heading.nextViewButton.set('targetView', this);
        		nextView.heading.previousViewButton.set('targetView', this);
        	}
        	this.heading.previousViewButton = new ToolBarButton({icon: "mblDomButtonWhiteLeftArrow", style: "float: left", targetView: previousView, onClick: function(evt){
        		self.mobileViews.selectPane(this.targetView, -1);
        	}}).placeAt(this.heading, 'first');
        	if (this.paneModuleType !== 'navigationPane'){
            	this.heading.navigationViewButton = new ToolBarButton({icon: "mblDomButtonWhiteUpArrow", style: "float: left", onClick: lang.hitch(this, function(){
            		self.mobileViews.navigationView();
            	})}).placeAt(this.heading, 1);
        	}
        	this.heading.nextViewButton = new ToolBarButton({icon: "mblDomButtonWhiteRightArrow", style: "float: left", targetView: nextView, onClick: function(evt){
        		self.mobileViews.selectPane(this.targetView);
        	}}).placeAt(this.heading, 2);
        	require([paneModules[this.paneModuleType]], function(PaneModule){
            	self.form = new PaneModule(viewPaneContent);
				if (self.actionsHeading){
					new ToolBarButton({icon: "mblDomButtonWhiteCross", style: "float: right", onClick: function(){
	             		console.log('here is where I need to act');
	             		self.destroy();
	             	}}).placeAt(self.actionsHeading, 1);
				}
            	self.form.noLoadingIcon = true;
				ready(function(){
					self.addChild(self.form);        		
				});	
        	});
        },
        destroy: function(){
        	var self = this, _arguments = arguments, previousView = this.heading.previousViewButton.get('targetView'), nextView = this.heading.nextViewButton.get('targetView');
        	if (previousView === this){
        		this.mobileViews.navigationView();
        		previousView = this.heading.nextViewButton.get('targetView');
        		nextView = this.heading.nextViewButton.get('targetView');
        	}else{
        		this.mobileViews.selectPane(nextView);
        	}
        	nextView.heading.previousViewButton.set('targetView', previousView);
        	previousView.heading.nextViewButton.set('targetView', nextView);
        	setTimeout(function(){self.inherited(_arguments);}, 100);
        },
        createPane: function(){
            if (!this.form){
            	this.paneModuleType = this.formContent ? 'objectPane' : (this.paneContent ? 'tukosPane' : 'navigationPane');
            	this.refresh(this.formContent || this.paneContent || this.navigationContent);
            }
        },
        onShow: function(){
        	console.log('TukosView - onShow ');
        	this.createPane();
        },
        isObjectPane: function(){
        	return typeof(this.form.object) !== "undefined";
        }, 
        isAccordion: function(){
        	return false;
        },
        isTab: function(){
        	return false;
        },
        isMobileView: function(){
        	return true;
        }
    })
});
