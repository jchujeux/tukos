define(["dojo", "tukos/utils", "tukos/PageManager"], function(dojo, utils, Pmg){
    var durations = {second: 1, minute: 60, hour: 3600, day: 24*3600, week: 7*24*3600, month: 24*3600*30, quarter: 24*3600*30*3, year: 24*3600*365},
    	daysName = ['sunday', 'monday', 'tuesday', 'wednesday','thursday', 'friday', 'saturday', 'sunday'];
	return {
        toISO: function(date, options){
            return dojo.date.stamp.toISOString(date, options || {zulu: true});
        },
        fromISO: function(dateString){
            return dojo.date.stamp.fromISOString(dateString);
        },
        timeToSeconds: function(time){
        	var duration = time.slice(-8).split(':');
        	return duration[0] * 3600 + duration[1] * 60 + parseInt(duration[2]);
        },
        secondsToTime: function(seconds){
        	var seconds = parseInt(seconds), hours;
        	return 'T' + utils.pad(hours = parseInt(seconds / 3600), 2) + ':' + utils.pad(parseInt((seconds - 3600 * hours) / 60), 2) + ':' + utils.pad(seconds % 60, 2);
        },
        durationString: function(fromDate, toDate, duration, correction, format){// format: '[number, interval]', where interval can be 'day', 'week', etc. if correction is true, then the durationString returned is the ceiling (nearest higher integer number of units)
            if (fromDate && toDate){
                var fromDateObject = (typeof fromDate === 'string' ? this.parseDate(fromDate) : fromDate),
                    toDateObject = (typeof toDate === 'string' ? this.parseDate(toDate) : toDate);
               if (format === 'time'){
            	   return this.secondsToTime((toDate - fromDate)/1000);
               }else{
                   interval = (duration == null || duration == '' || format === 'minute') ? 'minute' : (typeof duration === 'string' ? JSON.parse(duration)[1] : duration[1]);
                   var correctedToDate = (correction ? new Date(this.dateAdd(toDateObject, interval, 1).getTime() - 1) : toDateObject), difference = dojo.date.difference(fromDateObject, correctedToDate, interval);
                   return format === 'minute' ? difference : JSON.stringify([difference, interval]);
               }
            }else{
                return duration;
            }
        },
        dateTimeString: function(fromDateString, durationString, toDateString, correction){// format: ISO
            if (fromDateString && durationString){
                var durationArray = JSON.parse(durationString),
                       fromDate = this.parseDate(fromDateString);
                if (durationArray[0] && durationArray[1]){
                    var newToDate = (correction ? new Date(dojo.date.add(fromDate, durationArray[1], durationArray[0]).getTime() - 1) : dojo.date.add(fromDate, durationArray[1], durationArray[0]));
                    return dojo.date.stamp.toISOString(newToDate, {zulu: fromDateString.slice(-1) === 'Z' ? true : false});
                }
            }
            return fromDateString;
        },
        dateString: function(fromDate, duration, toDate, correction){
            var fromDateObject = (typeof fromDate === 'string' ? this.parseDate(fromDate) : fromDate);
            var durationArray = (typeof duration === 'string' ? JSON.parse(duration) : duration);
            if (fromDateObject && durationArray && durationArray[0] && durationArray[1]){
                var newToDateObject = (correction ? new Date(dojo.date.add(fromDateObject, durationArray[1], durationArray[0]).getTime() - 1) : dojo.date.add(fromDateObject, durationArray[1], durationArray[0]));
                return this.formatDate(newToDateObject);
            }else{
                return toDate || fromDate;
            }
        },
        addDurationString: function(durationString, toDate, format){
            if (durationString){
            	if (format === "minute"){
            		if (durationString){
            			return dojo.date.add(toDate, 'minute', durationString);
            		}
            	}else if(format === "time"){
            		return dojo.date.add(toDate, 'second', this.timeToSeconds(durationString));
            	}else{
                	var durationArray = JSON.parse(durationString);
                    if (durationArray[0] && durationArray[1]){
                        return dojo.date.add(toDate, durationArray[1], durationArray[0]);
                    }            		
            	}
            }
            return new Date(toDate);
        },
        dayName: function(dayNumber){
			return Pmg.message(daysName[dayNumber]);
		},
		getDayOfWeek: function (number, date) {// returns a date
          var day = date.getDay(),
              diff = date.getDate() - day + number + (day == 0 ? -7:0); // adjust when day is sunday
          return new Date(date.setDate(diff));
        },
        dateToDayName: function(date){
        	return daysName[date.getDay()];
        },
        getISOWeekOfYear: function(date){
			return this.getWeekOfYear(date, 1);
		},
		getWeekOfYear: function(date, dowOffset){
			/*getWeek() was developed by Nick Baicoianu at MeanFreePath: http://www.epoch-calendar.com */
			dowOffset = typeof(dowOffset) == 'int' ? dowOffset : 0; //default dowOffset to zero
			var newYear = new Date(date.getFullYear(),0,1);
			var day = newYear.getDay() - dowOffset; //the day of week the year begins on
			day = (day >= 0 ? day : day + 7);
			var daynum = Math.floor((date.getTime() - newYear.getTime() - 
			(date.getTimezoneOffset()-newYear.getTimezoneOffset())*60000)/86400000) + 1;
			var weeknum;
			//if the year starts before the middle of a week
			if(day < 4) {
				weeknum = Math.floor((daynum+day-1)/7) + 1;
				if(weeknum > 52) {
					nYear = new Date(date.getFullYear() + 1,0,1);
					nday = nYear.getDay() - dowOffset;
					nday = nday >= 0 ? nday : nday + 7;
					/*if the next year starts before the middle of
		 			  the week, it is week #1 of that year*/
					weeknum = nday < 4 ? 1 : 53;
				}
			}
			else {
				weeknum = Math.floor((daynum+day-1)/7);
			}
			return weeknum;
		},
        parseDate: function(dateString){
           return (typeof dateString === 'string' ? (dateString.length > 10 && dateString[10] === 'T' ?  this.fromISO(dateString) : dojo.date.locale.parse(dateString, {selector: 'date', datePattern: (dateString.length === 10 ? 'y-M-d' : 'y-M-d H:m:s')})): undefined);
        },
        formatDate: function(date, datePattern){
            //console.log('dateutils.formatDate - date: ' + date);
            return dojo.date.locale.format(date, {selector: 'date', datePattern: datePattern || 'yyyy-MM-dd'});
        },
        dateAdd: function(date, interval, units) {
          var ret = new Date(date); //don't change original date
          switch(interval.toLowerCase()) {
            case 'year'   :  ret.setFullYear(ret.getFullYear() + units);  break;
            case 'quarter':  ret.setMonth(ret.getMonth() + 3*units);  break;
            case 'month'  :  ret.setMonth(ret.getMonth() + units);  break;
            case 'week'   :  ret.setDate(ret.getDate() + 7*units);  break;
            case 'day'    :  ret.setDate(ret.getDate() + units);  break;
            case 'hour'   :  ret.setTime(ret.getTime() + units*3600000);  break;
            case 'minute' :  ret.setTime(ret.getTime() + units*60000);  break;
            case 'second' :  ret.setTime(ret.getTime() + units*1000);  break;
            default       :  ret = undefined;  break;
          }
          return ret;
        },
        difference: function (date1, date2, unit){
            if (typeof date1 === 'string'){
                date1 = dojo.date.locale.parse(date1, {selector: 'date', datePattern: 'y-M-d'});
            }
            if (typeof date2 === 'string'){
                date2 = dojo.date.locale.parse(date2, {selector: 'date', datePattern: 'y-M-d'}); 
           }
           return (date1 && date2 ? dojo.date.difference(date1, date2, unit) : '');
        },
        age: function(birthDateString){
            if (birthDateString){
                var birthDate = new Date(birthDateString), today= new Date(), age = today.getFullYear() - birthDate.getFullYear(), month = today.getMonth() - birthDate.getMonth();
                if (month < 0 || (month === 0 && today.getDate() < birthDate.getDate())){
                  age -= 1;
                 }
                 return age;
            }else{
                return '';
            }
         },
         seconds: function(durationString, format){
        	 if (durationString){
            	 if (format === 'time'){
            		 return this.timeToSeconds(durationString);
            	 }else{
            		 var durationArray = JSON.parse(durationString);
                	 return (durationArray != null && durationArray[0] && durationArray[1]) ? durationArray[0] * durations[durationArray[1]] : 0;	 
            	 }
        	 }else{
        		 return 0;
        	 }
         },
         convert: function(duration, fromUnit, toUnit){
            var durations = {second: 1, minute: 60, hour: 3600, day: 24*3600, week: 7*24*3600, month: 24*3600*30, quarter: 24*3600*30*3, year: 24*3600*365};
            var fromUnitDuration = durations[fromUnit], toUnitDuration = durations[toUnit];
            if (fromUnitDuration && toUnitDuration){
                return duration * fromUnitDuration / toUnitDuration;
            }else{
                return duration;
            } 
        }
    }
});
