/**
 * Created by gaohuabin on 2016/4/29.
 */
var myBbsAdminFilters = angular.module('myBbsAdminFilters', []);
//显示HTML到页面
myBbsAdminFilters.filter('to_trusted', ['$sce', function ($sce) {
    return function (text) {
        return $sce.trustAsHtml(text);
    };
}]);
