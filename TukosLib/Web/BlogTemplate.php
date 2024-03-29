<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= $this->headerTitle ?></title>
        <?= $this->structuredDataHeaderScript ?>
        <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dijit/themes/claro/claro.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dgridLocation ?>/css/dgrid.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dojox/editor/plugins/resources/css/StatusBar.css" media="screen">
        <link rel="stylesheet" href="<?= $this->tukosLocation ?>/resources/tukos.css" media="screen">
    </head>
    <body class="claro">
		<div id="loadingOverlay" class="loadingOverlay pageOverlay">
			<div class="loadingMessage"><?= $this->loadingMessage ?></div>
		</div>
        <audio id="beep" src="<?= $this->tukosLocation ?>/sounds/beep.wav"></audio>
        <div id="appLayout" class="demoLayout" ></div>
        <!-- load dojo and provide config via data attribute -->
        <script>var dojoConfig ={
                    //baseUrl: "", isDebug: false, 
                    async: true, locale: "<?= $this->language ?>",
        			selectorEngine: 'lite',
					packages: <?= $this->/*__raw()->*/packagesString ?>,
                    map: {'dojo' : {'dojo/dnd/Selector': "dojoFixes/dojo/dnd/Selector"}, 'dijit/Menu': {'dijit/popup': 'dojoFixes/dijit/popup'}, 'dojox/charting/plot2d': {'dojox/charting/plot2d/Default': 'dojoFixes/dojox/charting/plot2d/Default'},
                    	  'dojox/mobile': {'dojox/mobile/SpinWheel': 'dojoFixes/dojox/mobile/SpinWheel', 'dojox/mobile/SpinWheelSlot': 'dojoFixes/dojox/mobile/SpinWheelSlot'},
                    	  'tukos/mobile': {'dojox/mobile/SpinWheel': 'dojoFixes/dojox/mobile/SpinWheel', 'dojox/mobile/SpinWheelSlot': 'dojoFixes/dojox/mobile/SpinWheelSlot'}}
                };
        </script>
        <script src="<?= $this->dojoBaseLocation ?>dojo/dojo.js"></script>
        <script>
            <!-- Page layout -->
			require(["tukos/PageManager"], 
            	function(PageManager){
                	PageManager.initializeBlog(<?= $this->/*__raw()->*/pageManagerArgs;?>);
            		document.body.className += ' loaded';
        	});
        </script>
    </body>
</html>
