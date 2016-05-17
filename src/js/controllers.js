/**
 * Created by gaohuabin on 2016/4/29.
 */
var tools = {
    checkLogin: function($scope) { //判断登录状态
        return $scope.user ? true : false;
    },
    goLogin: function($scope, $state) { //没有登录引导到登录界面
        !this.checkLogin($scope) && $state.go("login");
    },
    logout: function($cookies, $rootScope, $state) {
        $cookies.remove('user');
        $rootScope.user = $cookies.getObject('user');
        localStorage.clear();
        $state.go("login");
    },
    toTwo: function(num) {
        return num < 10 ? '0' + num : '' + num;
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
    alertError: function($rootScope, data) { //失败弹窗
        $rootScope.alert = true;
        $rootScope.isActive = false;
        setTimeout(function() {
            $rootScope.alert = false;
        }, 2000);
        $rootScope.alertValue = data;
    }
};
var myBbsCtrls = angular.module('myBbsCtrls', ['cfp.loadingBar']);
myBbsCtrls.controller('headerController', ['$scope', '$rootScope', '$cookies', '$state', '$interval', 'userServer', 'alertServer',
    function($scope, $rootScope, $cookies, $state, $interval, userServer, alertServer) {
        $scope.vm = {
            username: $rootScope.user ? $rootScope.user.name : "",
            isError: false,
            errorInfo: "",
            tabSwitch: 'login',
            title: '用户登录',
            noUserCode: false,
            remember: false,
            searchType: window.localStorage.getItem("searchType") || '帖子',
            searchText: window.localStorage.getItem("searchText") || "",
            reg: {
                action: 'reg',
                username: '',
                password: '',
                code: '',
                repassword: ''
            },
            login: {
                action: 'login',
                username: '',
                password: ''
            },
            logout: {
                action: 'logout'
            },
            useCode: {
                code: ""
            },
            getAlert: {
                action: "getAlert"
            }
        }
        var timer=null;
        $(".collapse .nav a").on('click', function() {
            $(".collapse").collapse("hide");
        })
        $rootScope.isLogin = $rootScope.user ? true : false;
        $rootScope.allAlert = 0;
        $scope.state = $state;
        $scope.initAlert = function() {
            alertServer.service($scope.vm.getAlert).then(function(data) {
                if (data.status === true) {
                    $rootScope.allAlert = (parseInt(data.no_agree_friend_nums) + parseInt(data.no_read_whispers) + parseInt(data.no_read_message)) || 0;
                    $rootScope.messageAlert = data.no_read_message;
                    $rootScope.friendAlert = parseInt(data.no_agree_friend_nums) + parseInt(data.no_read_whispers);
                    $scope.goRead = function() {
                        if ($rootScope.messageAlert > 0 && $rootScope.friendAlert == 0) {
                            $state.go("main.user.userMessageList", {}, {
                                reload: true
                            });
                        } else if ($rootScope.messageAlert == 0 && $rootScope.friendAlert > 0) {
                            $state.go("main.user.userFriendList", {}, {
                                reload: true
                            });
                        } else {
                            $state.go("main.user.userInfo", {}, {
                                reload: true
                            });
                        }
                        $interval.cancel(timer);
                    }
                } else {
                    $rootScope.allAlert = 0;
                }
            });
        }
        if ($rootScope.isLogin) {
            timer = $interval(function() {
                $scope.initAlert();
            }, 5000);
            $scope.initAlert();
        }
        $scope.goSearch = function() {
            if ($scope.vm.searchText != "") {
                console.log(1)
                window.localStorage.setItem("searchType", $scope.vm.searchType);
                window.localStorage.setItem("searchText", $scope.vm.searchText);
                $state.go("main.search", {}, {
                    reload: true
                });
            }
        };
        $scope.goUserInfo = function() {
            $state.go("main.user.userInfo");
        };
        $scope.changeCheck = function() {
            $scope.vm.remember = !$scope.vm.remember;
        };
        $scope.loginShow = function() {
            $scope.vm.tabSwitch = 'login';
            $scope.vm.login.username = '';
            $scope.vm.login.password = '';
            $scope.vm.reg.code = '';
            $scope.vm.isError = false;
            $scope.vm.title = '用户登录';
            $("#loginModal").modal('show');
        };
        $scope.regShow = function() {
            $scope.vm.reg.username = '';
            $scope.vm.reg.password = '';
            $scope.vm.reg.repassword = '';
            $scope.vm.reg.code = '';
            $scope.vm.tabSwitch = 'reg';
            $scope.vm.isError = false;
            $scope.vm.title = '用户注册';
            $("#loginModal").modal('show');
        };
        $scope.changeToLogin = function() {
            $scope.vm.isError = false;
            $scope.vm.tabSwitch = 'login';
            $scope.vm.title = '用户登录';
        };
        $scope.changeToReg = function() {
            $scope.vm.isError = false;
            $scope.vm.tabSwitch = 'reg';
            $scope.vm.title = '用户注册';
        };
        $scope.reg = function() {
            $scope.vm.isError = false;
            userServer.service($scope.vm.reg).then(function(data) {
                if (data.status === true) {
                    $cookies.putObject('user', {
                        name: data.data.user_name
                    });
                    $rootScope.user = $cookies.getObject('user');
                    $("#loginModal").modal('hide');
                    $rootScope.isLogin = true;
                    $scope.vm.username = $rootScope.user.name;
                } else {
                    $scope.vm.isError = true;
                    $scope.vm.errorInfo = data.data;
                }
            })
        };
        $scope.login = function() {
            $scope.vm.isError = false;
            if ($scope.vm.noUserCode) {
                $scope.vm.login.code = $scope.vm.useCode.code;
            }
            userServer.service($scope.vm.login).then(function(data) {
                console.log(data);
                if (data.status === true) {
                    var expires = new Date();
                    expires.setTime(expires.getTime() + 30 * 24 * 3600000);
                    $scope.vm.remember ? $cookies.putObject('user', {
                        name: data.data.user_name
                    }, {
                        expires: expires.toUTCString()
                    }) : $cookies.putObject('user', {
                        name: data.data.user_name
                    });
                    $rootScope.user = $cookies.getObject('user');
                    $("#loginModal").modal('hide');
                    $state.go("main.post.postList");
                    $rootScope.isLogin = true;
                    $scope.vm.username = $rootScope.user.name;
                    timer = $interval(function() {
                        $scope.initAlert();
                    }, 5000);
                    $scope.initAlert();
                } else {
                    $scope.vm.isError = true;
                    $scope.vm.errorInfo = data.data;
                    if (data.fail_count > data.conf_fail_count) {
                        $scope.vm.noUserCode = true;
                    }
                }
            }, function() {
                $scope.vm.isError = true;
                $scope.vm.errorInfo = "登录失败，请重试";
            })
        };
        $scope.logout = function() {
            userServer.service($scope.vm.logout).then(function(data) {
                if (data.status === true) {
                    $cookies.remove('user');
                    $rootScope.user = $cookies.getObject('user');
                    $scope.vm.username = "";
                    localStorage.clear();
                    $rootScope.isLogin = false;
                    $interval.cancel(timer);
                    $state.go("main.post.postList");
                } else {
                    tools.alertError($rootScope, "退出失败");
                }
            }, function() {
                tools.alertError($rootScope, "退出失败");
            });
        }
        $scope.enter = function(e) { //回车跳转登录
            var _submit = $('#submit');
            if (e.keyCode == 13 && !_submit.prop("disabled")) {
                $scope.login();
            }
        };
        $scope.regEnter = function(e) { //回车跳转登录
            var _submit = $('#submit');
            if (e.keyCode == 13 && !_submit.prop("disabled")) {
                $scope.reg();
            }
        };
    }
]);
myBbsCtrls.controller('postController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer', 'postServer',
    function($scope, $rootScope, $cookies, $state, moduleServer, postServer) {
        $rootScope.vm = {
            post_tile_show: false
        }
        window.localStorage.removeItem("searchType");
        window.localStorage.removeItem("searchText");
    }
]);
myBbsCtrls.controller('postListController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer', 'postServer',
    function($scope, $rootScope, $cookies, $state, moduleServer, postServer) {
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            postList: {},
            module_id: window.localStorage.getItem("module_id"),
            module_name: window.localStorage.getItem("module_name"),
            subNavShow: window.localStorage.getItem("module_name") ? true : false,
            getPosts: {
                action: 'getPosts',
                pageIndex: 1,
                pageSize: 15
            },
            getPostsByModuleId: {
                action: 'getPostsByModuleId',
                module_id: window.localStorage.getItem("module_id"),
                pageIndex: 1,
                pageSize: 15
            }
        }
        $rootScope.vm.post_tile_show = false;
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                if (window.localStorage.getItem("module_name")) {
                    postServer.service($scope.vm.getPostsByModuleId).then(function(data) {
                        if (data.status === true) {
                            $scope.vm.bOff = true;
                            angular.forEach(data.data, function(data) {
                                data.post_time = data.post_time.substring(5);
                            });
                            $scope.vm.postList = data.data;
                            $scope.vm.tableFlag = 1;
                            $scope.paginationConf.totalItems = data.totalNum;
                            $scope.paginationConf.currentPage = data.pageIndex;
                            $scope.paginationConf.itemsPerPage = data.pageSize;
                        } else {
                            $scope.vm.tableFlag = 2;
                        }
                    });
                } else {
                    postServer.service($scope.vm.getPosts).then(function(data) {
                        if (data.status === true) {
                            $scope.vm.bOff = true;
                            angular.forEach(data.data, function(data) {
                                data.post_time = data.post_time.substring(5);
                            });
                            $scope.vm.postList = data.data;
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
        }
        $scope.init();
        $scope.paginationConf = {
            currentPage: 0,
            totalItems: 0,
            itemsPerPage: 0,
            onChange: function() {
                $scope.vm.getPosts.pageIndex = this.currentPage;
                $scope.vm.getPosts.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.showAllPost = function() {
            window.localStorage.removeItem("module_name");
            window.localStorage.removeItem("module_id");
            $scope.vm.bOff = true;
            $scope.init();
            $scope.vm.subNavShow = false;
        }
        $scope.goPostDetail = function(id, title) {
            window.localStorage.setItem("post_title", title);
            window.localStorage.setItem("post_id", id);
            $rootScope.vm.post_title = window.localStorage.getItem("post_title");
            $state.go("main.post.postDetail");
        }
    }
]);
myBbsCtrls.controller('sendPostController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer', 'postServer',
    function($scope, $rootScope, $cookies, $state, moduleServer, postServer) {
        $scope.vm = {
            moduleList: {},
            getModule: {
                "action": "getAllListNoLimt"
            },
            postData: {
                action: "sendPost",
                title: "",
                content: "",
                type: 0
            }
        }
        moduleServer.service($scope.vm.getModule).then(function(data) {
            if (data.status === true) {
                $scope.vm.moduleList = data.data;
                var _nums = [];
                angular.forEach(data.data, function(data) {
                    _nums.push(parseInt(data.module_id));
                })
                $scope.vm.bigest = Math.max.apply(null, _nums);
                $scope.vm.postData.module = $scope.vm.bigest;
            }
        });
        $rootScope.vm.post_tile_show = false;
        $scope.sendPost = function() {
            postServer.service($scope.vm.postData).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, data.data);
                    $state.go("main.post.postList");
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        }
    }
]);
myBbsCtrls.controller('postDetailController', ['$scope', '$rootScope', '$cookies', '$sce', '$state', 'moduleServer', 'postServer', 'friendServer', 'whispersServer', 'messageServer',
    function($scope, $rootScope, $cookies, $sce, $state, moduleServer, postServer, friendServer, whispersServer, messageServer) {
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            isUserModule: false,
            isMe: false,
            isNotJin: false,
            isNotRe: false,
            isJin: false,
            isRe: false,
            repostList: {},
            getModule: {
                "action": "getAllListNoLimt"
            },
            getPostDetail: {
                action: "getPostDetail",
                post_id: window.localStorage.getItem("post_id")
            },
            addCollection: {
                action: "addCollection",
                post_id: window.localStorage.getItem("post_id")
            },
            addSupport: {
                action: "addSupport",
                post_id: window.localStorage.getItem("post_id")
            },
            addAgainst: {
                action: "addAgainst",
                post_id: window.localStorage.getItem("post_id")
            },
            addRepostSupport: {
                action: "addRepostSupport"
            },
            addRepostAgainst: {
                action: "addRepostAgainst"
            },
            addPostComment: {
                action: "addPostComment",
                post_id: window.localStorage.getItem("post_id"),
                repost_content: ''
            },
            addRepostComment: {
                action: "addRepostComment",
                rerepost_content: ''
            },
            getRepostList: {
                action: 'getRepostList',
                post_id: window.localStorage.getItem("post_id"),
                pageIndex: 1,
                pageSize: 10
            },
            checkIsUserModule: {
                action: 'checkIsUserModule'
            },
            checkIsMe: {
                action: 'checkIsMe',
                post_id: window.localStorage.getItem("post_id")
            },
            setJin: {
                action: 'setJin',
                post_id: window.localStorage.getItem("post_id")
            },
            removeJin: {
                action: 'removeJin',
                post_id: window.localStorage.getItem("post_id")
            },
            setRe: {
                action: 'setRe',
                post_id: window.localStorage.getItem("post_id")
            },
            removeRe: {
                action: 'removeRe',
                post_id: window.localStorage.getItem("post_id")
            },
            deletePost: {
                action: 'deletePost',
                post_id: window.localStorage.getItem("post_id")
            },
            updatePost: {
                action: 'updatePost'
            }
        }
        $rootScope.vm.post_tile_show = true;
        $rootScope.vm.post_title = window.localStorage.getItem("post_title");
        $scope.initPost = function() {
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
                    $scope.vm.checkIsUserModule.module_id = data.data.module_id;
                    //是否为版主
                    moduleServer.service($scope.vm.checkIsUserModule).then(function(data) {
                        if (data.status === true) {
                            $scope.vm.isUserModule = true;
                        }
                    });
                    //是否为我的帖子
                    postServer.service($scope.vm.checkIsMe).then(function(data) {
                        if (data.status === true) {
                            $scope.vm.isMe = true;
                        }
                    });
                    $scope.vm.checkIsMyFriend = {
                        action: "checkIsMyFriend",
                        to_user_id: data.data.user_id
                    }
                    //是否已经是好友
                    friendServer.service($scope.vm.checkIsMyFriend).then(function(data) {
                        if (data.status === true) {
                            $scope.vm.postDetail.isMyFriend = true;
                            $scope.vm.postDetail.isNotMyFriend = false;
                        } else {
                            $scope.vm.postDetail.isMyFriend = false;
                            $scope.vm.postDetail.isNotMyFriend = true;
                        }
                    });
                }
            });
        }
        $scope.initPost();
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                postServer.service($scope.vm.getRepostList).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        angular.forEach(data.data, function(data) {
                            data.rerepostListShow = data.rerepostList == null ? false : true;
                            data.isNotMyFriend = data.isNotMyFriend == null ? true : data.isNotMyFriend;
                        });
                        $scope.vm.repostList = data.data;
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
                $scope.vm.getRepostList.pageIndex = this.currentPage;
                $scope.vm.getRepostList.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.addCollection = function() {
            postServer.service($scope.vm.addCollection).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "收藏成功");
                    $scope.vm.postDetail.post_collection_num = parseInt($scope.vm.postDetail.post_collection_num) + 1;
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.addSupport = function() {
            postServer.service($scope.vm.addSupport).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, data.data);
                    $scope.vm.postDetail.post_support = parseInt($scope.vm.postDetail.post_support) + 1;
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.addAgainst = function() {
            postServer.service($scope.vm.addAgainst).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, data.data);
                    $scope.vm.postDetail.post_against = parseInt($scope.vm.postDetail.post_against) + 1;
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.addRepostSupport = function(id, event) {
            $scope.vm.addRepostSupport.repost_id = id;
            postServer.service($scope.vm.addRepostSupport).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, data.data);
                    $(event.target).parents(".detail-main").find(".num1").text(parseInt($(event.target).parents(".detail-main").find(".num1").text()) + 1);
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.addRepostAgainst = function(id, event) {
            $scope.vm.addRepostAgainst.repost_id = id;
            postServer.service($scope.vm.addRepostAgainst).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, data.data);
                    $(event.target).parents(".detail-main").find(".num2").text(parseInt($(event.target).parents(".detail-main").find(".num2").text()) + 1);
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.addPostComment = function() {
            if ($scope.vm.addPostComment.repost_content != "") {
                postServer.service($scope.vm.addPostComment).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, data.data);
                        $scope.vm.postDetail.post_commentcount = parseInt($scope.vm.postDetail.post_commentcount) + 1;
                        $scope.vm.bOff = true;
                        $scope.init();
                        $scope.vm.addPostComment.repost_content = "";
                    } else {
                        tools.alertError($rootScope, data.data);
                    }
                });
            } else {
                tools.alertError($rootScope, "回帖不得为空");
            }
        };
        $scope.getRerepostForm = function(event, id) {
            $scope.vm.addRepostComment.repost_id = id;
            var _this = $(event.target);
            var _parent = _this.parent().parent();
            if (_this.data('flag') === true) {
                $("#rerepostForm").hide().slideDown().insertBefore(_parent.find(".rerepostList-box"));
                _this.data("flag", false);
            } else {
                $("#rerepostForm").slideUp();
                _this.data("flag", true);
            }
            $scope.addRepostComment = function() {
                if ($scope.vm.addRepostComment.rerepost_content != "") {
                    if ($scope.vm.addRepostComment.rerepost_content.length > 256) {
                        tools.alertError($rootScope, "不得大于256个字符");
                    } else {
                        postServer.service($scope.vm.addRepostComment).then(function(data) {
                            if (data.status === true) {
                                tools.alertSuccess($rootScope, data.data);
                                $scope.init();
                                $("#rerepostForm").appendTo($(".detail-wrap")).hide();
                                $scope.vm.addRepostComment.rerepost_content = "";
                            } else {
                                tools.alertError($rootScope, data.data);
                            }
                        });
                    }
                } else {
                    tools.alertError($rootScope, "回帖不得为空");
                }
            }
        };
        $scope.setJin = function(id) {
            $scope.vm.setJin.module_id = id;
            postServer.service($scope.vm.setJin).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "设置成功");
                    $scope.vm.isJin = true;
                    $scope.vm.isNotJin = false;
                    $scope.vm.postDetail.post_Jin = "精华帖";
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.setRe = function(id) {
            $scope.vm.setRe.module_id = id;
            postServer.service($scope.vm.setRe).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "设置成功");
                    $scope.vm.isRe = true;
                    $scope.vm.isNotRe = false;
                    $scope.vm.postDetail.post_Re = "热帖";
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.removeJin = function(id) {
            $scope.vm.removeJin.module_id = id;
            postServer.service($scope.vm.removeJin).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "设置成功");
                    $scope.vm.isNotJin = true;
                    $scope.vm.isJin = false;
                    $scope.vm.postDetail.post_Jin = "";
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.removeRe = function(id) {
            $scope.vm.removeRe.module_id = id;
            postServer.service($scope.vm.removeRe).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "设置成功");
                    $scope.vm.isNotRe = true;
                    $scope.vm.isRe = false;
                    $scope.vm.postDetail.post_Re = "";
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.deletePost = function(id) {
            postServer.service($scope.vm.deletePost).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "删除成功");
                    $state.go("main.post.postList");
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.updatePostModal = function(id, title, module_id, type, content) {
            $("#updateModal").modal('show');
            $scope.vm.postData = {
                title: title,
                module_id: module_id,
                type: type,
                content: content
            }
            moduleServer.service($scope.vm.getModule).then(function(data) {
                if (data.status === true) {
                    $scope.vm.moduleList = data.data;
                    angular.forEach(data.data, function(data) {
                        data.module_active = data.module_id == module_id ? true : false;
                    });
                }
            });
            $scope.updatePost = function() {
                $scope.vm.updatePost.post_id = id;
                $scope.vm.updatePost.post_title = $scope.vm.postData.title;
                $scope.vm.updatePost.module_id = $scope.vm.postData.module_id;
                $scope.vm.updatePost.post_type = $scope.vm.postData.type;
                $scope.vm.updatePost.post_content = $scope.vm.postData.content;
                postServer.service($scope.vm.updatePost).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "修改成功");
                        $("#updateModal").modal('hide');
                        $scope.initPost();
                    } else {
                        tools.alertError($rootScope, data.data);
                    }
                });
            }
        };
        $scope.addFriendModalShow = function(id, name) {
            $("#addFriend").modal('show');
            $scope.vm.addFriend = {
                name: name,
                desc: "跟我交朋友吧",
                action: "addFriend",
                to_user_id: id
            }
            $scope.addFriend = function() {
                if ($scope.vm.addFriend.desc != "") {
                    if ($scope.vm.addFriend.desc.length > 256) {
                        tools.alertError($rootScope, "验证信息256字符");
                    } else {
                        friendServer.service($scope.vm.addFriend).then(function(data) {
                            if (data.status === true) {
                                tools.alertSuccess($rootScope, data.data);
                                $("#addFriend").modal('hide');
                            } else {
                                tools.alertError($rootScope, data.data);
                            }
                        });
                    }
                } else {
                    tools.alertError($rootScope, "输入验证信息");
                }
            }
        };
        $scope.addMessageModalShow = function(id, name) {
            $("#addMessage").modal('show');
            $scope.vm.addMessage = {
                name: name,
                content: "",
                action: "addMessage",
                to_user_id: id
            }
            $scope.addMessage = function() {
                if ($scope.vm.addMessage.content != "") {
                    if ($scope.vm.addMessage.content.length > 256) {
                        tools.alertError($rootScope, "留言不能大于256字符");
                    } else {
                        messageServer.service($scope.vm.addMessage).then(function(data) {
                            if (data.status === true) {
                                tools.alertSuccess($rootScope, data.data);
                                $("#addMessage").modal('hide');
                            } else {
                                tools.alertError($rootScope, data.data);
                            }
                        });
                    }
                } else {
                    tools.alertError($rootScope, "留言不能为空");
                }
            }
        };
        $scope.addWhispersModalShow = function(id, name) {
            $("#addWhispers").modal('show');
            $scope.vm.addWhispers = {
                name: name,
                content: "",
                action: "addWhispers",
                to_user_id: id
            }
            $scope.addWhispers = function() {
                if ($scope.vm.addWhispers.content != "") {
                    if ($scope.vm.addWhispers.content.length > 256) {
                        tools.alertError($rootScope, "私信不能大于256字符");
                    } else {
                        whispersServer.service($scope.vm.addWhispers).then(function(data) {
                            if (data.status === true) {
                                tools.alertSuccess($rootScope, data.data);
                                $("#addWhispers").modal('hide');
                            } else {
                                tools.alertError($rootScope, data.data);
                            }
                        });
                    }
                } else {
                    tools.alertError($rootScope, "私信不能为空");
                }
            }
        };
    }
]);
myBbsCtrls.controller('userController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer', 'userServer',
    function($scope, $rootScope, $cookies, $state, moduleServer, userServer) {
        $rootScope.vm = {
            user_tile_show: false
        }
        window.localStorage.removeItem("searchType");
        window.localStorage.removeItem("searchText");
    }
]);
myBbsCtrls.controller('userInfoController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer', 'userServer',
    function($scope, $rootScope, $cookies, $state, moduleServer, userServer) {
        $rootScope.vm = {
            getUserInfo: {
                action: "getUserInfo"
            },
            userInfo: {}
        }
        $scope.init = function() {
            userServer.service($scope.vm.getUserInfo).then(function(data) {
                if (data.status === true) {
                    $scope.vm.userInfo = data.data;
                    $scope.vm.userInfo.user_sexs = data.data.user_sex == 0 ? "男" : "女";
                    $scope.vm.userInfo.user_sign_actives = data.data.user_sign_active == 0 ? "开" : "关";
                }
            });
        }
        $scope.init();
        $scope.userUpdateUserInfoShow = function(id, user_face, name, sex, user_sign_active, user_signatrue) {
            $("#userUpdateUser").modal('show');
            $scope.vm.userUpdateUser = {
                action: "userUpdateUser",
                id: id,
                name: name,
                user_face: user_face,
                sex: sex,
                password: "",
                repassword: "",
                user_sign_active: user_sign_active,
                user_signatrue: user_signatrue
            }
            $scope.userUpdateUser = function() {
                $scope.vm.userUpdateUser.face = $('#user_face').val();
                userServer.service($scope.vm.userUpdateUser).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "修改成功");
                        $scope.init();
                        if ($scope.vm.userUpdateUser.name != $scope.user.name) {
                            $scope.vm.logout = {
                                action: "logout"
                            }
                            userServer.service($scope.vm.logout).then(function(data) {
                                if (data.status === true) {
                                    $cookies.remove('user');
                                    $rootScope.user = $cookies.getObject('user');
                                    $scope.vm.username = "";
                                    localStorage.clear();
                                    $rootScope.isLogin = false;
                                    $state.go("main.post.postList");
                                } else {
                                    tools.alertError($rootScope, "退出失败");
                                }
                            }, function() {
                                tools.alertError($rootScope, "退出失败");
                            });
                        }
                        $("#userUpdateUser").modal('hide');
                    } else {
                        tools.alertError($rootScope, data.data);
                    }
                });
            }
        };
        /*$scope.uploadFileModalShow = function() {
            $("#uploadFile").modal("show");
        }*/
        $scope.centerWindow = function(url, name, width, height) {
            var left = (screen.width - width) / 2;
            var top = (screen.height - height) / 2 - 50;
            window.open(url, name, 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
        }
    }
]);
myBbsCtrls.controller('userPostListController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer', 'userServer', 'postServer',
    function($scope, $rootScope, $cookies, $state, moduleServer, userServer, postServer) {
        $rootScope.vm = {
            tableFlag: 0,
            bOff: true,
            getUserPostList: {
                action: "getUserPostList",
                pageIndex: 1,
                pageSize: 15
            },
            userPostList: {}
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                postServer.service($scope.vm.getUserPostList).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        angular.forEach(data.data, function(data) {
                            data.post_time = data.post_time;
                            data.active = data.post_status == 1 ? true : false;
                            data.post_status = data.post_status == 1 ? "通过" : "未通过";
                        });
                        $scope.vm.userPostList = data.data;
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
                $scope.vm.getUserPostList.pageIndex = this.currentPage;
                $scope.vm.getUserPostList.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.goPostDetail = function(id, title) {
            window.localStorage.setItem("post_title", title);
            window.localStorage.setItem("post_id", id);
            $rootScope.vm.post_title = window.localStorage.getItem("post_title");
            $state.go("main.post.postDetail");
        }
    }
]);
myBbsCtrls.controller('userPostCollectionListController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer', 'userServer', 'collectionServer',
    function($scope, $rootScope, $cookies, $state, moduleServer, userServer, collectionServer) {
        $rootScope.vm = {
            tableFlag: 0,
            bOff: true,
            getPostCollectionList: {
                action: "getPostCollectionList",
                pageIndex: 1,
                pageSize: 15
            },
            postCollectionList: {}
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                collectionServer.service($scope.vm.getPostCollectionList).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        $scope.vm.postCollectionList = data.data;
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
                $scope.vm.getPostCollectionList.pageIndex = this.currentPage;
                $scope.vm.getPostCollectionList.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.goPostDetail = function(id, title) {
            window.localStorage.setItem("post_title", title);
            window.localStorage.setItem("post_id", id);
            $rootScope.vm.post_title = window.localStorage.getItem("post_title");
            $state.go("main.post.postDetail");
        }
        $scope.deleteCollection = function(id) {
            $scope.vm.deleteCollection = {
                action: "deleteCollection",
                id: id
            }
            collectionServer.service($scope.vm.deleteCollection).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "删除成功");
                    $scope.init();
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
    }
]);
myBbsCtrls.controller('userFriendListController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer', 'userServer', 'friendServer', 'whispersServer',
    function($scope, $rootScope, $cookies, $state, moduleServer, userServer, friendServer, whispersServer) {
        $rootScope.vm = {
            tableFlag: 0,
            tableFlagFriend: 0,
            tableFlagWhispers: 0,
            tabSwitch: "friend",
            bOff: true,
            bOffWhispers: true,
            loginUser: $scope.user.name,
            getIsUserFriendList: {
                action: "getIsUserFriendList",
                pageIndex: 1,
                pageSize: 10
            },
            getAddUserFriendList: {
                action: "getAddUserFriendList"
            },
            userFriendList: {},
            addFriendList: {}
        }
        $scope.init = function() {
            $("#friendAlert").hide();
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                friendServer.service($scope.vm.getIsUserFriendList).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        $scope.vm.userFriendList = data.data;
                        $scope.vm.tableFlag = 1;
                        $scope.paginationConf.totalItems = data.totalNum;
                        $scope.paginationConf.currentPage = data.pageIndex;
                        $scope.paginationConf.itemsPerPage = data.pageSize;
                        if (data.totalNum == 0) {
                            $scope.vm.tableFlag = 2;
                        }
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
                $scope.vm.getIsUserFriendList.pageIndex = this.currentPage;
                $scope.vm.getIsUserFriendList.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.initFriend = function() {
            friendServer.service($scope.vm.getAddUserFriendList).then(function(data) {
                if (data.status === true) {
                    $scope.vm.bOff = true;
                    $scope.vm.addFriendList = data.data;
                    $scope.vm.tableFlagFriend = 1;
                } else {
                    $scope.vm.tableFlagFriend = 2;
                }
            });
        };
        $scope.initFriend();
        $scope.getWhispers = function(id, friend_id, event) {
            $(event.target).find('span').hide();
            $scope.vm.bOffWhispers = true;
            $scope.vm.tabSwitch = "whispers";
            $scope.vm.getWhispersByUserId = {
                action: "getWhispersByUserId",
                user_id: id,
                pageIndex: 1,
                pageSize: 10,
                noSetStatus: "no"
            }
            $scope.vm.whispersList = {};
            $scope.initWhispers = function() {
                if ($scope.vm.bOffWhispers) {
                    $scope.vm.bOffWhispers = false;
                    whispersServer.service($scope.vm.getWhispersByUserId).then(function(data) {
                        if (data.status === true) {
                            $scope.vm.bOffWhispers = true;
                            $scope.vm.whispersList = data.data;
                            $scope.vm.tableFlagWhispers = 1;
                            $scope.paginationConfWhispers.totalItems = data.totalNum;
                            $scope.paginationConfWhispers.currentPage = data.pageIndex;
                            $scope.paginationConfWhispers.itemsPerPage = data.pageSize;
                        } else {
                            $scope.vm.tableFlagWhispers = 2;
                        }
                    });
                }
            };
            $scope.initWhispers();
            $scope.paginationConfWhispers = {
                currentPage: 0,
                totalItems: 0,
                itemsPerPage: 0,
                onChange: function() {
                    $scope.vm.getWhispersByUserId.pageIndex = this.currentPage;
                    $scope.vm.getWhispersByUserId.pageSize = this.itemsPerPage;
                    $scope.initWhispers();
                }
            };
            $scope.vm.addWhispers = {
                content: "",
                action: "addWhispers",
                to_user_id: id
            }
            $scope.addWhispers = function() {
                whispersServer.service($scope.vm.addWhispers).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, data.data);
                        $scope.vm.addWhispers.content = "";
                        $scope.vm.bOffWhispers = true;
                        $scope.vm.getWhispersByUserId.noSetStatus = "yes";
                        $scope.initWhispers();
                    } else {
                        tools.alertError($rootScope, data.data);
                    }
                });
            };
            $scope.deleteWhispers = function(id) {
                $scope.vm.deleteWhispers = {
                    action: "deleteWhispers",
                    whispers_id: id
                }
                whispersServer.service($scope.vm.deleteWhispers).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "删除成功");
                        $scope.vm.bOffWhispers = true;
                        $scope.initWhispers();
                    } else {
                        tools.alertError($rootScope, data.data);
                    }
                });
            };
            $scope.deleteFriend = function() {
                $scope.vm.deleteFriend = {
                    action: "deleteFriend",
                    id: friend_id
                }
                friendServer.service($scope.vm.deleteFriend).then(function(data) {
                    if (data.status === true) {
                        tools.alertSuccess($rootScope, "删除成功");
                        $scope.vm.bOff = true;
                        $scope.init();
                        $scope.vm.tabSwitch = "friend";
                    } else {
                        tools.alertError($rootScope, data.data);
                    }
                });
            };
        }
        $scope.agreeFriend = function(id) {
            $scope.vm.agreeFriend = {
                action: "agreeFriend",
                id: id
            }
            friendServer.service($scope.vm.agreeFriend).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "添加成功");
                    $scope.init();
                    $scope.initFriend();
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
        $scope.deleteFriend = function(id) {
            $scope.vm.deleteFriend = {
                action: "deleteFriend",
                id: id
            }
            friendServer.service($scope.vm.deleteFriend).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "删除成功");
                    $scope.initFriend();
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
    }
]);
myBbsCtrls.controller('userMessageListController', ['$scope', '$rootScope', '$cookies', '$state', 'messageServer', 'friendServer', 'whispersServer', 'messageServer',
    function($scope, $rootScope, $cookies, $state, messageServer, friendServer, whispersServer, messageServer) {
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            messageList: {},
            getAllMessageByUserId: {
                "action": "getAllMessageByUserId",
                pageIndex: 1,
                pageSize: 15
            }
        }
        $scope.init = function() {
            $("#messageAlert").hide();
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                messageServer.service($scope.vm.getAllMessageByUserId).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        $scope.vm.messageList = data.data;
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
                $scope.vm.getAllMessageByUserId.pageIndex = this.currentPage;
                $scope.vm.getAllMessageByUserId.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.addFriendModalShow = function(id, name) {
            $("#addFriend").modal('show');
            $scope.vm.addFriend = {
                name: name,
                desc: "跟我交朋友吧",
                action: "addFriend",
                to_user_id: id
            }
            $scope.addFriend = function() {
                if ($scope.vm.addFriend.desc != "") {
                    if ($scope.vm.addFriend.desc.length > 256) {
                        tools.alertError($rootScope, "验证信息256字符");
                    } else {
                        friendServer.service($scope.vm.addFriend).then(function(data) {
                            if (data.status === true) {
                                tools.alertSuccess($rootScope, data.data);
                                $("#addFriend").modal('hide');
                            } else {
                                tools.alertError($rootScope, data.data);
                            }
                        });
                    }
                } else {
                    tools.alertError($rootScope, "输入验证信息");
                }
            }
        };
        $scope.addMessageModalShow = function(id, name) {
            $("#addMessage").modal('show');
            $scope.vm.addMessage = {
                name: name,
                content: "",
                action: "addMessage",
                to_user_id: id
            }
            $scope.addMessage = function() {
                if ($scope.vm.addMessage.content != "") {
                    if ($scope.vm.addMessage.content.length > 256) {
                        tools.alertError($rootScope, "留言不能大于256字符");
                    } else {
                        messageServer.service($scope.vm.addMessage).then(function(data) {
                            if (data.status === true) {
                                tools.alertSuccess($rootScope, data.data);
                                $("#addMessage").modal('hide');
                            } else {
                                tools.alertError($rootScope, data.data);
                            }
                        });
                    }
                } else {
                    tools.alertError($rootScope, "留言不能为空");
                }
            }
        };
        $scope.addWhispersModalShow = function(id, name) {
            $("#addWhispers").modal('show');
            $scope.vm.addWhispers = {
                name: name,
                content: "",
                action: "addWhispers",
                to_user_id: id
            }
            $scope.addWhispers = function() {
                if ($scope.vm.addWhispers.content != "") {
                    if ($scope.vm.addWhispers.content.length > 256) {
                        tools.alertError($rootScope, "私信不能大于256字符");
                    } else {
                        whispersServer.service($scope.vm.addWhispers).then(function(data) {
                            if (data.status === true) {
                                tools.alertSuccess($rootScope, data.data);
                                $("#addWhispers").modal('hide');
                            } else {
                                tools.alertError($rootScope, data.data);
                            }
                        });
                    }
                } else {
                    tools.alertError($rootScope, "私信不能为空");
                }
            }
        };
        $scope.deleteMessage = function(id) {
            $scope.vm.deleteMessage = {
                action: "deleteMessage",
                id: id
            }
            messageServer.service($scope.vm.deleteMessage).then(function(data) {
                if (data.status === true) {
                    tools.alertSuccess($rootScope, "删除成功");
                    $scope.init();
                } else {
                    tools.alertError($rootScope, data.data);
                }
            });
        };
    }
]);
myBbsCtrls.controller('userIntegralListController', ['$scope', '$rootScope', '$cookies', '$state', 'integralServer',
    function($scope, $rootScope, $cookies, $state, integralServer) {
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            integralList: {},
            getAllIntegralListByUserId: {
                "action": "getAllIntegralListByUserId",
                pageIndex: 1,
                pageSize: 15
            }
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                integralServer.service($scope.vm.getAllIntegralListByUserId).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        $scope.vm.integralList = data.data;
                        $scope.vm.tableFlag = 1;
                        $scope.vm.totalIntegral = data.totalIntegral;
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
                $scope.vm.getAllIntegralListByUserId.pageIndex = this.currentPage;
                $scope.vm.getAllIntegralListByUserId.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
    }
]);
myBbsCtrls.controller('moduleController', ['$scope', '$rootScope', '$cookies', '$state', 'moduleServer',
    function($scope, $rootScope, $cookies, $state, moduleServer) {
        window.localStorage.removeItem("searchType");
        window.localStorage.removeItem("searchText");
        $scope.vm = {
            moduleList: {},
            postData: {
                "action": "getAllListNoLimt"
            }
        }
        moduleServer.service($scope.vm.postData).then(function(data) {
            if (data.status === true) {
                $scope.vm.moduleList = data.data;
            }
        });
        $scope.goPostList = function(id, name) {
            window.localStorage.setItem("module_id", id);
            window.localStorage.setItem("module_name", name);
            $state.go("main.post.postList");
        }
    }
]);
myBbsCtrls.controller('searchController', ['$scope', '$rootScope', '$cookies', '$state', 'searchServer',
    function($scope, $rootScope, $cookies, $state, searchServer) {
        $scope.vm = {
            tableFlag: 0,
            bOff: true,
            searchList: {},
            doSearch: {
                "action": "doSearch",
                searchType: window.localStorage.getItem("searchType") || "帖子",
                searchText: window.localStorage.getItem("searchText") || "",
                pageIndex: 1,
                pageSize: 15
            }
        }
        $scope.init = function() {
            if ($scope.vm.bOff) {
                $scope.vm.bOff = false;
                searchServer.service($scope.vm.doSearch).then(function(data) {
                    if (data.status === true) {
                        $scope.vm.bOff = true;
                        $scope.vm.searchList = data.data;
                        $scope.vm.tableFlag = 1;
                        $scope.vm.totalIntegral = data.totalIntegral;
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
                $scope.vm.doSearch.pageIndex = this.currentPage;
                $scope.vm.doSearch.pageSize = this.itemsPerPage;
                $scope.init();
            }
        };
        $scope.goPostDetail = function(id, title) {
            window.localStorage.setItem("post_title", title);
            window.localStorage.setItem("post_id", id);
            $state.go("main.post.postDetail");
            $rootScope.vm.post_title = window.localStorage.getItem("post_title");
        }
    }
]);