/**
 * Created by bozh on 9/25/14.
 */

function lengthInUtf8Bytes(str) {
    // Matches only the 10.. bytes that are non-initial characters in a multi-byte sequence.
    var m = encodeURIComponent(str).match(/%[89ABab]/g);
    return str.length + (m ? m.length : 0);
}

var home24demo = angular.module('home24demo', ['infinite-scroll', 'ngCookies'])
    .controller('listing',function($scope, $http, $cookieStore, $timeout){
        var apilnk = 'http://home24demo.grybov.com/api';
        var init = function(){
            $scope.config = {page: 0, max_page: 1, category: ''};
            $scope.products = [];
        };
        var pageloaded = true;
        $scope.loadMore = function(){
            if($scope.config.page < $scope.config.max_page && pageloaded){
                pageloaded = false;
                $scope.config.page++;
                var lnk = '/listing';
                if($scope.config.category != ''){
                    lnk += '/' +  $scope.config.category;
                }
                lnk += '/' + $scope.config.page;
                $http.get(apilnk + lnk).success(function(e){
                    $scope.config.max_page = e.max_page;
                    for(var i in e.data){
                        $scope.products.push(e.data[i]);
                    }
                    pageloaded = true;
                });
            }
        };

        $scope.cart = {
            show: false,
            data : {},
            total: 0,
            add: function(id){
                $http.put(apilnk + '/cart/'+id).success(function(e){
                    $scope.cart.data = e.cart;
                    $scope.cart.total = e.total;
                    if(!$scope.notification.disallow){
                        $scope.notification.productTitle = $scope.cart.data[id].title;
                        $scope.notification.show = true;
                        $timeout(function(){ $scope.notification.show = false; }, 3000);
                    }
                });
            },
            rm: function(id){
                $http.delete(apilnk+'/cart/'+id).success(function(e){
                    $scope.cart.data = e.cart;
                    $scope.cart.total = e.total;
                });
            },
            clear: function(){
                $http.post(apilnk+'/cart/', {clear: true}).success(function(e){
                    $scope.cart.data = e.cart;
                    $scope.cart.total = e.total;
                });
            },
            init: function(){
                $http.get(apilnk + '/cart').success(function(e){
                    $scope.cart.data = e.cart;
                    $scope.cart.total = e.total;
                });
            }
        };

        $scope.notification = {
            show : false,
            productTitle : '',
            disallow: false
        };

        init();
        $scope.cart.init();

    }).controller('add_products', function($scope, $http){
        $scope.new = {img:'',title:'',category:'',price:0};
        $scope.add = function(){
            $http.put('http://home24demo.grybov.com/api/add', $scope.new).success(function(e){
                $scope.new = {img:'',title:'',category:$scope.new.category,price:0};
            });
        };
    });
