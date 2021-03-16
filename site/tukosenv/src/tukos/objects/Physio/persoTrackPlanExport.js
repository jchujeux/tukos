define(["dojo/_base/declare", "tukos/PageManager"], 
function(declare, Pmg){
    return {
		paneDescription: {
			widgetsDescription:{
            	exercisestable: {type: 'HtmlContent', atts: {label: Pmg.message('exercisestable'), style: {minHeight: 100, backgroundColor: 'White'}}}
			},
			layout: {
				contents: {
					row8: {
						tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, labelWidth: 100},
						widgets: ['exercisestable']
					}
				}
			},
			openAction: function(){
				var form = this.form, exercisesWidget = form.getWidget('exercises'), collection = exercisesWidget.collection, i= 0, nuToC = this.nuToC,
					tableContent = '<table border="1" cellpadding="0" cellspacing="0" style="break-inside: avoid; margin-left: auto; margin-right: auto; text-align: left; width: 80%;"><tr><td colspan=2 style="text-align: center;"><H2>Liste d\'exercises</H2></td></tr>';
				collection.fetchSync().forEach(function(exercise){
					i += 1;
					tableContent += '<tr><td style="vertical-align: top;text-align:center;width: 2em;">' + i + '</td><td>' + exercise.name + ' ' + nuToC(exercise.series) + '*' + nuToC(exercise.repeats) + ' ' + (exercise.extra || '') + '<p>' + (exercise.progression || '') + '</td></tr>';
				});
				tableContent += '</table>';
				this.setValueOf('exercisestable', tableContent);
			},
			nuToC: function(numberUnit){//["10","repetition"] 
				if (numberUnit){
					var values = JSON.parse(numberUnit);
					return values[0] + ' ' + values[1] + 's';
				}else{
					return '';
				}
			}
		}
	}
});
