define(['tukos/ExpressionParser', 'tukos/dateutils'], function (parser, dutils) {

    var unpackArgs = function(f){
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
		};
	};
	var filterLanguage =function (filter) {
		return {
			INFIX_OPS: {
				'+': function(a, b){
					return a + b;
				},
				'-': function(a, b){
					return a - b;
				},
				'>': function(a, b){
					return filter.gt(a, b);
				},
				'>=': function(a, b){
					return filter.gte(a, b);
				},
				'<': function(a, b){
					return filter.lt(a, b);
				},
				'<=': function(a, b){
					return filter.lte(a, b);
				},
				'=': function(a, b){
					return filter.eq(a, b);
				},
				'<>': function(a, b){
					return filter.ne(a, b);
				},
				'IN': function(a, b){
					return filter.in(a, b);
				},
				'RLIKE': function(a, b){
					return filter.rlike(a, b);
				},
	            ",": (a, b) => {
	                const aVal = a();
	                const aArr = (0, parser.isArgumentsArray)(aVal)
	                    ? aVal
	                    : [() => aVal];
	                const args = aArr.concat([b]);
	                args.isArgumentsArray = true;
	                return args;
	            },
				'OR': function (a, b){
					return filter.or(a(), b());
				},
				'AND': function(a, b){
					return filter.and(a(), b());
				}
			},
			PREFIX_OPS: {
				'ARRAY': function(a){
					return a;
				},
				'NEG':  unpackArgs(function(a){
					return - a();
				}),
				'DATE': unpackArgs(function(date, offset){
					var dateVal = date(), offsetVal = offset();
					return offset ? dutils.formatDate(dutils.dateAdd(Date(dateVal), 'day', Number(offsetVal))) : dateVal;
				})
			},
			PRECEDENCE:[['ARRAY', 'NEG', 'DATE'], ['+', '-'], ['>=', '>', '<=', '<', '=', 'IN', 'RLIKE'], ['OR', 'AND'], [',']],
			LITERAL_OPEN: '"',
			LITERAL_CLOSE: '"',
			GROUP_OPEN: '(',
			GROUP_CLOSE: ')',
			SEPARATORS: ',',
			WHITESPACE_CHARS: [" "],
			SYMBOLS: ['+', '-', '>', '<', '=', '(', ')', '[', ']', ','],
			AMBIGUOUS: {'-': 'NEG'},
            SURROUNDING: {
                ARRAY: {
                    OPEN: "[",
                    CLOSE: "]",
                },
            },
			termDelegate: function(term){
				let x;
				switch (term){
					case 'TODAY':
						return dutils.formatDate(new Date());
					case 'MONDAY': x = 1; case 'TUESDAY': x = 2; case 'WEDNESDAY': x = 3; case 'THURSDAY': x = 4; case 'FRIDAY': x = 5; case 'SATURDAY': x = 6; case 'SUNDAY': x = 7;						
						return dutils.formatDate(dutils.getDayOfWeek(x, new Date()));
					case 'undefined':
						return undefined;
					default:
						return term;
				}
			}
		};
	};

	return {
		expression: function(filter){
			return new parser.default(filterLanguage(filter));
		}
	};
});
