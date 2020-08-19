<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Image inside a dijit Dialog</title>
        <link rel="stylesheet" href="tukosenv/src/dijit/themes/claro/claro.css" media="screen">
 
    </head>
    <body class="claro">
		<b>Dialog:</b>
		<div id='result'></div>
        <script>var dojoConfig ={
                    baseUrl: "", isDebug: true, async: true, locale: "en-en",
                    packages: [{"name": "dojo", "location": "tukosenv/src/dojo"},   {"name": "dijit", "location": "tukosenv/src/dijit"},  
                    	{"name": "tukos", "location": "tukosenv/src/tukos"}, {"name": "dojoFixes", "location": "tukosenv/src/dojoFixes"}
                    ]
                };
        </script>
        <script src="tukosenv/src/dojo/dojo.js"></script>
		<script>
		    require(["dojo/date/stamp", "dojo/dom", "dojoFixes/dojo/date"], function(stamp, dom, date){
		    	var SDate=new Date(stamp.fromISOString("2020-08-07T00:00:01Z"));
		    	var tempDate = date.add(SDate, "weekday", 2);
		    	var result = stamp.toISOString(tempDate, {zulu: true});
		    	console.log('the result is: ' + result);
		    	dom.byId("result").innerHTML = result;
	    	});
		</script>
    </body>
</html>
