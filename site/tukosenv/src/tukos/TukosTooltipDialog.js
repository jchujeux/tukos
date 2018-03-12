define (["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dijit/TooltipDialog", "dijit/popup",  "dijit/focus", "tukos/TukosPane"], 
    function(declare, lang, when, TooltipDialog, popup, focus, TukosPane){
    return declare(TooltipDialog, {
         postCreate: function(){
            var self = this;
            this.pane = new TukosPane(this.paneDescription);
            this.pane.scrollOnFocus = false;
            this.pane.close = this.close = function(){
                popup.close(self)
            };
            this.set('content', this.pane);
        },
         open: function(kwArgs){
            return this.pane.onInstantiated(lang.hitch(this, function(){
                kwArgs.popup = this;
                popup.open(kwArgs);
                focus.focus(this.domNode);
                return this.pane.openAction(this.paneDescription.onOpenAction);
            }));
        }
    });
});
