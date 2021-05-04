define(['dojo/_base/declare', 'dojo/_base/lang', "dojo/_base/window", 'dojo/_base/Deferred', 'dojo/dom-construct', 'dijit/Dialog', 'dijit/form/Button', "tukos/PageManager"], 
    function(declare, lang, win, Deferred, dct, Dialog, Button, Pmg) {
    return declare(Dialog, {
        postCreate: function() {
            this.inherited(arguments);            
            var self = this, label, div, remember = false;            
            div = dct.create('div', {className: 'dijitDialogPaneContent dialogConfirm'}, this.domNode, 'last');            
            this.domNode.appendChild((this.okButton = new Button({label: Pmg.message('Ok'), onClick: function(){self.dfd.resolve(true); self.hide();}})).domNode);
        	this.domNode.appendChild((this.cancelButton = new Button({label: Pmg.message('Cancel'), onClick: function(){self.dfd.cancel(true);self.hide();}})).domNode);
        	win.body().appendChild(this.domNode);
        },
        show: function(atts, mode) {
        	this.set('title', atts.title);
        	this.set('content', atts.content);
        	this.cancelButton.set('style', {display: mode === 'alert' ? 'none' : ''});
            this.inherited(arguments);
            return this.dfd = new Deferred();
        }
    });
});
