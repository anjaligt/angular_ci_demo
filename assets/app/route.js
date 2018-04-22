var app =  angular.module('main-App',['ngRoute','angularUtils.directives.dirPagination']);

app.config(['$routeProvider',

    function($routeProvider) {

        $routeProvider.

            when('/', {

                templateUrl: '../views/home_view.html',

                controller: 'UserController'

            }).

            when('/items', {

                templateUrl: 'templates/items.html',

                controller: 'ItemController'

            });

}]);