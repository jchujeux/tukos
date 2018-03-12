define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dijit/_WidgetBase", "tukos/hiutils", "dojo/domReady!"], 
function(declare, lang, dct, Widget, hiutils){
    return declare(Widget, {
        _setValueAttr: function(value){
                if (this.valueNode){
                    dct.empty(this.valueNode);
                }else{
                    if (!this.objectNode){
                        if (this.divAtts){
                            this.objectNode = dct.create('div', args.divAtts, this.domNode);
                        }else{
                            this.objectNode = this.domNode;
                        }
                    }
                }
                this.selectedLeaves = {};
                //this.valueNode = lang.hitch(hiutils, hiutils.objectTable)(value, this.hasCheckboxes, this.selectedLeaves, this);
                this.valueNode =hiutils.objectTable(value, this.hasCheckboxes, this.selectedLeaves, this);
                this.objectNode.appendChild(this.valueNode);
        }
    });
}); 
