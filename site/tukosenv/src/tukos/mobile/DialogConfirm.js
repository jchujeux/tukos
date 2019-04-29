define(["dojo/_base/declare", "dojo/_base/window", "dojo/_base/Deferred", "dojo/dom-construct", "dojox/mobile/SimpleDialog", "dojox/mobile/Button", "tukos/PageManager"], 
    function(declare, win, Deferred, dct, SimpleDialog, Button, Pmg){
    return declare([SimpleDialog], {
        postCreate: function(){
        	var self = this;
        	this.inherited(arguments);
        	this.title = dct.create('div', {'class': "mblSimpleDialogTitle", style: {textAlign: "center"}}, this.domNode);
        	this.content = dct.create('div', {'class': "mblSimpleDialogText", style: {textAlign: "center"}}, this.domNode);
        	this.domNode.appendChild((this.okButton = new Button({'class': "mblSimpleDialogButton", label: Pmg.message('ok'), onClick: function(){self.dfd.resolve(true);self.hide();}})).domNode);
        	this.domNode.appendChild((this.cancelButton = new Button({'class': "mblSimpleDialogButton", label: Pmg.message('cancel'), onClick: function(){self.dfd.cancel(true);self.hide();}})).domNode);
        	win.body().appendChild(this.domNode);
        },
        show: function(atts, mode){
        	this.title.innerHTML = atts.title;
        	this.content.innerHTML = atts.content;
        	this.cancelButton.set('style', {display: mode === 'alert' ? 'none' : ''});
        	this.inherited(arguments);
        	return this.dfd = new Deferred();
        }
    });
});
