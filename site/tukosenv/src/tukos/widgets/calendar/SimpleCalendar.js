define(["dojo/_base/declare",
		"dojo/_base/lang",
		"dojox/calendar/CalendarBase",
		"dojox/calendar/SimpleColumnView",
		"tukos/widgets/calendar/VerticalRenderer",
		"dojox/calendar/DecorationRenderer",
		"dojox/calendar/MatrixView",
		"dojox/calendar/HorizontalRenderer",
		"dojox/calendar/LabelRenderer",
		"dojox/calendar/ExpandRenderer",
		"dojox/calendar/Keyboard",
		"dojox/calendar/Mouse",
		"dojo/text!dojox/calendar/templates/Calendar.html",
		"dijit/form/Button", "dijit/Toolbar", "dijit/ToolbarSeparator"],

	function(
		declare,
		lang,
		CalendarBase,
		SimpleColumnView,
		VerticalRenderer,
		DecorationRenderer,
		MatrixView,
		HorizontalRenderer,
		LabelRenderer,
		ExpandRenderer,
		Keyboard,
		Mouse,
		template){

	return declare(CalendarBase, {

		templateString: template,
		// summary:
		//		This class defines a calendar widget that display events in time.

		_createDefaultViews: function(){
			// summary:
			//		Creates the default views:
			//		- A dojox.calendar.ColumnView instance used to display one day to seven days time intervals,
			//		- A dojox.calendar.MatrixView instance used to display the other time intervals.
			//		The views are mixed with Mouse and Keyboard to allow editing items using mouse and keyboard.

			var colView = declare([SimpleColumnView, Keyboard, Mouse])(lang.mixin({
				verticalRenderer: VerticalRenderer,
				horizontalRenderer: HorizontalRenderer,
				expandRenderer: ExpandRenderer,
				horizontalDecorationRenderer: DecorationRenderer,
				verticalDecorationRenderer: DecorationRenderer
			}, this.columnViewProps));

			var matrixView = declare([MatrixView, Keyboard, Mouse])(lang.mixin({
				horizontalRenderer: HorizontalRenderer,
				horizontalDecorationRenderer: DecorationRenderer,
				labelRenderer: LabelRenderer,
				expandRenderer: ExpandRenderer
			}, this.matrixViewProps));

			this.columnView = colView;
			this.matrixView = matrixView;

			var views = [colView, matrixView];

			this.installDefaultViewsActions(views);

			return views;
		},

		installDefaultViewsActions: function(views){
			// summary:
			//		Installs the default actions on newly created default views.
			//		By default this action is registering:
			//		- the matrixViewRowHeaderClick method on the rowHeaderClick event of the matrix view.
			//		- the columnViewColumnHeaderClick method on the columnHeaderClick event of the column view.
			this.matrixView.on("rowHeaderClick", lang.hitch(this, this.matrixViewRowHeaderClick));
			this.columnView.on("columnHeaderClick", lang.hitch(this, this.columnViewColumnHeaderClick));
		}

	});
});
