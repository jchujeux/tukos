define(["dojo/dom-construct", "dojo/dom-style", "dojo/_base/lang", "dojo/Deferred", "dojo/when", "dojo/string", "dijit/registry", "dijit/focus",  "tukos/utils", "tukos/hiutils", "tukos/dateutils", "tukos/widgetUtils", "tukos/PageManager"], 
	function(dct, domstyle, lang, Deferred, when, string, registry, focusUtil, utils, hiutils, dutils, wutils, Pmg){
    return {
           functionNamePattern: "([^.]?)([a-zA-Z0-9]+)(\\()",
           
          eval: function(body, args){
            if (args !== undefined){
                return eval('(function(' + args + '){\n' + body + '\n})');
            }else{
                return eval(body);
            }
        },
        nameToFunction: function(name){
            return name.replace(new RegExp(this.functionNamePattern), function(match, p1, p2, p3){
                var name = p1 + p2;
            	return (p1 === '.'  
                	? '' 
                	: (typeof window[name] === 'function'
                		? '' 
                		: (typeof this[name] === 'function'
                			? 'this.'
                			: (typeof utils[name] === 'function'
                				? 'utils.'
                				: (typeof dutils[name] === 'function'
                					? 'dutils.'
                					: (typeof Math[name] === 'function'
                						? 'Math.' 
                						: ''
                	)))))) + p1 + p2 +  p3;
            });
        },
        actionFunction: function(object, action, body, evalArgs, args){
            if (body){
                var functionName = action + 'ActionFunction';
            	if (!object[functionName]){
                    if (typeof body === 'function'){
						object[functionName] = body;
					}else{
	                    var myEval = object.myEval || (object.myEval = lang.hitch(object, this.eval));
	                    object[functionName] = myEval(body, evalArgs || '');
					}
                }
                return object[functionName](args);
            }
            return true;
        }
    }
});
