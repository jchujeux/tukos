<!DOCTYPE HTML>
<html lang="en">
       <!-- Displays a login form and submits a POST for script validation on the server side -->
	<head>
		<meta  http-equiv="Content-Type" content="text/html;" charset="utf-8">
		<title>Login into the system</title>
	</head>
	<body style="text-align: center;">
		<script>
			function hideSvrFeedback(){
				var svrFeedback = document.getElementById('svrFeedback');
				svrFeedback.style.display = "none";
			}
		</script>
		<table style="margin-left: auto; margin-right: auto;">
			<tr>
				<td><img alt="logo" src="<?= $this->logo?>"><br><?= $this->headerBanner ?></td>
				<td>
            		<form id="formNode">
                		<h1><?= $this->authentication?></h1>
                		<div id="svrFeedback"><i><?= $this->serverFeedback ?></i></div>
            			<table style="margin-left: auto; margin-right: auto;">
            				<tr><th><?= $this->username?>: </th><td><input type="text" name="username" oninput="hideSvrFeedback()" /></td></tr>
            				<tr><th><?= $this->password?>: </th><td><input type="password" name="password" oninput="hideSvrFeedback()" /></td></tr>
            				<tr><th></th><td><button type="submit"><?= $this->login?></button></td></tr>
            			</table>
            		</form>
				</td>
			</tr>
		</table>
		<!-- load dojo and provide config via data attribute -->
            <script src="<?= $this->dojoBaseLocation ?>dojo/dojo.js" daja-dojo-config="async: true"></script>
		    <script>
			require(["dojo/dom", "dojo/on", "dojo/request", "dojo/dom-form", "dojo/domReady!"],
				function(dom, on, request, domForm){
					var form = dom.byId('formNode');
					// Attach the onsubmit event handler of the form
					on(form, "submit", function(evt){
						evt.stopPropagation();
						evt.preventDefault();
						request.post("<?= $this->requestUrl ?>",	{data: domForm.toObject("formNode"), timeout: 2000}).then(
                                                function(response){
													var serverFeedback = dom.byId('svrFeedback');
                                                    serverFeedback.innerHTML = '<i>' + response + '</i>';
                                                    serverFeedback.style.display = "block";
                                                    document.location.reload();
                                                },
                                                function(error){
													var serverFeedback = dom.byId('svrFeedback');
                                                    serverFeedback.innerHTML = "<i><?= $this->error; ?></i>";
                                                    serverFeedback.style.display = "block";
                                                }
                                          );
					});
				}
			);
		    </script>
	</body>
</html>
