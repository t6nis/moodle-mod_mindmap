define(['core/ajax', 'core/notification'], function(ajax, notification) {

    function Mindmap() {
        this.value = "";
    };

    Mindmap.prototype.mindmapsubmit = function(mindmapid, mindmapdata) {

        var promises = ajax.call([{
            methodname: 'mod_mindmap_submit_mindmap',
            args: {mindmapid: mindmapid, mindmapdata: mindmapdata},
            //done: console.log(""),
            fail: notification.exception
        }]);
        promises[0].then(function(data) {
            //console.log(data);
        });

    };

    return Mindmap;
});