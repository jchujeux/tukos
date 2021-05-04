
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Demo: Horizontall Slider with Rules and RuleLabels</title>
	<link rel="stylesheet" href="../../_common/demo.css" media="screen">
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/dojo/1.14.1/dijit/themes/claro/claro.css" media="screen">
</head>
<body class="claro">
<h1>Demo: Simple Horizontal Slider</h1>

<div style="width: 400px;">
	<input type="range" value="3" data-dojo-type="dijit/form/HorizontalSlider" data-dojo-props="minimum: 0, maximum: 5, showButtons: false, discreteValues: 6"></input>
	<div data-dojo-type="dijit/form/HorizontalRule" data-dojo-props="container: 'bottomDecoration',	count: 6" style="height: 5px; margin: 0 12px;"></div>
	<ol data-dojo-type="dijit/form/HorizontalRuleLabels" data-dojo-props="container: 'bottomDecoration'" style="height: 2em; margin: 0 12px; font-weight: bold;">
		<li>0</li><li>1</li><li>2</li><li>3</li><li>4</li><li>5</li>
	</ol>
</div>
<div id="mycalendar"></div>

<!-- load dojo and provide config via data attribute -->
<script src="https://ajax.googleapis.com/ajax/libs/dojo/1.14.1/dojo/dojo.js" data-dojo-config="isDebug: true, async: true"></script>
<script>

	// Load the dependencies
	require(["dijit/form/HorizontalSlider", "dijit/form/HorizontalRuleLabels", "dijit/form/HorizontalRule", "dojo/parser", "dojox/calendar/MobileCalendar", "dojo/Store/Memory", "dojo/Store/Observable", "dojo/domReady!"],
			function(HorizontalSlider, HorizontalRuleLabels, HorizontalRule, parser, Calendar, Memory, Observable){

				parser.parse();
				var cal = new Calendar({store: new Observable(new Memory({data: []}))}, "mycalendar");

			});

</script>
</body>
</html>
