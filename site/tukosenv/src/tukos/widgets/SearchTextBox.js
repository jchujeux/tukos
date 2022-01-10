define(["dojo/_base/declare", "dojo/_base/config", "dojo/dom-construct", "dijit/form/TextBox", 	"tukos/evalutils", "dojo/text!tukos/widgets/templates/SearchTextBox.html"], 
    function(declare, config, dct, TextBox, eutils, template){
    return declare([TextBox], {
		templateString: template,
        buildRendering: function(){
			console.log('toto');
			this._onClick = eutils.eval(this.searchAction, 'evt');
			this._onKeyDown = function(event){
				if (event.key === "Enter"){
					event.preventDefault();
					event.stopPropagation();
					this._onClick(event);
				}
			}
			this.inherited(arguments);
		}
    });
});
