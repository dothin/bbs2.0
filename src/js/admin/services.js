/**
 * Created by gaohuabin on 2016/4/29.
 */
var myBbsAdminServices = angular.module('myBbsAdminServices', ['chieffancypants.loadingBar']);
/**
 * http服务
 */
myBbsAdminServices.factory("httpServer", function ($http, $q) {
    return {
        postHttp: function (url, data) {
            var deferred = $q.defer();
            if (data) {
                $http({
                    method: "post",
                    url: url,
                    data: data
                }).success(function (resp) {
                    deferred.resolve(resp);
                }).error(function (resp) {
                    deferred.reject(resp);
                });
            } else {
                $http({
                    method: "post",
                    url: url
                }).success(function (resp) {
                    deferred.resolve(resp);
                }).error(function (resp) {
                    deferred.reject(resp);
                });
            }
            return deferred.promise;
        }
    };
});
/**
 * 用户服务
 */
myBbsAdminServices.factory('userServer', function (httpServer) {
    var myServices = {};
    myServices.service = function (data) {
        return httpServer.postHttp("../php/controller/UserAction.class.php", data);
    };
    return myServices;
});
/**
 * 管理员服务
 */
myBbsAdminServices.factory('manageServer', function (httpServer) {
    var myServices = {};
    myServices.service = function (data) {
        return httpServer.postHttp("../php/controller/ManageAction.class.php", data);
    };
    return myServices;
});
/**
 * 积分服务
 */
myBbsAdminServices.factory('integralServer', function (httpServer) {
    var myServices = {};
    myServices.service = function (data) {
        return httpServer.postHttp("../php/controller/IntegralAction.class.php", data);
    };
    return myServices;
});
/**
 * 模块服务
 */
myBbsAdminServices.factory('moduleServer', function (httpServer) {
    var myServices = {};
    myServices.service = function (data) {
        return httpServer.postHttp("../php/controller/ModuleAction.class.php", data);
    };
    return myServices;
});
/**
 * 帖子服务
 */
myBbsAdminServices.factory('postServer', function (httpServer) {
    var myServices = {};
    myServices.service = function (data) {
        return httpServer.postHttp("../php/controller/PostAction.class.php", data);
    };
    return myServices;
});
/**
 * 系统配置服务
 */
myBbsAdminServices.factory('systemServer', function (httpServer) {
    var myServices = {};
    myServices.service = function (data) {
        return httpServer.postHttp("../php/controller/SystemAction.class.php", data);
    };
    return myServices;
});
