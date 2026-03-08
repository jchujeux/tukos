define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils"], 
function(declare, lang, utils){
	return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		setValue: function(name, value){
			this[name] = value;
		},
		getValue: function(name){
			return this.value;
		},
		addValue: function(name, value){
			if (this[name]){
				if (Array.isArray(this[name])){
					this.name === this[name].push(value);
				}else{
					throw new Error("Parameter is not an array: " + name)
				}
			}else{
				this[name] = [value];
			}
		},
		updateValue: function(name, value, i, j){
			if (typeof i === "undefined"){
				this[name] = value;
			}else if(typeof j === "undefined"){
				this[name][i] = value;
			}else{
				this[name][i][j] = value;
			}
		},
		mergeValues: function(name, values){
			this[name] = utils.mergeRecursive(this[name], values);
		}
	});
});
