define (["dojo/cookie", "dojo/request/iframe", "tukos/PageManager"], 
    function(cookie, iframe, Pmg){
    return {
        download: function(urlArgs, options){
            var stamp = new Date().getTime();
            urlArgs.query.downloadtoken = stamp;
            var dfd = iframe(Pmg.requestUrl(urlArgs), options || {});
            var intervalId = setInterval(
                function(){
                    var cookieStamp = cookie('downloadtoken');
                    if (stamp.toString() === cookieStamp){
                        iframe._currentDfd.cancel();
                        clearInterval(intervalId);
                    }
                },
                1000
            );
        },
        customContextMenu: function(){
            rowData = this.row(evt).data;
            if (rowData.id > 0 && rowData.size > 0){
                var onClickCallBack = function(evt){
                    this.download({object: 'documents', view: 'NoView', action: 'Download', query: {id: rowData.id}});
                }
                return {row: [{label: "download", onClick: onClickCallback}], idCol:  [{label: "download", onClick: onClickCallBack}]};
            }else{
                return{};
            }
        }
    }
});
