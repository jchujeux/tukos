define (["dojo/_base/declare", "dojo/_base/lang", "dojox/mobile/View", "dojox/mobile/Heading", "dojox/mobile/ToolBarButton", "tukos/mobile/ObjectPane", "tukos/PageManager"], 
    function(declare, lang, View, Heading, ToolBarButton, ObjectPane, Pmg){
    var paneModules = {objectPane: "tukos/mobile/ObjectPane", tukosPane: "tukos/TukosPane", navigationPane: "tukos/Mobile/NavigationPane"};
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
            	previousView = this.heading.previousView.get('targetView');
            	nextView = this.heading.nextView.get('targetView');
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
        	}
        	previousView = previousView || this.mobileViews.lastPane() || this;
        	nextView = nextView || this.mobileViews.firstPane() || this;
        	if (previousView !== this){
        		previousView.nextView.set('targetView', this);
        		nextView.previousView.set('targetView', this);
        	}
        	this.previousView = new ToolBarButton({icon: "mblDomButtonWhiteLeftArrow", style: "float: left", targetView: previousView, onClick: function(evt){
        		console.log('to be done');
        		self.mobileViews.selectPane(this.targetView, -1);
        	}}).placeAt(this.heading, 'first');
        	if (this.paneModuleType !== 'navigationPane'){
            	this.navigationView = new ToolBarButton({icon: "mblDomButtonWhiteUpArrow", style: "float: left", onClick: lang.hitch(this, function(){
            		self.mobileViews.navigationView();
            	})}).placeAt(this.heading, 1);
        	}
        	this.nextView = new ToolBarButton({icon: "mblDomButtonWhiteRightArrow", style: "float: left", targetView: nextView, onClick: function(evt){
        		self.mobileViews.selectPane(this.targetView);
        	}}).placeAt(this.heading, 2);
        	require([paneModules[this.paneModuleType]], function(PaneModule){
            	self.form = new PaneModule(viewPaneContent);
            	self.form.noLoadingIcon = true;
            	//self.addChild(self.heading);
            	//self.addChild(self.actionsHeading);
            	self.addChild(self.form);        		
        	});
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
