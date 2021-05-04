define (["dojo/_base/declare", "tukos/StoreDgridNoDnd", "tukos/_GridDndMixin"], 
    function(declare, StoreDgridNoDnd, _GridDndMixin){
    	return declare([StoreDgridNoDnd, _GridDndMixin], {}); 
	}
);
