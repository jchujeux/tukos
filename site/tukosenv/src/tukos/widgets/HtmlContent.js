define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/dom-construct", "dijit/_WidgetBase",  "tukos/PageManager"], 
    function(declare, lang, on, dct, Widget, Pmg){
    return declare([Widget], {
        postCreate: function(){
            this.inherited(arguments);
        },
        _setValueAttr: function(value){
        	this._set("value", value);
        	dct.empty(this.domNode);
            if(typeof value === "string" && value.substring(0, 7) === '#tukos{'){
                node = dct.create('div', {style: {textDecoration: "underline", color: "blue", cursor: "pointer"}});
            	node.innerHTML = Pmg.message('loadOnClick');
            	node.onClickHandler = on(node, 'click', lang.hitch(this, this.loadContentOnClick));
            	dct.place(node, this.domNode);
            }else{
                this.domNode.innerHTML = value;            	
            }
        },
        loadContentOnClick: function(evt){
        	var source = RegExp("#tukos{id:([^,]*),object:([^,]*),col:([^}]*)}", "g").exec(this.get('value')), targetCol = source[3], node = evt.currentTarget;
			evt.stopPropagation();
			evt.preventDefault();
			node.onClickHandler.remove();
			Pmg.serverDialog({object: source[2], view: 'noview', mode: 'NoMode', action: 'RestSelect', query: {one: true, params: {getOne: 'getOne'}, storeatts: {cols: [targetCol], where: {id: source[1]}}}}).then(lang.hitch(this, function(response){
        		this.set('value', response.item[targetCol]);
        	}));
        }
    }); 
});
