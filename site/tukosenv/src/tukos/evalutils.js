define(["dojo/dom-style", "dojo/_base/lang", "dojo/when", "dijit/registry", "tukos/utils", "tukos/dateutils", "tukos/hiutils", "tukos/PageManager", "tukos/DialogConfirm"], 
		function(domstyle, lang, when, registry, utils, dutils, hiutils, Pmg, DialogConfirm){
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
                return (p1 === '.'  ? '' : (typeof window[p2] === 'function' ? '' : (typeof this[p2] === 'function' ? 'this.' : (typeof utils[p2] ? 'utils.' : (typeof dutils[p2] === 'function' ? 'dutils.' : (typeof wutils[p2] === 'function' ? 'wutils.' : ''))))))+p1 + p2 +  p3;
            });
        }
    }
});
