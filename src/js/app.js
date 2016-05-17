/**
 * Created by gaohuabin on 2016/4/29.
 */
var myBbsApp = angular.module("myBbsApp", ["ui.router", "ngCookies", "myBbsCtrls", "myBbsServices", "myBbsFilters", "myBbsDirectives", "chieffancypants.loadingBar"]);
myBbsApp.run(function ($rootScope, $cookies) {
    if ($cookies.getObject('user')) {
        $rootScope.user = $cookies.getObject('user');
    }
    //操作成功或失败弹窗
    /*$rootScope.isActive=true;
    $rootScope.alertValue="";
    $rootScope.alert=false;*/
});
myBbsApp.config(function ($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/main');
    $stateProvider
        .state('main', {
            url: '/main',
            views: {
                '': {
                    templateUrl: 'tpls/main.html'
                },
                'header@main': {
                    templateUrl: 'tpls/header.html',
                    controller: "headerController"
                },
                'content@main': {
                    templateUrl: 'tpls/post.html',
                    controller: function ($state) {
                        $state.go("main.post.postList");
                    }
                },
                'footer@main': {
                    templateUrl: 'tpls/footer.html'
                }
            }
        })
        .state('main.post', {
            url: '/post',
            views: {
                'content@main': {
                    templateUrl: 'tpls/post.html',
                    controller: "postController"
                }
            }
        })
        .state('main.post.postList', {
            url: '/postList',
            templateUrl: 'tpls/post/postList.html',
            controller:"postListController"
        })
        .state('main.post.sendPost', {
            url: '/sendPost',
            templateUrl: 'tpls/post/sendPost.html',
            controller:"sendPostController"
        })
        .state('main.post.postDetail', {
            url: '/postDetail',
            templateUrl: 'tpls/post/postDetail.html',
            controller: "postDetailController"
        }).state('main.user', {
            url: '/user',
            views: {
                'content@main': {
                    templateUrl: 'tpls/user.html',
                    controller: "userController"
                }
            }
        })
        .state('main.user.userInfo', {
            url: '/userInfo',
            templateUrl: 'tpls/user/userInfo.html',
            controller:"userInfoController"
        })
        .state('main.user.userPostList', {
            url: '/userPostList',
            templateUrl: 'tpls/user/userPostList.html',
            controller:"userPostListController"
        })
        .state('main.user.userFriendList', {
            url: '/userFriendList',
            templateUrl: 'tpls/user/userFriendList.html',
            controller:"userFriendListController"
        })
        .state('main.user.userMessageList', {
            url: '/userMessageList',
            templateUrl: 'tpls/user/userMessageList.html',
            controller:"userMessageListController"
        })
        .state('main.user.userPostCollectionList', {
            url: '/userPostCollectionList',
            templateUrl: 'tpls/user/userPostCollectionList.html',
            controller:"userPostCollectionListController"
        })
        .state('main.user.userIntegralList', {
            url: '/userIntegralList',
            templateUrl: 'tpls/user/userIntegralList.html',
            controller:"userIntegralListController"
        })
        .state('main.modules', {
            url: '/modules',
            views: {
                'content@main': {
                    templateUrl: 'tpls/modules.html',
                    controller:"moduleController"
                }
            }
        }).state('main.search', {
            url: '/search',
            views: {
                'content@main': {
                    templateUrl: 'tpls/search.html',
                    controller: "searchController"
                }
            }
        })
        
});