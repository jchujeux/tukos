define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dijit/_WidgetBase"], 
    function(declare, lang, dct, _WidgetBase){
    return declare(_WidgetBase, {
        _i: 0,
        increment: function(){
        	this.counter.innerHTML = ++this._i;
        },
    	postCreate: function(){
            this.inherited(arguments);
            this.button = dct.create('button', {innerHTML: 'press me', onclick: lang.hitch(this, this.increment)}, this.domNode);
            this.counter = dct.create('span', {innerHTML: 0}, this.domNode);
        }
    }); 
});
