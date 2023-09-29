<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <title>Tukos mobile Login</title>
    <script type="text/javascript">
    	dojoBaseLocation = "<?= $this->dojoBaseLocation ?>";<!-- used in deviceTheme.js -->
    </script>
    <script type="text/javascript" src="<?= $this->dojoBaseLocation ?>dojox/mobile/deviceTheme.js"></script>
    <!-- dojo configuration options -->
    <script type="text/javascript">
        dojoConfig = {
            async: true,
            parseOnLoad: false,
            packages: [{name: "dojo", location: "<?= $this->dojoBaseLocation ?>dojo"}, {name: "tukos", location: "<?= $this->tukosBaseLocation ?>tukos"}]
        };
    </script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <!-- dojo bootstrap -->
    <script type="text/javascript" src="<?= $this->dojoBaseLocation ?>dojo/dojo.js"></script>
    <!-- dojo application code -->
    <script type="text/javascript">
    require(["dojo/dom", "dojo/dom-construct", "dojo/request", "dijit/registry", "dojox/mobile", "dojox/mobile/View", "dojox/mobile/Heading", "tukos/mobile/FormLayout", "dojox/mobile/Pane", 
    	"tukos/login", "tukos/google/clientOAuth", "tukos/PageManager"], function (dom, dct, request, registry, Mobile, View, Heading, FormLayout, Pane, login, clientOAuth, Pmg) {
         // now parse the page for widgets
        Pmg.initializeNoPage(<?= $this->pageManagerArgs ?>);
		clientOAuth.windowOnLoad("<?= $this->requestGoogleValidationUrl ?>");
        var loginForm = {columns: 'two', rows: [
			{label: {innerHTML: "<?= $this->username?>" }, widget: {type: "TextBox", atts: {id: "username", title: "<?= $this->username?>", mobileWidgetType: "TextBox", onInput: function(){dom.byId('svrFeedback').hidden = true}}}}, 
			{label: {innerHTML: "<?= $this->password?>"}, widget: {type: 'TextBox', atts: {id: 'password', mobileWidgetType: "TextBox", title: "<?= $this->password?>", type: "password"}}},
			{label: {}, widget: {type: 'MobileButton', atts: {title: 'login', label: "<?= $this->login?>", onClick: function(evt){
				request.post("<?= $this->requestUrl ?>", {data: {username: registry.byId('username').get('value'), password: registry.byId('password').get('value')}, timeout: 2000}).then(
                    function(response){
						login.onSuccess(response);
                	},
                	function(error){
						login.onError(error);
                	}
          		);
			}}}}
		]};
        var loginView = new View(null, "loginView"), loginHeading = new Heading({label: "<?= $this->authentication?>"}), formLayout = new FormLayout(loginForm), 
        	logoWidget = new Pane({innerHTML: '<div style="text-align: center;"><img alt="logo" src=<?= $this->logo?> style="max-height: 80px; width: auto;"></div>'}),
        	googleAuthPane = new Pane({innerHTML: '<p><div style="display: flex; justify-content: center; align-items: center;" id="buttonDiv" ></div>'});
        loginView.addChild(logoWidget);
        loginView.addChild(loginHeading);
        loginView.addChild(googleAuthPane);
        <?= $this->addUserNameForm?>
		loginView.startup();
		var svrFeedbackDiv = dct.create('div', {hidden: true, id: 'svrFeedback', style: {textAlign: 'center'}}, loginView.domNode);
		dct.create('div', {style: {textAlign: 'center'}, innerHTML: "<br><a href=\"<?= $this->orgLink?>\" target=\"_blank\"><?= $this->headerBanner ?><br><br><?= $this->confidentialityPolicy ?>"}, loginView.domNode);
    });
</script>
</head>
<body >
<div id="loginView"></div>
</body>
</html>