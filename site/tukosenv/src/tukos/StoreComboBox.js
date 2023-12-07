"use strict";
define (["dojo/_base/declare", "dijit/form/ComboBox", "dojo/store/Memory", 'tukos/utils'], 
    function(declare, ComboBox, Memory, utils){
    return declare([ComboBox], {
        constructor: function(args){
            args.store = new Memory(args.storeArgs);
        },
        _setValueAttr: function _setValueAttr (value){
			/*if (this.translations){
				utils.forEach(this.translations, function(translated, untranslated){
					if (value.includes(untranslated)){
						value = value.replaceAll(untranslated, translated);
					}
				});
			}*/
			value = utils.translate(value, this.translations);
			this.inherited(_setValueAttr, arguments);
		},
        _getServerValueAttr: function(){
			let value = this.get('value');
			if (value/* && this.translations*/){
				/*utils.forEach(this.translations, function(translated, untranslated){
					if (value.includes(translated)){
						value = value.replaceAll(translated, untranslated);
					}
				});*/
				value = utils.untranslate(value, this.translations);
			}
			return value;
		}
    }); 
});
