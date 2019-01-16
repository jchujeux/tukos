define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/promise/all", "dojo/ready", "dojo/when", "tukos/utils", "tukos/hiutils", "tukos/TukosTooltipDialog", "tukos/Download",  "tukos/PageManager", "dojo/json", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, on, all, ready, when, utils, hiutils, TukosTooltipDialog, download, Pmg, JSON, messages){
    return declare(TukosTooltipDialog, {
        postCreate: function(){
            var form = this.form;
        	lang.mixin(this, this._dialogAtts(form));
            this.inherited(arguments);
            lang.mixin(this.pane, {attachedWidget: this, previewContent: lang.hitch(this, this.previewContent), tabContextId: lang.hitch(form, form.tabContextId)});
        	this.onOpen = lang.hitch(this, function(){
        		var pane = this.pane, _arguments = arguments;
        		this.setVisibility();
        		when(this.previewContent(), lang.hitch(this, function(){
            		pane.watchOnChange = true;
            		dijit.TooltipDialog.prototype.onOpen.apply(this, _arguments);
            		ready(function(){
            			pane.resize();
            		});
            	}));
        	});
        	this.blurCallback = on.pausable(this, 'blur', this.close);
        },
        _dialogAtts: function(form){
            var onWatch = lang.hitch(this, this.onWatchLocalAction), onWatchCheckBox =  lang.hitch(this, this.onWatchCheckBoxLocalAction);
            var description = {paneDescription: {form: form, widgetsDescription: {
                	exportas: {type: 'StoreSelect', atts: {label: messages.exportoption, value: 'email', storeArgs: {data: [{id: 'email', name: messages.email}, {id: 'file', name: messages.file}]}, onWatchLocalAction: onWatch('exportas', this.exportAsWatchAction), value: 'email'}},
                	formatas: {type: 'StoreSelect', atts: {label: Pmg.message('formatOptions'), value: 'pdf', storeArgs: {data: [{id: 'pdf', name: 'pdf'}, {id: 'html', name: 'html'}]}, onWatchLocalAction: onWatch('formatas', this.exportAsWatchAction), value: 'pdf'}},
                    filename: {type: 'TextBox', atts: {label: messages.filename, style: {width: '30em'}, onWatchLocalAction: onWatch('filename')}},
                    orientation: {type: 'StoreSelect', atts: {label: messages.orientation, value: 'portrait', storeArgs: {data: [{id: 'portrait', name: messages.portrait}, {id: 'landscape', name: messages.landscape}]}, onWatchLocalAction: onWatch('orientation')}},
                    smartshrinking: {type: 'StoreSelect', atts: {label: messages.smartshrinking, value: 'on', storeArgs: {data: [{id: 'on', name: messages.on}, {id: 'off', name: messages.off}]}, onWatchLocalAction: onWatch('smartshrinking')}},
                    zoom: {type: 'TukosNumberBox', atts: {label: messages.zoom, style: {width: '3em'},  value: 100, onWatchLocalAction: onWatch('zoom')}},
                    contentmargin: {type: 'TukosNumberBox', atts: {label: messages.contentmargin, style: {width: '3em'}, onWatchLocalAction: onWatch('contentmargin')}},
                    marginoffset: {type: 'TukosNumberBox', atts: {label: messages.marginoffset, style: {width: '3em'}, onWatchLocalAction: onWatch('marginoffset')}},
                    margincoef: {type: 'TukosNumberBox', atts: {label: messages.margincoef, style: {width: '3em'}, onWatchLocalAction: onWatch('margincoef')}},
                    headerfooter: {type: 'CheckBox', atts: {label:messages.headerfooter, checked: false, onWatchLocalAction: onWatchCheckBox('headerfooter', this.headerFooterWatchAction)}},
                    coverpage: {type: 'CheckBox', atts: {label:messages.coverpage, checked: false, onWatchLocalAction: onWatchCheckBox('coverpage', this.coverPageWatchAction)}},
                    fileheader: {type: 'HtmlContent', atts: {label: messages.fileheader, disabled: true, style: {minHeight: '100px', backgroundColor: 'Snow'}}},
                    content: {type: 'HtmlContent', atts: {label: messages.content, disabled: true, style: {minHeight: '250px', maxHeight: '600px', overflow: "auto", backgroundColor: 'Snow'}}},
                    filefooter: {type: 'HtmlContent', atts: {label: messages.filefooter, disabled: true, style: {minHeight: '100px', backgroundColor: 'Snow'}}},
                    filecover: {type: 'HtmlContent', atts: {label: messages.filecover, disabled: true, style: {minHeight: '100px', maxHeight: '450px', overflow: "auto", backgroundColor: 'Snow'}}},
                    from: {type: 'ObjectSelect', atts: {label: messages.from, object: 'mailaccounts', dropdownFilters: {contextpathid: this.form.tabContextId()}, onWatchLocalAction: onWatch('from')}},
                    to: {type: 'TextBox', atts: {label: messages.to, onWatchLocalAction: onWatch('to')}},
                    cc: {type: 'TextBox', atts: {label: messages.cc, onWatchLocalAction: onWatch('cc')}},
                    subject: {type: 'TextBox', atts: {label: messages.subject, style: {width: '60em'}, onWatchLocalAction: onWatch('subject')}},
                    header: {type: 'LazyEditor', atts: {label: messages.bodyheader, height: '130px', style: {backgroundColor: 'White'}, onWatchLocalAction: onWatch('header')}},
                    sendas: {type: 'StoreSelect', atts: {
                        label: messages.sendingoption, value: 'asattachment', 
                        storeArgs: {data: [{id: 'asattachment', name: messages.asattachment}, {id: 'appendtobody', name: messages.appendtobody}, {id: 'bodyandattachment', name: messages.bodyandattachment}]},
                        onWatchLocalAction: onWatch('sendas', this.sendAsWatchAction)
                    }},
                    fileheadertemplate: {type: 'LazyEditor', atts: {label: messages.fileheadertemplate, height: '100px', style: {backgroundColor: 'White'}, onWatchLocalAction: onWatch('fileheader', this.templateWatchAction)/*, optionalPlugins: ['Page', 'ToPage']*/}},
                    template: {type: 'LazyEditor', atts: {label: messages.template, height: '130px', style: {backgroundColor: 'White'}, onWatchLocalAction: onWatch('content', this.templateWatchAction)}},
                    filefootertemplate: {type: 'LazyEditor', atts: {label: messages.filefootertemplate, height: '100px', style: {backgroundColor: 'White'}, onWatchLocalAction: onWatch('filefooter', this.templateWatchAction)/*, optionalPlugins: ['Page', 'ToPage']*/}},
                    filecovertemplate: {type: 'LazyEditor', atts: {label: messages.filecovertemplate, height: '100px', style: {backgroundColor: 'White'}, onWatchLocalAction: onWatch('coverpage', this.templateWatchAction)}},
                    close: {type: 'TukosButton', atts: {label: messages.close, onClick: function(){this.pane.close();}}},
                    sendemail: {type: 'TukosButton', atts: {label: messages.sendemail, onClick: lang.hitch(this, this.sendEmail)}},
                    savefile: {type: 'TukosButton', atts: {label: messages.savefile, onClick: lang.hitch(this, this.saveFile)}}
                },
                layout: {
                    tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false, labelWidth: 150},
                    contents: {
                        headerRow: {tableAtts: {cols: 1, customClass: 'labelsAndValues',showLabels: true, orientation: 'vert'}, contents: {title: {tableAtts: {cols: 1, customClass: 'labelsAndValues', label: messages.exportTitle}}}},
                        row0: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true},  widgets: ['exportas', 'formatas', 'sendas']},
                        row1: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true},  widgets: ['from', 'to', 'cc']},
                        row2: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true},  widgets: ['subject']},
                        row3: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['header']},
                        row4: {tableAtts: {cols: 6, customClass: 'labelsAndValues', showLabels: true}, widgets: ['headerfooter', 'coverpage', 'filename', 'orientation', 'smartshrinking', 'zoom','contentmargin', 'marginoffset', 'margincoef']},
                        row6: {tableAtts: {cols: 4, customClass: 'labelsAndValues', showLabels: false, widgetWidths: ['33%', '33%', '34%']}, widgets: ['close',  'sendemail', 'savefile']},
                        row7: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['fileheader', 'filecover', 'content', 'filefooter']},
                        row5: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['fileheadertemplate', 'template', 'filefootertemplate','filecovertemplate']}
                }},
                style: {minWidth: (dojo.window.getBox().w*0.8) + 'px', overflow: 'auto'},
                widgetsHider: true
            }};
            if (this.dialogDescription){
                return utils.mergeRecursive(description, this.dialogDescription);
            }else{
                return description;
            }
        },
        _watchAction: function(sWidget, tWidget, newValue, att){
            var pane = sWidget.pane, newCustom = {}, watchOnChange = pane.watchOnChange;
            when(pane.valueOf(sWidget.widgetName), function(value){
                if (watchOnChange){
            		var attachedWidgetName = sWidget.pane.attachedWidget.widgetName;
                    lang.setObject('customization.widgetsDescription.' + sWidget.pane.attachedWidget.widgetName + '.atts.dialogDescription.paneDescription.widgetsDescription.' + sWidget.widgetName + '.atts.' + att, value, pane.form);
                }
            });
        },
        watchAction: function(sWidget, tWidget, newValue){
            this._watchAction(sWidget, tWidget, newValue, 'value');
            return true;
        },
        watchCheckBoxAction: function(sWidget, tWidget, newValue){
            this._watchAction(sWidget, tWidget, newValue, 'checked');
        },
        onWatchLocalAction: function(widgetName, watchAction){
            var watchArgs = {value: {}};
            watchArgs.value[widgetName] = {localActionStatus: lang.hitch(this, watchAction || this.watchAction)};
            return watchArgs;
        },
        onWatchCheckBoxLocalAction: function(widgetName, watchAction){
            var watchArgs = {checked: {}};
            watchArgs.checked[widgetName] = {localActionStatus: lang.hitch(this, watchAction || this.watchCheckBoxAction)};
            return watchArgs;
        },
        exportAsWatchAction: function(sWidget, tWidget, newValue){
            this.watchAction(sWidget, tWidget, newValue);
            this.setVisibility();
            this.resize();
            return true;
        },
        sendAsWatchAction: function(sWidget, tWidget, newValue){
            this.watchAction(sWidget, tWidget, newValue);
            var paneGetWidget = lang.hitch(sWidget.pane, sWidget.pane.getWidget), hasAttachment = this.hasAttachment(newValue);
            ['filename', 'orientation', 'smartshrinking', 'zoom', 'headerfooter', 'pagecover'].forEach(function(name){
                paneGetWidget(name).set('hidden', !self.hasAttachment);
            });
            this.setVisibility();
            this.resize();
            return true;
        },
        templateWatchAction: function(sWidget, tWidget, newValue){
            this.watchAction(sWidget, tWidget, newValue);
            var pane = this.pane, form = this.form;
            when(pane.valueOf(sWidget.widgetName), function(newValue){
                when(hiutils.processTemplate(newValue, {'@': form, '§': pane}), function(newValue){
                    tWidget.set('value', newValue || '');
                });
            });
            return true;
        },
        headerFooterWatchAction: function(sWidget, tWidget, newValue){
            this.watchCheckBoxAction(sWidget, tWidget, newValue);
            var pane = sWidget.pane, paneGetWidget = lang.hitch(pane, sWidget.pane.getWidget);
            ['fileheader', 'filefooter', 'fileheadertemplate', 'filefootertemplate'].forEach(function(widgetName){
                paneGetWidget(widgetName).set('hidden', !newValue);
            });
            pane.resize();
        },
        coverPageWatchAction: function(sWidget, tWidget, newValue){
            this.watchCheckBoxAction(sWidget, tWidget, newValue);
            var pane = sWidget.pane, paneGetWidget = lang.hitch(pane, sWidget.pane.getWidget);
            ['filecover', 'filecovertemplate'].forEach(function(widgetName){
                paneGetWidget(widgetName).set('hidden', !newValue);
            });
            pane.resize();
        },
        previewContent: function(){
            var pane = this.pane, form = this.form;
            return when(hiutils.processTemplate(pane.valueOf('filecovertemplate'), {'@': form, '§': pane}), function(newContent){
                pane.getWidget('filecover').set('value', newContent || '');
                return when(hiutils.processTemplate(pane.valueOf('fileheadertemplate'), {'@': form, '§': pane}), function(newContent){
                    pane.getWidget('fileheader').set('value', newContent || '');
	                return when(hiutils.processTemplate(pane.valueOf('template'), {'@': form, '§': pane}), function(newContent){
	                     pane.getWidget('content').set('value', newContent || '');
	                    return when(hiutils.processTemplate(pane.valueOf('filefootertemplate'), {'@': form, '§': pane}), function(newContent){
	                        pane.getWidget('filefooter').set('value', newContent || '');
	                    });
	                });
                });
            });
        },
        sendEmail: function(){ 
            setTimeout(lang.hitch(this, function(){
	            var form = this.form, widget = this.pane.getWidget('sendemail');
	            lang.hitch(this, this.dataToProcess)().then(function(data){
	                Pmg.setFeedback(Pmg.message('sendingemail'));
	            	Pmg.serverDialog(lang.hitch(form, form.completeUrlArgs)({action: 'Process', query: {id: true, params: {process: 'sendContent', noget:  true}}}), {data: data, timeout: 32000},{widget: widget, att: 'label'});
	            });
            }), 100);
        },
        saveFile: function(){ 
            setTimeout(lang.hitch(this, function(){
            	var form = this.pane.form;
                lang.hitch(this, this.dataToProcess)().then(function(data){
                    Pmg.setFeedback(Pmg.message('savingfile'));
                	download.download({object: form.object, view: form.viewMode, mode: form.paneMode, action: 'process', query: {id: form.valueOf('id'), params: {process: 'fileContent', noget:  true}}}, {data: data});
                });           	
            }), 100);
        },
        dataToProcess: function(){
            var pane = this.pane, targetFormat = pane.getWidget('formatas').get('value');
                  valuesToPostProcess = (['content'].concat(pane.getWidget('headerfooter').get('checked') ? ['fileheader', 'filefooter'] : [])).concat(pane.getWidget('coverpage').get('checked') ? ['filecover'] : []);
            valuesToSend = ['from', 'to', 'cc', 'subject', 'header', 'sendas', 'formatas', 'filename', 'orientation', 'smartshrinking', 'zoom', 'contentmargin', 'marginoffset', 'margincoef'].concat(valuesToPostProcess);
            var data = pane.widgetsValue(valuesToSend);
            valuesToPostProcess.forEach(function(widgetName){
                data[widgetName] = hiutils.postProcess(data[widgetName], targetFormat);
            });
            return all(data);
        },
        setVisibility: function(){
            var  pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), exportOption = paneGetWidget('exportas').get('value'),  formatOption = paneGetWidget('formatas').get('value'),
            	hideEmail = (exportOption === 'email' ? false : true), hidePdfAtts = (formatOption === 'pdf' ? false : true);
            ['from', 'to', 'cc', 'subject', 'header', 'sendas', 'sendemail'].forEach(function(widgetName){
                paneGetWidget(widgetName).set('hidden', hideEmail);
            });
            paneGetWidget('savefile').set('hidden', !hideEmail);
            var hasFileName = hideEmail || this.hasAttachment(paneGetWidget('sendas').get('value')), hasHeadersAndFooters = paneGetWidget('headerfooter').get('checked'), hasCoverPage = paneGetWidget('coverpage').get('checked');
            ['filename', 'headerfooter', 'coverpage'].forEach(function(widgetName){
                paneGetWidget(widgetName).set('hidden', !hasFileName);
            });
            ['orientation',  'smartshrinking', 'zoom', ,'contentmargin', 'marginoffset', 'margincoef'].forEach(function(widgetName){
                paneGetWidget(widgetName).set('hidden', !hasFileName || hidePdfAtts);            	
            });
            ['fileheader', 'filefooter', 'fileheadertemplate', 'filefootertemplate'].forEach(function(widgetName){
                paneGetWidget(widgetName).set('hidden', !hasFileName || !hasHeadersAndFooters);
            });
            ['filecover', 'filecovertemplate'].forEach(function(widgetName){
                paneGetWidget(widgetName).set('hidden', !hasFileName || !hasCoverPage);
            });
        }, 
        hasAttachment: function(sendAsValue){
            return ['asattachment', 'bodyandattachment'].indexOf(sendAsValue) !== -1;
        }
    });
});
