define(["dojo/_base/declare", "dojo/_base/lang", "dojo/aspect", "dojo/dom-construct",  "dojo/dom-style", "dijit/_WidgetBase", "dojox/mobile/SpinWheel", "dojox/mobile/SpinWheelSlot", "tukos/utils", "tukos/widgetUtils", "tukos/PageManager"], 
  function(declare, lang, aspect, dct, dst, _WidgetBase, SpinWheel, SpinWheelSlot, utils, wutils, Pmg){
	return declare([_WidgetBase], {
    	constructor: function(args){
    		delete args.style.width;
    		this.digits = args.constraints.pattern.split('.');
			this.digits[1] = this.digits[1] || '';
    	},
		postCreate: function(){
			var self = this;
			this.inherited(arguments);
    		this.integerPartSlots = [];
    		this.decimalPartSlots = [];
			this.spinWheel = new SpinWheel({reset: lang.hitch(this, this.resetSpinWheel), style: lang.mixin({height: '40px', width: (32*(this.digits[0].length - 1 + this.digits[1].length) + 10 + (this.digits[1] ? 10 : 0)) + 'px'}, this.params.style)});// set empirically});    		
			this.domNode.appendChild(this.spinWheel.domNode);
			this.spinWheel.startup();
			for (var i = 0; i < this.digits[0].length-1; i++){
				this.integerPartSlots[i] = new SpinWheelSlot({labelFrom:0, labelTo:9, style:{width:"30px", textAlign:"right"}});
				this.spinWheel.addChild(this.integerPartSlots[i]);
				aspect.after(this.integerPartSlots[i], 'slideTo', function(){//only there to trigger setStyleToChange
            		self.set('value', self.get('value'));
				});
			}
			if(this.digits[1].length > 0){
				dct.create("div", {className: "mblSpinWheelSlot", style: {width: "10px"}}, this.spinWheel.containerNode);
				dct.create("div", {className: "mblSpinWheelSlot", style: {width: "10px", marginLeft: "-10px", paddingTop: "5px", fontSize: "24px", fontWeight: "bold", border: "none"}, innerHTML: Pmg.message('decimalseparator')}, this.spinWheel.containerNode);
				for (var i = 0; i < this.digits[1].length; i++){
					this.decimalPartSlots[i] = new SpinWheelSlot({labelFrom:0, labelTo:9, style:{width:"25px", textAlign:"right"}});
					this.spinWheel.addChild(this.decimalPartSlots[i]);
					aspect.after(this.decimalPartSlots[i], 'slideTo', function(){
	            		self.set('value', self.get('value'));
					});
				}
			}
		},
		/*startup: function(){
			var barNode = Array.apply(null, this.spinWheel.domNode.getElementsByClassName('mblSpinWheelBar')).shift()
			dst.set(barNode, {top: (parseInt(this.spinWheel.style.height) - dst.get(barNode, "height"))/2 + 'px'});
			this.inherited(arguments);
		},*/
        setStyleToChanged: function(widget){
            this.spinWheel.getChildren().forEach(function(slot){
            	slot.set('style', {backgroundColor: wutils.changeColor});
            });
        },
        setStyleToUnchanged: function(){
            this.spinWheel.getChildren().forEach(function(slot){
            	slot.set('style', {backgroundColor: ''});
            });
        }, 
		resetSpinWheel: function(){
			/*this.integerPartSlots.forEach(function(w){
				w.setInitialValue();
			});
			this.decimalPartSlots.forEach(function(w){
				w.setInitialValue();
			});*/
		},
		_setValueAttr: function(value){
			this._set('value', value);
			var decimalDigits = this.digits[1].length, integerDigits = this.digits[0].length-1, digits = integerDigits + decimalDigits, divider = Math.pow(10, digits-1), remainder = parseInt(value * Math.pow(10, decimalDigits)), digit;
			for (var i = 0; i < digits; i++){
				digit = Math.trunc(remainder / divider);
				remainder = remainder - digit * divider;
				divider = divider / 10;
				(i < integerDigits ? this.integerPartSlots[i] : this.decimalPartSlots[i-integerDigits]).set('value', digit);
			}
		},
		_getValueAttr: function(){
			var decimalDigits = this.digits[1].length, integerDigits = this.digits[0].length-1, digits = integerDigits + decimalDigits, value = 0, multiplier = Math.pow(10, digits-1);
			for (var i = 0; i < digits; i++){
				value = value + (i < integerDigits ? this.integerPartSlots[i] : this.decimalPartSlots[i-integerDigits]).get('value') * multiplier;
				multiplier = multiplier / 10;
			}
			return value / Math.pow(10, decimalDigits);
		}
	});
});
