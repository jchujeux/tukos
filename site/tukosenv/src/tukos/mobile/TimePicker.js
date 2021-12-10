define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-class", "dojo/aspect", "dojo/dom-style", "dojox/mobile/_TimePickerMixin", "dojox/mobile/SpinWheel", "dojox/mobile/SpinWheelSlot", "tukos/utils", "tukos/widgetUtils"], 
  function(declare, lang, domClass, aspect, dst, TimePickerMixin, SpinWheel, SpinWheelSlot, utils, wutils){
	return declare([SpinWheel, TimePickerMixin], {
		constructor: function(args){
			this.slotClasses = [SpinWheelSlot, SpinWheelSlot];
			this.slotProps = [
					{labelFrom:0, labelTo:23, style:{width:"50px", textAlign:"right"}},
					{labelFrom:0, labelTo:59, zeroPad:2, style:{width:"40px", textAlign:"right"}}
			];
			if (args.constraints.timePattern === 'HH:mm:ss'){
				this.slotClasses.push(SpinWheelSlot);
				this.slotProps.push({labelFrom:0, labelTo:59, zeroPad:2, style:{width:"40px", textAlign:"right"}});
			}
    		args.style = lang.mixin({height: '40px', width: (46 * this.slotClasses.length + 10) + 'px'}, args.style);// set empirically
    	},
		buildRendering: function(){
			var self = this;
			this.inherited(arguments);
			domClass.add(this.domNode, "mblSpinWheelTimePicker");
            var slots = this.getChildren();
            slots.forEach(function(slot){
				aspect.after(slot, "slideTo", function(){
            		var _self = this;
					utils.waitUntil(function(){return !_self._duringSlideTo}, function(){self.set('value', self.get('value'));}, 200);
            	});
            });
		},
        setStyleToChanged: function(){
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
			if (! this._started){
				this.startup();
			}
			this._set('value', value);
			var values = value.substring(1).split(':');
			values[0] = String(parseInt(values[0]));
			if (this.constraints.timePattern !== 'HH:mm:ss'){
				values = values.slice(0,2);
			}
			this.set('values', values);
		},
		_getValueAttr: function(){
			var values = this.get('values');
			values[0] = values[0] ? utils.pad(values[0], 2) : '00';
			values[1] = values[1] || '00';
			if (this.constraints.timePattern === 'HH:mm:ss'){
				values[2] = values[2] || '00';
				return 'T' + values.join(':');
			}else{
				return 'T' + values.join(':') + ':00';
			}
		},
		reset: function(){
		}
	});
});
