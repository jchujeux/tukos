define (["dojo/_base/declare", "dojo/_base/lang", "dijit/TooltipDialog", "dijit/popup",  "dijit/focus", "tukos/TukosPane"], 
    function(declare, lang, TooltipDialog, popup, focus, TukosPane){
    return declare(TooltipDialog, {
         postCreate: function(){
            var self = this;
            this.pane = new TukosPane(this.paneDescription);
            this.pane.scrollOnFocus = false;
            this.pane.close = lang.hitch(this, this.close);
            this.set('content', this.pane);
        },
         open: function(kwArgs){
            return this.pane.onInstantiated(lang.hitch(this, function(){
                kwArgs.popup = this;
                popup.open(kwArgs);
                focus.focus(this.domNode);
                return this.onOpenAction();
            }));
        },
        onOpenAction: function(){
        	return this.pane.openAction(this.paneDescription.onOpenAction);
        },
        close: function(){
        	popup.close(this);
        },
        onBlur: function(){
        	if (this.closeOnBlur){
        		popup.close(this);
        	}
        }
    });
});
