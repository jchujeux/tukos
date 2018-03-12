define (["dojo/_base/declare", "dojo/_base/array", "dojo/dom-construct", "dojo/dom-attr", "dojo/on", "dojo/aspect", "dijit/_WidgetBase", "dijit/registry", "dijit/form/Button", "dojoFixes/dojox/form/Uploader", "dojoFixes/dojox/form/uploader/FileList", "tukos/PageManager", "dojo/json", "dojo/i18n!tukos/nls/messages"],
    function(declare, arrayUtil, dct, dattr, on, aspect, Widget, registry, Button, Uploader, FileList, Pmg, JSON, messages){
    return declare(Widget, {
        postCreate: function(){
            var self = this;
            var table = dct.create('table', {}, this.domNode);
            var tr = dct.create('tr', {}, table);
            var uploadSelectorNode  = dct.create('td', {}, tr);
            this.uploaderAtts.url = Pmg.requestUrl({object: 'documents', view: 'noview', action: 'upload'});
            this.uploadSelector = new Uploader(this.uploaderAtts);
            on(this.uploadSelector, 'complete', function(evt){
                self.uploadButton.set('style', 'display: none;');
                dattr.set(self.fileListNode, 'style', 'display: none;');
                var resetWidget = registry.byId(self.form.id + 'reset');
                resetWidget.resetDialogue().then(function(){Pmg.setFeedback(evt['feedback']);});
            });
            uploadSelectorNode.appendChild(this.uploadSelector.domNode); 

            var uploadButtonNode  = dct.create('td', {}, tr);
            this.uploadButton = new Button(this.uploadButtonAtts);
            on(this.uploadButton, "click", function(evt){
                var theId   = dijit.registry.byId(self.form.id + 'id').get('value');
                if ((theId == '') || self.form.hasChanged()){
                    var dialog = new tukos.DialogConfirm({title: messages.newOrFieldsHaveBeenModified, content: messages.saveOrReloadFirst, hasSkipCheckBox: false});
                    dialog.show().then(function(){Pmg.setFeedback(messages.actionCancelled);},
                                       function(){Pmg.setFeedback(messages.actionCancelled);});/* user pressed Cancel: no action */
                }else{
                    if (this.subFiles){
                        var theName = dijit.registry.byId(self.form.id + 'name').get('value');
                        self.uploadSelector.upload({mdate: JSON.stringify(self.filesMdate), parentid: JSON.stringify({id: theId, name: theName, object: self.form.object})});
                    }else{
                        self.uploadSelector.upload({mdate: JSON.stringify(self.filesMdate), id: theId});
                    }
                }
            });
            uploadButtonNode.appendChild(this.uploadButton.domNode);
            var tr = dct.create('tr', {}, table);
            this.fileListNode  = dct.create('td', {colspan: 2, style: 'display: none;'}, tr);
            this.fileList = new FileList({uploader:this.uploadSelector}/*, fileListNode*/);
            this.fileListNode.appendChild(this.fileList.domNode); 
            aspect.after(this.uploadSelector, 'onChange', function(){
                self.filesMdate = [];
                arrayUtil.forEach(self.uploadSelector._files, function(f, i){
                    self.filesMdate[i] = dojo.date.stamp.toISOString(f.lastModifiedDate, {zulu: true});
                }, self);
                self.uploadButton.set('style', 'display: table-cell;');
                dattr.set(self.fileListNode, 'style', 'display: table-cell;');
                self.layoutHandle.resize();
            });
            this.uploadSelector.startup();
        }
    }); 
});
