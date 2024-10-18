"use strict";
define(['tukos/ExpressionParser', 'tukos/utils', 'tukos/dateutils', 'tukos/evalutils'], function (parser, utils, dutils, eutils) {
    let x, _lastNaNtoSecondsInput, _lastNaNtoSecondsReturned;
    const unpackArgs = function(f){
		return function(expr){
			let result = expr();
			return (0, parser.isArgumentsArray)(result) ? f.apply(null, result) : f(function(){return result;});
		}
	};
	const _operation = function(a, b, operation){
		const x = a(), y = b();
		if (Array.isArray(x)){
			if (Array.isArray(y)){
				let result = [];
				for (let i in x){
					if (Array.isArray(x[i])){
						result.push([x[i][0], operation(x[i][1], y[i][1])]);
					}else{
						result.push(operation(x[i], y[i]));
					}
				}
				return result;
			}else{
				let result = [];
				for (let i in x){
					if (Array.isArray(x[i])){// assumes it is [a,b] and divider should apply to b 
						result.push([x[i][0], operation(x[i][1], y)]);
					}else{
						result.push(operation(x[i], y));
					}
				}
				return result;
			}
		}else{
			if (Array.isArray(y)){
				let result = [];
				for (let i in y){
					result.push(operation(x, y[i]));
				}
				return result;
			}else{
				return operation(x,y);
			}
		}
	};
	const kpiLanguage = function (items, idProperty, cache, valueOf, previousKpiValuesCache, previousItems, kpiDate) {
		const getValueOf = function(value){
			return dojo.isString(value) && value[0] === '@' ? valueOf(value.substring(1)): value;
		}
		const nanToSecondsOrZero = function(value){
			return value === _lastNaNtoSecondsInput ? _lastNaNtoSecondsReturned : _lastNaNtoSecondsReturned = (isNaN(value) ? (typeof value === 'string' ? dutils.timeToSeconds(value) : 0) : value);
		}
		const formulaCache = {}, 
			  getFormula = function(arg){
				if (formulaCache[arg]){
					return formulaCache[arg];
				}else{
					let  formulaString = arg.replaceAll(/[$]((?:\w|_)+)/g, '(!Number.isNaN(x=Number(item.$1)) ? x : (item.$1 === undefined ? utils.putInCache("$1", item.' + idProperty + ', cache)  : item.$1))');
					/*
					* here, if we finc @xxx inside formulaString
					*/
					formulaString = formulaString.replace(/@(\w+)/g, function(match, p1){
						return valueOf(p1);
					});
					return formulaCache[arg] = eutils.eval('return ' + formulaString + ';', 'item, cache, ' + idProperty + ', x');
				}
			},
			formulaVector = function(arg){
				const formula = getFormula(arg);
				let result = [];
				items.forEach(function(item){
					result.push(formula(item, cache, idProperty, x));
				});
				return result;
			},
			formulaSum = function(arg){
				const formula = getFormula(arg);
				let result = 0;
				items.forEach(function(item){
					result += nanToSecondsOrZero(formula(item, cache, idProperty, x));
				});
				return result;
			},
			formulaExpAvg = function(arg, initialAvg, initialDate, daysConstant, kpiItemCol){
				const formula = getFormula(arg), dailyDecay = Math.exp(-1/getValueOf(daysConstant));
				let average, previousDate;
				if (!previousKpiValuesCache[arg + daysConstant]){
					average = Number(getValueOf(initialAvg)); previousDate = getValueOf(initialDate);
					previousItems.forEach(function(item){
						average =  nanToSecondsOrZero(formula(item, cache, idProperty, x)) * (1 - dailyDecay) + average * Math.pow(dailyDecay, dutils.difference(previousDate, item.startdate));
						previousDate = item.startdate
						if (kpiItemCol){
							item[kpiItemCol] = average;
						}
					});
				}else{
					[average, previousDate] = previousKpiValuesCache[arg + daysConstant];
				}
				if (previousDate === kpiDate){
					return average;
				}else{
					items.forEach(function(item){
						//if (previousDate < item.startdate){
							average =  nanToSecondsOrZero(formula(item, cache, idProperty, x)) * (1 - dailyDecay) + average * Math.pow(dailyDecay, dutils.difference(previousDate, item.startdate));
							previousDate = item.startdate
							if (kpiItemCol){
								item[kpiItemCol] = average;
							}
						//}
					});
					const kpiValue = kpiDate && kpiDate !== previousDate ? average * Math.pow(dailyDecay, dutils.difference(previousDate, kpiDate)) : average;
					previousKpiValuesCache[arg + daysConstant] = [kpiValue, kpiDate ? kpiDate : previousDate];
					return kpiValue;
				}
			},
			formulaExpIntensity = function(arg, min, max, a){
				const valueToIntensity = function(value){
					const rate = (value - min) / (max - min);
					return rate * Math.exp(a * (rate - 1)) * 100;
				}
				if (Array.isArray(arg)){
					let result = [];
					for (let i in arg){
						const entityItem = arg[i];
						if (Array.isArray(entityItem)){
							result.push([entityItem[0], valueToIntensity(entityItem[1])]);
						}else{
							result.push(valueToIntensity(entityItem));
						}
					}
					return result;
				}else{
					return valueToIntensity(arg);
				}
			},
			formulaMin = function(arg){
				const formula = getFormula(arg, cache);
				let result = MAX_VALUE;
				items.forEach(function(item){
					result = Math.min(result, nanToSecondsOrZero(formula(item, cache, idProperty, x)));
				});
				return result;
			},
			formulaMax = function(arg){
				const formula = getFormula(arg);
				let result = MIN_VALUE;
				items.forEach(function(item){
					result = Math.max(result, nanToSecondsOrZero(formula(item, cache, idProperty, x)));
				});
				return result;
			},
			formulaFirst = function(arg){
				return items.length ? getFormula(arg)(items[0], cache, idProperty, x) : undefined;
			},
			formulaLast = function(arg){
				return items.length ? getFormula(arg)(items[items.length-1], cache, idProperty, x) : undefined;
			},
			formulaItem = function(arg, index){
				const formula = getFormula(arg);
				if (items.length){
					const targetIndex = index < 0 ? items.length+index -1 : index - 1;
					return items[targetIndex] ? formula(items[targetIndex], cache, idProperty, x) : undefined;
				}else{
					return undefined;
				}
			},
			formulaTimeToSeconds = function(timeString){//if timeString is a number, we assume it is seconds, so no need to convert
				return timeString && isNaN(timeString) ? dutils.timeToSeconds(timeString) : timeString;
			},
			formulaDate = function(formulaString){
				return dutils.formulaStringToDate(formulaString, valueOf);
			};
		return {
			INFIX_OPS: {
				'+': function(a, b){
					return _operation(a, b, function(x, y){
						return x + y;
					});
				},
				'-': function(a, b){
					return _operation(a, b, function(x, y){
						return x - y;
					});
				},
				'*': function(a, b){
					return _operation(a, b, function(x, y){
						return x * y;
					});
				},
				'/': function(a,b){
					return _operation(a,b,function(x,y){
						const divider = nanToSecondsOrZero(y);
						return (divider ? nanToSecondsOrZero(x) / divider : 0);
					});
				},
	            ",": (a, b) => {
	                const aVal = a();
	                const aArr = (0, parser.isArgumentsArray)(aVal)
	                    ? aVal
	                    : [() => aVal];
	                const args = aArr.concat([b]);
	                args.isArgumentsArray = true;
	                return args;
	            }
			},
			PREFIX_OPS: {
				'ARRAY': function(a){
					return a;
				},
				'NEG':  unpackArgs(function(a){
					return - a();
				}),
				'TOFIXED': unpackArgs(function(a, digits){
					let x = a(), dg = digits();
					if (Array.isArray(x)){
						for(let i in x){
							if (Array.isArray(x[i])){// assumes it is [a,b] and divider should apply to b 
								x[i][1] = Number(nanToSecondsOrZero(x[i][1]).toFixed(dg));
							}else{
								x[i] = Number(nanToSecondsOrZero(x[i]).toFixed(dg));
							}
						}
						return x;
					}else{
						return x === undefined ? '' : Number(nanToSecondsOrZero(a()).toFixed(dg));
					}
				}),
				'JSONPARSE': function(a){
					return JSON.parse(a());
				},
				'VECTOR': function(col){
					return formulaVector(col());
				},
				XY: unpackArgs(function(a,b, c){
					const aValue = a(), x = typeof aValue === "string" ? JSON.parse(aValue) : aValue;
					if (!b){
						return x;
					}
					const bValue = b(), y = typeof bValue === "string" ? JSON.parse(bValue) : bValue;
					const cValue = !c ? undefined : c(), z = utils.in_array(cValue, [undefined, 'index', 'xValue', 'xTime']) ? null : (typeof cValue === 'string' ? JSON.parse(cValue) : cValue);
					let thePoint;
					if (Array.isArray(x[0])){
						switch(cValue){
							case undefined:
								thePoint = function(i, x, y){
									return [x[i][1], y[i][1]];
								};
								break;
							case 'index':
								thePoint = function(i, x, y, z){
									return [x[i][1], y[i][1], i];
								}
								break;
							case 'xValue':
								thePoint = function(i, x, y){
									return [x[i][1], y[i][1], x[i][0]];
								}
								break;
							case 'xTime':
								thePoint = function(i, x, y){
									return [x[i][1], y[i][1], dutils.secondsToTime(x[i][0]).substring(1)];
								}
								break;
							default:
								thePoint = function(i, x, y, z){
									return [x[i][1], y[i][1], z[i][1]];
								}
						}
					}else{
						switch(cValue){
							case undefined:
								thePoint = function(i, x, y){
									return [x[i], y[i]];
								};
								break;
							case 'index':
								thePoint = function(i, x, y, z){
									return [x[i], y[i], i];
								}
								break;
							case 'xValue'://should not happen, fallback to undefined
							case 'xTime':
								thePoint = function(i, x, y){
									return [x[i], y[i]];
								}
								break;
							default:
								thePoint = function(i, x, y, z){
									return [x[i], y[i], z[i]];
								}
						}
					}

					let result = [];
					for (let i in x){
						result.push(thePoint(i, x, y, z));
					}
					return result;
				}),
				'SUM': function(col){
					return formulaSum(col());
				},
				'AVG': function(col){					
					return items.length > 0 ? formulaSum(col()) / items.length : 0;
				},
				'DAILYAVG': unpackArgs(function(col, durationDays){
					return formulaSum(col()) / durationDays;
				}),
				'EXPAVG': unpackArgs(function(col, initialAvg, initialDate, daysConstant, kpiItemCol){
					return formulaExpAvg(col(), initialAvg(), initialDate(), daysConstant(), kpiItemCol ? kpiItemCol() : undefined);
				}),
				'EXPINTENSITY': unpackArgs(function(col, min, max, a){
					return formulaExpIntensity(col(), min(), max(), a());
				}),
				'MIN': function(col){
					return formulaMin(col());
				},
				'MAX': function(col){
					return formulaMax(col());
				},
				'FIRST': function(col){
					return formulaFirst(col());
				},
				'LAST': function(col){
					return formulaLast(col());
				},
				'ITEM': unpackArgs(function(col, index){
					return formulaItem(col(), index());
				}),
				'TIMETOSECONDS': function(timeString){
					return formulaTimeToSeconds(timeString());
				},
				'DATE': function(formulaString){
					return formulaDate(formulaString());
				}
			},
			PRECEDENCE:[['ARRAY', 'VECTOR', 'NEG', 'TOFIXED', 'JSONPARSE', 'XY', 'SUM', 'AVG', 'EXPINTENSITY', 'EXPAVG', 'MIN', 'MAX', 'FIRST', 'LAST', 'ITEM', 'TIMETOSECONDS', 'DATE'], ['*', '/'], ['+', '-'], [',']],
			LITERAL_OPEN: '"',
			LITERAL_CLOSE: '"',
			GROUP_OPEN: '(',
			GROUP_CLOSE: ')',
			SEPARATOR: ' ',
			SYMBOLS: ['*', '/', '+', '-', '(', ')', '[', ']', ','],
			AMBIGUOUS: {'-': 'NEG'},
            SURROUNDING: {
                XARRAY: {
                    OPEN: "[",
                    CLOSE: "]",
                },
            },
			termDelegate: function(term){
				switch (term){
					default:
						return term;
				}
			}
		}
	};

	return {
		expression: function(itemsAscendingStartDateArray, idProperty, missingColsCache, valueOf, previousKpiValuesCache, previousItems, kpiDate){
			return new parser.default(kpiLanguage(itemsAscendingStartDateArray, idProperty, missingColsCache, valueOf, previousKpiValuesCache, previousItems, kpiDate));
		}
	};
});
