/**
 * Created by gaohuabin on 2016/4/29.
 */
var myBbsAdminApp = angular.module("myBbsAdminApp", ["ui.router", "ngCookies", "myBbsAdminCtrls", "myBbsAdminServices", "myBbsAdminFilters", "myBbsAdminDirectives", "chieffancypants.loadingBar"]);
myBbsAdminApp.run(function($rootScope, $cookies) {
    if ($cookies.getObject('admin')) {
        $rootScope.admin = $cookies.getObject('admin');
    }
    //操作成功或失败弹窗
    $rootScope.isActive = true;
    $rootScope.alertValue = "";
    $rootScope.alert = false;
});
myBbsAdminApp.config(function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/login');
    $stateProvider.state('login', {
        url: '/login',
        templateUrl: 'tpls/admin/login.html',
        controller: "loginController"
    }).state('main', {
        url: '/main',
        views: {
            '': {
                templateUrl: 'tpls/admin/main.html'
            },
            'header@main': {
                templateUrl: 'tpls/admin/header.html',
                controller: "headerController"
            },
            'aside@main': {
                templateUrl: 'tpls/admin/aside.html',
                controller: "asideController"
            },
            'section@main': {
                templateUrl: 'tpls/admin/manage.html',
                controller: function($rootScope, $state) {
                    var _premissionArr = $rootScope.admin.premission;
                    var smallPremission = Math.min.apply(null, _premissionArr);
                    switch (smallPremission) {
                        case 1:
                            $state.go("main.manage.manageList");
                            break;
                        case 2:
                            $state.go("main.user.userList");
                            break;
                        case 3:
                            $state.go("main.module.moduleList");
                            break;
                        case 4:
                            $state.go("main.post.postList");
                            break;
                        case 5:
                            $state.go("main.integral.integralList");
                            break;
                        case 6:
                            $state.go("main.system.systemConf");
                            break;
                    }
                }
            }
        }
    }).state('main.manage', {
        url: '/manage',
        views: {
            'section@main': {
                templateUrl: 'tpls/admin/manage.html'
            }
        }
    }).state('main.manage.manageList', {
        url: '/manageList',
        templateUrl: 'tpls/admin/manage/manageList.html',
        controller: "manageListController"
    }).state('main.manage.addManage', {
        url: '/addManage',
        templateUrl: 'tpls/admin/manage/addManage.html',
        controller: "addManageController"
    }).state('main.manage.manageLevelList', {
        url: '/manageLevelList',
        templateUrl: 'tpls/admin/manage/manageLevelList.html',
        controller: "manageLevelListController"
    }).state('main.manage.addManageLevel', {
        url: '/addManageLevel',
        templateUrl: 'tpls/admin/manage/addManageLevel.html',
        controller: "addManageLevelController"
    }).state('main.manage.premissionList', {
        url: '/premissionList',
        templateUrl: 'tpls/admin/manage/premissionList.html',
        controller: "premissionListController"
    }).state('main.module', {
        url: '/module',
        views: {
            'section@main': {
                templateUrl: 'tpls/admin/module.html'
            }
        }
    }).state('main.module.moduleList', {
        url: '/moduleList',
        templateUrl: 'tpls/admin/module/moduleList.html',
        controller: "moduleListController"
    }).state('main.module.addModule', {
        url: '/addModule',
        templateUrl: 'tpls/admin/module/addModule.html',
        controller: "addModuleController"
    }).state('main.user', {
        url: '/user',
        views: {
            'section@main': {
                templateUrl: 'tpls/admin/user.html'
            }
        }
    }).state('main.user.userList', {
        url: '/userList',
        templateUrl: 'tpls/admin/user/userList.html',
        controller: "userListController"
    }).state('main.user.addUser', {
        url: '/addUser',
        templateUrl: 'tpls/admin/user/addUser.html',
        controller: "addUserController"
    }).state('main.user.userRoleList', {
        url: '/userRoleList',
        templateUrl: 'tpls/admin/user/userRoleList.html',
        controller: "userRoleListController"
    }).state('main.user.addRole', {
        url: '/addRole',
        templateUrl: 'tpls/admin/user/addRole.html',
        controller: "addRoleController"
    }).state('main.user.userLevelList', {
        url: '/userLevelList',
        templateUrl: 'tpls/admin/user/userLevelList.html',
        controller: "userLevelListController"
    }).state('main.user.addLevel', {
        url: '/addLevel',
        templateUrl: 'tpls/admin/user/addLevel.html',
        controller: "addLevelController"
    }).state('main.post', {
        url: '/post',
        views: {
            'section@main': {
                templateUrl: 'tpls/admin/post.html'
            }
        }
    }).state('main.post.postList', {
        url: '/postList',
        templateUrl: 'tpls/admin/post/postList.html',
        controller: "postListController"
    }).state('main.system', {
        url: '/system',
        views: {
            'section@main': {
                templateUrl: 'tpls/admin/system.html'
            }
        }
    }).state('main.system.systemConf', {
        url: '/systemConf',
        templateUrl: 'tpls/admin/system/systemConf.html',
        controller: "systemConfController"
    }).state('main.system.systemLog', {
        url: '/systemLog',
        templateUrl: 'tpls/admin/system/systemLog.html',
        controller: "systemLogController"
    }).state('main.integral', {
        url: '/integral',
        views: {
            'section@main': {
                templateUrl: 'tpls/admin/integral.html'
            }
        }
    }).state('main.integral.integralList', {
        url: '/integralList',
        templateUrl: 'tpls/admin/integral/integralList.html',
        controller: "integralListController"
    }).state('main.integral.integralLogs', {
        url: '/integralLogs',
        templateUrl: 'tpls/admin/integral/integralLogs.html',
        controller: "integralLogsController"
    })
});