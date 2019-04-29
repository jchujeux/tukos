
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready",  "tukos/_PanesManager", "tukos/TukosAccordionPane"], 
    function(declare, lang, ready, _PanesManager, TukosAccordionPane){
    return declare(_PanesManager, {
        constructor: function(args){
            this.container.on('.dijitAccordionTitle:contextmenu', lang.hitch(this, this.contextMenuCallback));
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
