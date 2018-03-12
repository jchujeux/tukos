define(["dojo/_base/array", "dojo/_base/lang", "dojo/dom-style", "dijit/registry", "tukos/utils", "tukos/evalutils"], 
	function(arrayUtil, lang, domstyle, registry, utils, eutils){
    return {

        specialCharacters: '$@#',
        changeColor: 'LightYellow',

        changeStyle: function(widget, property, value){
            if (typeof widget.changeStyle === 'function'){
                widget.changeStyle(property, value);
            }else{
                domstyle.set(widget.domNode, property, value);
            }
        },
        
        setStyleToChanged: function(widget){
            if (typeof widget.setStyleToChanged === 'function'){
                widget.setStyleToChanged();
            }else{
                this.changeStyle(widget, 'backgroundColor', this.changeColor);
            }
        },
        setStyleToUnchanged: function(widget){
            if (typeof widget.setStyleToUnchanged === 'function'){
                widget.setStyleToUnchanged();
            }else{
                this.changeStyle(widget, 'backgroundColor', 'White');
            }
        },

        markAsChanged: function(widget, styleFlag){
           var name = widget.widgetName;
        	if (styleFlag !== 'noStyle'){
        		this.setStyleToChanged(widget);
           }
            widget.form.changedWidgets[name] = widget;
            if (widget.form.watchContext === 'user'){
            	widget.form.userChangedWidgets[name] = widget;
            }
        },
/*        
        markAsUnchanged: function(widget){
            this.setStyleToUnchanged(widget);
            delete(widget.form.changedWidgets[widget.widgetName]); 
        },
*/
        setWatchers: function(widget){
            if (!widget.onWatchLocalAction || !widget.onWatchLocalAction['value']){
                widget.watch('value',  lang.hitch(this, this.watchCallback, widget));
                widget.localActionFunctions = {};
                widget.localActionFunctions['value'] = {};
            }
            if (widget.onWatchLocalAction){
                widget.localActionFunctions = {};
                for (var att in widget.onWatchLocalAction){
                    widget.localActionFunctions[att] = {};
                    widget.watch(att, lang.hitch(this, this.watchCallback, widget));
                }
            }
        },

        watchCallback: function(widget, attr, oldValue, value){
            //console.log('in watchCallback');
            var form = widget.form;
            if (oldValue !== value && form){
                if (attr === 'value' && form.markIfChanged && arrayUtil.indexOf(form.postElts, widget.widgetName)!= -1){
                   this.markAsChanged(widget);
                }
                if ( form.watchOnChange){
                    if (!form.inWatchCallback){
                        form.inWatchCallback = 0;
                        form.mayNeedResize = false;
                    }
                    if (!widget.inWatchCallback){
                        widget.inWatchCallback = true;
                        form.inWatchCallback +=1;
                        if (attr === 'value' && form.watchContext === 'user'){
                            if (widget.onChangeServerAction && !widget.inOnChangeServerAction){
                                form.widgetChangeServerDialog(widget);
                                //form.mayNeedResize = true;
                            }
                            if (widget.onChangeLocalAction){
                                if (utils.empty(widget.localActionFunctions['value'])){
                                    form.buildLocalActionFunctions(widget.localActionFunctions['value'], widget.onChangeLocalAction);
                                }
                                form.widgetWatchLocalAction(widget, widget.localActionFunctions['value'], value);
                                //form.mayNeedResize = true;
                            }
                        }
                        if (widget.onWatchServerAction && widget.onWatchServerAction[attr]){
                            form.widgetWatchServerAction(widget, widget.onWatchServerAction[attr], value);
                            //form.mayNeedResize = true;
                        } 
                        if (widget.onWatchLocalAction && widget.onWatchLocalAction[attr]){
                            if (utils.empty(widget.localActionFunctions[attr])){
                                form.buildLocalActionFunctions(widget.localActionFunctions[attr], widget.onWatchLocalAction[attr]);
                            }
                            form.widgetWatchLocalAction(widget, widget.localActionFunctions[attr], value);
                            //form.mayNeedResize = true;
                        } 
                        widget.inWatchCallback = false;
                        form.inWatchCallback += -1;
                    }
                    if (form.inWatchCallback === 0){
                        form.inWatchCallback = false;
                        if (form.mayNeedResize){
                            form.resize();// as some elements may have become hidden / unhidden during server or local actions
                            form.mayNeedResize = false;
                        }
                    }
                }
            }
        },
        

        subWidgetWatchCallback: function(widget, subWidget, attr, oldValue, value){
            if (oldValue != value){
                if (! widget.inSubWidgetWatchCallback){
                    widget.inSubWidgetWatchCallback = true;
                    if (subWidget.onWatchLocalAction && subWidget.onWatchLocalAction[attr]){
                        if (!subWidget.localActionFunctions){
                            subWidget.localActionFunctions = {};
                        }
                        if (utils.empty(subWidget.localActionFunctions[attr])){
                            subWidget.localActionFunctions[attr] = eutils.eval(subWidget.onWatchLocalAction[attr], 'widget, oldValue, newValue');
                        }
                        subWidget.localActionFunctions[attr](widget, oldValue, value);
                    }
                    var newWidgetValue = widget.get(attr);
                    widget.set(attr, newWidgetValue);
                    var form = widget.form || widget.getParent().form;
                    if (attr == 'value' && form.markIfChanged && arrayUtil.indexOf(form.postElts, widget.widgetName) != -1){
                        this.setStyleToChanged(subWidget);
                    }
                    widget.inSubWidgetWatchCallback = false;
                }
            }
        },
        
        valueOf: function(name){
            var specialCharacters = '$@#',
                firstChar = name[0],
                realName,
                parent = this.parent || this.getParent(),
                form = this.form || parent.form;
            if (specialCharacters.indexOf(firstChar) === -1){
                realName = name;
            }else{
                var realName = name.substring(1);
                switch (firstChar){
                    case '$'://return the value of the form attribute or function 
                        switch (typeof form[realName]){
                            case 'undefined': return undefined;
                            case 'function' : return form[realName]();
                            default: return form[realName];
                        }
                        //return (typeof form[realName] === 'undefined') ? form[realName] :  form[realName]();
                    case '@':
                        return form.valueOf(realName);
                }
            }
            if (parent && typeof parent.cellValueOf === "function"){
                return parent.cellValueOf(realName);
            }else{
                return form.valueOf(realName);
            }
        },

        setValueOf: function(name, value){
            var specialCharacters = '$@#',
                firstChar = name[0],
                realName;
            if (specialCharacters.indexOf(firstChar) === -1){
                realName = name;
            }else{
                var realName = name.substring(1);
                switch (firstChar){
                    case '$':
                        console.log('special character $ not supported in widgetUtils.setValueOf()');
                        return;
                    case '@ ':
                        this.form.setValueOf(realName, value);
                        return;
                }
            }
            var parent = this.parent || this.getParent();
            if (parent && typeof parent.setCellValueOf === "function"){
                parent.setCellValueOf(value, realName);
            }else{
                this.form.setValueOf(realName, value);
            }
        }
    }
});
