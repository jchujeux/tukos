define (["dojo/_base/declare", "tukos/SimpleDgridNoDnd", "tukos/_GridDndMixin"], 
    function(declare, SimpleDgridNoDnd, _GridDndMixin){
    	return declare([SimpleDgridNoDnd, _GridDndMixin], {}); 
	}
);
