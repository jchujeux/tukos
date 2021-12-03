define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-class", "dojo/aspect", "dojo/dom-style", "dojox/mobile/_TimePickerMixin", "dojox/mobile/SpinWheel", "dojox/mobile/SpinWheelSlot", "tukos/utils", "tukos/widgetUtils"], 
  function(declare, lang, domClass, aspect, dst, TimePickerMixin, SpinWheel, SpinWheelSlot, utils, wutils){
	return declare([SpinWheel, TimePickerMixin], {
	slotClasses: [SpinWheelSlot, SpinWheelSlot],
	slotProps: [
			{labelFrom:0, labelTo:23, style:{width:"50px", textAlign:"right"}},
			{labelFrom:0, labelTo:59, zeroPad:2, style:{width:"40px", textAlign:"right"}}
		],
	constructor: function(args){
    		args.style = lang.mixin({height: '40px'}, args.style);
    	},
		postCreate: function(){
			var self = this;
			this.inherited(arguments);
            var slots = this.getChildren();
            slots.forEach(function(slot){
            	aspect.after(slot, "slideTo", function(){
            		self.set('value', self.get('value'));
            	});
            });
		},
		buildRendering: function(){
			this.inherited(arguments);
			domClass.add(this.domNode, "mblSpinWheelTimePicker");
		},
		/*startup: function(){
			var barNode = Array.apply(null, this.domNode.getElementsByClassName('mblSpinWheelBar')).shift();
			console.log(' barNode height: ' + dst.get(barNode, "height"));
			dst.set(barNode, {top: (parseInt(this.style.height) - dst.get(barNode, "height"))/2 + 'px'});
			this.inherited(arguments);
		},*/
        setStyleToChanged: function(widget){
            this.getChildren().forEach(function(slot){
            	slot.set('style', {backgroundColor: wutils.changeColor});
            });
        },
        setStyleToUnchanged: function(){
            this.getChildren().forEach(function(slot){
            	slot.set('style', {backgroundColor: ''});
            });
        },
		_setValueAttr: function(value){
			this._set('value', value);
			var values = value.substring(1).split(':').slice(0,2);
			values[0] = String(parseInt(values[0]));
			this.set('values', values);
		},
		_getValueAttr: function(){
			var values = this.get('values');
			values[0] = values[0] ? utils.pad(values[0], 2) : '00';
			values[1] = values[1] || '00';
			return 'T' + values.join(':') + ':00';
		}
	});
});
