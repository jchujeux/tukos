define(["dojo/_base/array", "dojo/_base/lang", "dojo/dom-style", "dijit/registry", "tukos/utils"], 
	function(arrayUtil, lang, domstyle, registry, utils){
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
            var form = widget.form, Pmg = form.Pmg;
            if (oldValue !== value && form){
                if (attr === 'value' && form.markIfChanged && arrayUtil.indexOf(form.postElts, widget.widgetName)!= -1){
                   	if (widget.widgetName === 'permission'){
                    		Pmg.setFeedback(Pmg.message('permissionchanged'));
                	}else if (!form.changedWidgets.permission && utils.in_array(form.valueOf('permission'), ['PL', 'RL'])){
                		Pmg.setFeedback(Pmg.message('itemislocked')); Pmg.beep();
                   	}
                   	this.markAsChanged(widget);
                }
                if (form.watchOnChange){
                    form.mayNeedResize = false;
                    if (attr === 'value' && form.watchContext === 'user'){
                        if (widget.onChangeServerAction && !widget.inOnChangeServerAction){
                            form.widgetChangeServerDialog(widget);
                        }
                        if (widget.onChangeLocalAction){
                            if (utils.empty(widget.localActionFunctions['value'])){
                                form.buildLocalActionFunctions(widget.localActionFunctions['value'], widget.onChangeLocalAction);
                            }
                            form.widgetWatchLocalAction(widget, 'value', widget.localActionFunctions['value'], value, oldValue);
                        }
                    }
                    if (widget.onWatchLocalAction && widget.onWatchLocalAction[attr]){
                        if (utils.empty(widget.localActionFunctions[attr])){
                            form.buildLocalActionFunctions(widget.localActionFunctions[attr], widget.onWatchLocalAction[attr]);
                        }
                        form.widgetWatchLocalAction(widget, attr, widget.localActionFunctions[attr], value, oldValue);
                    } 
                    if (form.mayNeedResize){
                        form.resize();// as some elements may have become hidden / unhidden during local actions
                        form.mayNeedResize = false;
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
                            subWidget.localActionFunctions[attr] = widget.form.buildSubWidgetLocalActionFunction(subWidget.onWatchLocalAction[attr]);
                        }
                        subWidget.localActionFunctions[attr](widget, oldValue, value, oldValue);
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
                    case '@':
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
        },
        setValuesOf: function(data){
        	utils.forEach(data, lang.hitch(this, function(value, name){
        		this.setValueOf(name, value);
        	}));
        }
    }

});
