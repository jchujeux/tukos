define([
	'dojo/_base/declare',
	'dojo/_base/array',
    'dojo/_base/lang'
], function (declare, arrayUtil, lang) {
	return declare(null, {
        constructor: function(){
            this.idpLast = 0;
        },
        setData : function(value){
            const self = this, idp = this.idProperty;
            this.idpLast = 0;
            value.forEach(function(row){
                row[idp] = self.idpLast +=1;
            });
            this.inherited(arguments); 
        },
        addSync: function(object, options){
            this.idpLast += 1;
            object[this.idProperty] = this.idpLast;
            return this.inherited(arguments);
        }
    });
});
