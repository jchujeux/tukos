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
			var self = this, form = pane.form, athleteid = form.valueOf(this.athlete), coachid = form.valueOf(this.coach), grid = form.getWidget(this.grid), contentMessage = !athleteid ?  ' & <br> '  + Pmg.message('needtodefineathlete') : '',
				defaultItemsCols = pane.defaultItemsCols;
			pane.close();
			if (contentMessage){
				Pmg.alert({title: Pmg.message('cannotsynchronizestrava'), content: contentMessage});
			}else{
            	Pmg.setFeedback(Pmg.message('actionDoing'));
            	let query = {athleteid: athleteid, coachid: coachid, synchrostart: pane.valueOf('synchrostart'), synchroend: pane.valueOf('synchroend'), synchrostreams: pane.valueOf('synchrostreams'), synchroweatherstation: pane.valueOf('synchroweatherstation'),
            		params:  JSON.stringify({process: 'stravaSynchronize', save: false, noget: true})};
            	if (defaultItemsCols){
					defaultItemsCols.forEach(function(col){
						const colValue = pane.valueOf(col);
						if (colValue !== ""){
							query[col] = colValue;
						}
					});
				}
            	form.serverDialog({action:'Process', query: query}, form.changedValues(), form.get('postElts'), Pmg.message('actionDone')).then(function(response){
					if (response.usersItems.length){
						grid.usersAclCache = {sportsmanId: athleteid, coachId: coachid, usersItems: response.usersItems};
					}
					if (!utils.empty(response.stravaActivities)){
						self.mergeStravaActivities(grid, response.stravaActivities);
					}
				});
			}
		},
		mergeStravaActivities: function(grid, stravaActivities){
			const self = this, collection = grid.collection, filter = collection.Filter(), idp = grid.store.idProperty, synchedItems = [], targetObject = self.targetObject, sportsMapping = self.sportsMapping, dayDateCol = self.dayDateCol;
			const itemSportCol = utils.empty(sportsMapping) ? 'sport' : sportsMapping.col, map = utils.empty(sportsMapping) ? null : sportsMapping.map;
			utils.forEach(stravaActivities, function(dayActivities, startdate){
				dayActivities.forEach(function(activity){
					if (map && !map[activity.sport]){
						exit;
					}else{
						const items = collection.filter(filter.eq('stravaid', activity.stravaid)).fetchSync();
						if (items.length === 1){
							grid.updateRow(lang.mixin({[idp]: items[0][idp]}, activity));
							synchedItems.push(items[0][idp]);
						}else{
							const itemSportToSync = map ? map[activity.sport] : activity.sport, mappedActivity = lang.clone(activity),
								  dayItems = collection.filter(filter.eq(self.dayDateCol, startdate).eq(itemSportCol, itemSportToSync).ni(idp, synchedItems)).sort(self.daySortCol).fetchSync();
							delete mappedActivity.sport;
							if(dayItems.length >=1){
								grid.updateRow(lang.mixin({[idp]: dayItems[0][idp]}, mappedActivity));
								synchedItems.push(dayItems[0][idp]);
							}else{
								mappedActivity[itemSportCol] = itemSportToSync;
								mappedActivity[dayDateCol] = startdate;
								if (targetObject === 'sptplans'){
									mappedActivity.mode = 'performed';
								}
								synchedItems.push(grid.addRow(undefined, mappedActivity)[idp]);
							}
						}
					}
				});
			});
		}
	});
});
