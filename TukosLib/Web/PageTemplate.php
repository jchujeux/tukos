<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8"> 
        <title>Tukos 2.0</title>
        <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dijit/themes/claro/claro.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dgridLocation ?>/css/dgrid.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dojox/editor/plugins/resources/editorPlugins.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dojox/editor/plugins/resources/css/StatusBar.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dojox/editor/plugins/resources/css/FindReplace.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dojox/editor/plugins/resources/css/InsertEntity.css" media="screen">
		<link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dojox/calendar/themes/claro/Calendar.css" media="screen">
        <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dojox/form/resources/UploaderFileList.css" media="screen">
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
                    map: {'dojo' : {'dojo/dnd/Selector': "dojoFixes/dojo/dnd/Selector"}, 'dijit/Menu': {'dijit/popup': 'dojoFixes/dijit/popup'}, dgrid: {'dgrid/List': 'dojoFixes/dgrid/List'}, 'dojox/charting/plot2d': {'dojox/charting/plot2d/Default': 'dojoFixes/dojox/charting/plot2d/Default'},
                    	  'dojox/mobile': {'dojox/mobile/SpinWheel': 'dojoFixes/dojox/mobile/SpinWheel', 'dojox/mobile/SpinWheelSlot': 'dojoFixes/dojox/mobile/SpinWheelSlot'},
                    	  'tukos/mobile': {'dojox/mobile/SpinWheel': 'dojoFixes/dojox/mobile/SpinWheel', 'dojox/mobile/SpinWheelSlot': 'dojoFixes/dojox/mobile/SpinWheelSlot'}}
                };
        </script>
        <script src="<?= $this->dojoBaseLocation ?>dojo/dojo.js"></script>
        <script>
        	if (<?= $this->enableOffline ? 'false' : 'true' ?>){
        		if ("serviceWorker" in navigator){
        			navigator.serviceWorker.getRegistrations().then( function(registrations) { for(let registration of registrations) { registration.unregister(); } }); 
        		}
        	}else{
        	  	const registerServiceWorker = async () => {
            	  if ("serviceWorker" in navigator) {
            	    try {
            	      const registration = await navigator.serviceWorker.register("/tukos/tukosServiceWorker.js", {
            	        scope: "/tukos/",
            	      });
            	      if (registration.installing) {
            	        console.log("Service worker installing");
            	      } else if (registration.waiting) {
            	        console.log("Service worker installed");
            	      } else if (registration.active) {
            	        console.log("Service worker active");
            	      }
            	    } catch (error) {
            	      console.error(`Registration failed with ${error}`);
            	    }
            	  }
            	};
            	registerServiceWorker();
        	}
        </script>
        <script>
            <!-- Page layout -->
			require(["tukos/PageManager"], 
            	function(PageManager){
                	PageManager.initialize(<?= $this->/*__raw()->*/pageManagerArgs;?>);
            		document.body.className += ' loaded';
        	});
        </script>
    </body>
</html>
