define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/dom-construct", "dijit/_WidgetBase",  "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, on, dct, Widget, Pmg, messages){
    return declare([Widget], {
        postCreate: function(){
            this.inherited(arguments);
/*
            if (this.value){
                this.domNode.innerHTML = this.value;
            }
*/
        },
        _setValueAttr: function(value){
        	this._set("value", value);
        	dct.empty(this.domNode);
            if(typeof value === "string" && value.substring(0, 7) === '#tukos{'){
                node = dct.create('div', {style: {textDecoration: "underline", color: "blue", cursor: "pointer"}});
            	node.innerHTML = messages.loadOnClick;
            	node.onClickHandler = on(node, 'click', lang.hitch(this, this.loadContentOnClick));
            	dct.place(node, this.domNode);
            }else{
                this.domNode.innerHTML = value;            	
            }
        },
/*
        _getValueAttr: function(){
            return this.domNode.innerHTML || '';
        },
*/
        loadContentOnClick: function(evt){
        	var source = RegExp("#tukos{id:([^,]*),object:([^,]*),col:([^}]*)}", "g").exec(this.get('value')), targetCol = source[3], node = evt.currentTarget;
			evt.stopPropagation();
			evt.preventDefault();
			node.onClickHandler.remove();
			Pmg.serverDialog({object: source[2], view: 'noview', mode: 'nomode', action: 'RestSelect', query: {one: true, params: {getOne: 'getOne'}, storeatts: {cols: [targetCol], where: {id: source[1]}}}}).then(lang.hitch(this, function(response){
        		this.set('value', response.item[targetCol]);
        	}));
        	console.log('here implement load on click');
        	
        }
    }); 
});
