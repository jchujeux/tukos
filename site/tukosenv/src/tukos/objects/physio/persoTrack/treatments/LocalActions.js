define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/PageManager"], 
function(declare, lang, utils, Pmg){
    return declare(null, {
        constructor: function(args){
			var form = args.form, sessionsGrid = this.sessionsGrid = form.getWidget('physiopersosessions'), dailiesGrid = this.dailiesGrid = form.getWidget('physiopersodailies');
			this.sessionsStore = sessionsGrid.store;
			this.dailiesStore = dailiesGrid.store;
        },
		getSessionsFilter: function(){
			return this.sessionsFilter || (this.sessionsFilter =  new this.sessionsStore.Filter());
			
		},
		getDailiesFilter: function(){
			return this.dailiesFilter || (this.dailiesFilter =  new this.dailiesStore.Filter());
			
		},
		dailyAssesmentUpdate: function(date, fields){
				var changes = {}, fieldsToUpdate = fields, dailiesGrid = this.dailiesGrid;
				fields.forEach(function(field){
					changes[field] = '';
				});
				dateDaily = this.dailiesStore.filter(this.getDailiesFilter().eq('startdate', date)).fetchSync();
				if (dateDaily.length === 0){
					fieldToUpdate = ['name', 'painduring', 'painafter'];
					changes.startdate = date;
				}
				dateSessions = this.sessionsStore.filter(this.getSessionsFilter().eq('startdate', date)).fetchSync();
				dateSessions.forEach(function(session){
					fieldsToUpdate.forEach(function(field){
						switch (field){
							case 'name' : changes.name += session.name + '<br>'; break;
							case 'painduring' : changes.painduring = (session.painduring > changes.painduring) ? session.painduring : changes.painduring; break;
							case 'painafter' : changes.painafter = (session.painafter > changes.painafter) ? session.painafter : changes.painafter;
						}
					});
				});
				if (dateDaily.length === 0){
					if (dateSessions.length > 0){
						dailiesGrid.addRow(undefined, changes);
					}
				}else{
				dateDailyIdpV = dateDaily[0][this.sessionsStore.idProperty];
					utils.forEach(changes, function(change, field){
						dailiesGrid.updateDirty(dateDailyIdpV, field, change);
					});
				}
			
		},
		startdateChangeLocalAction: function(sWidget, tWidget, newValue, oldValue){
			var oldStartDate = oldValue, newStartDate = newValue;
			if (oldStartDate){
				this.dailyAssesmentUpdate(oldStartDate, ['name', 'painduring', 'painafter']);
			}
			if (newStartDate){
				this.dailyAssesmentUpdate(newStartDate, ['name', 'painduring', 'painafter']);
			}
			this.dailiesGrid.refresh({keepScrollPosition: true});
		},
		fieldChangeLocalAction: function(field){
			var session = this.sessionsStore.getSync(this.sessionsGrid.clickedRow.data[this.sessionsStore.idProperty]), date = session['startdate'];
			if (date){
				this.dailyAssesmentUpdate(date, [field]);
				this.dailiesGrid.refresh({keepScrollPosition: true});
			}
			return true;
		},
		nameChangeLocalAction: function(sWidget, tWidget, newValue, oldValue){
			return this.fieldChangeLocalAction('name');
		},
		painduringChangeLocalAction: function(sWidget, tWidget, newValue, oldValue){
			return this.fieldChangeLocalAction('painduring');
		},
		painafterChangeLocalAction: function(sWidget, tWidget, newValue, oldValue){
			return this.fieldChangeLocalAction('painafter');
		},
		afterCreateSessionRow: function(){
			var session = arguments[0] || this.sessionsGrid.clickedRow.data;
			if (session.startdate){
			    this.dailyAssesmentUpdate(session.startdate, ['name', 'painduring', 'painafter']);
				this.dailiesGrid.refresh({keepScrollPosition: true});
			}
		},
		beforeSessionRowChange: function(args){
			var session = this.sessionsStore.getSync((args || this.sessionsGrid.clickedRow.data)[this.sessionsStore.idProperty]);
			this.sessionBeforeChange = lang.clone(session);
		},
		afterUpdateSessionRow: function(){
			var session = arguments[0] || this.sessionsGrid.clickedRow.data, sessionBeforeChange = this.sessionBeforeChange;
			this.startdateChangeLocalAction(undefined, undefined, session.startdate === sessionBeforeChange.startdate ? sessions.startdate : undefined, session.startdate);
			delete this.rowBeforeChange;
		},
		beforerDeleteSessionsRows: function(args){
			console.log('I am here');
		},
		afterDeleteSessionsRows: function(){
			var dates = [], self = this, needsRefresh = false;;
			arguments[0].forEach(function(sessionToDelete){
				dates.push(sessionToDelete.startdate);
			});
			dates = utils.array_unique(dates);
			dates.forEach(function(date){
				if (date){
					self.dailyAssesmentUpdate(date, ['name', 'painduring', 'painafter']);
					needsRefresh = true;
				}
			});
			if (needsRefresh){
				this.dailiesGrid.refresh({keepScrollPosition: true});
			}
		}
	});
});
