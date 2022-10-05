"use strict"
define([
		"dojo/_base/lang", 
		"dojo/_base/declare",
		"dojo/_base/Color", "dojo/dom-construct", "dijit/_WidgetBase",
		"dojox/dgauges/components/utils",
		"dojox/dgauges/RectangularGauge", 
		"dojox/dgauges/LinearScaler", 
		"dojox/dgauges/RectangularScale", 
		"dojox/dgauges/RectangularValueIndicator", 
		"dojox/dgauges/TextIndicator",
		"dojox/dgauges/components/DefaultPropertiesMixin",
		"dijit/registry", "tukos/utils", "tukos/widgetUtils",  "tukos/widgets/CheckBox", "tukos/PageManager"
	], 
	function(lang, declare, Color, dct, _WidgetBase, dgutils, RectangularGauge, LinearScaler, RectangularScale, RectangularValueIndicator, TextIndicator, DefaultPropertiesMixin, registry, utils, wutils, TukosCheckbox, Pmg){
	const TheGauge =  declare([_WidgetBase, RectangularGauge, DefaultPropertiesMixin], {
		// summary:
		//		A horizontal gauge widget.

		// borderColor: Object|Array|int
		borderColor: "#C9DFF2",
		// fillColor: Object|Array|int
		fillColor: "#FCFCFF",
		// indicatorColor: Object|Array|int
		indicatorColor: "#F01E28",
		constructor: function(args){
			// Base colors
			this.borderColor = new Color(this.borderColor);
			this.fillColor = new Color(this.fillColor);
			this.indicatorColor = new Color(this.indicatorColor);
			
			// Draw background
			this.addElement("background", lang.hitch(this, this.drawBackground));
			
			// Scaler			
			var scaler = new LinearScaler();
			
			// Scale
			var scale = new RectangularScale();
			scale.set("scaler", scaler);
			scale.set("labelPosition", "trailing");
			scale.set("paddingTop", 15);
			scale.set("paddingRight", 23);
			scale.tickLabelFunc =  function(tickItem){
				if(tickItem.isMinor){
					return null;
				}else{
					return String(tickItem.value) + this._gauge.tickLabel || '';
				}
			};
			this.addElement("scale", scale);
			
			// Value indicator
			var indicator = new RectangularValueIndicator();			
			indicator.indicatorShapeFunc = lang.hitch(this, function(group){
				var indic = group.createPolyline([-10, 10, 0, 20, 10, 10, 0, 0, -10, 10]).setStroke({
					color: "blue",
					width: 0.25
				}).setFill(this.indicatorColor);
				
				return indic;
			});
			indicator.set("paddingTop", 5);
			indicator.set("interactionArea", "gauge");
			scale.addIndicator("indicator", indicator);
			
			// Indicator Text Border
			
			// Indicator Text
			if (args.showValue){
				this.addElement("indicatorTextBorder", lang.hitch(this, this.drawTextBorder), "leading");
				const indicatorText = new TextIndicator();
				indicatorText.set("indicator", indicator);
				indicatorText.set("x", 32.5);
				indicatorText.set("y", 20);
				this.addElement("indicatorText", indicatorText);
			}
		},
		
		drawBackground: function(g, w, h){
			// summary:
			//		Draws the background shape of the gauge.
			// g: dojox/gfx/Group
			//		The group used to draw the background. 
			// w: Number
			//		The width of the gauge.
			// h: Number
			//		The height of the gauge.
			// tags:
			//		protected
			h = this.height;
			let gap = 5, cr = 0;
			let gradient = this.gradient;
			if (this.showValue){
				gradient[ gradient.length - 2] = 1 - 63/w;
				gradient = gradient.concat([1-62/w, 'white', 1, 'white']);
			}
			let entries = dgutils.createGradient(gradient);
			g.createRect({
				x: gap,
				y: gap,
				width: w - 2 * gap,
				height: h - 2 * gap,
				r: cr
			}).setFill(lang.mixin({
				type: "linear",
				x1: w,
				y1: 0,
				x2: 0,
				y2: h
			}, entries));
			
		},
		drawTextBorder: function(g){
			// summary:
			//		Internal method.
			// tags:
			//		private
			return g.createRect({
				x: 5,
				y: 5,
				width: 60,
				height: 20
			}).setStroke({
				color: "#CECECE",
				width: 1
			});
		},
	});
	return  declare(_WidgetBase, {
        postCreate: function postCreate(){
            const self = this;
            this.inherited(postCreate, arguments);
            this.set('style', {height: this.checkboxes ? '100px' : '60px'});
            if (Pmg.isMobile()){
				if (this.leftTd && !(this.leftTd.style || {}).color){
					this.leftTd.style = lang.mixin({color: 'white'}, this.leftTd.style);
				}
				if (this.rightTd && !(this.rightTd.style || {}).color){
					this.rightTd.style = lang.mixin({color: 'white'}, this.rightTd.style);
				}
			}
            const gaugeDiv = dct.create('div', {style: this.gaugeDivStyle});
            const gaugeTable = dct.create('table', {align: 'center', style: this.gaugeTableStyle}, gaugeDiv), tr = dct.create('tr', null, gaugeTable), leftTd = dct.create('td', this.leftTd, tr), gaugeTd = dct.create('td', {style: {width: '100%'}}, tr), rightTd = dct.create('td', this.rightTd, tr);
            
            const gauge = this.gauge = new TheGauge(lang.mixin(this.gaugeAtts, {id: this.id + "_gauge"}), dct.create('div', null, gaugeTd));
            this.domNode.appendChild(gaugeDiv); 
			this.gauge.on("endEditing", function(event){
				//self.set('value', self.checkboxes ? {gauge: event.indicator.value} : event.indicator.value);
				                    		self.gauge.set('value', event.indicator.value);
				                    		self.set('value', self.get('value'));

			});
            if (this.checkboxes){
            	const id = this.id, table = this.table = dct.create('table', {align: 'center', style: this.gaugeTableStyle}, this.domNode);
            	const tr = dct.create('tr', {}, table);
            	const checkboxWidgets = this.checkboxWidgets = [], checkBoxAtts = Pmg.isMobile() ? {style: {color: 'white'}} : {};
            	utils.forEach(this.checkboxes, function(atts){
					atts.name = atts.id;
					atts.id = id + '_' + atts.id;
					const td = dct.create('td', {align: 'center'}, tr);
					const checkbox = new TukosCheckbox(atts);
            		//checkbox.watch('value', lang.hitch(wutils, wutils.subWidgetWatchCallback, self, checkbox));
					checkbox.watch('checked', function(attr, oldValue, newValue){
						if (self.watchCheckboxes && oldValue !== newValue){
                    		self.set('value', self.get('value'));
						}
					});
					dct.place(checkbox.domNode, td);
					checkboxWidgets.push(checkbox);
					dct.create('span', lang.mixin({innerHTML: atts.title}, checkBoxAtts), td);
				})
            }
        },
        resize: function resize(){
			this.inherited(resize, arguments);
			this.gauge.resize();
		},
         setStyleToUnchanged: function(){
            this.set('style', {backgroundColor: ''});
        }, 
       _setValueAttr: function(value){
			this._set('value', value);
			const pValue = value === '' ? (this.checkboxes ? {gauge: 0} : 0) : JSON.parse(value);
			if (this.checkboxes){
				this.gauge.set('value', pValue.gauge);
				const id = this.id;
				this.watchCheckboxes = false;
				this.checkboxWidgets.forEach(function(widget){
					widget.set('checked', false);
				});
				utils.forEach(pValue.checkboxes, function(value, index){
					registry.byId(id + '_' + index).set('checked', value);
				});
				this.watchCheckboxes = true;
			}else{
				this.gauge.set('value', pValue);
			}
		},
		_getValueAttr: function(){
			let value;
			if (this.checkboxes){
				value = {gauge: this.gauge.value, checkboxes: {}};
				this.checkboxWidgets.forEach(function(widget){
					const checked = widget.get('checked');
					if (checked){
						value.checkboxes[widget.name] = checked;
					}
				});
			}else{
				value = this.gauge.value;
			}
			return JSON.stringify(value);
		},
		_getNumericValueAttr: function(){
			return this.gauge.value || 0;
		}
	});
});