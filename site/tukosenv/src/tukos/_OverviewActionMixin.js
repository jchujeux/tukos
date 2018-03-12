define (["dojo/_base/array", "dojo/_base/declare", "dijit/registry", "dijit/Dialog", "tukos/_WidgetsMixin", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
	function(arrayUtil, declare, registry, Dialog, _WidgetsMixin, messages){
	return declare("tukos._OverviewActionMixin", _WidgetsMixin, {

		idsToProcess: function(grid){
			var idsToProcess = new Array;
			for (var id in grid.selection){
				if (grid.selection[id]){
					idsToProcess.push(id);
				}
			}
			return {ids: idsToProcess};
		},	

		editableIdsToProcess: function(grid){
			var deselect = 0;
			var idsToProcess = new Array;
			for (var id in grid.selection){
				if (grid.selection[id]){
					var row = grid.row(id); 
					if (row.data['canEdit']){
						idsToProcess.push(id);
					}else{
						grid.deselect(id);
						deselect += 1;
					}
				}
			}
			return {ids: idsToProcess, warning: (deselect > 0  ? '<p>' + deselect + messages.werereadonlyexcluded : '')};
		}
	});
});
