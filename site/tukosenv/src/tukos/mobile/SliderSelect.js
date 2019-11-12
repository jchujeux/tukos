define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dijit/_WidgetBase", "dojox/mobile/Slider", "dojo/store/Memory", "tukos/widgetUtils"], 
    function(declare, lang, dct, dst, _WidgetBase, Slider, Memory, wutils){
    return declare([_WidgetBase], {
        constructor: function(args){
            args.store= new Memory(args.storeArgs);
            args.style.backgroundColor = "";
        },
        postCreate: function(args){
        	this.inherited(arguments);
        	var ratings = this.storeArgs.data.slice(1), initialValue = this.storeArgs.data[0].id, min = ratings.reduce(function(a, b){
        		return Math.min(a, b.id);
        		}, initialValue), 
        		max = ratings.reduce(function(a, b){return Math.max(a, b.id);}, initialValue), step = 1;
        	var table = dct.create('table', {style: {width: '100%'}}, this.domNode), tr = dct.create('tr', null, table);
        	dct.create('td', {innerHTML: min, style: {width: '0.5em', color: 'white'}}, tr);
        	this.slider = new Slider({min: min, max: max, step: step, style: {width: "90%"}, onChange: lang.hitch(this, this.setDescription)}, dct.create('td', null, tr));
            this.slider.watch('value', lang.hitch(wutils, wutils.subWidgetWatchCallback, this, this.slider));
            this.slider.setStyleToChanged = lang.hitch(this, this.setStyleToChanged);
            this.slider.setStyleToUnchanged = lang.hitch(this, this.setStyleToUnchanged);
            dct.create('td', {innerHTML: max, style: {width: '2em', color: 'white'}}, tr);
            this.descriptionNode = dct.create('div', {style: {textAlign: 'center', color: 'white', fontStyle: 'italic'}}, this.domNode);
        },
        setDescription: function(newValue){
        	if (newValue){
            	this.descriptionNode.innerHTML = this.store.get(newValue).name;
            	this._set('value', newValue);      		
        	}else{
        		this.descriptionNode.innerHTML = this.placeHolder;
        		this._set('value', '');
        	}
        },
        setStyleToChanged: function(){
            dst.set(this.descriptionNode, 'color', wutils.changeColor);
        },
        setStyleToUnchanged: function(){
            dst.set(this.descriptionNode, 'color', 'white');
        }, 
        _setValueAttr: function(value){
        	this.inherited(arguments);
        	this.slider.set('value', value);
        	this.descriptionNode.innerHTML = (value.length == 0) ? this.placeHolder : this.store.get(value);
        },
        _getValueAttr: function(){
        	return this.slider.get('value');
        }
    }); 
});
