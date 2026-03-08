<!DOCTYPE HTML>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title> Test Gauss integration</title>
        <link rel="stylesheet" href="tukosenv/src/dijit/themes/claro/claro.css" media="screen">
 
    </head>
    <body class="claro">
		<b>Gauss integration (see console):</b>
		<div id='Gauss integration'></div>
        <script>var dojoConfig ={
                    baseUrl: "", isDebug: true, async: true, locale: "en-en",
                    packages: [{"name": "dojo", "location": "tukosenv/src/dojo"},   {"name": "dijit", "location": "tukosenv/src/dijit"},  
                               {"name": "tukos", "location": "tukosenv/src/tukos"}
                    ],
                };
        </script>
        <script src="tukosenv/src/dojo/dojo.js"></script>
		<script>
		    require(['tukos/maths/utils'], function (mathUtils) {
				const func1 = function(x){
					return 7* x**3 - 8 * x**2 - 3 * x + 3;
				}
				const int1 = mathUtils.gaussIntegration(func1, 3);
				console.log('int1: ' + int1);
				const func2 = function(coord){
					return coord[0]**2 * coord[1]**2 + coord[2] **2;
				}
				const int2 = mathUtils.multiDimensionalGaussIntegation(func2, 3, 3);
				console.log('int2: ' + int2);
	    	});
		</script>
    </body>
</html>
