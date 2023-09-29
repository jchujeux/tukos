define (["dojo/_base/lang", "dojo/dom-class", "dojo/dom-construct", "dojo/keys", "dojo/string", "dojo/parser", "tukos/utils", "dijit/form/HorizontalSlider", "dijit/form/HorizontalRuleLabels", "dijit/form/HorizontalRule",
		 "dojo/domReady!"], 
    function(lang, dcl, dct, keys, string, parser, utils, HorizontalSlider, HorizontalRuleLabels, HorizontalRule){
	var widgetHolder = 	' <div class="tukosContainer" data-widgetid="${name}" data-params=\'${params}\' style="display: inline;" title="${name}">${visualPreTag}${widgetSource}${visualPostTag}</div> ',
		widgetTemplates = {
			Slider: 
				'<div style="width: ${width};padding-left: ${paddingLeft};">' +
				'<ol data-dojo-type="dijit/form/HorizontalRuleLabels" data-dojo-props="container: \'topDecoration\'" style="height: 1.2em;" labelStyle="width: 6em; font-weight: bold;">${liTopTags}</ol>' + 
				'<div data-dojo-type="dijit/form/HorizontalRule" data-dojo-props="container: \'topDecoration\',	count: ${count}" style="height: 5px; margin: 0 12px;"></div>' +
					'<input data-widgetid="${name}" class="tukosWidget" type="range" value="${value}" data-dojo-type="dijit/form/HorizontalSlider" data-dojo-props="minimum: ${minimum}, maximum: ${maximum}, ' +
					 'showButtons: false, discreteValues: ${count}" />' +
					'<ol data-dojo-type="dijit/form/HorizontalRuleLabels" data-dojo-props="container: \'bottomDecoration\'" labelStyle="width: 6em; font-weight: bold;">${liBottomTags}</ol>' + 
				'</div>',
			TextBox: '<input class="tukosWidget", value="${value}" style="width: ${width}; background-color: ${backgroundColor}; color: ${color};" data-dojo-type="dijit/form/TextBox" data-dojo-props="trim:true, ' +
					  'placeHolder:\'${placeHolder}\'" />',
			Textarea: '<textarea class="tukosWidget" value="${value}" style="width: ${width};background-color:${backgroundColor};color:${color};" data-dojo-type="dijit/form/Textarea"></textarea>',
			SimpleTextarea: '<textarea class="tukosWidget" value="${value}" style="width: ${width};height: ${height}; background-color: ${backgroundColor}; color: ${color};" data-dojo-type="dijit/form/SimpleTextarea"></textarea>',
			RichTextarea:
				'<div class="tukosWidget" value="${value}" style="width: ${width};border:1px solid;border-color:#b5bcc7;" width="${width}"' +
				'data-dojo-type="dijit/InlineEditBox" data-dojo-props="editor:\'dijit/Editor\', renderAsHtml:true, autoSave:false, editorParams:{height: \'\', extraPlugins: [\'dijit/_editor/plugins/AlwaysShowToolbar\']}"></div>',
			CheckBox: '<input class="tukosWidget" data-dojo-type="dijit/form/CheckBox" value="ok" />',
			SendMailButton: 
				'<button data-dojo-type="dijit/form/Button" type="button" ><span>${name}</span>' +
					'<script type="dojo/on" data-dojo-event="click">var tukosForms=require("tukos/tukosForms");tukosForms.sendFormMail(this);</script>' +
				'</button>',
			SendFileButton: 
				'<button data-dojo-type="dijit/form/Button" type="button" ><span>${name}</span>' +
					'<script type="dojo/on" data-dojo-event="click">var tukosForms=require("tukos/tukosForms");tukosForms.sendFormFile(this);</script>' +
				'</button>',
			SaveButton: '<button data-dojo-type="dijit/form/Button" type="button" ><span>${name}</span><script type="dojo/on" data-dojo-event="click">var tukosForms=require("tukos/tukosForms");tukosForms.saveForm(this);</script></button>',
			ReloadButton: 
				'<button data-widgetid="${name}" multiple="false" type="file" data-dojo-type="dojox/form/Uploader" label="${name}" data-dojo-props="uploadOnSelect: false, url:\'dummy\'">' +
					'<script type="dojo/on" data-dojo-event="change">var tukosForms=require("tukos/tukosForms");tukosForms.loadForm(this);</script>' +
				'</button>',
			MultiCheckInput:'<table class="tukosWidget" value="${value}" uniquechoice="${uniquechoice}" style="width: ${width}; background-color: ${backgroundColor}; color: ${color};" ' +
			 'data-dojo-type="tukos/widgets/MultiCheckInput"><tbody>${inputTrs}</tbody></table>',
			MultiGridCheckInput:'<table class="tukosWidget" uniquechoice="${uniquechoice}" style="width: ${width}; background-color: ${backgroundColor}; color: ${color};" ' +
			 'data-dojo-type="tukos/widgets/MultiGridCheckInput"><tbody>${inputTrs}</tbody></table>',
			DateTextBox: '<input class="tukosWidget" type="text" value="${value}"  style="width: ${width}; background-color: ${backgroundColor}; color: ${color};" data-dojo-type="dijit/form/DateTextBox" />',
			TimeTextBox: '<input class="tukosWidget" type="text" value="${value}"  style="width: ${width}; background-color: ${backgroundColor}; color: ${color};" data-dojo-type="dijit/form/TimeTextBox" />',
			NumberSpinner: '<input class="tukosWidget" value="${value}" style="width: ${width}; background-color: ${backgroundColor}; color: ${color};" data-dojo-type="dijit/form/NumberSpinner" ' +
				'data-dojo-props="smallDelta:${increment}, constraints:{min:${min},max:${max},places:${digits}}" />',
			DropdownList: '<DropdownList class="tukosWidget" data-dojo-type="dijit/form/Select">${options}</select>'
		},
		widgetParams = {// all widgets include 'name' and 'type', so concatenated in this.widgetParams(widgetType)
			TextBox: ['width', 'backgroundColor', 'color', 'value', 'placeHolder'],
			Textarea: ['width', 'backgroundColor', 'color', 'value'],
			SimpleTextarea:	['width', 'height', 'backgroundColor', 'color', 'value'],
			RichTextarea:	['width', 'value'],
			DateTextBox: ['width', 'backgroundColor', 'color', 'value'],
			TimeTextBox: ['width', 'backgroundColor', 'color', 'value'], 
			NumberSpinner: ['width', 'backgroundColor', 'color', 'value', 'increment', 'min', 'max', 'digits'],
			CheckBox: [],
			MultiCheckInput: ['width', 'backgroundColor', 'color', 'value', 'values', 'numCols', 'orientation', 'uniquechoice'],
			MultiGridCheckInput: ['width', 'backgroundColor', 'color', 'values', 'topics', 'uniquechoice'],
			DropdownList: ['width', 'values'],
			Slider:  ['width', 'paddingLeft', 'color', 'value', 'values', 'labels'],
			SendMailButton: ['subject', 'body', 'to', 'filename', 'subjectPrepend'], SendFileButton: ['filename'], SaveButton: [],	ReloadButton: []
		},
		defaultParams = {width: '100%'};
			
	return {
    	widgetTypes: function(){
    		return Object.keys(widgetParams);
    	},
    	widgetParams: function(widgetType){
    		return ['name', 'type'].concat(widgetParams[widgetType]);
    	},
    	defaultParams: function(widgetType){
    		var type = widgetType || 'TextBox', params = this.widgetParams(type), defaults = {};
    		utils.forEach(params, function(param){
    			defaults[param] = defaultParams[param] || '';
    		});
    		return lang.mixin(defaults, {name: '', type: type});
    	},
    	getParams: function(tukosContainer){
        	var paramsValues = JSON.parse(tukosContainer.getAttribute('data-params'));
        	paramsValues.name = tukosContainer.getAttribute('data-widgetid');
        	return paramsValues;
    	},
    	targetToSource: function(tukosContainer){
    		return this.sourceHTML(this.getParams(tukosContainer), '').trim();
    	},
		sourceHTML: function(params, visualTag){
    		var type = params.type;
    		if (!params.params){
    			params = lang.mixin(params, {params: JSON.stringify(params)});
    		}
    		switch(type){
    			case 'Slider': 
    				var values = params.values.split(','), length = values.length, labels = params.labels.split(',');
    				//delete params.values;
    				params = lang.mixin(params, {count: length, minimum: 1, maximum: length, liBottomTags: '<li>' + labels.join('</li><li>') + '</li>', liTopTags: '<li>' + values.join('</li><li>') + '</li>'});
    				if (!params.value){
    					params.value = values[0];
    				}
    				break;
    			case 'MultiCheckInput':
    				var values = params.values.split(','), length = values.length, numCols = params.numCols || length, labelOrientation = params.orientation, name = params.name, cellTemplate, inputTrs = '<tr>', col = 1,
						inputTemplate = '<input ${inputType} data-dojo-type="dijit/form/${widget}" name="${name}" value="${value}"/>', inputType = params.uniquechoice ? 'type="radio"' : "", 
						widget = params.uniquechoice ? "RadioButton" : "CheckBox";
					//delete params.values; delete params.numCols, delete params.orientation;
					cellTemplate = '<td style="width:' + 100/numCols + '%;margin: auto;"><table style="margin: auto;text-align: center"><tbody><tr><td>${label}</td>' + (labelOrientation === 'vertical' ? '</tr><tr>' : '') + 
								   '<td>' + inputTemplate + '</td></tr></tbody></table></td>';
					values.forEach(function(value){
						if (col > numCols){
							inputTrs += '</tr><tr>';
							col = 1;
						}
						inputTrs += string.substitute(cellTemplate, {name: name, label: value, value: value, inputType: inputType, widget: widget});
						col += 1;
					});
					params.inputTrs = inputTrs + '</tr>';
					break;
    			case 'MultiGridCheckInput':
    				var values = params.values.split(','), numCols = values.length, topics = params.topics.split(','), name = params.name, inputTrs = '<tr><td></td>',
						inputTemplate = '<td style="text-align:center;"><input ${inputType} data-dojo-type="dijit/form/${widget}" name="${name}" value="${value}"/></td>', inputType = params.uniquechoice ? 'type="radio"' : "", 
						widget = params.uniquechoice ? "RadioButton" : "CheckBox";
					values.forEach(function(value){
						inputTrs += '<td style="width:' + 100/(numCols+1) + '%;margin: auto;text-align:center">' + value + '</td>';
					});
					inputTrs += '</tr>';
					topics.forEach(function(topic){
						inputTrs += '<tr><td>' + topic + '</td>';
						values.forEach(function(value){
							inputTrs += string.substitute(inputTemplate, {inputType: inputType, widget: widget, name: topic, value: value});
						});
						inputTrs += '</tr>';
					});
					params.inputTrs = inputTrs;
					break;
    			case 'DropdownList':
    				var values = params.values.split(','), length = values.length;
    				//delete params.values;
    				optionTemplate = '<option value="${value}>${option}</option>', options='';
					values.forEach(function(value){
						options += string.substitute(optionTemplate, {value: value});
				});
				params.options = options;
    				
    		}
    		return string.substitute(widgetHolder, lang.mixin(params, {visualPreTag: visualTag, visualPostTag: visualTag, widgetSource: string.substitute(widgetTemplates[type], params)}));
    	},
		targetHTML: function(params){
			var node = dct.toDom(params.type === 'raw' ? params.content : '<div>' + this.sourceHTML(params, utils.visualTag()) + '</div>');
			return parser.parse(node).then(function(instances){
				return node.innerHTML;
			});
		}
	}
});
