define(["dojo", "dojo/_base/lang", "dojo/_base/Color", "dojo/date/stamp", "dojo/number", "dojo/currency", "dojo/json", "dojo/i18n!tukos/nls/messages"],
	function(dojo, lang, Color, stamp, number, currency, JSON, messages) {
		return {
			wasModified: false,
			previousUniqueIds: [],

			visualTag: function() {
				return '<span class="visualTag" style="background-color:lightgrey; font-size: 5px;">¤</span>';
			},
			sizeMultiplier: function(size, multiplier){
				let splittedSize;
				return Number(size) ? size * multiplier : (splittedSize = size.split('%'))[0] * multiplier + (splittedSize.length === 2 ? '%' : '');
			},
			trimExt: function(string) {
				return string.replace(/^[\s(&nbsp;)]+/g, '').replace(/[\s(&nbsp;)]+$/g, '');
			},
			capitalize: function(string) {
				return typeof string !== 'string' ? '' : (string.charAt(0).toUpperCase() + string.slice(1));
			},
			typeOf: function(item) {
				return item === null ? "null" : typeof item;
			},
			isObject: function(item) {
				return item !== null && typeof item === 'object';
			},
			newObj: function(sourceArray) {
				var result = {};
				sourceArray.forEach(function(item) {
					result[item[0]] = item[1];
				});
				return result;
			},
			setObject: function(path, value, obj) {
				var result = this.empty(obj) ? {} : obj;
				lang.setObject(typeof path === 'string' ? path : path.join('.'), value, result);
				return result;
			},
			empty: function(mixed) {
				if (!mixed) {
					return true;
				} else {
					switch (typeof(mixed)) {
						case "object":
							if (Array.isArray(mixed)) {
								return mixed.length === 0;
							} else {
								for (var key in mixed) {
									if (mixed.hasOwnProperty(key))
										return false;
								}
								return true;
							}
						default:
							return false;
					}
				}
			},
			isEquivalent: function(value1, value2) {
				const emptyValues = ['undefined', 'null', ''];
				return value1 === value2 || String(value1) === String(value2) || (this.in_array(String(value1), emptyValues) && this.in_array(String(value2), emptyValues));
				/*if (typeof value1 === "number" && typeof value2 === "number") {
					return value1 === value2;
				}else if (typeof value1 === "boolean" || typeof value2 === "boolean") {
					return value1 === value2;
				}else{
					return String(value1 || '') == String(value2 || '');
				}*/
			},
			forEach: function(object, callback, inherited) {
				if (object){
					switch (typeof(object)){
						case 'object':
							if (Array.isArray(object)){
								object.forEach(function(item, key, object){
									callback(item, key, object);
								});
							}else{
								if (inherited){
									for(const property in object){
										callback(object[property], property, object);
									}
								}else{
									for (const key of Object.keys(object)){
										callback(object[key], key, object);
									}
								}
							}
					}
				}
			},
			some: function(object, callback, inherited) {
				var result = false;
				if (object){
					switch (typeof(object)){
						case 'object':
							if (Array.isArray(object)){
								for (const key of object){
									if ((result = callback(object[key], key, object))) {
										return result;
									}
								}
							}else{
								if (inherited){
									for (const property in object){
										if ((result = callback(object[property], property, object))) {
											return result;
										}
									}
								}else{
									for (var key of Object.keys(object)){
										if ((result = callback(object[key], key, object))) {
											return result;
										}
									}					
								}
							} 		
					}
				}
				return result;
			},
			count: function(object) {
				var count = 0;
				this.forEach(object, function() {
					count += 1;
				});
				return count;
			},
			filter: function(object) {
				for (var key in object) {
					if (object.hasOwnProperty(key)) {
						var value = object[key];
						if (value === null || typeof value === "undefined" || value === '') {
							delete object[key];
						}
					}
				}
				return object;
			},
			filterRecursive: function(object, callback){//removes branches with empty leaves
				if (this.isObject(object)){
					const keys = Object.keys(object);
					for (var key of keys){
						if ((callback || this.empty)(object[key])){
								delete object[key];
							}else{
								this.filterRecursive(object[key]);
								if ((callback || this.empty)(object[key])){
									delete object[key];
								}
							}
					}
				}
				return object;
			},
			/*objectKeys: function(object) {
				var objectKeys = [];
				for (var key in object) {
					if (object.hasOwnProperty(key)) {
						objectKeys.push(key);
					}
				}
				return objectKeys;
			},*/
			object_search: function(needle, object) {
				for (var key in object) {
					if (object.hasOwnProperty(key) && object[key] === needle) {
						return key;
					}
				}
				return false;
			},
			drillDown: function(object, path, notFoundValue) {
				var pointer = object,
					property;
				while (path.length) {
					property = path.shift();
					if (!pointer.hasOwnProperty(property)) {
						return notFoundValue;
					} else {
						pointer = pointer[property];
					}
				}
				return pointer;
			},
			drillDownDelete: function(object, objectFilter){
				const self = this;
				let pointer = object;
				self.forEach(objectFilter, function(subFilter, key){
					if (pointer.hasOwnProperty(key)){
						if (subFilter === true){
							delete(pointer[key]);
						}else{
							self.drillDownDelete(pointer[key], subFilter);
							if (self.count(pointer[key]) === 0){
								delete pointer[key];
							}
						}
					}
				});
			},
			replace: function(comparisonOperator, arrayToSearch, searchProperty, searchValue, returnProperty, cache, ignoreCase, ignoreAccent) {
				var search = function(row) {
					var targetValue = String(row[searchProperty]),
						sourceValue = String(searchValue);
					if (ignoreCase) {
						targetValue = targetValue.toLowerCase();
						sourceValue = sourceValue.toLowerCase();
					}
					if (ignoreAccent) {
						targetValue = targetValue.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
						sourceValue = sourceValue.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
					}
					return comparisonOperator === 'find' ? targetValue === sourceValue : targetValue.includes(sourceValue);
				};
				return cache[searchValue] || ((cache[searchValue] = arrayToSearch.find(search)) === undefined ? (cache[searchValue] = searchValue) : (cache[searchValue] = cache[searchValue][returnProperty]));
			},
			findReplace: function(arrayToSearch, searchProperty, searchValue, returnProperty, cache, ignoreCase, ignoreAccent) {
				return this.replace('find', arrayToSearch, searchProperty, searchValue, returnProperty, cache, ignoreCase, ignoreAccent);
			},
			includesReplace: function(arrayToSearch, searchProperty, searchValue, returnProperty, cache, ignoreCase, ignoreAccent) {
				return this.replace('includes', arrayToSearch, searchProperty, searchValue, returnProperty, cache, ignoreCase, ignoreAccent);
			},
			objectContains: function(subObject, object) {
				for (var key in subObject) {
					if (subObject.hasOwnProperty(key) && subObject[key] != object[key]) {
						return false;
					}
				}
				return true;
			},

			in_array: function(needle, haystack /*, argStrict*/ ) {
				return haystack && haystack.indexOf(needle) > -1;
			},

			array_diff: function(array1, array2) {
				return array1.filter(function(elm) {
					return array2.indexOf(elm) === -1;
				});
			},
			flip: function(input) {
				var key, result = {};
				for (key in input) {
					if (input.hasOwnProperty(key)) {
						result[input[key]] = key;
					}
				}
				return result;
			},
			array_unique_push: function(newValue, array) {
				if (!this.in_array(newValue)) {
					array.push(newValue);
				}
			},
			array_unique_merge: function(array1, array2) {
				var self = this;
				array2.forEach(function(value) {
					if (self.in_array(value, array1)) {
						return;
					} else {
						array1.push(value);
					}
				});
				return array1;
			},
			array_unique: function(array) {
				return array.filter(function(value, index, self) {
					return self.indexOf(value) === index;
				});
			},
			toObject: function(theArray, theIndexKeyName, theTargetKeyName, deleteFlag, keepIndexKey){
				var theObject = {};
				if(theArray){
					theArray.forEach(function(item){
						theObject[item[theIndexKeyName]] = theTargetKeyName ? item[theTargetKeyName] : (deleteFlag && item['~deleted'] ? '~deleted' : item);
						if (!keepIndexKey){
							delete item[theIndexKeyName];
						}
					});
					return theObject;
				}else{
					return undefined;
				}
				
			},
			toNumeric: function(object, keyName, asString){
				const self = this, theResult = [];
				self.forEach(object, function(item, keyValue){
					let theItem = lang.clone(item);
					keyValue = asString ? keyValue.toString() : parseInt(keyValue);
					if (typeof theItem === 'object'){
						theItem[keyName] = keyValue;
					}else{
						theItem = self.newObj([[keyName, keyValue]])
					}
					theResult.push(theItem);
				});
				return theResult;
			},
			merge: function(target, source) { //Use the returned value to be sure to get the modified value in all cases
				this.wasModified = false;
				if (this.isObject(target) && this.isObject(source)) {
					for (var i in source) {
						if (target[i] !== source[i]) {
							this.wasModified = true;
							target[i] = source[i];
						}
					}
					return target;
				}
				if (source === target || source === undefined) {
					return target;
				} else {
					this.wasModified = true;
					return source;
				}
			},
			mergeRecursive: function(target, source, concatenateArrays) { //Use the returned value to be sure to get the modified value in all cases
				var self = this;
				this.wasModified = false;
				var mergeRecursive = function(target, source) {
					for (var i in source) {
						if (self.isObject(target[i]) && self.isObject(source[i])) {
							if (concatenateArrays && Array.isArray(target[i]) && Array.isArray(source[i])){
								target[i] = target[i].concat(source[i]);
							}else{
								mergeRecursive(target[i], source[i]);
							}
						} else {
							if (target[i] !== source[i]) {
								target[i] = source[i];
								self.wasModified = true;
							}
						}
					}
				};
				if (this.isObject(target) && this.isObject(source)) {
					mergeRecursive(target, source);
					return target;
				}
				if (source === target || source === undefined) {
					return target;
				} else {
					this.wasModified = true;
					return source;
				}
			},
			deleteRecursive: function(target, source) { // a "poor's man" approach to undo of mergeRecursive: deletes leaves that were modified by a mergeRecursive with the same target and source.
				var deleteRecursive = function(target, source) {
					for (var i in source) {
						if (this.isObject(target[i]) && typeof source[i] === 'object') {
							target[i] = deleteRecursive(target[i], source[i]);
						} else {
							delete(target[i]);
						}
					}
					return target;
				};
				if (typeof source === 'object') {
					return deleteRecursive(target, source);
				} else {
					return undefined;
				}
			},
			storeData: function(ids) {
				var store = [{
					id: '',
					name: ''
				}];
				ids.forEach(function(id) {
					store.push({
						id: id,
						name: id
					});
				});
				return store;
			},
			pad: function(number, size) {
				var result = String(number);
				while (result.length < size) {
					result = '0' + result;
				}
				return result;
			},
			transform: function(value, formatType, formatOptions, Pmg) {
				var seconds, minutes, hours;
				if ((typeof value == 'string' && value != '') || typeof value === 'number') {
					switch (formatType) {
						case 'string':
							break;
						case 'datetimestamp': // from yyyy-mm-ddThh:mm:ssZ to yyyy-mm-dd hh:mm:ss (server time format i.e. ISO to dojo widgets time format
							value = stamp.toISOString(dojo.date.stamp.fromISOString(value)).substring(0, 19);
							value = value.replace("T", " ");
							break;
						case 'datetime': // from yyyy-mm-ddThh:mm:ssZ to yyyy-mm-dd hh:mm:ss (server time format i.e. ISO to dojo widgets time format
							const isTime = value && value[0] === 'T';
							value = dojo.date.locale.format(stamp.fromISOString(value));
							if (isTime){
								value = value.substring(11);
							}
							break;
						case 'date':
							if (value.length > 10 && value[10] !== 'T') { //hack as we change from datetime to date for sptsessions. 
								value = value.substring(0, 10);
							}
							value = dojo.date.locale.format(stamp.fromISOString(value), formatOptions || {
								selector: 'date'
							});
							break;
						case 'minutesToHHMM':
							minutes = parseInt(value);
							return this.pad(parseInt(minutes / 60), 2) + ':' + this.pad(minutes % 60, 2);
						case 'minutesToHHMMSS':
							seconds = parseInt(value * 60);
							return this.pad(hours = parseInt(seconds / 3600), 2) + ':' + this.pad(parseInt((seconds - hours * 3600) / 60), 2) + ':' + this.pad(seconds % 60, 2);
						case 'secondsToHHMMSS':
							seconds = parseInt(value);
							return this.pad(hours = parseInt(seconds / 3600), 2) + ':' + this.pad(parseInt((seconds - hours * 3600) / 60), 2) + ':' + this.pad(seconds % 60, 2);
						case 'tHHMMSSToHHMM':
							return (value && value.substring) ? value.substring(1, 6) : value;
						case 'tHHMMSSToHHMMSS':
							return (value && value.substring) ? value.substring(1, 9) : value;
						case 'numberunit':
							var values = JSON.parse(value),
								count = values[0],
								unit = values[1],
								localUnit = messages[unit] || unit;
							value = count + ' ' + localUnit + (count > 1 ? ((formatOptions || {}).noPlural ? '' : 's') : '');
							break;
						case 'percent':
							value = (value == 0 ? '' : number.format(value * 100, {
								places: 2
							}) + '%');
							break;
						case 'currency':
							value = currency.format(value, formatOptions || {
								currency: 'EUR'
							});
							break;
						case 'image':
							value = '<img src="' + value + '" >';
							break;
						case 'number':
							value = number.format(value, formatOptions);
							break;
						case 'translate':
							if (formatOptions.translations){
								value = this.translate(value, formatOptions.translations);
							}else{
								value = Pmg.message(value, (formatOptions || {}).object);
							}
							break;
						default:
							if (!isNaN(value)) {
								if (value % 1 !== 0) {
									value = number.format(value, {
										places: 2
									});
								} else {
									value = value.toString();
								}
							}
					}
				}
				return value;
			},
			unTransform: function(value, formatType) { // from yyyy-mm-dd hh:mm:ss to yyyy-mm-ddThh:mm:ssZ
				if (typeof value == 'string' && value != '') {
					switch (formatType) {
						case 'datetimestamp': // from yyyy-mm-dd hh:mm:ss to yyyy-mm-ddThh:mm:ssZ
							value = value.replace(" ", "T");
							value = stamp.toISOString(stamp.fromISOString(value), {
								zulu: true
							});
							break;
						default:
							break;
					}
				}
				return value;
			},
			alphabet: function(position) { //from position in alphabet to 'C' (for position == 3) or 'AC' (for position == 29 = 26 + 3), etc.
				if (position > 26) {
					return String.fromCharCode(Math.trunc(position / 26) + 64, (position % 26) + 64);
				} else {
					return String.fromCharCode(position + 64);
				}
			},
			fromAlphabet: function(label) {
				var result = 0;
				for (var i = 0; i < label.length; i++) {
					result += label.charCodeAt(i) - 64;
				}
				return result;
			},
			join: function(objectOrString, valueSeparator, keyObjectSeparator) {
				if (objectOrString && typeof objectOrString === 'object') {
					valueSeparator = valueSeparator || '&';
					keyObjectSeparator = keyObjectSeparator || '=';
					var arrayValues = [];
					this.forEach(objectOrString, function(value, key) {
						arrayValues.push(key + keyObjectSeparator + (typeof value === 'object' ? JSON.stringify(value) : value));
					});
					return arrayValues.join(valueSeparator);
				} else if (typeof objectOrString === 'string') {
					return objectOrString;
				} else {
					return '';
				}
			},
			iterate: function(contributeFunction, initialResult, functionArguments) {
				var result = initialResult;
				for (let item of functionArguments){
					if (typeof item === 'object') {
						for (let value of item) {
							if (typeof value === "object") {
								result = this.iterate(contributeFunction, result, value);
							} else {
								result = contributeFunction(result, value);
							}
						}
					}else{
						result = contributeFunction(result, item);
					}					
				}
				return result;
			},
			sum: function(){
				const contributeFunction = function(result, value){
					value = parseFloat(value);
					result += isNaN(value) ? 0 : value;	
					return result;				
				}
				return this.iterate(contributeFunction, 0, arguments);
			},
			max: function(){
				const contributeFunction = function(result, value){
					return value > result ? value : result;				
				}
				return this.iterate(contributeFunction, Number.NEGATIVE_INFINITY, arguments);
			},
			min: function(){
				const contributeFunction = function(result, value){
					return value < result ? value : result;				
				}
				return this.iterate(contributeFunction, Number.POSITIVE_INFINITY, arguments);
			},
			hashId: function() { // see https://gist.github.com/fiznool/73ee9c7a11d1ff80b81c#file-hashid-js-L11
				var alphabet = '23456789abdegjkmnpqrvwxyz',
					alphabet_length = alphabet.length,
					id_length = 8,
					rtn = '';
				for (var i = 0; i < id_length; i++) {
					rtn += alphabet.charAt(Math.floor(Math.random() * alphabet_length));
				}
				return rtn;
			},
			uniqueId: function(previous) { // see https://gist.github.com/fiznool/73ee9c7a11d1ff80b81c#file-hashid-js-L11             
				var unique_retries = 9999, retries = 0, id, previousUniqueIds;
				previous = previous || previousUniqueIds;
				while (!id && retries < unique_retries) {
					id = this.hashId();
					if (previous.indexOf(id) !== -1) {
						id = null;
						retries++;
					}
				}
				return id;
			},
			inject: function(valueOrArrayToInject, intoArray, atIndex) {
				return intoArray.slice(0, atIndex).concat(valueOrArrayToInject).concat(intoArray.slice(atIndex));
			},
			debounce: function(func, wait, immediate, context) {
			    var result;
			    var timeout = null;
			    return function() {
			        var ctx = context || this, args = arguments;
			        var later = function() {
			            timeout = null;
			            if (!immediate) result = func.apply(ctx, args);
			        };
			        var callNow = immediate && !timeout;
			        // Tant que la fonction est appelée, on reset le timeout.
			        clearTimeout(timeout);
			        timeout = setTimeout(later, wait);
			        if (callNow) result = func.apply(ctx, args);
			        return result;
				}
			},
			throttle: function(func, wait, leading, trailing, context) {
			    var ctx, args, result;
			    var timeout = null;
			    var previous = 0;
			    var later = function() {
			        previous = new Date;
			        timeout = null;
			        result = func.apply(ctx, args);
			    };
			    return function() {
			        var now = new Date;
			        if (!previous && !leading) previous = now;
			        var remaining = wait - (now - previous);
			        ctx = context || this;
			        args = arguments;
			        // Si la période d'attente est écoulée
			        if (remaining <= 0) {
			            // Réinitialiser les compteurs
			            clearTimeout(timeout);
			            timeout = null;
			            // Enregistrer le moment du dernier appel
			            previous = now;
			            // Appeler la fonction
			            result = func.apply(ctx, args);
			        } else if (!timeout && trailing) {
			            // Sinon on s’endort pendant le temps restant
			            timeout = setTimeout(later, remaining);
			        }
			        return result;
			    };
			},
			waitUntil: function(untilCallback, actionCallback, delay) {
				if (untilCallback()) {
					actionCallback();
				} else {
					var waiter, wait = function() {
							if (untilCallback()) {
								clearInterval(waiter);
								actionCallback();
							}
						};
					waiter = setInterval(wait, delay);
				}
			},
	        viewInBrowserWindow: function(windowName, htmlContent, screenX, screenY, innerWidth, innerHeight){
	            const newWindow = window.open('', windowName, 'screenX=' + (screenX || 50) + ',screenY=' + (screenY || 50) + ',innerWidth=' + (innerWidth || 800) + ',innerHeight=' + (innerHeight || 800));
	            newWindow.document.write(htmlContent);
	            newWindow.document.close();
	            newWindow.tukos = window.tukos;
	            return newWindow;
	            //newWindow.focus();// in case the window already existed
	        },
	       putInCache: function(property, id, cache){//cache should be initialized as an object
				if (!cache[id]){
					cache[id] = [];
					cache[id].push(property);
				}else if(!cache[id].includes(property)){
					cache[id].push(property);
				}
				return 0;
			},
			valueToGradientColor: function(value, gradient){
				if (value >= 1){
					return gradient[1];
				}else{
					let length = gradient.length, i = 0, pValue = 1 - value;
					while (i < length){
						const right = gradient[i], rightColor = gradient[i+1], left = gradient[i+2], leftColor = gradient[i+3];
						if (pValue <= left){
							return Color.blendColors(Color.fromHex(leftColor), Color.fromHex(rightColor), (pValue - left) / (right - left)).toHex();
						}
						 i += 2;
					}
					return gradient[length-1];
				}
			},
	        widgetNumericValue: function(widgetType, value){
				switch(widgetType){
					case 'HorizontalLinearGauge': 
						const pValue = value ? JSON.parse(value) : value;
						return (pValue !== null && typeof pValue === 'object') ? (pValue.gauge || 0) : (pValue  || 0);
					default:
						return value;
				}
			},
	        toNumericValues: function(data, grid){
				let self = this, transformedData = [];
				data.forEach(function(row){
					let transformedRow = {};
					for (const column in row){
						transformedRow[column] = self.widgetNumericValue((grid.columns[column] || {}).editor, row[column]);
					}
					transformedData.push(transformedRow);
				});
				return transformedData;
			},
			selectedAction: function(sWidget, tWidget, newValue){
				if (newValue){
				    var grid = sWidget.parent, collection = grid.collection, idp = collection.idProperty, dirty = grid.dirty;
				    collection.fetchSync().forEach(function(item){
				        var idv = item[idp], dirtyItem = dirty[idv], sName = sWidget.widgetName;
				        if (((dirtyItem && dirtyItem.hasOwnProperty(sName) && (dirtyItem[sName]) || item[sName])) && tWidget.row.id != idv){
				            grid.updateDirty(idv, sName, false);
				        }
				    })
				}
			},
			escapeRegExp: function (string) {
    			return string.replace(/[/\-\\^$*+?.()|[\]{}]/g, '\\$&');
			},
			_translate : function(value, translations, mode/* 'translate' | 'untranslate' */){
				if (translations){
					let self = this, matchingTranslations = [];
					this.forEach(translations, function(translated, untranslated){
						if (translated){
							let source = (mode === 'translate' ? untranslated : translated), target = (mode === 'translate' ? translated : untranslated), sourceRegExp = new RegExp('(\\b|_)(' + self.escapeRegExp(source) + ')((?!\\w)|_)', 'g');
							if (sourceRegExp.test(value)){
	                        	matchingTranslations.push([source, sourceRegExp, target]);
							}
						}
					});
					if (matchingTranslations.length <= 0){
						return value;
					}else if(matchingTranslations.length === 1){
						const [source, sourceRegExp, target] = matchingTranslations[0];
						return value.replaceAll(sourceRegExp, function(match, p1, p2, p3){
								return p1 + target + p3;
							});
					}else{
						matchingTranslations.sort(function(a, b){
							return b[0].length - a[0].length;
						});
						matchingTranslations.forEach(function(translation){
							const [source, sourceRegExp, target] = translation;
							value = value.replaceAll(sourceRegExp, function(match, p1, p2, p3){
								return p1 + target + p3;
							});
						});
						return value;
					}
				}else{
					return value;
				}
			},
			translate: function(value, translations){
				return this._translate(value, translations, 'translate');
			},
			untranslate: function(value, translations){
				return this._translate(value, translations, 'untranslate');
			}
		};
	});