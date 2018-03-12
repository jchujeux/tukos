define(["dojo", "dojo/number", "dojo/currency", "dojo/json", "dojo/i18n!tukos/nls/messages"], function(dojo, number, currency, JSON, messages){
    return {
        wasModified: false, previousUniqueIds: [],
        
        newObj: function(sourceArray){
        	var result = {};
        	sourceArray.forEach(function(item){
        		result[item[0]] = item[1];
        	});
        	return result;
        },
        
        empty: function(obj) {
            for(var key in obj) {
                if(obj.hasOwnProperty(key))
                    return false;
            }
            return true;
        },

        forEach: function(object, callback){
            for (var key in object){
                if (object.hasOwnProperty(key)){
                    callback(object[key], key, object);
                }
            }
        },
        
        assign: function(object, property, value){
            object[property] = value;
            return object;
        },
        
        filter: function(object){
            for (var key in object){
                if (object.hasOwnProperty(key)){
                    var value = object[key];
                	if (value === null || typeof value === "undefined" || value === ''){
                		delete object[key];
                	};
                }
            }
            return object;
        },

        objectKeys: function(object){
            var objectKeys = [];
            for (var key in object){
                if (object.hasOwnProperty(key)){
                    objectKeys.push(key);
                }
            }
            return objectKeys;
        },

        object_search: function(needle, object){
            for (var key in object){
                if (object.hasOwnProperty(key) && object[key] === 'needle'){
                    return key;
                }
            }
            return false;
        },
        
        find: function (arrayToSearch, searchProperty, searchValue, returnProperty, cache){
        	var search = function (row){
        		return String(row[searchProperty]) === String(searchValue);
        	};
        	return cache[searchValue] || (cache[searchValue] = ((arrayToSearch.find(search)|| [])[returnProperty] || searchValue));       		
        },
        
        objectContains: function(subObject, object){
        	for (var key in subObject){
        		if (subObject.hasOwnProperty(key) && subObject[key] != object[key]){
        			return false;
        		}
        	}
        	return true;
        },

        in_array: function(needle, haystack/*, argStrict*/) {
          //  discuss at: http://phpjs.org/functions/in_array/
          // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
          // improved by: vlado houba
          // improved by: Jonas Sciangula Street (Joni2Back)
        
          return haystack && haystack.indexOf(needle) > -1;
/*
        	var key = '',
            strict = !! argStrict;
        
          //we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] == ndl)
          //in just one for, in order to improve the performance 
          //deciding wich type of comparation will do before walk array
          if (strict) {
            for (var key in haystack) {
              if (haystack[key] === needle) {
                return true;
              }
            }
          } else {
            for (var key in haystack) {
              if (haystack[key] == needle) {
                return true;
              }
            }
          }        
          return false;
*/
        },
        
        array_diff: function(array1, array2) {
          return array1.filter(function(elm) {
            return array2.indexOf(elm) === -1;
          })
        },
        
        array_flip: function(input){
        	var key, result = {};
        	for (key in input){
        		if (input.hasOwnProperty(key)){
        			result[input[key]] = key;
        		}
        	}
        	return result;
        },

        array_unique_merge: function(array1, array2){
            array2.forEach(function(value){
                if (this.in_array(array1, value)){
                    return;
                }else{
                    array1.push(value);
                }
            });
            return array1;
        },
        
        merge: function (target, source){//Use the returned value to be sure to get the modified value in all cases
            this.wasModified = false;
            if (typeof target === 'object' && typeof source === 'object'){
                for (var i in source){
                    if (target[i] !== source[i]){
                        this.wasModified = true;
                        target[i] = source[i];
                    }
                }
                return target;
            }
            if (source === target){
                return target;
            }else{
                this.wasModified = true;
                return source;
            }
        }, 

        mergeRecursive: function (target, source){//Use the returned value to be sure to get the modified value in all cases
            var self = this;
            this.wasModified = false;
            var mergeRecursive = function(target, source){
                for (var i in source){    
                    if (typeof target[i] === 'object' && typeof source[i] === 'object'){
                        mergeRecursive(target[i], source[i]);
                    }else{
                        if (target[i] !== source[i]){
                            target[i] = source[i];
                            self.wasModified = true;
                        }
                    }
                }
            }
            if (typeof source === 'object' && typeof target === 'object'){
                mergeRecursive(target, source);
               return target;
            }
            if (source === target){
                return target;
            }else{
                this.wasModified = true;
                return source;
            }
        }, 

        deleteRecursive: function(target, source){// a "poor's man" approach to undo of mergeRecursive: deletes leaves that were modified by a mergeRecursive with the same target and source.
            var deleteRecursive = function(target, source){
                for (var i in source){    
                    if (target[i] && typeof target[i] === 'object' && typeof source[i] === 'object'){
                        target[i] = deleteRecursive(target[i], source[i]);
                    }else{
                        delete(target[i]);
                    }
                }
                return target;
            }
            if (typeof source === 'object'){
                return deleteRecursive(target, source);
            }else{
                return undefined;
            }
        }, 

        transform: function(value, formatType, formatOptions){
            if ((typeof value == 'string' && value != '') || typeof value === 'number'){
                switch (formatType){
                    case 'datetimestamp': // from yyyy-mm-ddThh:mm:ssZ to yyyy-mm-dd hh:mm:ss (server time format i.e. ISO to dojo widgets time format
                        value = dojo.date.stamp.toISOString(dojo.date.stamp.fromISOString(value)).substring(0,19);
                        value = value.replace("T", " ");
                        break;
                    case 'datetime': // from yyyy-mm-ddThh:mm:ssZ to yyyy-mm-dd hh:mm:ss (server time format i.e. ISO to dojo widgets time format
                        value = dojo.date.locale.format(dojo.date.stamp.fromISOString(value));
                        break;
                    case 'date':
                        if (value.length > 10 && value[10] !== 'T'){//hack as we change from datetime to date for sptsessions. 
                            value = value.substring(0, 10);
                        }
                        value = dojo.date.locale.format(dojo.date.stamp.fromISOString(value), {selector: 'date'} );
                        break;
                    case 'numberunit':
                     	var values = JSON.parse(value), count = values[0], unit=values[1], localUnit = messages[unit] || unit;
                        value = count + ' ' + localUnit + (count > 1 ? 's' : '');
                        break;
                    case 'percent' : 
                        value =  (value == 0 ? '' : number.format(value*100,{places: 2}) + '%');
                        break;
                    case 'currency' : 
                        value = currency.format(value, formatOptions || {currency: 'EUR'});
                    default: 
                        if (!isNaN(value)){
                            if (value % 1 !== 0){
                                value = number.format(value, {places: 2});
                            }
                        }
                }
            }
            return value;
        },
        
        unTransform: function(value, formatType){// from yyyy-mm-dd hh:mm:ss to yyyy-mm-ddThh:mm:ssZ
            if (typeof value == 'string' && value != ''){
                switch (formatType){
                    case 'datetimestamp': // from yyyy-mm-dd hh:mm:ss to yyyy-mm-ddThh:mm:ssZ
                        value = value.replace(" ", "T");
                        value = dojo.date.stamp.toISOString(dojo.date.stamp.fromISOString(value), {zulu: true});
                        break;
                    default:
                        break;
                }
            }
            return value;
        }, 

        join: function(objectOrString, valueSeparator, keyObjectSeparator){
            if (objectOrString && typeof objectOrString === 'object'){
                valueSeparator = valueSeparator || '&';
                keyObjectSeparator = keyObjectSeparator || '=';
                var arrayValues = [];
                this.forEach(objectOrString, function(value, key){
                    arrayValues.push(key + keyObjectSeparator + (typeof value === 'object' ? JSON.stringify(value) : value));
                });
/*
                for (var key in objectOrString){
                    arrayValues.push(key + keyObjectSeparator + (typeof objectOrString[key] === 'object' ? JSON.stringify(objectOrString[key]) : objectOrString[key]));
                }
*/
                return arrayValues.join(valueSeparator);
            }else if (typeof objectOrString === 'string'){
                return objectOrString;
            }else{
                return '';
            }
        },

        sum: function(arrayOrObject){
            if (typeof arrayOrObject === 'object'){
                var result = 0;
                for (var i in arrayOrObject){
                    var value = arrayOrObject[i];
                    if (typeof value === "object"){
                        result += this.sum(value);
                    }else{                     
                        value = parseFloat(value);
                        result += isNaN(value) ? 0 : value;
                    }
                }
                return result;
            }else{
                value = parseFloat(value);
                result += isNaN(value) ? 0 : value;
            }
        },
        
        hashId: function(){// see https://gist.github.com/fiznool/73ee9c7a11d1ff80b81c#file-hashid-js-L11
          var alphabet= '23456789abdegjkmnpqrvwxyz', alphabet_length = alphabet.length, id_length = 8, rtn = '';
          for (var i = 0; i < id_length; i++) {
            rtn += alphabet.charAt(Math.floor(Math.random() * alphabet_length));
          }
          return rtn;
        },
        
        uniqueId: function(previous){// see https://gist.github.com/fiznool/73ee9c7a11d1ff80b81c#file-hashid-js-L11             
            var unique_retries = 9999, retries = 0, id;
            previous = previous || previousUniqueIds;
            while(!id && retries < unique_retries) {
                id = this.hashId();
                if(previous.indexOf(id) !== -1) {
                  id = null;
                  retries++;
                }
            }
            return id;
        },
        
        inject: function(valueOrArrayToInject, intoArray, atIndex){
            return intoArray.slice(0, atIndex).concat(valueOrArrayToInject).concat(intoArray.slice(atIndex));
        },
        
        debounce: function(func, wait, immediate) {
        	var timeout;
        	return function(){
        		var context = this, args = arguments;
        		var later = function() {
        			timeout = null;
        			if (!immediate) func.apply(context, args);
        		};
        		var callNow = immediate && !timeout;
        		clearTimeout(timeout);
        		timeout = setTimeout(later, wait);
        		if (callNow) func.apply(context, args);
        	};
        },

        throttle: function(func, limit) {
        	var wait = false;
        	return function(){
        		if (!wait) {
        			func.apply(this, arguments);
        			wait = true;
        			setTimeout(function(){wait = false;}, limit);
        	    }
        	  }
        }
    }
});
