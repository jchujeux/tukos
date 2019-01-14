<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Tukos 2.0</title>
        <link rel="stylesheet" href="<?= $this->dojoDir ?>dijit/themes/claro/claro.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoDir ?>dgrid/css/dgrid.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoDir ?>dojox/editor/plugins/resources/editorPlugins.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoDir ?>dojox/editor/plugins/resources/css/StatusBar.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoDir ?>dojox/editor/plugins/resources/css/FindReplace.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoDir ?>dojox/calendar/themes/claro/Calendar.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoDir ?>dojox/form/resources/UploaderFileList.css" media="screen">
        <link rel="stylesheet" href="<?= $this->jsTukosDir ?>/resources/tukos.css" media="screen">
    </head>
    <body class="claro">
		<div id="loadingOverlay" class="loadingOverlay pageOverlay">
			<div class="loadingMessage"><?= $this->loadingMessage ?></div>
		</div>
        <audio id="beep" src="<?= $this->jsTukosDir ?>/sounds/beep.wav"></audio>
        <div id="appLayout" class="demoLayout" >
        </div>

        <!-- load dojo and provide config via data attribute -->

        <script>var dojoConfig ={
                    //baseUrl: "", isDebug: false, 
                    async: true, locale: "<?= $this->language ?>",
        			selectorEngine: 'lite',
					packages: <?= $this->__raw()->packagesString ?>,
                    map: {'dojo' : {'dojo/dnd/Selector': "dojoFixes/dojo/dnd/Selector"},/* '*': {'dojo/dnd/common': 'dojoFixes/dojo/dnd/common'}, */'dijit/Menu': {'dijit/popup': 'dojoFixes/dijit/popup'}},
                    //transparentColor: [255,255,255,0]
                };
        </script>
        <script src="<?= $this->dojoDir ?>dojo/dojo.js"></script>

        <script>
            <!-- Page layout -->
			require(["tukos/PageManager"], 
            	function(PageManager){
                	PageManager.initialize(<?= $this->__raw()->pageManagerArgs;?>);
            		document.body.className += ' loaded';
        	});
            </script>
    </body>
</html>
