define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "dijit/_WidgetBase", "dijit/_FocusMixin", "tukos/_WidgetsMixin", "tukos/widgetUtils", "tukos/dateutils", "tukos/TukosDateBox", "dijit/form/TimeTextBox", "tukos/StoreSelect", "dijit/registry", "dojo/json"], 
function(declare, lang, domstyle, Widget, _FocusMixin, _WidgetsMixin, wutils, dutils, DateTextBox, TimeTextBox, StoreSelect, registry, JSON){
    return declare([Widget, _FocusMixin,  _WidgetsMixin], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            if (this.dateArgs){
                this.dateField = new DateTextBox(this.dateArgs, dojo.doc.createElement('div'));
                this.dateField.watch('value', lang.hitch(wutils, wutils.subWidgetWatchCallback, this, this.dateField));
                this.domNode.appendChild(this.dateField.domNode);
            }
            if (this.timeArgs){
                this.timeField = new TimeTextBox(this.timeArgs, dojo.doc.createElement('div')); 
                this.timeField.watch('value', lang.hitch(wutils, wutils.subWidgetWatchCallback, this, this.timeField));
                this.domNode.appendChild(this.timeField.domNode);
            }
            if (this.TZArgs){
                this.TZField = new StoreSelect({style: {width: '8em'}, value: 'l', storeArgs: {data: [{id: 'l', name: 'local'}, {id: 'i', name: 'zone insensitive'}]}});
                this.TZField.watch('value', lang.hitch(wutils, wutils.subWidgetWatchCallback, this, this.TZArgs));
                this.domNode.appendChild(this.TZField.domNode);
            }
        },

        focus: function(){
            if (this.dateField){
                this.dateField.focus();
            }else{
                this.timeField.focus();
            }
        },

        setStyleToChanged: function(widget){
            if (this.dateField){ domstyle.set(this.dateField.domNode, 'backgroundColor', wutils.changeColor); }
            if (this.timeField){ domstyle.set(this.timeField.domNode, 'backgroundColor', wutils.changeColor); }
            if (this.TZField){ domstyle.set(this.TZField.domNode, 'backgroundColor', wutils.changeColor); }
        },

        setStyleToUnchanged: function(){
            if (this.dateField){ domstyle.set(this.dateField.domNode, 'backgroundColor', ''); }
            if (this.timeField){ domstyle.set(this.timeField.domNode, 'backgroundColor', ''); }
            if (this.TZField){ domstyle.set(this.TZField.domNode, 'backgroundColor', ''); }
        }, 

        _setValueAttr: function(value){
            if (typeof value == 'string' && value != ''){
                var dateAndTime = dutils.fromISO(value);
                if (this.dateField){
                    this.dateField.set('value', dojo.date.locale.format(dateAndTime, {selector: 'date', datePattern: 'yyyy-MM-dd'}), arguments[1]);
                }
                if (this.timeField){
                    this.timeField.set('value', 'T' + dojo.date.locale.format(dateAndTime, {selector: 'time', timePattern: 'HH:mm:ss'}), arguments[1]);
                }
                if (this.TZField){
                    this.TZField.set('value', (value.slice(-1) == 'Z' ? 'l' : 'i'), arguments[1]);
                }   
                this._set("value", value);
            }
        },
        _getValueAttr: function(){
            if (this.dateField && this.dateField.get('value') === ''){
            	return '';
            }
            var result = (this.dateField ? this.dateField.get('value') || '0000-00-00' : '') +  'T' + (this.timeField ? this.timeField.get('displayedValue') || '00:00:00' : '');
            if (this.TZField && this.TZField.get('value') == 'l'){
                result = dutils.toISO(dutils.fromISO(result));
            }
            return (result ===  '0000-00-00T00:00:00' || result ===  '0000-00-00T00:00:00Z') ? '' : result;
        },

        _setDisabledAttr: function(value){
            this.inherited(arguments);
            if (this.dateField){
                this.dateField.set('disabled', value);
            }
            if (this.timeField){
                this.timeField.set('disabled', value);
            }
        }
    });
}); 
