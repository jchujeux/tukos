<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <title>Tukos mobile Login</title>
    <script type="text/javascript" src="<?= $this->dojoDir ?>dojox/mobile/deviceTheme.js"></script>
    <!-- dojo configuration options -->
    <script type="text/javascript">
        dojoConfig = {
            async: true,
            parseOnLoad: false,
            packages: [{name: "dojo", location: "<?= $this->dojoDir ?>dojo"}, {name: "tukos", location: "<?= $this->tukosDir ?>tukos"}]
        };
    </script>
    <!-- dojo bootstrap -->
    <script type="text/javascript" src="<?= $this->dojoDir ?>dojo/dojo.js"></script>
    <!-- dojo application code -->
    <script type="text/javascript">

    require([
         "dojo/dom", "dojo/dom-construct", "dojo/request", "dijit/registry", "dojox/mobile", "dojox/mobile/view", "dojox/mobile/heading", "tukos/mobile/FormLayout",
         //"dojox/mobile/compat",
         "dojo/domReady!"
     ], function (dom, dct, request, registry, Mobile, View, Heading, FormLayout) {
         // now parse the page for widgets
        var loginForm = {columns: 'two', rows: [
			{label: {innerHTML: "<?= $this->username?>" }, widget: {type: 'MobileTextBox', atts: {id: 'username', title: '<?= $this->username?>', onInput: function(){dom.byId('svrFeedback').hidden = true}}}}, 
			{label: {innerHTML: "<?= $this->password?>"}, widget: {type: 'MobileTextBox', atts: {id: 'password', title: '<?= $this->password?>', type: 'password'}}},
			{label: {}, widget: {type: 'MobileButton', atts: {title: 'login', label: '<?= $this->login?>', onClick: function(evt){
				request.post("<?= $this->requestUrl ?>", {data: {username: registry.byId('username').get('value'), password: registry.byId('password').get('value')}, timeout: 2000}).then(
                    function(response){
						var feedback = dom.byId('svrFeedback');
                        feedback.innerHTML = '<i>' + response + '</i>';
                        feedback.hidden = false;
                        document.location.reload();
                	},
                	function(error){
						var feedback = dom.byId('svrFeedback');
                        feedback.innerHTML = "<i><?= $this->error; ?></i>";
                        feedback.hidden = false;
                	}
          		);
			}}}}
		]};
        var loginView = new View(null, "loginView"), loginHeading = new Heading({label: "Tukos - <?= $this->authentication?>"}), formLayout = new FormLayout(loginForm);

        loginView.addChild(loginHeading);
        loginView.addChild(formLayout); 
		loginView.startup();
		var svrFeedbackDiv = dct.create('div', {hidden: true, id: 'svrFeedback'}, loginView.domNode);
    });
</script>
</head>
<body style="visibility:hidden;">
    <div id="loginView"></div>
</body>
</html>