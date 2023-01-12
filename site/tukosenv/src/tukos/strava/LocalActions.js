"use strict"
define(["dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/PageManager"], 
function(declare, lang, utils, Pmg){
    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		authorizeStrava: function(pane){
			var form = pane.form, athleteId = form.valueOf(this.athlete), coachId = form.valueOf(this.coach), contentMessage = athleteId ? '' : Pmg.message('needtodefineathlete', form.object);
			contentMessage = coachId ? '' : ((contentMessage ? ' & ' : '') + Pmg.message('needtodefinecoach', form.object));
			pane.close();
			if (contentMessage){
				Pmg.alert({title: Pmg.message('cannotsynchronizestrava', form.object), content: contentMessage});
			}else{
            	Pmg.setFeedback(Pmg.message('actionDoing'));
            	form.serverDialog({action:'Process', query: {id: form.valueOf('id'), athleteid: athleteId, coachid: coachId,
					params:  JSON.stringify({process: 'stravaEmailAuthorize', save: true})}}, form.changedValues(), form.get('postElts'), Pmg.message('actionDone')); 
			}
		},
		synchronizeWithStrava: function(pane){
			var self = this, form = pane.form, athleteid = form.valueOf(this.athlete), grid = form.getWidget(this.grid), contentMessage = !athleteid ?  ' & <br> '  + Pmg.message('needtodefineathlete') : '';
			pane.close();
			if (contentMessage){
				Pmg.alert({title: Pmg.message('cannotsynchronizestrava'), content: contentMessage});
			}else{
            	Pmg.setFeedback(Pmg.message('actionDoing'));
            	form.serverDialog({action:'Process', query: {athleteid: athleteid, synchrostart: pane.valueOf('synchrostart'), synchroend: pane.valueOf('synchroend'), synchrostreams: pane.valueOf('synchrostreams'), 
            			params:  JSON.stringify({process: 'stravaSynchronize', save: false, noget: true})}}, form.changedValues(), form.get('postElts'), Pmg.message('actionDone')).then(function(response){
					self.mergeStravaActivities(grid, response.stravaActivities);
/*					const stravaActivities = response.stravaActivities, collection = grid.collection, filter = collection.Filter(), idp = grid.store.idProperty, synchedItems = [], sportsMapping = self.sportsMapping, dayDateCol = self.dayDateCol;
					const itemSportCol = typeof sportsMapping === 'object' ? sportsMapping.col : 'sport', map = typeof sportsMapping === 'object' ? sportsMapping.map : null;
					utils.forEach(stravaActivities, function(dayActivities, startdate){
						dayActivities.forEach(function(activity){
							if (map && !map[activity.sport]){
								exit;
							}else{
								const items = collection.filter(filter.eq('stravaid', activity.stravaid)).fetchSync();
								if (items.length === 1){
									grid.updateRow(lang.mixin({idp: items[0][idp]}, activity));
									synchedItems.push(items[0][idp]);
								}else{
									const itemSportToSync = map ? map[activity.sport] : activity.sport, mappedActivity = lang.clone(activity),
										  dayItems = collection.filter(filter.eq(self.dayDateCol, startdate).eq(itemSportCol, itemSportToSync).ni(idp, synchedItems)).sort(self.daySortCol).fetchSync();
									delete mappedActivity.sport;
									if(dayItems.length >=1){
										grid.updateRow(lang.mixin({idp: items[0][idp]}, mappedActivity));
										synchedItems.push(items[0][idp]);
									}else{
										mappedActivity[itemSportCol] = itemSportToSync;
										mappedActivity[dayDateCol] = startdate;
										grid.addRow(undefined, mappedActivity);
									}
								}
							}
						});
					});*/
				});
			}
		},
		mergeStravaActivities: function(grid, stravaActivities){
			const self = this, collection = grid.collection, filter = collection.Filter(), idp = grid.store.idProperty, synchedItems = [], sportsMapping = self.sportsMapping, dayDateCol = self.dayDateCol;
			const itemSportCol = typeof sportsMapping === 'object' ? sportsMapping.col : 'sport', map = typeof sportsMapping === 'object' ? sportsMapping.map : null;
			utils.forEach(stravaActivities, function(dayActivities, startdate){
				dayActivities.forEach(function(activity){
					if (map && !map[activity.sport]){
						exit;
					}else{
						const items = collection.filter(filter.eq('stravaid', activity.stravaid)).fetchSync();
						if (items.length === 1){
							grid.updateRow(lang.mixin({idp: items[0][idp]}, activity));
							synchedItems.push(items[0][idp]);
						}else{
							const itemSportToSync = map ? map[activity.sport] : activity.sport, mappedActivity = lang.clone(activity),
								  dayItems = collection.filter(filter.eq(self.dayDateCol, startdate).eq(itemSportCol, itemSportToSync).ni(idp, synchedItems)).sort(self.daySortCol).fetchSync();
							delete mappedActivity.sport;
							if(dayItems.length >=1){
								grid.updateRow(lang.mixin({idp: items[0][idp]}, mappedActivity));
								synchedItems.push(items[0][idp]);
							}else{
								mappedActivity[itemSportCol] = itemSportToSync;
								mappedActivity[dayDateCol] = startdate;
								grid.addRow(undefined, mappedActivity);
							}
						}
					}
				});
			});
		}
	});
});
