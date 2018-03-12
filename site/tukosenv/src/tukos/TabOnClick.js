/*
    Behavior widget that sets the onclick event of the target dom node to open a new tab with action as specified in target.url
 */
define (["dojo/_base/declare", "dojo/on", "tukos/PageManager", "dijit/_WidgetBase"], 
    function(declare, on, PageManager, Widget){
    return declare(Widget, {
        postCreate: function(){
            this.inherited(arguments);    
            var url = this.url;
            on(this.domNode, "click", function(evt){
                PageManager.tabs.request(url);
            });
        }    
    }); 
});
