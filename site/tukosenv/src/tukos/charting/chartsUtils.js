define(["dojo/_base/lang", "tukos/utils", "tukos/PageManager"],
	function(lang, utils, Pmg) {
		return {
			setFilterString: function (description, expression, dateCol){
				const filterStrings = [];
				if (description.firstdate){
					filterStrings.push('"' + dateCol + '" >= "' + expression.expressionToValue(description.firstdate) + '"');
				}
				if (description.lastdate){
					filterStrings.push('"' + dateCol + '" <= "' + expression.expressionToValue(description.lastdate) + '"');
				}
				if (description.itemsFilter){
					filterStrings.push(description.itemsFilter);
				}
				return filterStrings.join(' AND ');
			},
			processMissingKpis: function(missingItemsKpis, grid, self, chartWidgetName, chartData, tableData, tableColumns, axes, plots, series){
				const form = self.form, chartWidget = form.getWidget(chartWidgetName);
				if (!utils.empty(missingItemsKpis)){
				    let data = {};
					utils.forEach(missingItemsKpis, function(missingItemKpis, idp){
						const item = grid.store.getSync(idp), dirtyToSend = lang.clone(grid.dirty[idp]) || {};
						delete dirtyToSend.connectedIds;
						delete dirtyToSend.acl;
						data[idp] = {'kpisToGet': missingItemKpis, 'itemValues': lang.mixin({'id': item.id || '', 'stravaid' : item.stravaid}, dirtyToSend)};
					});
				    if (!utils.empty(data)){
					    if (self.recursionDepth > 2){
							Pmg.addFeedback(Pmg.message('too many recursions') + ': ' + self.recursionDepth + ' (' + chartWidgetName + ')');
							self.recursionDepth = 0;
							form.isCharting = false;
							return;
						}
					    const defaultTimeout = Pmg.getCustom('defaultClientTimeout', 32000), requiredTimeout = utils.count(data) * 2000, query = {programId: form.valueOf('id'), athleteid: form.valueOf('parentid'), params: {process: 'getKpis', noget: true}},
							options = {data: data};
						if (requiredTimeout > defaultTimeout){
							query.timeout = requiredTimeout;
							options.timeout = requiredTimeout;
							Pmg.addFeedback(Pmg.message('adjustedTimeout') + ': ' + requiredTimeout, null, ' ', true);
						}
						Pmg.serverDialog({action: 'Process', object: grid.object, view: 'edit', query: query}, options).then(
					            function(response){
					           		const itemsKpis = response.data.kpis;
									utils.forEach(itemsKpis, function(itemKpis, idp){
										utils.forEach(itemKpis, function(kpi, j){
											const ignoreChange = j.includes('shrink'), isUserEdit = !ignoreChange;
											grid.updateDirty(idp, j, kpi, false, isUserEdit, ignoreChange);
											if (kpi === false){
												Pmg.addFeedback('Pmg.serverKpierror' + ': ' + ' - ' + Pmg.message('col') + ': ' + j + Pmg.message('idp') + ': ' + idp);
											}
										});
									});
									self.setChartValue(chartWidgetName);//recursive call. Risk of infinite loop ?
					            },
					            function(error){
					                console.log('error:' + error);
									self.recursionDepth = 0;
									form.isCharting = false;
					            }
					    );
					}else{
						chartWidget.set('value', {data: chartData, tableData: tableData, tableColumns: tableColumns, axes: axes, plots: plots, series: series, title: chartWidget.title});
						self.recursionDepth = 0;
						form.isCharting = false;
						Pmg.addFeedback(Pmg.message('updatedchart') + ' ' + chartWidget.title + ' (' + chartWidgetName + ')');
					}
				}else{
					chartWidget.set('value', {data: chartData, tableData: tableData, tableColumns: tableColumns, axes: axes, plots: plots, series: series, title: chartWidget.title});
					self.recursionDepth = 0;
					form.isCharting = false;
					Pmg.addFeedback(Pmg.message('updatedchart') + ' '  + chartWidget.title + ' (' + chartWidgetName + ')');
				}
			}
		};
	});