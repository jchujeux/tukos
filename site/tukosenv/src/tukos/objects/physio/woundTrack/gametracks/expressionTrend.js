"use strict";
define(['tukos/ExpressionParser', 'tukos/dateutils', 'tukos/evalutils'], function (parser, dutils, eutils) {
    let x;
    const unpackArgs = function(f){
		return function(expr){
			let result = expr();
			if (!(0, parser.isArgumentsArray)(result)){
				if (f.length > 1){
                	throw new Error(`Too few arguments. Expected ${f.length}, found 1 (${JSON.stringify(result)})`);
				}
				return f(function(){return result;});
			}else if(result.length === f.length || f.length === 0){
				return f.apply(null, result);
			}else{
            	throw new Error(`Incorrect number of arguments. Expected ${f.length}`);
			}
		}
	};
	const kpiLanguage = function (items, cache, idp) {
		const formulaCache = {}, 
			  getFormula = function(arg){
				if (formulaCache[arg]){
					return formulaCache[arg];
				}else{
					const formulaString = arg.replaceAll(/[$](\w+)/g, '(!Number.isNaN(x=Number(item.$1)) ? x : (item.$1 === undefined ? utils.putInCache("$1", item[idp], cache)  : dutils.timeToSeconds(item.$1)))');
					return formulaCache[arg] = eutils.eval('return ' + formulaString + ';', 'item, cache, x');
				}
			},
			formulaSum = function(arg){
				const formula = getFormula(arg);
				let result = 0;
				items.forEach(function(item){
					const value = formula(item, cache, x);
					if (!isNaN(value)){
						result += value;
					}
				});
				return result;
			},
			formulaExpAvg = function(arg, initialAvg, initialDate, daysConstant){
				const formula = getFormula(arg, cache);
				let average = Number(initialAvg), previousDate = initialDate, dailyDecay = Math.exp(-1/daysConstant);
				items.forEach(function(item){
					const value = formula(item, cache, x);
					average =  (isNaN(value) ? 0 : value) * (1 - dailyDecay) + average * Math.pow(dailyDecay, dutils.difference(previousDate, item.startdate));
					previousDate = item.startdate
				});
				return kpiDate && kpiDate !== previousDate ? average * Math.pow(dailyDecay, dutils.difference(previousDate, kpiDate)) : average;
			},
			formulaMin = function(arg){
				const formula = getFormula(arg, cache);
				let result = 0;
				items.forEach(function(item){
					const value = Math.min(result, formula(item, cache, x));
					if (!isNaN(value)){
						result = value;
					}
				});
				return result;
			},
			formulaMax = function(arg){
				const formula = getFormula(arg);
				let result = 0;
				items.forEach(function(item){
					const value = Math.max(result, formula(item, cache, x));
					if (!isNaN(value)){
						result = value;
					}
				});
				return result;
			},
			formulaLast = function(arg){
				return getFormula(arg)(items[items.length-1], cache, x);
			},
			formulaSession = function(arg, index){
				const formula = getFormula(arg);
				return formula(items[index], cache, x);
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
					return x ? x / y : 0;
				},
	            ",": (a, b) => {
	                const aVal = a();
	                const aArr = (0, parser.isArgumentsArray)(aVal)
	                    ? aVal
	                    : [() => aVal];
	                const args = aArr.concat([b]);
	                args.isArgumentsArray = true;
	                return args;
	            }			},
			PREFIX_OPS: {
				'ARRAY': function(a){
					return a;
				},
				'NEG':  unpackArgs(function(a){
					return - a();
				}),
				'SUM': function(col){
					return formulaSum(col());
				},
				'AVG': function(col){
					
					return items.length > 0 ? formulaSum(col()) / items.length : 0;
				},
				'DAILYAVG': function(col){
					return formulaSum(col()) / durationDays;
				},
				'EXPAVG': unpackArgs(function(col, initialAvg, initialDate, daysConstant){
					return formulaExpAvg(col(), initialAvg(), initialDate(), daysConstant());
				}),
				'MIN': function(col){
					return formulaMin(col());
				},
				'MAX': function(col){
					return formulaMax(col());
				},
				'LAST': function(col){
					return formulaLast(col());
				},
				'SESSION': unpackArgs(function(col, index){
					return formulaSession(col(), index());
				})
			},
			PRECEDENCE:[['ARRAY', 'NEG', 'SUM', 'AVG', 'EXPAVG', 'MIN', 'MAX', 'item'], ['*', '/'], ['+', '-'], [',']],
			LITERAL_OPEN: '"',
			LITERAL_CLOSE: '"',
			GROUP_OPEN: '(',
			GROUP_CLOSE: ')',
			SEPARATOR: ' ',
			SYMBOLS: ['*', '/', '+', '-', '(', ')', '[', ']', ','],
			AMBIGUOUS: {'-': 'NEG'},
            SURROUNDING: {
                ARRAY: {
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
		expression: function(itemsAscendingStartDateArray, missingColsCache){
			return new parser.default(kpiLanguage(itemsAscendingStartDateArray, missingColsCache, idp));
		}
	};
});
