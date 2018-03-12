/*
 *  Provides a a common access to Stores for all UI modules,  avoiding multiple stores for the same store  url
 *  Limitation: does not take into account the query part, which may specify different columns to return => limit usage to ObjectSelect which
 *  returns the same columns, or plan to generalize this component (or from the server side return all columns systematically ?)
 */
define (["dojo/_base/declare", "dojo/string", "dojo/store/Memory", "dojo/store/Cache", "dojo/store/Observable", "tukos/PageManager", "tukos/store/ActionRequest", "tukos/store/ObjectSelect", "dojo/json"], 
    function(declare, string, Memory, Cache, Observable, Pmg, ActionRequestStore, ObjectSelectStore, JSON){
    return declare(null, {
        constructor: function(args){
            this.stores = new Object();
            this.i = 0;
        },

        get: function (args){
            var action = args.action;
        	if (action || args.target){
                var theStore = args.target = (args.target || Pmg.requestUrl(args));
                if (action === 'objectselect'){
                	if (theStore in this.stores){
                        return this.stores[theStore].myStore;
                    }
                }else{
                    theStore = theStore + this.i;
                    this.i += 1;               	
                }
                args.sortParam = args.sortParam || Pmg.getItem('sortParam');
                this.stores[theStore] = {myStore: new Observable(new (action === 'objectselect' ? ObjectSelectStore : ActionRequestStore)(args))};
                return this.stores[theStore].myStore;
            }else{
                var theStore = 'target' + this.i;
                this.stores[theStore] = new Object();
                this.stores[theStore].myStore = new Observable(new Memory(args));
                this.i +=1;
                return this.stores[theStore].myStore;
            }
        } 
    });
});
