define(["dojo/_base/declare", "dojo/when", "tukos/ArrayIterator", "tukos/utils", "tukos/dateutils"], 
function(declare, when, ArrayIterator, utils, dutils){
    return declare(null, {
        constructor: function(args){
			var filter = new args.sessionsStore.Filter();
			this.sessionsStore = args.sessionsStore;
			this.tsbFilter = filter.eq('mode', 'performed').ne('sts', NaN).ne('lts', NaN).gt('startdate', '');
			this.sessionsIterator = new ArrayIterator();
			this.stsDailyDecay = this.ltsDailyDecay = this.initialSts = this.initialLts = 0;
			this.stsRatio = 1;
        },
		initialize: function(params){
			var self = this, mapping = {stsdays: 'stsDailyDecay', ltsdays: 'ltsDailyDecay', stsratio: 'stsRatio', initialsts: 'initialSts', initiallts: 'initialLts'};
			utils.forEach(params, function(value, name){
				var property = mapping[name];
				switch(name){
					case 'stsdays':
					case 'ltsdays':
						self[property] = value ? Math.exp(-1/value) : 0.0;
						break;
					default:
						self[property] = value || 0;
				}
			})
		},
		get: function(property){
			return this[property];
		},
		isActive: function(){
			return (this.stsDailyDecay || this.ltsDailyDecay);
		},
		createRowAction: function(grid, row){
			this.rowAction(grid, row, 'create');
		},
		updateRowAction: function(grid, row, isUserEdit){
			this.rowAction(grid, row, 'update', isUserEdit);
		},
		deleteRowAction: function(grid, row){
			this.rowAction(grid, row, 'delete');
		},
		getCollection: function(){
			return this.sessionsStore.filter(this.tsbFilter).sort([{property: 'startdate'}, {property: 'sessionid'}]);
		},
		rowAction: function(grid, row, cudMode, isUserEdit){
			var self = this, collection = this.getCollection();
			if ((row === false || row.startdate) && self.isActive()){
				when(collection.fetchSync(), function(data){
					var iterator = self.sessionsIterator, idp = collection.idProperty, item, sessionStress, sts, lts, tsb, stsHasChanged, ltsHasChanged, tsbHasChanged, previousItem, daysDifference, previousLts, previousSts,
				    currentRow, updateRowOrDirty;
					switch(cudMode){
						case 'create':
							previousItem = iterator.initialize(data, 'last');
							while (previousItem !== false && previousItem.startdate > row.startdate){
								previousItem = iterator.previous();
								item = iterator.next();
							}
							break;
						case 'update':
							if (row === false){
								item = iterator.initialize(data, false);
								previousItem = false;
							}else{
								item = iterator.initialize(data, collection.getSync(row[idp]));
								previousItem = iterator.previous();
								iterator.next();
							}
							break;
						case 'delete':
							item = iterator.initialize(data, collection.getSync(row[idp]));
							previousItem = iterator.previous();
							iterator.next();
					}
					if (item){
						sessionStress = item.trimphr || item.trimpavghr || 0;
						currentRow = cudMode === 'delete' ? iterator.next() : item;
						updateRowOrDirty = function(col, value){
							if (Math.abs(value - Number(currentRow[col] || 0)) > 0.01){
								if (cudMode !== 'delete'){
									if (isUserEdit){
										grid.updateDirty(currentRow[idp], col, value);
									}else{
										currentRow[col] = value;
									}
								}
								return true;
							}else{
								return false;
							}
							
						}
						if (currentRow){
							if (previousItem){
								daysDifference = dutils.difference(previousItem.startdate, currentRow.startdate);
								previousSts = previousItem.sts;
								previousLts = previousItem.lts;
							}else{
								daysDifference = dutils.difference(grid.valueOf('@fromdate'), currentRow.startdate);
								previousSts = grid.valueOf('@initialsts');
								previousLts = grid.valueOf('@initiallts');
							}
							sts = self.stsDailyDecay && (sessionStress || previousSts) ? self.exponentialAvg(sessionStress, previousSts, daysDifference, self.stsDailyDecay) : 0;
							stsHasChanged = updateRowOrDirty('sts', sts);
							lts = self.ltsDailyDecay && (sessionStress || previousLts) ? self.exponentialAvg(sessionStress, previousLts, daysDifference, self.ltsDailyDecay) : 0;
							ltsHasChanged = updateRowOrDirty('lts', lts);
							tsb = lts - sts * self.stsRatio;
							tsbHasChanged = updateRowOrDirty('tsb', tsb);
						}
						if (row === false || (stsHasChanged || ltsHasChanged || tsbHasChanged)){
							if (cudMode === 'delete'){
								previousItem = previousItem || {startdate: grid.valueOf('@fromdate'), sts: previousSts, lts: previousLts, tsb: previousLts - previousSts * self.stsRatio};
								iterator.previous();
							}else{
								previousItem = item;
							}
							while (item = iterator.next()){
								daysDifference = dutils.difference(previousItem.startdate, item.startdate);
								sts = self.exponentialAvg(item.trimphr || item.trimpavghr || 0, previousItem.sts, daysDifference, self.stsDailyDecay);
								if (Math.abs(sts - Number(item.sts || 0)) > 0.01){
									grid.updateDirty(item[idp], 'sts', sts);
								}
								lts = self.exponentialAvg(item.trimphr || item.trimpavghr || 0, previousItem.lts, daysDifference, self.ltsDailyDecay);
								if (Math.abs(lts - Number(item.lts || 0)) > 0.01){
									grid.updateDirty(item[idp], 'lts', lts);
								}
								tsb = lts - sts * self.stsRatio;
								if (Math.abs(tsb - Number(item.tsb || 0)) > 0.01){
									grid.updateDirty(item[idp], 'tsb', tsb);
								}
								previousItem = item;
							}
						}
						//grid.refresh({keepScrollPosition: true});
					}
				});
			}
		},
		exponentialAvg: function(sessionStress, previousStress, daysDifference, dailyDecay){
			return sessionStress * (1-dailyDecay) + previousStress * Math.pow(dailyDecay, daysDifference);
		}
    });
}); 

