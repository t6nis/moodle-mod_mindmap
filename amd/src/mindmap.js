define(['core/ajax', 'core/notification', 'core/str'], function (ajax, notification, str) {

    function Mindmap() {
        this.value = "";
    };

    Mindmap.prototype.mindmapsubmit = function (mindmapid, mindmapdata, mindmapmode) {
        var promises = ajax.call([{
            methodname: 'mod_mindmap_submit_mindmap',
            args: {mindmapid: mindmapid, mindmapdata: mindmapdata, mindmapmode: mindmapmode},
            done:
                str.get_strings([
                    {key: 'changessaved', component: 'core'},
                    {key: 'mindmapsaved', component: 'mod_mindmap'},
                ]).done(function (strs) {
                    notification.alert(strs[0], strs[1]);
                }).fail(notification.exception),
            fail: notification.exception
        }]);
        promises[0].then(function (data) {
            //console.log(data);
        });

    };

    return Mindmap;
});