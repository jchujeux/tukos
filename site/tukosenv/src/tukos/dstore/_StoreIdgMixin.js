define([
	'dojo/_base/declare',
	'dojo/_base/array',
    'dojo/_base/lang'
], function (declare, arrayUtil, lang) {
	return declare(null, {
        constructor: function(){
            this.idgLast = 0;
        },
        setData : function(value){
            this.idgLast = 0, self = this;
            value.forEach(function(row){
                row.idg = self.idgLast +=1;
            });
/*
            for (var row in value){
                this.idgLast += 1;
                value[row].idg = this.idgLast;
            }
*/
            this.inherited(arguments); 
        },
        addSync: function(object, options){
            this.idgLast += 1;
            object.idg = this.idgLast;
            return this.inherited(arguments);
        }
    });
});
