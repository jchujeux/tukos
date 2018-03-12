
/*
 * Tukos tabs manager
 */
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/ready",  "tukos/_PanesManager", "tukos/TukosAccordion"], 
    function(declare, lang, on, ready, _PanesManager, TukosAccordion){
    return declare(_PanesManager, {
        constructor: function(args){
            this.container = args.container;
            on(this.container.domNode, '.dijitAccordionTitle:mousedown', lang.hitch(this, this.mouseDownCallback));
        },
        
        create: function(args){
            var theNewAccordion = new TukosAccordion(args);
            theNewAccordion.accordionsManager = this;
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
