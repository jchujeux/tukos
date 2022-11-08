"use strict";
define(["dojo/dom", "tukos/login", "tukos/PageManager"], 
function(dom, login, Pmg){
    return {
		onSuccess: function(response){
			const serverFeedback = dom.byId('svrFeedback');
            serverFeedback.innerHTML = '<i>' + response + '</i>';
            serverFeedback.style.display = "block";
            document.location.reload();
        },
        onError: function(error){
			const serverFeedback = dom.byId('svrFeedback');
            serverFeedback.innerHTML = "<i>" + Pmg.message('authenticationfailed') + " - " + JSON.parse(error.response.data).join() + "</i>";
            serverFeedback.style.display = "block";
        }
    };
});
