define (["dojo/_base/declare", "dojo/dom", "dojo/on", "dojo/request", "dijit/form/Button", "dijit/registry"], 
    function(declare, dom, on, request, Button, registry){
    return declare(Button, {
        postCreate: function(){
          var self = this;
          this.inherited(arguments);
          on(this, "click", function(evt){
  	    	//request("http://localtukos.com:12021/Jean-Claude%20Hujeux?metrics=TriScore&since=2019/12/01", {jsonp: "callback", method: 'GET'}).then(
    	    request("https://localtukos.com:12020/", {headers: {"X-Requested-With": null}}).then(
    	    //request("http://127.0.0.1:12021").then(
    	    //request("https://localhost/tukos/index20.php/TukosApp/Dialogue/sptprograms/Edit/Tab/Reset?id=34538&contextpathid=34433&timezoneOffset=-60").then(
        		function(response){
        			//console.log('cheetah is: ' + cheetah);
        			console.log('the response is: ' + response);
        			//widget.set('label', label + '... ' + Pmg.message('sent'));
        		},
        		function(error){
        			console.log('the error is: ', error);
        		}
        	);
          });
        }
    });
});
