define(["dojo/_base/declare", "dojo/dom-construct", "dijit/_WidgetBase", "Quill"], 
    function(declare, dct, _WidgetBase, Quill){
    return declare([_WidgetBase], {

        postCreate: function(){
        	this.quillParent = dct.create('div', null, this.domNode);
        	this.quill = new Quill(this.quillParent, {theme: "snow"}/*, {modules: {toolbar: [[{header: [1, 2, false]}], ['bold', 'italic', 'underline']]}}*/);
        },

        _setValueAttr: function(value){
            //this.inherited(arguments);
        	this.quill.clipboard.dangerouslyPasteHTML(value);        		
        },
        _getValueAttr: function(value){
            return this.quill.root.innerHTML;
        }

    });
});
