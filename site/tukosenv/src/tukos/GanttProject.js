define(["dojo/_base/declare", "dijit/_WidgetBase", "dojo/json", "dojo/store/Memory", "dojo/parser",	"dojo/query",
		"dojox/gantt/GanttChart", "dojox/gantt/GanttProjectItem", "dojox/gantt/GanttTaskItem", "dojo/dom"/*, "dojo/domReady!"*/], 
function(declare, Widget, JSON, Memory, parser, query, GanttChart, GanttProjectItem, GanttTaskItem, dom){
    return declare("tukos.GanttProject", Widget, {

        constructor: function(args){
            var date;
            if (typeof args.startDate == 'string' && args.startDate != ''){
                var day    = parseInt(args.startDate.substring(0,2));
                var month  = parseInt(args.startDate.substring(3,5));
                var year   = parseInt(args.startDate.substring(6,10)); 
                date   = new Date(year, month-1, day)
            }else{
                date = new Date;
            }         
            this.project = new new GanttProjectItem({id: args.projectId, name: args.title, startDate: date});
            for (i in args.value){
                var task = new GanttTaskItem(value[i]);
                this.project.addTask(task);
            }
            ganttChart = new GanttChart(args.gantt, this.domNode);
			ganttChart.addProject(project);
			ganttChart.init();
            none = "none";
        }
    });
}); 

