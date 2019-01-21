var api_host = 'http://hws.test.com:8080';
var bucket_name = 'blog';
var appid = ''; // COS APPID
var sign = '';
var filename = 'hws/';

$.extend({
    getUrlParam : function() {
        var aQuery = window.location.href.split("?");  //取得Get参数
        var aGET = new Array();
        if(aQuery.length > 1)
        {
            var aBuf = aQuery[1].split("&");
            for(var i=0, iLoop = aBuf.length; i<iLoop; i++)
            {
                var aTmp = aBuf[i].split("=");  //分离key与Value
                aGET[aTmp[0]] = aTmp[1];
            }
         }
         return aGET;
    },

    alertWithoutLay : function(title, text, type, timer) {
        type = type || "success";
        timer = timer || 5000;
        swal({
            title: title,
            text: text,
            type: type,
            customClass: "common-alert",
            timer: timer,
            showConfirmButton: false
        });
        $(".sweet-overlay").hide();
    },

    getRandom : function(n) {
        return Math.floor(Math.random()*n+1);
    }
});