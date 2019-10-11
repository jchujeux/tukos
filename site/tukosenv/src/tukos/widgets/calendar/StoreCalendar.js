define (["dojo/_base/declare", "dojox/calendar/Calendar", "tukos/widgets/calendar/_StoreCalendarMixin", "tukos/widgets/calendar/VerticalRenderer"], 
    function(declare, Calendar, _StoreCalendarMixin, VerticalRenderer){
    return declare([Calendar, _StoreCalendarMixin], {
        postCreate: function(){
            this.columnView.set('verticalRenderer', VerticalRenderer);
            this.inherited(arguments);
        }
    }); 
});
