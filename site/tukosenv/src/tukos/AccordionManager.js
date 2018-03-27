
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/ready",  "tukos/_PanesManager", "tukos/TukosAccordionPane"], 
    function(declare, lang, on, ready, _PanesManager, TukosAccordionPane){
    return declare(_PanesManager, {
        constructor: function(args){
            this.container = args.container;
            on(this.container.domNode, '.dijitAccordionTitle:mousedown', lang.hitch(this, this.mouseDownCallback));
        },
        
        create: function(args){
            var theNewAccordion = new TukosAccordionPane(args);
            theNewAccordion.accordionManager = this;
            this.container.addChild(theNewAccordion);
            return theNewAccordion;
        },
        
        gotoPane: function(target){
            this.container.selectChild(target);           	
        },
        
        currentPaneNode: function(){
        	return this.currentPane()._wrapperWidget.domNode.childNodes[0];
        }
    }); 
});
