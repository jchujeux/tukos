"use strict";
define(["dojo/_base/lang", "dojo/dom", "tukos/login"], 
function(lang, dom, login){
    return {
		loginValidationUrl: '',
		windowOnLoad: function(loginValidationUrl, clientId){
	        const self = this;
	        this.loginValidationUrl = loginValidationUrl;
	        window.onload = function () {
	          google.accounts.id.initialize({
	            client_id: clientId,
	            callback: lang.hitch(self, self.handleCredentialResponse)
	          });
	          google.accounts.id.renderButton(
	            document.getElementById("buttonDiv"),
	            { theme: "outline", size: "large"}  // customization attributes
	          );
	          google.accounts.id.prompt(); // also display the One Tap dialog
	        };
		},
	    handleCredentialResponse: function(response) {
			const self = this;
			require(["dojo/dom", "dojo/request"], function(dom, request){
        		request.post(self.loginValidationUrl,	{data: {credential: response.credential}, timeout: 2000}).then(
                    function(response){
                    	login.onSuccess(response);
                    },
                    function(error){
						login.onError(error);
                    }
                );
            });
        }
    };
});
