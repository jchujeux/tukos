define (["dojo/_base/declare", "dojo/_base/lang", "dojox/mobile/View", "dojox/mobile/Heading", "dojox/mobile/ToolBarButton", "tukos/mobile/ObjectPane", "tukos/PageManager"], 
    function(declare, lang, View, Heading, ToolBarButton, ObjectPane, Pmg){
    return declare(View, {
    	postCreate: function (){    
            var formOrPaneContent = this.formContent || this.paneContent;
    		this.inherited(arguments);
    		this.widgetType = "TukosMobileView";
    		//formOrPaneContent.viewPane = this;
    		this.createPane(formOrPaneContent);
        },
        refresh: function(formOrPaneContent){
        	var previousView, nextView, self = this;
        	formOrPaneContent.viewPane = this;
        	if (this.form){
            	this.form.destroyRecursive();
            	previousView = this.heading.previousView.get('targetView');
            	nextView = this.heading.nextView.get('targetView');
            	this.heading.destroyRecursive();
            	this.actionsHeading.destroyRecursive();
            }
        	this.heading = new Heading({label: this.title, fixed: 'top'});
        	this.actionsHeading = new Heading({style: {display: 'none'}});
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
        	this.navigationView = new ToolBarButton({icon: "mblDomButtonWhiteUpArrow", style: "float: left", onClick: lang.hitch(this, function(){
        		console.log('to be done');
        		self.mobileViews.navigationView();
        	})}).placeAt(this.heading, 1);
        	this.nextView = new ToolBarButton({icon: "mblDomButtonWhiteRightArrow", style: "float: left", targetView: previousView, onClick: function(evt){
        		console.log('to be done');
        		self.mobileViews.selectPane(this.targetView);
        	}}).placeAt(this.heading, 2);
        	this.form = this.formContent ? new ObjectPane(formOrPaneContent) : new TukosPane(formOrPaneContent);
        	this.form.noLoadingIcon = true;
        	this.addChild(this.heading);
        	this.addChild(this.actionsHeading);
        	this.addChild(this.form);
        },
        createPane: function(){
            if (!this.form){
            	this.refresh(this.formContent || this.paneContent);
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
