<!DOCTYPE HTML>
<html lang="en">
       <!-- Displays a login form and submits a POST for script validation on the server side -->
	<head>
		<meta  http-equiv="Content-Type" content="text/html;" charset="utf-8">
		<title>Login into the system</title>
	</head>
	<body style="text-align: center;">
		<table style="margin-left: auto; margin-right: auto;">
			<tr><td>
            		<form id="formNode">
                		<h1><?= $this->authentication?></h1>
                		<div id="svrFeedback"><i><?= $this->serverFeedback ?></i></div>
            			<table style="margin-left: auto; margin-right: auto;">
            				<tr><th></th><td><div id="buttonDiv"></div></td></tr>
            				<?= $this->loginPwd ?>
            			</table>
            		</form>
			</td></tr>
			<tr><td><img alt="logo" src="<?= $this->logo?>"><br><a href="<?= $this->orgLink?>" target="_blank"><?= $this->headerBanner ?></span></td></tr>
			<tr><td><br><?= $this->confidentialityPolicy ?></td></tr>
			
		</table>
		<!-- load dojo and provide config via data attribute -->
        <script src="<?= $this->dojoBaseLocation ?>dojo/dojo.js" daja-dojo-config="async: true"></script>
      <script src="https://accounts.google.com/gsi/client" async defer></script>
     
		<script>
			function hideSvrFeedback(){
				var svrFeedback = document.getElementById('svrFeedback');
				svrFeedback.style.display = "none";
			}
		</script>
		    <script>
			require(["dojo/dom", "dojo/on", "dojo/request", "dojo/dom-form", "tukos/login", "tukos/google/clientOAuth", "tukos/PageManager"],
				function(dom, on, request, domForm, login, clientOAuth, Pmg){
        			Pmg.initializeNoPage(<?= $this->pageManagerArgs ?>);
					clientOAuth.windowOnLoad("<?= $this->requestGoogleValidationUrl ?>");
					const form = dom.byId('formNode');
					// Attach the onsubmit event handler of the form
					on(form, "submit", function(evt){
						evt.stopPropagation();
						evt.preventDefault();
						request.post("<?= $this->requestUrl ?>",	{data: domForm.toObject("formNode"), timeout: 2000}).then(
                            function(response){
                            	login.onSuccess(response);
                            },
                            function(error){
								login.onError(error);
                            }
                      );
					});
				}
			);
		    </script>
	</body>
</html>
