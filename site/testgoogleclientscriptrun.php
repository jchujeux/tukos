<!DOCTYPE html>
<html>
  <head>
    <title>Google Apps Script API Quickstart</title>
    <meta charset="utf-8" />
  </head>
  <body>
    <p>Google Apps Script API Quickstart</p>

    <!--Add buttons to initiate auth sequence and sign out-->
    <button id="authorize_button" style="display: none;">Authorize</button>
    <button id="signout_button" style="display: none;">Sign Out</button>

    <pre id="content" style="white-space: pre-wrap;"></pre>

    <script type="text/javascript">
      // Client ID and API key from the Developer Console
      var CLIENT_ID = '722055405701-ukrl22bn0e6s3r0rfkmvflo7q8gjv43v.apps.googleusercontent.com';
      var API_KEY = 'AIzaSyCei4MoC6rmMqjOsVCaXIKRiF-CaTb3f2I';

      // Array of API discovery doc URLs for APIs used by the quickstart
      var DISCOVERY_DOCS = ["https://script.googleapis.com/$discovery/rest?version=v1"];

      // Authorization scopes required by the API; multiple scopes can be
      // included, separated by spaces.
      var SCOPES = 'https://www.googleapis.com/auth/script.projects https://www.googleapis.com/auth/forms';

      var authorizeButton = document.getElementById('authorize_button');
      var signoutButton = document.getElementById('signout_button');

      /**
       *  On load, called to load the auth2 library and API client library.
       */
      function handleClientLoad() {
        gapi.load('client:auth2', initClient);
      }

      /**
       *  Initializes the API client library and sets up sign-in state
       *  listeners.
       */
      function initClient() {
        gapi.client.init({
          apiKey: API_KEY,
          clientId: CLIENT_ID,
          discoveryDocs: DISCOVERY_DOCS,
          scope: SCOPES
        }).then(function () {
          // Listen for sign-in state changes.
          gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

          // Handle the initial sign-in state.
          updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
          authorizeButton.onclick = handleAuthClick;
          signoutButton.onclick = handleSignoutClick;
        }, function(error) {
          appendPre(JSON.stringify(error, null, 2));
        });
      }

      /**
       *  Called when the signed in status changes, to update the UI
       *  appropriately. After a sign-in, the API is called.
       */
      function updateSigninStatus(isSignedIn) {
        if (isSignedIn) {
          authorizeButton.style.display = 'none';
          signoutButton.style.display = 'block';
          callAppsScript();
        } else {
          authorizeButton.style.display = 'block';
          signoutButton.style.display = 'none';
        }
      }

      /**
       *  Sign in the user upon button click.
       */
      function handleAuthClick(event) {
        gapi.auth2.getAuthInstance().signIn();
      }

      /**
       *  Sign out the user upon button click.
       */
      function handleSignoutClick(event) {
        gapi.auth2.getAuthInstance().signOut();
      }

      /**
       * Append a pre element to the body containing the given message
       * as its text node. Used to display the results of the API call.
       *
       * @param {string} message Text to be placed in pre element.
       */
      function appendPre(message) {
        var pre = document.getElementById('content');
        var textContent = document.createTextNode(message + '\n');
        pre.appendChild(textContent);
      }

      /**
       * Shows basic usage of the Apps Script API.
       *
       * Call the Apps Script API to create a new script project, upload files
       * to the project, and log the script's URL to the user.
       */
      function callAppsScript() {
    	  var scriptId = "1dBua1fOU7lBsnSrlSmkuC3ttXdMId6yWJzgU6YYJOV1KXyI76ISkth0H";
		  //var scriptId = "MQhtFXtLgPADkObmKghB4--v8KNRW3u7y";
    	  // Call the Apps Script API run method
    	  //   'scriptId' is the URL parameter that states what script to run
    	  //   'resource' describes the run request body (with the function name
    	  //              to execute)
    	  //gapi.client.script.scripts.run({
         gapi.client.script.projects.get({
    	    'scriptId': scriptId,
    	    //'resource': {
    	      //'function': 'myGoogleForm',
    	      //'devMode': true
    	    //}
    	  }).then(function(resp) {
    	    var result = resp.result;
    	    if (result.error && result.error.status) {
    	      // The API encountered a problem before the script
    	      // started executing.
    	      appendPre('Error calling API:');
    	      appendPre(JSON.stringify(result, null, 2));
    	    } else if (result.error) {
    	      // The API executed, but the script returned an error.

    	      // Extract the first (and only) set of error details.
    	      // The values of this object are the script's 'errorMessage' and
    	      // 'errorType', and an array of stack trace elements.
    	      var error = result.error.details[0];
    	      appendPre('Script error message: ' + error.errorMessage);

    	      if (error.scriptStackTraceElements) {
    	        // There may not be a stacktrace if the script didn't start
    	        // executing.
    	        appendPre('Script error stacktrace:');
    	        for (var i = 0; i < error.scriptStackTraceElements.length; i++) {
    	          var trace = error.scriptStackTraceElements[i];
    	          appendPre('\t' + trace.function + ':' + trace.lineNumber);
    	        }
    	      }
    	    } else {
    	      // The structure of the result will depend upon what the Apps
    	      // Script function returns. 

    	       appendPre('Folders under your root folder:');
    	    }
    	  });
      }

    </script>

    <script async defer src="https://apis.google.com/js/api.js"
      onload="this.onload=function(){};handleClientLoad()"
      onreadystatechange="if (this.readyState === 'complete') this.onload()">
    </script>
  </body>
</html>