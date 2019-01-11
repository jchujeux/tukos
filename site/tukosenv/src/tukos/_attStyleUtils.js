define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-construct", "dojo/ready", "dojo/on", "tukos/utils", "tukos/TukosTooltipDialog", "dijit/ColorPalette", 
    function(declare, lang, domAttr, domStyle, dct, ready, on, utils, TukosTooltipDialog, colorPicker){

    var sizeUnits = [{id: '', name: ''}, {id: 'auto', name: 'auto'}, {id: '%', name: '%'}, {id: 'em', name: 'em'}, {id: 'px', name: 'px'}];

    return declare(TukosTooltipDialog, {
       
        sizeAttValue: function(attName){
            return {
                get: function(node){
                    var sizeAttValue = node.style[attName] || domAttr.get(node, attName);
                    if (utils.empty(sizeAttValue)){
                        return '';
                    }else if (dojo.isString(sizeAttValue)){
                        return '[' + (/\d+/.exec(sizeAttValue) || '""') + ',"' + /[%a-z]+/i.exec(sizeAttValue) + '"]';
                    }else{
                        return  '[' + sizeAttValue + ', ""]';
                    }
                },
                set: lang.hitch(this, function(node, att, value){
                    var widget = this.pane.getWidget(attName);
                    domStyle.set(node, attName, widget.numberField.get('value') + widget.unitField.get('value'));
                }),
                remove: function(node, att){
                    domStyle.set(node, attName, '');
                }
            };
        },

        domAttValue: function(){
            return {
                get: function(node, att){
                    return domAttr.get(node, att);
                },
                set: function(node, att, value){
                    value === '' ? domAttr.remove(node, att) : domAttr.set(node, att, value);
                },
                remove: function(node, att){
                    domAttr.remove(node, att);
                }
            };
        },
        styleAttValue: function(){
            return {
                get: function(node, att){
                    return node.style[att];// we do not want the computed style, but the style set for this node
                },
                set: function(node, att, value){
                    domStyle.set(node, att, value);
                },
                remove: function(node, att){
                    domStyle.set(node, att, '');
                }
            };
        }
    });
});
