<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Tukos 2.0</title>
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
                    baseUrl: "", isDebug: false, async: true, locale: "<?= $this->language ?>",
        			selectorEngine: 'lite',
        			tlmSiblingOfDojo: false,
                    packages: [{"name": "dojo", "location": "<?= $this->dojoDir ?>dojo"},   {"name": "dijit", "location": "<?= $this->dojoDir ?>dijit"},  
                               {"name": "dojox", "location": "<?= $this->dojoDir ?>dojox"}, {"name": "dstore", "location": "<?= $this->dojoDir ?>dstore"},
                               {"name": "dgrid", "location": "<?= $this->dojoDir ?>dgrid"},
                               {"name": "tukos", "location": "<?= $this->jsTukosDir ?>"}, {"name": "dojoFixes", "location": "<?= $this->dojoFixesDir ?>"},
                               {"name": "redips", "location": "<?= $this->redipsDir ?>"},
                    ],
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
