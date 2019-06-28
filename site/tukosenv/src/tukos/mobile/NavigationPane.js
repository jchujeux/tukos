define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/when",  "dojo/aspect", "dojo/ready", "dijit/popup", "dijit/focus", "dojox/mobile/Accordion", "dojox/mobile/ContentPane", "dojox/mobile/Pane", "tukos/_NavigationMenuMixin",
		 "tukos/utils", "tukos/menuUtils", "tukos/PageManager"], 
    function(declare, lang, on, when, aspect, ready, popup, focusUtil, Accordion, ContentPane, Pane, _NavigationMenuMixin, utils, mutils, Pmg){
    var iconBase = require.toUrl("dojox/mobile/tests/images/icons16.png"), domainIcon = "16,48,16,16", newIcon = "0,16,16,16", editIcon = "0,0,16,16", menuItemIcon = "0,32,16,16", objectSelectIcon = "0,0,16,16";
	return declare([Accordion, _NavigationMenuMixin], {
        constructor: function(args){
        	args = lang.mixin(args, {iconBase: iconBase, "class":"mblAccordionRoundRect"});
        },
		postCreate: function(){
            var items = this.items, item, pane;
        	this.inherited(arguments);
            for (var domain in items){
            	item = items[domain];
            	this.addChild(pane = new ContentPane(lang.mixin({iconPos1: domainIcon}, item.atts)));
            	this.fillPane(pane, item.popup);
            }
        	aspect.after(this, "expand", lang.hitch(this, this.setAutoHeight));
        },
        fillPane: function(parent, popupDescription){
        	var items = popupDescription.items, accordion, self = this;
        	parent.addChild(accordion = new Accordion({iconBase: iconBase, "class": "mblAccordionRoundRect", onClick: parent.resize()}));
        	aspect.after(accordion, "expand", lang.hitch(this, this.setAutoHeight));
        	utils.forEach(items, function(item){
        		var pane;
        		if (item.type === "MenuItem"){
        			parent.startup();
        			accordion.addChild(pane = new Pane(lang.mixin({iconPos1: menuItemIcon}, item.atts)));
        			pane._at.onClickArgs = item.atts.onClickArgs;
        			self.addTriggers(pane._at);
        		}else if(item.popup.type === 'DropDownMenu'){
        			accordion.addChild(pane = new ContentPane({iconPos1: domainIcon, label: item.atts.label}));
        			self.fillPane(pane, item.popup);
        		}else{
        			parent.startup();
        			accordion.addChild(pane = new Pane(lang.mixin({iconPos1: newIcon}, item.atts)));
                    when(mutils.buildMenu(item.popup, 'full', null, lang.hitch(self, self.addTriggers)), function(dropDown){
                        dropDown.on('blur', function(){popup.close(dropDown);});
                    	pane._at.on('click', function(evt){
                        	popup.open({popup: dropDown, around: pane._at.domNode});
                			focusUtil.focus(dropDown.domNode);            				
            			});
                    });
        		}
        		
        	});
        },
        setAutoHeight: function(){
    		var topAccordion = this;
    		ready(function(){
        		var nodes = Array.apply(null, topAccordion.domNode.getElementsByClassName("mblAccordionPane"));
        		nodes.forEach(function(node){
        			node.style.height = "";
        		});
    		});
    	}
    }); 
});
