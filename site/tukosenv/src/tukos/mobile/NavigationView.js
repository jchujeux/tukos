define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojox/mobile/View", "dojox/mobile/Heading", "dojox/mobile/Accordion", "dojox/mobile/ContentPane", "dojox/mobile/Pane", "tukos/PageManager"], 
    function(declare, lang, on, View, Heading, Accordion, ContentPane, Pane, Pmg){
    return declare([View], {
        postCreate: function(){
            var items = this.items, item, pane;
        	this.inherited(arguments);
            this.addChild (new Heading({label: Pmg.message('NavigationView'), fixed: 'top'}));
            this.addChild(this.accordion = new Accordion());
            for (var domain in items){
            	item = items[domain];
            	this.accordion.addChild(pane = new ContentPane({label: item.atts.label}));
            	this.fillPane(pane, item.popup);
            }
        },
        fillPane: function(parent, popup){
        	var items = popup.items, item, accordion, pane;
        	parent.addChild(accordion = new Accordion());
        	for (var index in items){
        		item = items[index];
        		if (item.type === "MenuItem"){
        			accordion.addChild(new ContentPane({label: item.atts.label}));
        		}else if (item.type === "ObjectSelect"){
        			accordion.addChild(new ContentPane({label: 'is an objectselect'}));
        		}else{
        			accordion.addChild(pane = new ContentPane({label: item.atts.label}));
        			this.fillPane(pane, item.popup);
        		}
        	}
        }
    }); 
});
