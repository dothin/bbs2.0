/**
 * Created by gaohuabin on 2016/4/29.
 */
var tools = {
    checkLogin: function($scope) { //判断登录状态
        return $scope.admin ? true : false;
    },
    goLogin: function($scope, $state) { //没有登录引导到登录界面
        !this.checkLogin($scope) && $state.go("login");
    },
    logout: function($cookies, $rootScope, $state) {
        $cookies.remove('admin');
        $rootScope.admin = $cookies.getObject('admin');
        localStorage.clear();
        $state.go("login");
    },
    strToJson: function(str) { //字符串转json
        return JSON.parse(str);
    },
    isNumber: function(n) { //判断number
        return n === +n;
    },
    htmlDecode: function(html) {
        var a = document.createElement('a');
        a.innerHTML = html;
        return a.textContent;
    },
    alertSuccess: function($rootScope, data) { //成功弹窗
        $rootScope.alert = true;
        $rootScope.isActive = true;
        setTimeout(function() {
            $rootScope.alert = false;
        }, 2000);
        $rootScope.alertValue = data;
    },
    alertError: function($cookies,$rootScope, $state, data) { //失败弹窗
        if (data == '用户验证失败，请先登录') {
            tools.logout($cookies, $rootScope, $state);
        }
        $rootScope.alert = true;
        $rootScope.isActive = false;
        setTimeout(function() {
            $rootScope.alert = false;
        }, 2000);
        $rootScope.alertValue = data;
    }
};
var myBbsAdminCtrls = angular.module('myBbsAdminCtrls', ['cfp.loadingBar']);
myBbsAdminCtrls.controller('headerController', ['$scope', '$rootScope', '$cookies', '$state', 'manageServer',
    function($scope, $rootScope, $cookies, $state, manageServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            logout: {
                action: 'logout'
            },
            adminName: $scope.admin.name
        }
        $scope.logout = function() {
            manageServer.service($scope.vm.logout).then(function(data) {
                console.log(data)
                if (data.status === true) {
                    tools.logout($cookies, $rootScope, $state);
                } else {
                    tools.alertError($rootScope, "退出失败");
                }
            }, function() {
                tools.alertError($cookies,$rootScope, $state, "退出失败");
            });
        }
    }
]);
myBbsAdminCtrls.controller('loginController', ['$scope', '$rootScope', '$cookies', '$state', 'manageServer',
    function($scope, $rootScope, $cookies, $state, manageServer) {
        if (tools.checkLogin($scope)) {
            $state.go("main");
        }
        $scope.vm = {
            isError: false,
            errorInfo: "",
            tabSwitch: 'login',
            title: '用户登录',
            remember: false,
            login: {
                action: 'login',
                username: '',
                password: '',
                code: ""
            },
        }
        $scope.changeCheck = function() {
            $scope.vm.remember = !$scope.vm.remember;
        };
        $scope.login = function() {
            $scope.vm.isError = false;
            manageServer.service($scope.vm.login).then(function(data) {
                if (data.status === true) {
                    var expires = new Date();
                    expires.setTime(expires.getTime() + 30 * 24 * 3600000);
                    $scope.vm.remember ? $cookies.putObject('admin', {
                        name: data.data.manage_name,
                        premission: data.data.premission
                    }, {
                        expires: expires.toUTCString()
                    }) : $cookies.putObject('admin', {
                        name: data.data.manage_name,
                        premission: data.data.premission
                    });
                    $rootScope.admin = $cookies.getObject('admin');
                    $state.go("main");
                } else {
                    $scope.vm.isError = true;
                    $scope.vm.errorInfo = data.data;
                }
            }, function() {
                $scope.vm.isError = true;
                $scope.vm.errorInfo = "登录失败，请重试";
            })
        };
    }
]);
myBbsAdminCtrls.controller('asideController', ['$scope', '$rootScope', '$cookies', '$state', 'manageServer',
    function($scope, $rootScope, $cookies, $state, manageServer) {
        tools.goLogin($scope, $state);
        $scope.state = $state;
        $scope.vm = {};
        $scope.vm.showManageNav = $rootScope.admin.premission.indexOf("1") != -1 ? true : false;
        $scope.vm.showUserNav = $rootScope.admin.premission.indexOf("2") != -1 ? true : false;
        $scope.vm.showModuleNav = $rootScope.admin.premission.indexOf("3") != -1 ? true : false;
        $scope.vm.showPostNav = $rootScope.admin.premission.indexOf("4") != -1 ? true : false;
        $scope.vm.showIntegralNav = $rootScope.admin.premission.indexOf("5") != -1 ? true : false;
        $scope.vm.showSystemNav = $rootScope.admin.premission.indexOf("6") != -1 ? true : false;
    }
]);
myBbsAdminCtrls.controller('manageListController', ['$scope', '$rootScope', '$cookies', '$state', 'manageServer',
    function($scope, $rootScope, $cookies, $state, manageServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            manageList: {},
            postData: {
                "action": "getAllList",
                "pageIndex": 1,
                "pageSize": 8
            },
            deleteModal: {
                manageName: "",
                manageId: 0
            },
            getAllLevelList: {
                "action": "getAllLevelList"
            }
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                manageServer.service($scope.vm.postData).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        angular.forEach(data.data, function(data) {
                            data.manage_sexs = data.manage_sex == 0 ? "男" : "女";
                        });
                        $scope.vm.manageList = data.data;
                        $scope.vm.tableFlag = 1;
                        $scope.paginationConf.totalItems = data.totalNum;
                        $scope.paginationConf.currentPage = data.pageIndex;
                        $scope.paginationConf.itemsPerPage = data.pageSize;
                    } else {
                        $scope.vm.tableFlag = 2;
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        }
        $scope.init();
        $scope.paginationConf = {
            currentPage: 0,
            totalItems: 0,
            itemsPerPage: 0,
            onChange: function() {
                $scope.vm.postData.pageIndex = this.currentPage;
                $scope.vm.postData.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.deleteModalShow = function(id, name) {
            $("#deleteModal").modal("show");
            $scope.vm.deleteModal = {
                manageName: name,
                manageId: id
            }
            $scope.deleteManage = function() {
                $scope.vm.deletePostData = {
                    action: "delete",
                    id: id
                }
                manageServer.service($scope.vm.deletePostData).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "删除成功");
                        $("#deleteModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
        $scope.updateModalShow = function(id, name, level, sex) {
            $("#updateModal").modal("show");
            $scope.vm.updateModal = {
                username: name,
                level: level,
                sex: sex
            }
            manageServer.service($scope.vm.getAllLevelList).then(function(data) {
                if (data.status === true) {
                    angular.forEach(data.data, function(data) {
                        data.isChecked = data.m_level_name == $scope.vm.updateModal.level ? true : false;
                    });
                    $scope.vm.levelList = data.data;
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
            $scope.updateManage = function() {
                $scope.vm.updatePostData = {
                    action: "update",
                    username: $scope.vm.updateModal.username,
                    password: $scope.vm.updateModal.password || "",
                    level: $("#level").val(),
                    sex: $scope.vm.updateModal.sex,
                    id: id
                }
                manageServer.service($scope.vm.updatePostData).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "修改成功");
                        $("#updateModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
    }
]);
myBbsAdminCtrls.controller('addManageController', ['$scope', '$rootScope', '$cookies', '$state', 'manageServer',
    function($scope, $rootScope, $cookies, $state, manageServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            levelList: {},
            postData: {
                "action": "addManage",
                "username": "",
                "password": "",
                "level": 1,
                "sex": 0
            },
            getAllLevelList: {
                "action": "getAllLevelList"
            }
        }
        manageServer.service($scope.vm.getAllLevelList).then(function(data) {
            if (data.status === true) {
                $scope.vm.levelList = data.data;
            } else {
                tools.alertError($cookies,$rootScope, $state, data.data);
            }
        });
        $scope.addManage = function() {
            manageServer.service($scope.vm.postData).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "添加成功");
                    $scope.vm.postData.username = "";
                    $scope.vm.postData.password = "";
                    $scope.vm.postData.level = 1;
                    $scope.vm.postData.sex = 0;
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        }
    }
]);
myBbsAdminCtrls.controller('manageLevelListController', ['$scope', '$rootScope', '$cookies', '$state', 'manageServer',
    function($scope, $rootScope, $cookies, $state, manageServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            levelList: {},
            premissionList: {},
            getAllLevelList: {
                "action": "getAllLevelList"
            },
            getAllpremissionList: {
                "action": "getAllpremissionList"
            }
        }
        $scope.vm.tableFlag = 0;
        $scope.init = function() {
            manageServer.service($scope.vm.getAllLevelList).then(function(data) {
                if (data.status === true) {
                    $scope.vm.levelList = data.data;
                    $scope.vm.tableFlag = 1;
                } else {
                    $scope.vm.tableFlag = 2;
                }
            });
        }
        $scope.init();
        $scope.deleteModalShow = function(id, name) {
            $("#deleteModal").modal("show");
            $scope.vm.deleteModal = {
                levelName: name,
                levelId: id
            }
            $scope.deleteLevel = function() {
                $scope.vm.deleteLevel = {
                    action: "deleteLevel",
                    id: id
                }
                manageServer.service($scope.vm.deleteLevel).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "删除成功");
                        $("#deleteModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
        $scope.updateModalShow = function(id, name, desc, premission) {
            $("#updateModal").modal("show");
            $scope.vm.updateModal = {
                name: name,
                desc: desc
            }
            manageServer.service($scope.vm.getAllpremissionList).then(function(data) {
                if (data.status === true) {
                    $scope.vm.premissionList = data.data;
                    var _arr = premission.split(',');
                    angular.forEach(data.data, function(data) {
                        data.isTrue = _arr.indexOf(data.premission_id) == -1 ? false : true;
                    })
                }
            });
            $scope.updateLevel = function() {
                $scope.vm.updateLevel = {
                    action: "updateLevel",
                    name: $scope.vm.updateModal.name,
                    desc: $scope.vm.updateModal.desc,
                    premission: [],
                    id: id
                }
                $("#addLevel label input").each(function() {
                    var _this = $(this);
                    _this.prop("checked") && $scope.vm.updateLevel.premission.push(parseInt(_this.val()));
                })
                if ($scope.vm.updateLevel.premission.length > 0) {
                    manageServer.service($scope.vm.updateLevel).then(function(data) {
                        if (data.status === true) {
                            tools.alertSuccess($rootScope, "修改成功");
                            $("#updateModal").modal("hide");
                            $scope.init();
                        } else {
                            tools.alertError($cookies,$rootScope, $state, data.data);
                        }
                    });
                } else {
                    tools.alertError($cookies,$rootScope, $state, "未选择权限");
                }
            }
        };
    }
]);
myBbsAdminCtrls.controller('addManageLevelController', ['$scope', '$rootScope', '$cookies', '$state', 'manageServer',
    function($scope, $rootScope, $cookies, $state, manageServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            postData: {
                "action": "addLevel",
                "name": "",
                "desc": "",
                "premission": []
            },
            premissionList: {},
            getAllpremissionList: {
                "action": "getAllpremissionList"
            }
        }
        manageServer.service($scope.vm.getAllpremissionList).then(function(data) {
            if (data.status === true) {
                $scope.vm.premissionList = data.data;
            }
        });
        $scope.addLevel = function() {
            $("#addLevel label input").each(function() {
                var _this = $(this);
                _this.prop("checked") && $scope.vm.postData.premission.push(parseInt(_this.val()));
            })
            if ($scope.vm.postData.premission.length > 0) {
                manageServer.service($scope.vm.postData).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "添加成功");
                        $scope.vm.postData.name = "";
                        $scope.vm.postData.desc = "";
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            } else {
                tools.alertError($cookies,$rootScope, $state, "未选择权限");
            }
        }
    }
]);
myBbsAdminCtrls.controller('premissionListController', ['$scope', '$rootScope', '$cookies', '$state', 'manageServer',
    function($scope, $rootScope, $cookies, $state, manageServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            premissionList: {},
            getAllpremissionList: {
                "action": "getAllpremissionList"
            }
        }
        manageServer.service($scope.vm.getAllpremissionList).then(function(data) {
            if (data.status === true) {
                $scope.vm.premissionList = data.data;
            }
        });
    }
]);
myBbsAdminCtrls.controller('moduleListController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer', 'userServer',
    function($scope, $rootScope, $cookies, $state, moduleServer, userServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            moduleList: {},
            postData: {
                "action": "getAllList",
                "pageIndex": 1,
                "pageSize": 8
            },
            deleteModal: {
                moduleName: "",
                moduleId: 0
            }
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                moduleServer.service($scope.vm.postData).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        $scope.vm.moduleList = data.data;
                        $scope.vm.tableFlag = 1;
                        $scope.paginationConf.totalItems = data.totalNum;
                        $scope.paginationConf.currentPage = data.pageIndex;
                        $scope.paginationConf.itemsPerPage = data.pageSize;
                    } else {
                        $scope.vm.tableFlag = 2;
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        }
        $scope.init();
        $scope.paginationConf = {
            currentPage: 0,
            totalItems: 0,
            itemsPerPage: 0,
            onChange: function() {
                $scope.vm.postData.pageIndex = this.currentPage;
                $scope.vm.postData.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.deleteModalShow = function(id, name) {
            $("#deleteModal").modal("show");
            $scope.vm.deleteModal = {
                moduleName: name,
                moduleId: id
            }
            $scope.deleteModule = function() {
                $scope.vm.deletePostData = {
                    action: "delete",
                    id: id
                }
                moduleServer.service($scope.vm.deletePostData).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "删除成功");
                        $("#deleteModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
        $scope.addUserModuleModalShow = function(id) {
            $("#allInfoModel").modal("show");
            $scope.vm.addModuleModal = {
                id: id,
                name: ""
            }
            $scope.addUserModule = function() {
                $scope.vm.addUserModule = {
                    action: "addUserModule",
                    id: id,
                    name: $scope.vm.addModuleModal.name || ""
                }
                moduleServer.service($scope.vm.addUserModule).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "添加成功");
                        $("#allInfoModel").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        }
        $scope.getUserModuleModalShow = function(id) {
            $("#getUserModuleModel").modal("show");
            $scope.vm.getUserModule = {
                action: "getUserModule",
                id: id
            }
            moduleServer.service($scope.vm.getUserModule).then(function(data) {
                if (data.status === true) {
                    $scope.vm.userModuleList = data.data;
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
            $scope.deleteUserModule = function(id) {
                $scope.vm.deleteUserModule = {
                    action: "deleteUserModule",
                    id: id
                }
                moduleServer.service($scope.vm.deleteUserModule).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "删除成功");
                        $("#getUserModuleModel").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        }
        $scope.updateModalShow = function(id, name, desc, url) {
            $("#updateModal").modal("show");
            $scope.vm.updateModal = {
                name: name,
                desc: desc,
                url: url
            }
            $scope.centerWindow = function(url, name, width, height) {
                var left = (screen.width - width) / 2;
                var top = (screen.height - height) / 2 - 50;
                window.open(url, name, 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
            }
            $scope.updateModule = function() {
                $scope.vm.updatePostData = {
                    action: "update",
                    name: $scope.vm.updateModal.name,
                    desc: $scope.vm.updateModal.desc,
                    id: id
                }
                $scope.vm.updatePostData.url = $('#module_url').val();
                moduleServer.service($scope.vm.updatePostData).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "修改成功");
                        $("#updateModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
    }
]);
myBbsAdminCtrls.controller('addModuleController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer',
    function($scope, $rootScope, $cookies, $state, moduleServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            postData: {
                "action": "addModule",
                "name": "",
                "desc": "",
                "url": ""
            }
        }
        $scope.centerWindow = function(url, name, width, height) {
            var left = (screen.width - width) / 2;
            var top = (screen.height - height) / 2 - 50;
            window.open(url, name, 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
        }
        $scope.addModule = function() {
            $scope.vm.postData.url = $('#module_url').val();
            moduleServer.service($scope.vm.postData).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "添加成功");
                    $scope.vm.postData.name = "";
                    $scope.vm.postData.desc = "";
                    $scope.vm.postData.desc = "";
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        }
    }
]);
myBbsAdminCtrls.controller('userListController', ['$scope', '$rootScope', '$cookies', '$state', 'userServer',
    function($scope, $rootScope, $cookies, $state, userServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            userList: {},
            getAllUserList: {
                "action": "getAllUserList",
                "pageIndex": 1,
                "pageSize": 8
            },
            deleteModal: {
                userName: "",
                userId: 0
            }
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                userServer.service($scope.vm.getAllUserList).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        angular.forEach(data.data, function(data) {
                            data.user_sex = data.user_sex == 0 ? "男" : "女";
                            data.active = data.user_active == 0 ? true : false;
                            data.user_actives = data.user_active == 0 ? "激活" : "禁用";
                        });
                        $scope.vm.userList = data.data;
                        $scope.vm.tableFlag = 1;
                        $scope.paginationConf.totalItems = data.totalNum;
                        $scope.paginationConf.currentPage = data.pageIndex;
                        $scope.paginationConf.itemsPerPage = data.pageSize;
                    } else {
                        $scope.vm.tableFlag = 2;
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        }
        $scope.init();
        $scope.paginationConf = {
            currentPage: 0,
            totalItems: 0,
            itemsPerPage: 0,
            onChange: function() {
                $scope.vm.getAllUserList.pageIndex = this.currentPage;
                $scope.vm.getAllUserList.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.getAllInfo = function(id) {
            $("#allInfoModel").modal("show");
            $scope.vm.getAllInfo = {
                action: 'getAllInfo',
                id: id
            }
            userServer.service($scope.vm.getAllInfo).then(function(data) {
                if (data.status === true) {
                    $scope.vm.bOff = true;
                    data.data.user_sex = data.data.user_sex == 0 ? "男" : "女";
                    data.data.user_active = data.data.user_active == 0 ? "激活" : "禁用";
                    data.data.user_sign_active = data.data.user_sign_active == 0 ? "关闭" : "开启";
                    $scope.vm.userAllInfoList = data.data;
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        }
        $scope.disablleModalShow = function(id, name) {
            $("#disablleModal").modal("show");
            $scope.vm.disablleModal = {
                userName: name,
                userId: id
            }
            $scope.disableUser = function() {
                $scope.vm.disableUser = {
                    action: "disableUser",
                    id: id
                }
                userServer.service($scope.vm.disableUser).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "禁用成功");
                        $("#disablleModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
        $scope.updateModalShow = function(id, name, active) {
            $("#updateModal").modal("show");
            $scope.vm.updateModal = {
                name: name,
                active: active
            }
            $scope.updateUser = function() {
                $scope.vm.updateUser = {
                    action: "updateUser",
                    name: $scope.vm.updateModal.name,
                    active: $scope.vm.updateModal.active,
                    password: $scope.vm.updateModal.password || "",
                    repassword: $scope.vm.updateModal.repassword || "",
                    id: id
                }
                userServer.service($scope.vm.updateUser).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "修改成功");
                        $("#updateModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
    }
]);
myBbsAdminCtrls.controller('addUserController', ['$scope', '$rootScope', '$cookies', '$state', 'userServer',
    function($scope, $rootScope, $cookies, $state, userServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            postData: {
                "action": "addUser",
                "username": "",
                "password": "",
                "repassword": ""
            }
        }
        $scope.addUser = function() {
            userServer.service($scope.vm.postData).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "添加成功");
                    $scope.vm.postData.username = "";
                    $scope.vm.postData.password = "";
                    $scope.vm.postData.repassword = "";
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        }
    }
]);
myBbsAdminCtrls.controller('userRoleListController', ['$scope', '$rootScope', '$cookies', '$state', 'userServer',
    function($scope, $rootScope, $cookies, $state, userServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            roleList: {},
            getAllRoleList: {
                "action": "getAllRoleList"
            }
        }
        $scope.vm.tableFlag = 0;
        $scope.init = function() {
            userServer.service($scope.vm.getAllRoleList).then(function(data) {
                if (data.status === true) {
                    $scope.vm.roleList = data.data;
                    $scope.vm.tableFlag = 1;
                } else {
                    $scope.vm.tableFlag = 2;
                }
            });
        }
        $scope.init();
        $scope.deleteModalShow = function(id, name) {
            $("#deleteModal").modal("show");
            $scope.vm.deleteModal = {
                roleName: name,
                roleId: id
            }
            $scope.deleteRole = function() {
                $scope.vm.deleteRole = {
                    action: "deleteRole",
                    id: id
                }
                userServer.service($scope.vm.deleteRole).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "删除成功");
                        $("#deleteModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
        $scope.updateModalShow = function(id, name, desc) {
            $("#updateModal").modal("show");
            $scope.vm.updateModal = {
                name: name,
                desc: desc
            }
            $scope.updateRole = function() {
                $scope.vm.updateRole = {
                    action: "updateRole",
                    name: $scope.vm.updateModal.name,
                    desc: $scope.vm.updateModal.desc,
                    id: id
                }
                userServer.service($scope.vm.updateRole).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "修改成功");
                        $("#updateModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
    }
]);
myBbsAdminCtrls.controller('addRoleController', ['$scope', '$rootScope', '$cookies', '$state', 'userServer',
    function($scope, $rootScope, $cookies, $state, userServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            postData: {
                "action": "addRole",
                "name": "",
                "desc": ""
            }
        }
        $scope.addRole = function() {
            userServer.service($scope.vm.postData).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "添加成功");
                    $scope.vm.postData.name = "";
                    $scope.vm.postData.desc = "";
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        }
    }
]);
myBbsAdminCtrls.controller('userLevelListController', ['$scope', '$rootScope', '$cookies', '$state', 'userServer',
    function($scope, $rootScope, $cookies, $state, userServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            levelList: {},
            getAllLevelList: {
                "action": "getAllLevelList"
            }
        }
        $scope.vm.tableFlag = 0;
        $scope.init = function() {
            userServer.service($scope.vm.getAllLevelList).then(function(data) {
                if (data.status === true) {
                    $scope.vm.levelList = data.data;
                    $scope.vm.tableFlag = 1;
                } else {
                    $scope.vm.tableFlag = 2;
                }
            });
        }
        $scope.init();
        $scope.deleteModalShow = function(id, name) {
            $("#deleteModal").modal("show");
            $scope.vm.deleteModal = {
                levelName: name,
                levelId: id
            }
            $scope.deleteLevel = function() {
                $scope.vm.deleteLevel = {
                    action: "deleteLevel",
                    id: id
                }
                userServer.service($scope.vm.deleteLevel).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "删除成功");
                        $("#deleteModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
        $scope.updateModalShow = function(id, name, desc, piece) {
            $("#updateModal").modal("show");
            var pieceArr = piece.split(',');
            $scope.vm.updateModal = {
                name: name,
                desc: desc,
                start: pieceArr[0],
                end: pieceArr[1]
            }
            $scope.updateLevel = function() {
                $scope.vm.updateLevel = {
                    action: "updateLevel",
                    name: $scope.vm.updateModal.name,
                    desc: $scope.vm.updateModal.desc,
                    piece: $scope.vm.updateModal.start + ',' + $scope.vm.updateModal.end,
                    id: id
                }
                userServer.service($scope.vm.updateLevel).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "修改成功");
                        $("#updateModal").modal("hide");
                        $scope.init();
                    } else {
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        };
    }
]);
myBbsAdminCtrls.controller('addLevelController', ['$scope', '$rootScope', '$cookies', '$state', 'userServer',
    function($scope, $rootScope, $cookies, $state, userServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            postData: {
                "action": "addLevel",
                "name": "",
                "desc": "",
                "start": "",
                "end": ""
            }
        }
        $scope.addLevel = function() {
            $scope.vm.postData.piece = $scope.vm.postData.start + ',' + $scope.vm.postData.end;
            userServer.service($scope.vm.postData).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "添加成功");
                    $scope.vm.postData.name = "";
                    $scope.vm.postData.desc = "";
                    $scope.vm.postData.start = "";
                    $scope.vm.postData.end = "";
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        }
    }
]);
myBbsAdminCtrls.controller('postListController', ['$scope', '$rootScope', '$cookies', '$state', 'postServer', 'userServer',
    function($scope, $rootScope, $cookies, $state, postServer, userServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            postList: {},
            getPostsByManage: {
                "action": "getPostsByManage",
                "pageIndex": 1,
                "pageSize": 8
            }
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                postServer.service($scope.vm.getPostsByManage).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        $scope.vm.postList = data.data;
                        angular.forEach(data.data, function(data) {
                            data.active = data.post_status == 1 ? true : false;
                            data.post_status = data.post_status == 1 ? "通过" : "未通过";
                        });
                        $scope.vm.tableFlag = 1;
                        $scope.paginationConf.totalItems = data.totalNum;
                        $scope.paginationConf.currentPage = data.pageIndex;
                        $scope.paginationConf.itemsPerPage = data.pageSize;
                    } else {
                        $scope.vm.tableFlag = 2;
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        }
        $scope.init();
        $scope.paginationConf = {
            currentPage: 0,
            totalItems: 0,
            itemsPerPage: 0,
            onChange: function() {
                $scope.vm.getPostsByManage.pageIndex = this.currentPage;
                $scope.vm.getPostsByManage.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.letGo = function(id) {
            $scope.vm.letGo = {
                action: 'letGo',
                post_id: id
            }
            postServer.service($scope.vm.letGo).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "设置成功");
                    $scope.init();
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        };
        $scope.letStop = function(id) {
            $scope.vm.letStop = {
                action: 'letStop',
                post_id: id
            }
            postServer.service($scope.vm.letStop).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "设置成功");
                    $scope.init();
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        };
        $scope.getPostDetailModalShow = function(id) {
            $("#getPostDetailModalShow").modal("show");
            $scope.vm.getPostDetail = {
                action: 'getPostDetail',
                post_id: id
            }
            postServer.service($scope.vm.getPostDetail).then(function(data) {
                if (data.status === true) {
                    $scope.vm.postDetail = data.data;
                    if ($scope.vm.postDetail.post_Jin == 0) {
                        $scope.vm.postDetail.post_Jin = "";
                        $scope.vm.isNotJin = true;
                    } else {
                        $scope.vm.postDetail.post_Jin = "精华帖";
                        $scope.vm.isJin = true;
                    }
                    if ($scope.vm.postDetail.post_Re == 0) {
                        $scope.vm.postDetail.post_Re = "";
                        $scope.vm.isNotRe = true;
                    } else {
                        $scope.vm.postDetail.post_Re = "热帖";
                        $scope.vm.isRe = true;
                    }
                    $scope.vm.postDetail.post_content = tools.htmlDecode($scope.vm.postDetail.post_content);
                }
            });
        };
    }
]);
myBbsAdminCtrls.controller('integralListController', ['$scope', '$rootScope', '$cookies', '$state', 'integralServer',
    function($scope, $rootScope, $cookies, $state, integralServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            ruleList: {},
            getIntegralRuleList: {
                "action": "getIntegralRuleList"
            }
        }
        $scope.init = function() {
            integralServer.service($scope.vm.getIntegralRuleList).then(function(data) {
                if (data.status === true) {
                    $scope.vm.ruleList = data.data;
                    angular.forEach(data.data, function(data) {
                        data.rule_status = data.rule_status == 1 ? true : false;
                    })
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        }
        $scope.init();
        $scope.updateIntegral = function() {
            for (var key in $scope.vm.ruleList) {
                $scope.vm.ruleList[key].get_integral = parseFloat($scope.vm.ruleList[key].get_integral);
                if ($scope.vm.ruleList[key].get_integral < 1 || !tools.isNumber($scope.vm.ruleList[key].get_integral)) {
                    $scope.vm.ruleList[key].get_integral = 1;
                } else if ($scope.vm.ruleList[key].get_integral > 20) {
                    $scope.vm.ruleList[key].get_integral = 20;
                }
                $scope.vm.ruleList[key].get_integral = Math.ceil($scope.vm.ruleList[key].get_integral);
            }
            $scope.vm.updateIntegral = {
                action: "updateIntegral",
                ruleList: $scope.vm.ruleList
            }
            integralServer.service($scope.vm.updateIntegral).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, data.data);
                    $scope.init();
                } else {
                    tools.alertError($cookies,$rootScope, $state, "设置失败");
                }
            });
        };
    }
]);
myBbsAdminCtrls.controller('integralLogsController', ['$scope', '$rootScope', '$cookies', '$state', 'integralServer',
    function($scope, $rootScope, $cookies, $state, integralServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            integralLogs: {},
            getIntegralLogs: {
                "action": "getIntegralLogs",
                "pageIndex": 1,
                "pageSize": 8
            }
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                integralServer.service($scope.vm.getIntegralLogs).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        $scope.vm.integralLogs = data.data;
                        $scope.vm.tableFlag = 1;
                        $scope.paginationConf.totalItems = data.totalNum;
                        $scope.paginationConf.currentPage = data.pageIndex;
                        $scope.paginationConf.itemsPerPage = data.pageSize;
                    } else {
                        $scope.vm.tableFlag = 2;
                    }
                });
            }
        }
        $scope.init();
        $scope.paginationConf = {
            currentPage: 0,
            totalItems: 0,
            itemsPerPage: 0,
            onChange: function() {
                $scope.vm.getIntegralLogs.pageIndex = this.currentPage;
                $scope.vm.getIntegralLogs.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.vm.getOneUserLogsByUserName = {
            "action": "getOneUserLogsByUserName",
            "name": ""
        }
        $scope.getOneUserLogsByUserName = function() {
            $scope.vm.getOneUserLogsByUserName.pageIndex = 1;
            $scope.vm.getOneUserLogsByUserName.pageSize = 8;
            $scope.vm.bOff = true;
            $scope.init = function() {
                if ($scope.vm.bOff) {
                    $scope.vm.bOff = false;
                    integralServer.service($scope.vm.getOneUserLogsByUserName).then(function(data) {
                        if (data.status === true) {
                            $scope.vm.bOff = true;
                            $scope.vm.integralLogs = data.data;
                            $scope.vm.tableFlag = 1;
                            $scope.paginationConf.totalItems = data.totalNum;
                            $scope.paginationConf.currentPage = data.pageIndex;
                            $scope.paginationConf.itemsPerPage = data.pageSize;
                        } else {
                            $scope.vm.tableFlag = 2;
                        }
                    });
                }
            }
            $scope.init();
            $scope.paginationConf = {
                currentPage: 0,
                totalItems: 0,
                itemsPerPage: 0,
                onChange: function() {
                    $scope.vm.getOneUserLogsByUserName.pageIndex = this.currentPage;
                    $scope.vm.getOneUserLogsByUserName.pageSize = this.itemsPerPage;
                    $scope.init();
                }
            };
        };
    }
]);
myBbsAdminCtrls.controller('systemConfController', ['$scope', '$rootScope', '$cookies', '$state', 'systemServer',
    function($scope, $rootScope, $cookies, $state, systemServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            systemConf: {},
            getSystemConf: {
                "action": "getSystemConf"
            }
        }
        $scope.init = function() {
            systemServer.service($scope.vm.getSystemConf).then(function(data) {
                if (data.status === true) {
                    $scope.vm.systemConf = data.data;
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        }
        $scope.init();
        $scope.updateConf = function() {
            $scope.vm.updateConf = {
                action: "updateConf",
                bbs_login: $scope.vm.systemConf.bbs_login,
                bbs_register: $scope.vm.systemConf.bbs_register,
                bbs_login_fail_count: $scope.vm.systemConf.bbs_login_fail_count
            }
            console.log($scope.vm.updateConf)
            systemServer.service($scope.vm.updateConf).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, data.data);
                    $scope.init();
                } else {
                    tools.alertError($cookies,$rootScope, $state, data.data);
                }
            });
        };
    }
]);
myBbsAdminCtrls.controller('systemLogController', ['$scope', '$rootScope', '$cookies', '$state', 'postServer', 'systemServer',
    function($scope, $rootScope, $cookies, $state, postServer, systemServer) {
        tools.goLogin($scope, $state);
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            systemLogList: {},
            getSystemLog: {
                "action": "getSystemLog",
                "pageIndex": 1,
                "pageSize": 8
            }
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                systemServer.service($scope.vm.getSystemLog).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        $scope.vm.systemLogList = data.data;
                        $scope.vm.tableFlag = 1;
                        $scope.paginationConf.totalItems = data.totalNum;
                        $scope.paginationConf.currentPage = data.pageIndex;
                        $scope.paginationConf.itemsPerPage = data.pageSize;
                    } else {
                        $scope.vm.tableFlag = 2;
                        tools.alertError($cookies,$rootScope, $state, data.data);
                    }
                });
            }
        }
        $scope.init();
        $scope.paginationConf = {
            currentPage: 0,
            totalItems: 0,
            itemsPerPage: 0,
            onChange: function() {
                $scope.vm.getSystemLog.pageIndex = this.currentPage;
                $scope.vm.getSystemLog.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
    }
]);