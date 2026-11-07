self.dojoConfig = {async: 1, packages: [{"name":"dojo","location":"/tukos/tukosenv/src/dojo"}, {"name":"tukos","location":"/tukos/tukosenv/src/tukos"}]};
importScripts("https://localhost/tukos/tukosenv/src/dojo/dojo.js");
require(["tukos/maths/matrices"], function(matrices){
	console.log('matrices is loaded');
	self.postMessage('ready');			
});
onmessage = (e) => {
	console.log("Worker: message received from main script: " + e.data);
	switch(e.data){
		case 'isReady?':
			console.log('executing isReady');
			if (self.isReady){
				self.postMessage('ready');			
			}else{
				self.postMessage('notReady');			
			}
			break;
		case 'dofirst':
			self.isReady = false;	
			console.log('executing first');
			self.isReady = true;	
			self.postMessage('firstdone');
			break;
		case 'dosecond':
			self.isReady = false;	
			console.log('executing second');
			self.isReady = true;
			self.postMessage('seconddone');
	}
}