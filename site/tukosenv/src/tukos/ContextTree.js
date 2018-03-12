/*
 *  Provides a Tree widget field which data store is args.storeData, callable from ObjectPane.js
 *   
 */
define (["dojo/_base/declare", "tukos/PageManager", "tukos/StoreTree", "tukos/_WidgetsMixin", "dojo/json", "dojo/i18n!tukos/nls/messages"/*,  "dojo/domReady!"*/], 
    function(declare, Pmg, StoreTree, _TukosWidgetsMixin, JSON, messages){
    return declare([StoreTree, _TukosWidgetsMixin], {
        onClick: function(item){
            Pmg.tabs.currentPane().form.set('contextPaths', this.get('paths'));
            Pmg.setFeedback(messages.contexttabset);
        }
    }); 
});
