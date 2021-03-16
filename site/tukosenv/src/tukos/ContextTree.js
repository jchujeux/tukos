/*
 *  Provides a Tree widget field which data store is args.storeData, callable from ObjectPane.js
 *   
 */
define (["dojo/_base/declare", "tukos/PageManager", "tukos/StoreTree", "dojo/i18n!tukos/nls/messages"], 
    function(declare, Pmg, StoreTree, messages){
    return declare([StoreTree], {
        onClick: function(){
            Pmg.tabs.currentPane().form.set('contextPaths', this.get('paths'));
            Pmg.setFeedback(messages.contexttabset);
        }
    }); 
});
