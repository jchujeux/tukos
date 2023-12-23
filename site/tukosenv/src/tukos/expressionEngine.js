"use strict";
define(['tukos/ExpressionParser', 'tukos/utils', 'tukos/dateutils', 'tukos/evalutils'], function (parser, utils, dutils, eutils) {
    let x;
    const unpackArgs = function(f){
		return function(expr){
			let result = expr();
			return (0, parser.isArgumentsArray)(result) ? f.apply(null, result) : f(function(){return result;});
		}
	};
	const kpiLanguage = function (items, idProperty, cache, valueOf, previousKpiValuesCache, previousItems, kpiDate) {
		const getValueOf = function(value){
			return dojo.isString(value) && value[0] === '@' ? valueOf(value.substring(1)): value;
		}
		const nanToSecondsOrZero = function(value){
			return isNaN(value) ? (typeof value === 'string' ? dutils.timeToSeconds(value) : 0) : value;
		}
		const formulaCache = {}, 
			  getFormula = function(arg){
				if (formulaCache[arg]){
					return formulaCache[arg];
				}else{
					const formulaString = arg.replaceAll(/[$]((?:\w|_)+)/g, '(!Number.isNaN(x=Number(item.$1)) ? x : (item.$1 === undefined ? utils.putInCache("$1", item.' + idProperty + ', cache)  : item.$1))');
					return formulaCache[arg] = eutils.eval('return ' + formulaString + ';', 'item, cache, ' + idProperty + ', x');
				}
			},
			formulaVector = function(arg){
				const formula = getFormula(arg);
				let result = [];
				items.forEach(function(item){
					result.push(formula(item));
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
			formulaExpAvg = function(arg, initialAvg, initialDate, daysConstant){
				const formula = getFormula(arg), dailyDecay = Math.exp(-1/getValueOf(daysConstant));
				let average, previousDate;
				if (!previousKpiValuesCache[arg + daysConstant]){
					average = Number(getValueOf(initialAvg)); previousDate = getValueOf(initialDate);
					previousItems.forEach(function(item){
						average =  nanToSecondsOrZero(formula(item, cache, idProperty, x)) * (1 - dailyDecay) + average * Math.pow(dailyDecay, dutils.difference(previousDate, item.startdate));
						previousDate = item.startdate
					});
				}else{
					[average, previousDate] = previousKpiValuesCache[arg + daysConstant];
				}
				items.forEach(function(item){
					if (previousDate < item.startdate){
						average =  nanToSecondsOrZero(formula(item, cache, idProperty, x)) * (1 - dailyDecay) + average * Math.pow(dailyDecay, dutils.difference(previousDate, item.startdate));
						previousDate = item.startdate
					}
				});
				const kpiValue = kpiDate && kpiDate !== previousDate ? average * Math.pow(dailyDecay, dutils.difference(previousDate, kpiDate)) : average;
				previousKpiValuesCache[arg + daysConstant] = [kpiValue, kpiDate ? kpiDate : previousDate];
				return kpiValue;
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
				return items.length ? getFormula(arg)(items[0], cache, x) : undefined;
			},
			formulaLast = function(arg){
				return items.length ? getFormula(arg)(items[items.length-1], cache, x) : undefined;
			},
			formulaItem = function(arg, index){
				const formula = getFormula(arg);
				if (items.length){
					const targetIndex = index < 0 ? items.length+index -1 : index - 1;
					return items[targetIndex] ? formula(items[targetIndex], cache, x) : undefined;
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
					return a() + b();
				},
				'-': function(a, b){
					return a() - b();
				},
				'*': function(a, b){
					return a() * b();
				},
				'/': function(a, b){
					const x = a(), y = b();
					if (Array.isArray(x)){
						if (Array.isArray(y)){
							let result = [];
							for (let i in x){
								result.push(y[i] ? x[i] / y[i] : 0);
							}
							return result;
						}else{
							let result = [];
							for (let i in x){
								result.push(y ? x[i] / y : 0);
							}
							return result;
						}
					}else{
						if (Array.isArray(y)){
							let result = [];
							for (let i in y){
								result.push(y[i] ? x / y[i] : 0);
							}
							return result;
						}else{
							return y ? x / y : 0;
						}
					}
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
							x[i] = Number(x[i].toFixed(dg));
						}
						return x;
					}else{
						return Number(a().toFixed(dg));
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
				'EXPAVG': unpackArgs(function(col, initialAvg, initialDate, daysConstant){
					return formulaExpAvg(col(), initialAvg(), initialDate(), daysConstant());
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
			PRECEDENCE:[['ARRAY', 'VECTOR', 'NEG', 'TOFIXED', 'JSONPARSE', 'XY', 'SUM', 'AVG', 'EXPAVG', 'MIN', 'MAX', 'FIRST', 'LAST', 'ITEM', 'TIMETOSECONDS', 'DATE'], ['*', '/'], ['+', '-'], [',']],
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
