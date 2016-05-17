/**
 * Created by gaohuabin on 2016/4/29.
 */
var myBbsAdminDirectives = angular.module('myBbsAdminDirectives', []);
myBbsAdminDirectives.directive('validatePassword', [function () {
    return {
        require: "ngModel",
        link: function (scope, element, attr, ngModel) {
            if (ngModel) {
                //正则反向验证：密码要求长度8-20位，至少包括大写字母、小写字母、数字、特殊字符中的两项。
                var passwordRegexp = /^([0-9]*|[A-Z]*|[a-z]*|[^0-9a-zA-Z]*)$/;
            }
            var customValidator = function (value) {
                var validity = ngModel.$isEmpty(value) || !passwordRegexp.test(value);
                ngModel.$setValidity("validatePassword", validity);
                return validity ? value : undefined;
            };
            ngModel.$formatters.push(customValidator);
            ngModel.$parsers.push(customValidator);
        }
    };
}]);
myBbsAdminDirectives.directive("repeat", [function () {
    return {
        restrict: 'A',
        require: "ngModel",
        link: function (scope, element, attrs, ctrl) {
            if (ctrl) {
                var otherInput = element.inheritedData("$formController")[attrs.repeat];

                var repeatValidator = function (value) {
                    var validity = value === otherInput.$viewValue;
                    ctrl.$setValidity("repeat", validity);
                    return validity ? value : undefined;
                };

                ctrl.$parsers.push(repeatValidator);
                ctrl.$formatters.push(repeatValidator);

                otherInput.$parsers.push(function (value) {
                    ctrl.$setValidity("repeat", value === ctrl.$viewValue);
                    return value;
                });
            }
        }
    };
}]);
myBbsAdminDirectives.directive('tmPagination', [function () {
    return {
        restrict: 'EA',
        template: '<div class="page-list">' + '<ul class="pagination" ng-show="conf.totalItems > 0">'
        + '<li ng-class="{disabled: conf.currentPage == 1}" ng-click="prevPage()"><a href="javascript:">&larr;</a></li>'
        + '<li ng-repeat="item in pageList track by $index" ng-class="{active: item == conf.currentPage, separate: item == \'...\'}" '
        + 'ng-click="changeCurrentPage(item)">' + '<a  href="javascript:">{{ item }}</a>' + '</li>'
        + '<li ng-class="{disabled: conf.currentPage == conf.numberOfPages}" ng-click="nextPage()"><a  href="javascript:">&rarr;</a></li>' + '</ul>'
        + '<div class="page-total" ng-show="conf.totalItems > 0">'
        + '第<input type="text" ng-model="jumpPageNum"  ng-keyup="jumpToPage($event)"/>页 '
        + '每页<select ng-model="conf.itemsPerPage" ng-options="option for option in conf.perPageOptions "></select>'
        + '/共<strong>{{ conf.totalItems }}</strong>条' + '</div>' + '<div class="no-items hidden" ng-show="conf.totalItems <= 0">加载中……</div>' + '</div>',
        replace: true,
        scope: {
            conf: '='
        },
        link: function (scope, element, attrs) {
            // 变更当前页
            scope.changeCurrentPage = function (item) {
                if (item == '...') {
                    return;
                } else {
                    scope.conf.currentPage = item;
                }
            };

            // pageList数组
            function getPagination(newValue, oldValue) {
                // 定义分页的长度必须为奇数 (default:9)
                scope.conf.pagesLength = parseInt(scope.conf.pagesLength) ? parseInt(scope.conf.pagesLength) : 9;
                if (scope.conf.pagesLength % 2 === 0) {
                    // 如果不是奇数的时候处理一下
                    scope.conf.pagesLength = scope.conf.pagesLength - 1;
                }
                // conf.erPageOptions
                if (!scope.conf.perPageOptions) {
                    scope.conf.perPageOptions = [10, 15, 20, 30, 50];
                }
                // conf.currentPage
                scope.conf.currentPage = parseInt(scope.conf.currentPage) ? parseInt(scope.conf.currentPage) : 1;
                // conf.totalItems
                scope.conf.totalItems = parseInt(scope.conf.totalItems) ? parseInt(scope.conf.totalItems) : 0;
                // conf.itemsPerPage (default:15)
                scope.conf.itemsPerPage = parseInt(scope.conf.itemsPerPage) ? parseInt(scope.conf.itemsPerPage) : 15;
                // numberOfPages
                scope.conf.numberOfPages = Math.ceil(scope.conf.totalItems / scope.conf.itemsPerPage);
                // judge currentPage > scope.numberOfPages
                if (scope.conf.currentPage < 1) {
                    scope.conf.currentPage = 1;
                }
                // 如果分页总数>0，并且当前页大于分页总数
                if (scope.conf.numberOfPages > 0 && scope.conf.currentPage > scope.conf.numberOfPages) {
                    scope.conf.currentPage = scope.conf.numberOfPages;
                }
                // jumpPageNum
                scope.jumpPageNum = scope.conf.currentPage;
                // 如果itemsPerPage在不在perPageOptions数组中，就把itemsPerPage加入这个数组中
                var perPageOptionsLength = scope.conf.perPageOptions.length;
                // 定义状态
                var perPageOptionsStatus;
                for (var i = 0; i < perPageOptionsLength; i++) {
                    if (scope.conf.perPageOptions[i] == scope.conf.itemsPerPage) {
                        perPageOptionsStatus = true;
                    }
                }
                // 如果itemsPerPage在不在perPageOptions数组中，就把itemsPerPage加入这个数组中
                if (!perPageOptionsStatus) {
                    scope.conf.perPageOptions.push(scope.conf.itemsPerPage);
                }
                // 对选项进行sort
                scope.conf.perPageOptions.sort(function (a, b) {
                    return a - b;
                });
                scope.pageList = [];
                if (scope.conf.numberOfPages <= scope.conf.pagesLength) {
                    // 判断总页数如果小于等于分页的长度，若小于则直接显示
                    for (i = 1; i <= scope.conf.numberOfPages; i++) {
                        scope.pageList.push(i);
                    }
                } else {
                    // 总页数大于分页长度（此时分为三种情况：1.左边没有...2.右边没有...3.左右都有...）
                    // 计算中心偏移量
                    var offset = (scope.conf.pagesLength - 1) / 2;
                    if (scope.conf.currentPage <= offset) {
                        // 左边没有...
                        for (i = 1; i <= offset + 1; i++) {
                            scope.pageList.push(i);
                        }
                        scope.pageList.push('...');
                        scope.pageList.push(scope.conf.numberOfPages);
                    } else if (scope.conf.currentPage > scope.conf.numberOfPages - offset) {
                        scope.pageList.push(1);
                        scope.pageList.push('...');
                        for (i = offset + 1; i >= 1; i--) {
                            scope.pageList.push(Math.ceil(scope.conf.numberOfPages - i));
                        }
                        scope.pageList.push(scope.conf.numberOfPages);
                    } else {
                        // 最后一种情况，两边都有...
                        scope.pageList.push(1);
                        scope.pageList.push('...');
                        for (i = Math.ceil(offset / 2); i >= 1; i--) {
                            scope.pageList.push(scope.conf.currentPage - i);
                        }
                        scope.pageList.push(scope.conf.currentPage);
                        for (i = 1; i <= offset / 2; i++) {
                            scope.pageList.push(scope.conf.currentPage + i);
                        }
                        scope.pageList.push('...');
                        scope.pageList.push(scope.conf.numberOfPages);
                    }
                }
                if (scope.conf.onChange) {
                    // 防止初始化两次请求问题
                    if (!(oldValue != newValue && oldValue[0] === 0)) {
                        scope.conf.onChange();
                    }
                }
                scope.$parent.conf = scope.conf;
            }

            // prevPage
            scope.prevPage = function () {
                if (scope.conf.currentPage > 1) {
                    scope.conf.currentPage -= 1;
                }
            };
            // nextPage
            scope.nextPage = function () {
                if (scope.conf.currentPage < scope.conf.numberOfPages) {
                    scope.conf.currentPage += 1;
                }
            };
            // 跳转页
            scope.jumpToPage = function () {
                scope.jumpPageNum = scope.jumpPageNum.replace(/[^0-9]/g, '');
                if (scope.jumpPageNum !== '') {
                    scope.conf.currentPage = scope.jumpPageNum;
                }
            };
            scope.$watch(function () {
                if (!scope.conf.totalItems) {
                    scope.conf.totalItems = 0;
                }
                var newValue = scope.conf.totalItems + ' ' + scope.conf.currentPage + ' ' + scope.conf.itemsPerPage;
                return newValue;
            }, getPagination);
        }
    };
}]);