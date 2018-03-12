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
	'dojo/dnd/Target',
	'dojo/dnd/Manager',
	'dojo/_base/NodeList'
	//'dojo/has!touch?../util/touch',
	//'dojo/has!touch?./_DnD-touch-autoscroll',
	//'xstyle/css!dojo/resources/dnd.css'
], function (declare, lang, arrayUtil, aspect, dom, on, topic, has, when, DnDTarget,
		DnDManager, NodeList/*, touchUtil*/) {
	
	var CalendarDnDTarget = declare(DnDTarget, {
		calendar: null,

		getObject: function (node) {
			var calendar = this.calendar;
			// Extract item id from row node id (gridID-row-*).
			return calendar.get(node.id.slice(grid.id.length + 5));
		},
		// DnD method overrides
		onDndDrop: function (source, nodes, copy, target, e) {
			if (this == target){
				var target = this, calendar = this.calendar, start = calendar.floorDate(calendar.columnView.getTime(e), "minute", calendar.columnView.timeSlotDuration);
				nodes.forEach(function(node){
					when(source.getObject(node), function (item){
						calendar.addItem(item, source.grid, start);
					});
				});
			}
			this.onDndCancel();
		}
	});

	var DnD = declare(null, {
		dndTargetType: 'first-child',

		dndParams: null,

		dndConstructor: CalendarDnDTarget,

		postMixInProperties: function () {
			this.inherited(arguments);
			this.dndParams = lang.mixin({ accept: [this.dndTargetType] }, this.dndParams);
		},

		postCreate: function () {
			this.inherited(arguments);

			var Target = this.dndConstructor || CalendarDnDTarget;
			this.dndTarget = new Target(
										   this.views[0].itemContainerTable,
				lang.mixin(this.dndParams, {
					calendar: this//,
				})
			);

			var selectedNodes = this.dndTarget._selectedNodes = {};

			aspect.after(this, 'destroy', function () {
				delete this.dndTarget._selectedNodes;
				selectedNodes = null;
				this.dndTarget.destroy();
			}, true);
		}
	});
	DnD.CalendarTarget = CalendarDnDTarget;

	return DnD;
});
