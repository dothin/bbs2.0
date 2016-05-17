/**
 * Created by gaohuabin on 2016/4/29.
 */
var myBbsServices = angular.module('myBbsServices', ['chieffancypants.loadingBar']);
/**
 * http服务
 */
myBbsServices.factory("httpServer", function($http, $q) {
    return {
        postHttp: function(url, data) {
            var deferred = $q.defer();
            if (data) {
                $http({
                    method: "post",
                    url: url,
                    data: data
                }).success(function(resp) {
                    deferred.resolve(resp);
                }).error(function(resp) {
                    deferred.reject(resp);
                });
            } else {
                $http({
                    method: "post",
                    url: url
                }).success(function(resp) {
                    deferred.resolve(resp);
                }).error(function(resp) {
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
myBbsServices.factory('userServer', function(httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        return httpServer.postHttp("../php/controller/UserAction.class.php", data);
    };
    return myServices;
});
/**
 * 积分服务
 */
myBbsServices.factory('integralServer', function(httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        return httpServer.postHttp("../php/controller/IntegralAction.class.php", data);
    };
    return myServices;
});
/**
 * 模块服务
 */
myBbsServices.factory('moduleServer', function(httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        return httpServer.postHttp("../php/controller/ModuleAction.class.php", data);
    };
    return myServices;
});
/**
 * 帖子服务
 */
myBbsServices.factory('postServer', function(httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        return httpServer.postHttp("../php/controller/PostAction.class.php", data);
    };
    return myServices;
});
/**
 * 好友服务
 */
myBbsServices.factory('friendServer', function(httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        return httpServer.postHttp("../php/controller/FriendAction.class.php", data);
    };
    return myServices;
});
/**
 * 收藏服务
 */
myBbsServices.factory('collectionServer', function(httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        return httpServer.postHttp("../php/controller/CollectionAction.class.php", data);
    };
    return myServices;
});
/**
 * 留言服务
 */
myBbsServices.factory('messageServer', function(httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        return httpServer.postHttp("../php/controller/MessageAction.class.php", data);
    };
    return myServices;
});
/**
 * 私信服务
 */
myBbsServices.factory('whispersServer', function(httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        return httpServer.postHttp("../php/controller/WhispersAction.class.php", data);
    };
    return myServices;
});
/**
 * 搜索服务
 */
myBbsServices.factory('searchServer', function(httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        return httpServer.postHttp("../php/controller/SearchAction.class.php", data);
    };
    return myServices;
});
/**
 * 提醒服务
 */
myBbsServices.factory('alertServer', function($q, $http, httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        var deferred = $q.defer();
        $http.post("../php/controller/AlertAction.class.php", data, {
            ignoreLoadingBar: true
        }).success(function(resp) {
            deferred.resolve(resp);
        }).error(function(resp) {
            deferred.reject(resp);
        });
        return deferred.promise;
    };
    return myServices;
});
/**
 * 异步记载js
 */
myBbsServices.factory('jsServer', function($q, $http, httpServer) {
    var myServices = {};
    myServices.service = function(data) {
        var deferred = $q.defer();
        $.getScript(data, function() {
            deferred.resolve();
        });
        return deferred.promise;
    };
    return myServices;
});