define (["dojo/_base/declare", "dijit/_WidgetBase", "dijit/_TemplatedMixin", 'dojo/text!./templates/KaroZans.html'], 
    function(declare, _WidgetBase, _TemplatedMixin, template){
    return declare([_WidgetBase, _TemplatedMixin], {
        _i: 0,
        //templateString: '<div><button data-dojo-attach-event="onclick: increment">press me</button><span data-dojo-attach-point="counter">0</span>',
        templateString: template,
        increment: function(){
        	this.counter.innerHTML = ++this._i;
        }
    }); 
});
