define(["dojo/dom-style", "dojo/_base/lang", "dojo/when", "dijit/registry", "tukos/utils", "tukos/dateutils", "tukos/PageManager"], 
		function(domstyle, lang, when, registry, utils, dutils, Pmg){
    return {

           functionNamePattern: "([^.]?)([a-zA-Z0-9]+)(\\()",
           
          eval: function(body, args){
            if (args !== undefined){
                return eval('(function(' + args + '){' + body + '})');
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
        }
    }
});
