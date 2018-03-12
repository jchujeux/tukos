define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dijit/_WidgetBase", "dijit/_TemplatedMixin", "dijit/form/HorizontalSlider",
	"dojox/calendar/_RendererMixin", "dojo/text!tukos/widgets/calendar/VerticalRenderer.html", "tukos/utils"],
	
	function(declare, lang, dct, domStyle, _WidgetBase, _TemplatedMixin, HorizontalSlider, _RendererMixin, template, utils){
	
	return declare([_WidgetBase, _TemplatedMixin, _RendererMixin], {
		
		templateString: template,
		
		postCreate: function() {
			this.inherited(arguments);
			this._applyAttributes();
		},
	
		_isElementVisible: function(elt, startHidden, endHidden, size){
			var d;
			
			switch(elt){
				case "startTimeLabel":
					d = this.item.startTime;
					if(this.item.allDay || this.owner.isStartOfDay(d)){
						return false;
					}
					break;
				case "endTimeLabel":
					d = this.item.endTime;
					if(this.item.allDay || this.owner.isStartOfDay(d)){
						return false;
					}
					break;
			}
			return this.inherited(arguments);
		},

		updateRendering: function (w, h) {
			// summary:
			//		Updates the visual appearance of the renderer according the new values of the properties and the new size of the component.
			// w: Number?
			//		The width in pixels of the renderer.
			// h: Number?
			//		The height in pixels of the renderer.
			h = h || this.item.h;
			w = w || this.item.w;
			if(!h && !w){
				return;
			}
			this.item.h = h;
			this.item.w = w;
			
			var size = this._orientation == "vertical" ? h : w;
	                     var rd = this.owner.renderData;
			var startHidden = rd.dateModule.compare(this.item.range[0], this.item.startTime) != 0;
			var endHidden =  rd.dateModule.compare(this.item.range[1], this.item.endTime) != 0;
			var visible;
			
            this.customize(this.item, w, h);
			if(this.beforeIcon != null) {
				visible = this._orientation != "horizontal" || this.isLeftToRight() ? startHidden : endHidden;
				domStyle.set(this.beforeIcon, "display", visible ? this.getDisplayValue("beforeIcon") : "none");
			}
			if(this.afterIcon != null) {
				visible = this._orientation != "horizontal" || this.isLeftToRight() ? endHidden : startHidden;
				domStyle.set(this.afterIcon, "display", visible ? this.getDisplayValue("afterIcon") : "none");
			}
			if(this.moveHandle){
				visible = this._isElementVisible("moveHandle", startHidden, endHidden, size);
				domStyle.set(this.moveHandle, "display", visible?this.getDisplayValue("moveHandle"):"none");				
			}
			if(this.resizeStartHandle){
				visible = this._isElementVisible("resizeStartHandle", startHidden, endHidden, size);
				domStyle.set(this.resizeStartHandle, "display", visible?this.getDisplayValue("resizeStartHandle"):"none");				
			}
			if(this.resizeEndHandle){
				visible = this._isElementVisible("resizeEndHandle", startHidden, endHidden, size);
				domStyle.set(this.resizeEndHandle, "display", visible?this.getDisplayValue("resizeEndHandle"):"none");				
			}
			if(this.startTimeLabel) {
				visible = this._isElementVisible("startTimeLabel", startHidden, endHidden, size);
				
				domStyle.set(this.startTimeLabel, "display", visible?this.getDisplayValue("startTimeLabel"):"none");
				if(visible) {
					this._setText(this.startTimeLabel, this._formatTime(rd, this.item.startTime));
				}
			}
			if(this.endTimeLabel) {
				visible = this._isElementVisible("endTimeLabel", startHidden, endHidden, size);
				domStyle.set(this.endTimeLabel, "display", visible?this.getDisplayValue("endTimeLabel"):"none");
				if(visible) {
					this._setText(this.endTimeLabel, this._formatTime(rd, this.item.endTime));
				}
			}
			if(this.summaryLabel) {
				visible = this._isElementVisible("summaryLabel", startHidden, endHidden, size);
				domStyle.set(this.summaryLabel, "display", visible?this.getDisplayValue("summaryLabel"):"none");
				if(visible){
                    dct.empty(this.summaryLabel);
                    if (this.imageTag){
                        dct.place(this.imageTag + '<br>', this.summaryLabel);
                    }
                    if (this.ruler){
                        dct.place(this.ruler.domNode, this.summaryLabel);
                    }
                    //this._setText(this.summaryLabel, this.item.summary, true);
                    dct.place('<span>' + this.item.summary + '</span>', this.summaryLabel);
				}
			}
         },

         customize: function(item, w, h){
            var calendar = this.owner.owner, itemsCustom = (calendar.customization ||{}).items;
            var defaultStyleValue = function(styleDefaultAttValue, attr){
            	if (styleDefaultAttValue === 'calendars'){
            		return ((calendar.defaultItemAtts(item) || {}).style || {})[attr]
            	}else{
            		return styleDefaultAttValue;
            	}
            }
        	var margin = calendar.isItemSelected(item) ? '2px' : '';// to highlight selected items
        	Array.from(this.domNode.children).forEach(function(child){
        		domStyle.set(child, 'margin', margin);
        	});
            if (itemsCustom){
                this.imgTag = this.ruler = undefined;
                var customAction = lang.hitch(this, function(customType, customAtts, item, attr){
                	var map = customAtts.map || {}, fieldValue = item[customAtts.field];
                	switch(customType){
                		case 'style': 
                            var newAttValue = (fieldValue ? (map[fieldValue] || fieldValue) : defaultStyleValue(customAtts.defaultValue, attr)) || '';
                			domStyle.set(this.overallItem, attr, newAttValue);
                            domStyle.set(this.startTimeLabel, attr, newAttValue);
                            break;
                		case 'img':
                            var image = fieldValue ? customAtts.imagesDir + map[fieldValue] : (calendar.customization.defaultItemImage ? calendar.customization.defaultItemImage : undefined);
                			this.imageTag = image ? '<img src="' + image + '" alt="icon" height="48" width="48" >' :  undefined;
                            break;
                		case 'ruler':
                            var atts = customAtts.atts || {};
                            if (fieldValue){
                                this.ruler = new HorizontalSlider(utils.mergeRecursive({value: map.indexOf(fieldValue), minimum: 0, maximum:  4, style: {width: '100%'}}, atts), dojo.doc.createElement('div'));
                            }
                            break;
                	}
                });
                for (var customType in itemsCustom){
                    var customAtts = itemsCustom[customType];
                    if (customType === 'style'){
                		for (var att in customAtts){
                			customAction(customType, customAtts[att], this.item._item, att);
                		}
                    }else{
                    	customAction(customType, customAtts, this.item._item);
                    }
                }
            }
        },
        highlightSelection: function(){
            var calendar = this.owner.owner;
        	
        }
	});
});
