/*
 * Extension of dstore Memory to support querying on idCol objects, which are of the form: {id: idValue, name: nameValue, object: objectValue}
 */
define([
	'dojo/_base/declare',
	'dojo/_base/array',
    'dojo/_base/lang'
], function (declare, arrayUtil, lang) {
	return declare(null, {
        comparators: {
            ni: function(colValue, values){
            	if (colValue){
                    return (values ? arrayUtil.indexOf(values, colValue) === -1 : false);
                }else{
                    return true;
                }
	},
            eqColId: function(colId, value){
                return colId.id === value || colId === value;
            },
            neColId: function(colId, value){
                return colId.id !== value && colId !== value;
            },
            inColId: function(colId, value){
			    return arrayUtil.indexOf(value, colId.id) > -1 || arrayUtil.indexOf(value, colId) > -1;
		    },
            niColId: function(colId, value){
			    if (colId){
                    return (value ? arrayUtil.indexOf(value, colId.id) === -1 : false) || (value ? arrayUtil.indexOf(value, colId) === -1 : false);
                }else{
                    return true;
                }
	}
        },
        constructor: function(){
            lang.extend(this.Filter, {ni: this.Filter.filterCreator('ni'), eqColId: this.Filter.filterCreator('eqColId'), neColId: this.Filter.filterCreator('neColId'), 
                                      inColId: this.Filter.filterCreator('inColId'), niColId: this.Filter.filterCreator('niColId')}
            );
        },
        
        _getFilterComparator: function(type){
            return this.comparators[type] || this.inherited(arguments);
        }
    });
});
