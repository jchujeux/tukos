define(["dojo/_base/declare"], 
function(declare){
    return declare(null, {
		initialize: function(data, item){
			this.data = data;
			this.index = item ? (item === 'last' ? data.length - 1 : data.indexOf(item)) : 0;
			return this.index < 0 ? false : this.data[this.index];
		},
       	next: function(){
			switch(this.index){
				case this.data.length - 1:
					this.index = this.data.length;
				case this.data.length:
					return false;
				default:
					this.index += 1;
					return this.data[this.index];
			}
		},
       	previous: function(){
			switch(this.index){
				case 0:
					this.index = -1;
				case -1:
					return false;
				default:
					this.index += -1;
					return this.data[this.index];
			}
		},
		current: function(){
			switch (this.index){
				case this.data.length:
				case -1:
					return false;
				default:
					return this.data[this.index];
			}
		}
    });
}); 

