onmessage = (e) => {
	switch(e.data.action){
		case 'initialize':
			const baseUrl = e.data.baseUrl, dojoUrl = baseUrl + (e.data.environment === 'production' ? 'workerdojo/' : '');
			self.dojoConfig = {async: 1, packages: [{"name":"dojo","location":`${dojoUrl}dojo`}, {"name":"tukos","location":`${baseUrl}tukos`}], has: {"dom": false, "host-browser": false}};
			importScripts(`${self.origin}${dojoUrl}dojo/dojo.js`);
			self.isReady = true;
			break;
		case 'isReady?':
			if (self.isReady){
				self.postMessage({status: 'ready'});			
			}else{
				self.postMessage({status: 'notReady'});			
			}
			break;
		case 'run':
			self.isReady = false;	
			require(["dojo/when", "dojo/promise/all", "tukos/objects/modeling/rheologies/rheologiesLoader", "tukos/objects/modeling/simulations/" + e.data.simulator, "tukos/utils", "tukos/objects/modeling/Global"], function(when, all, rheologiesLoader, simulator, utils, Global){
				loadRheologies = function(){
					const instantiatingRheologies = [];
					utils.forEach(e.data.globalArgs.groups, function(group){
						if (!rheologiesLoader.isLoaded(group.problemType, group.rheologyType)){
							instantiatingRheologies.push(rheologiesLoader.load(group.problemType, group.rheologyType));
						}
					});
					return utils.empty(instantiatingRheologies) ? true : all(instantiatingRheologies);
				}
				when (loadRheologies(), function(){
					const global = new Global(e.data.globalArgs);
					self.postMessage({status: 'addFeedback', message: '... calling simulator.run()'});
					simulator.run(e.data.mode, global, e.data.diagramOptions,  self, e.data.nodalSolution, e.data.dofNames);
					self.postMessage({status: 'addFeedback', message: 'Simulation completed'});
					self.isReady = true;	
				});
			});
			break;
	}
}