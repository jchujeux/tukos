/*
 * mixin patterned after native dstore/tree, to support the tukos parent - child model, in lazy loading lode for children. 
 * - The server must set the property 'hasChildren' for each row (true if the row may have children)
 */
define([
	'dojo/_base/declare',
	'tukos/dstore/TreeObjects',
    'tukos/PageManager'
	/*=====, 'dstore/Store'=====*/
], function (declare, TreeObjects, Pmg /*=====, Store=====*/) {
    return declare(TreeObjects, {

    	constructor: function () {
            this.root = this;
        },
        getCollectionFilter: function(){
            if (!this.collectionFilter){
                return this.collectionFilter = this.defaultCollectionFilter = new this.Filter();
            }else{
                return this.collectionFilter;
            }
        },

        mayHaveChildren: function (object) {
            return 'hasChildren' in object ? object.hasChildren : false;
        },

        getRootCollection: function () {
            var self = this;
            var rootCollection = this.root;
            var ids = [];
            this.root.forEach(function(object){
                ids.push(object.id);
            });
            return this.root.filter(this.getCollectionFilter().ni('parentid', ids));
        },

        getChildren: function (object, options) {
            var self    = this;
            Pmg.serverDialog(this.childrenUrlArgs(object.id)).then(
                function(response){
                    //arrayUtil.forEach(response.values, function(item){
                    response.items.forEach(function(item){
                        self.addSync(item);
                    });
                    //return self.root.filter(self.getCollectionFilter().eq('parentid', object.id));
/*
                    var filter = new self.Filter();
                    var childrenFilter = filter.eq('parentid', object.id);
                    return self.root.filter(childrenFilter);
*/
                }
            );
            return self.root.filter(self.getCollectionFilter().eq('parentid', object.id));
        }
    });
});
