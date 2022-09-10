define (["dojo/_base/declare", "dojo/_base/lang", "dojo/has", "dojo/on", "dojo/dom-construct", "dijit/_WidgetBase",  "tukos/PageManager"], 
    function(declare, lang, has, on, dct, Widget, Pmg){
    return declare([Widget], {
        postCreate: function(){
            this.inherited(arguments);
        },
        _setValueAttr: function(value){
        	this._set("value", value);
        	dct.empty(this.domNode);
            if(typeof value === "string" && value.substring(0, 7) === '#tukos{'){
                var node = dct.create('div', {style: {textDecoration: "underline", color: "blue", cursor: "pointer"}});
            	node.innerHTML = Pmg.message('loadOnClick');
            	node.onClickHandler = on(node, 'click', lang.hitch(this, this.loadContentOnClick));
            	dct.place(node, this.domNode);
            }else{
				this.domNode.innerHTML = value;
				if (!this.noMathJax && !has('ff') && value.search('<math') !== -1){
					this.processMathTags();
				}
            }
        },
        loadContentOnClick: function(evt){
        	var source = RegExp("#tukos{id:([^,]*),object:([^,]*),col:([^}]*)}", "g").exec(this.get('value')), targetCol = source[3], node = evt.currentTarget;
			evt.stopPropagation();
			evt.preventDefault();
			node.onClickHandler.remove();
			Pmg.serverDialog({object: source[2], view: 'NoView', mode: 'NoMode', action: 'RestSelect', query: {one: true, params: {getOne: 'getOne'}, storeatts: {cols: [targetCol], where: {id: source[1]}}}}).then(lang.hitch(this, function(response){
        		this.set('value', response.item[targetCol]);
        	}));
        },
		processMathTags: function(){
			this.lazyLoadMathJax();
			try{// although an exception is triggerred the first time as MathJax is not defined, the conversion still takes place!
				var mathTagNodes = Array.apply(null, this.domNode.getElementsByTagName('math'));
				mathTagNodes.forEach(function(node){
					var mathString = node.outerHTML;
					dct.place(MathJax.mathml2chtml(mathString), node, 'replace');
				});
				MathJax.startup.document.clear();
				MathJax.startup.document.updateDocument();
			}catch(e){
				console.log(e);
			}
		},
		lazyLoadMathJax: function(){
			if (!window.MathJax){
          		var script = document.createElement('script');
				script.src = "https://cdn.jsdelivr.net/npm/mathjax@3/es5/mml-chtml.js";
		        document.head.appendChild(script);
			}
		}
    }); 
});
