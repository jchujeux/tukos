define([
	'dojo/_base/declare',
	'dojo/_base/lang',
	'dojo/_base/array',
	'dojo/aspect',
		   'dojo/dom',
	'dojo/on',
	'dojo/topic',
	'dojo/has',
	'dojo/when',
	'dojo/dnd/Target'
	//'dojo/has!touch?../util/touch',
	//'dojo/has!touch?./_DnD-touch-autoscroll',
], function (declare, lang, arrayUtil, aspect, dom, on, topic, has, when, DnDTarget/*, touchUtil*/) {

	var WidgetDnDTarget = declare(DnDTarget, {
		widget: null,

		getObject: function (node) {
			console.log('DnDWidget - getObject not implemented');
		},
		
        onDropExternal: function (sourceSource, nodes, copy) {
            var tWidget = this.widget, sGrid = sourceSource.grid, sWidget = sGrid || sourceSource.widget;
        	if (tWidget.onDropCondition){
            	if (!tWidget.onDropConditionFunction){
            		tWidget.onDropConditionFunction = eutils.eval(tWidget.onDropCondition);
            	}
            	if (!tWidget.onDropConditionFunction(sWidget, tWidget)){
            		return;
            	}
            }
            if (sGrid){
            	var field = (tWidget.onDropMap || {}).column || tWidget.widgetName, onDropMode = tWidget.onDropMode || 'append';
            	if (field){
	            	when (sourceSource.getObject(nodes[0]), function(item){
	            		var value = item[field] || item['comments'];
	            		if (value){
	            			if (onDropMode === 'append'){
	            				tWidget.set('value', (tWidget.get('value') || '') + value);
	            			}else{
	            				tWidget.set('value', value); 
	            			}
	            		}
	            	});
            	}
            }else{
            	console.log('DnDWidget - only grid drop is implemented');
            }
            if (!copy){
                console.log('DnDWidget - only copy mode is implemented')
            }
        },

		
		
		onDrop: function (source, nodes, copy, target, e) {
			if (source == target){
				console.log('DnDWidget - dropping on self is not implemented');
			}else{
				this.onDropExternal(source, nodes, true);
			}
			this.onDndCancel();
		}
	});

	var DnD = declare(null, {
		dndTargetType: ['widget', 'dgrid-row'],

		dndParams: null,

		dndConstructor: WidgetDnDTarget,

		postMixInProperties: function () {
			this.inherited(arguments);
			this.dndParams = lang.mixin({ accept: this.dndTargetType }, this.dndParams);
		},

		postCreate: function () {
			this.inherited(arguments);

			var Target = this.dndConstructor || WidgetDnDTarget;
			this.dndTarget = new Target(this.domNode, lang.mixin(this.dndParams, {widget: this/*, skipForm: true*/}));
		}
		
	});
	DnD.WidgetTarget = WidgetDnDTarget;

	return DnD;
});
