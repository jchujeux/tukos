<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <title>Tukos mobile</title>
    <script type="text/javascript" src="<?= $this->dojoBaseLocation ?>dojox/mobile/deviceTheme.js"></script>
    <!--  link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dijit/themes/claro/claro.css" media="screen">
    <!-- <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dijit/themes/dijit.css" media="screen"> -->
    <!--  <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dijit/themes/claro/Editor.css" media="screen"> -->
    <!--  <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dijit/icons/editorIcons.css" media="screen"> -->
    <link rel="stylesheet" href="<?= $this->dgridLocation ?>/css/dgrid.css" media="screen">
    <!--    <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dojox/calendar/themes/claro/Calendar.css" media="screen"> -->
    <!--    <link rel="stylesheet" href="<?= $this->dojoBaseLocation ?>dojox/form/resources/UploaderFileList.css" media="screen"> -->
    <link rel="stylesheet" href="<?= $this->tukosLocation ?>/resources/tukos.css" media="screen">
    <link rel="stylesheet" href="<?= $this->tukosLocation ?>/mobile/resources/tukos.css" media="screen">
</head>
<body class="claro">
	<div id="loadingOverlay" class="loadingOverlay pageOverlay">
			<div class="loadingMessage"><?= $this->loadingMessage ?></div>
	</div>
    <audio id="beep" src="<?= $this->tukosLocation ?>/sounds/beep.wav"></audio>
    <!-- dojo configuration options -->
    <script type="text/javascript">
        dojoConfig = {async: true, locale: "<?= $this->language ?>", selectorEngine: 'lite', packages: <?= $this->__raw()->packagesString ?>};
    </script>
    <!-- dojo bootstrap -->
    <script type="text/javascript" src="<?= $this->dojoBaseLocation ?>dojo/dojo.js"></script>
    <!-- dojo application code -->
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