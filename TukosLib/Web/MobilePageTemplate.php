<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <title>Tukos mobile</title>
    <!-- application stylesheet will go here -->
    <!-- dynamically apply native visual theme according to the browser user agent -->
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/dojo/1.10.4/dojox/mobile/deviceTheme.js"></script>
    <!-- dojo configuration options -->
    <script type="text/javascript">
        dojoConfig = {
            async: true,
            parseOnLoad: false,
            packages: [{"name": "tukos", "location": "<?= $this->tukosDir ?>"}]
        };
    </script>
    <!-- dojo bootstrap -->
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/dojo/1.10.4/dojo/dojo.js"></script>
    <!-- dojo application code -->
    <script type="text/javascript">
        require([
            //"dojox/mobile/parser",
            "dojox/mobile", "dojox/mobile/view", "dojox/mobile/heading",
            "dojox/mobile/compat",
            "dojo/domReady!"
        ], function (Mobile, View, Heading) {
            // now parse the page for widgets
            var entryView = new View(null, "tukosMobileView"), entryHeading = new Heading({label: "Homepage View"});
            entryView.addChild(entryHeading);
        });
    </script>
</head>
<body style="visibility:hidden;">
    <div id="tukosMobileView"></div>
</body>
</html>