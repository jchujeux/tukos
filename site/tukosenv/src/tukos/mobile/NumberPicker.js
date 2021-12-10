define(["dojo/_base/declare", "dojo/_base/lang", "dojo/aspect", "dojo/dom-style", "dojox/mobile/SpinWheel", "dojox/mobile/SpinWheelSlot", "tukos/utils", "tukos/widgetUtils"], 
  function(declare, lang, aspect, dst, SpinWheel, SpinWheelSlot, utils, wutils){
	return declare([SpinWheel], {
    	constructor: function(args){
    		delete args.style.width;
    		args.style = lang.mixin({height: '40px'}, args.style);
    		this.digits = args.constraints.pattern.split('.');
    		this.slotClasses = [];
    		this.slotProps = [];
    		for (var i = 1; i <= this.digits[0].length; i++){
    			this.slotClasses.push(SpinWheelSlot);
    			this.slotProps.push({labelFrom:0, labelTo:9, style:{width:"30px", textAlign:"right"}});
    		};
    		args.style = lang.mixin({height: '40px', width: (32*this.digits[0].length + 10) + 'px'}, args.style);// set empirically
    	},
		postCreate: function(){
			var self = this;
			this.inherited(arguments);
            var slots = this.getChildren();
            slots.forEach(function(slot){
            	aspect.after(slot, "slideTo", function(){
            		var _self = this;
					utils.waitUntil(function(){return !_self._duringSlideTo}, function(){self.set('value', self.get('value'));}, 200);
            	});
            });
		},
		/*startup: function(){
			var barNode = Array.apply(null, this.domNode.getElementsByClassName('mblSpinWheelBar')).shift()
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
		reset: function(){
			
		},
		_setValueAttr: function(value){
			if (! this._started){
				this.startup();
			}
			var values = [], digits = this.digits[0].length, divider = Math.pow(10, digits-1), remainder = parseInt(value), digit;
			this._set('value', value);
			for (var i = 0; i < digits; i++){
				values[i] = digit = Math.trunc(remainder / divider);
				remainder = remainder - digit * divider;
				divider = divider / 10;
			}
			this.set('values', values);
		},
		_getValueAttr: function(){
			var values = this.get('values'), digits = this.digits[0].length, value = 0, multiplier = Math.pow(10, digits-1);
			for (var i = 0; i < digits; i++){
				value = value + values[i] * multiplier;
				multiplier = multiplier / 10;
			}
			return value;
		}
	});
});
