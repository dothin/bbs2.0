/**
 * Created by gaohuabin on 2016/4/29.
 */
var myBbsFilters = angular.module('myBbsFilters', []);
//显示HTML到页面
myBbsFilters.filter('to_trusted', ['$sce', function ($sce) {
    return function (text) {
        return $sce.trustAsHtml(text);
    };
}]);
