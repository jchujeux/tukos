/*
 *  Provides a Tree widget field which data store is args.storeData, callable from ObjectPane.js
 *   
 */
define (["dojo/_base/declare", "tukos/PageManager", "tukos/StoreTree", "dojo/json", "dojo/i18n!tukos/nls/messages"], 
    function(declare, Pmg, StoreTree, JSON, messages){
    return declare([StoreTree], {
        onClick: function(item){
            Pmg.tabs.currentPane().form.set('contextPaths', this.get('paths'));
            Pmg.setFeedback(messages.contexttabset);
        }
    }); 
});
