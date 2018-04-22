app.factory("productsFactory", function($http){
 
    var factory = {};
 
    // read all products
    factory.readProducts = function(){
        return $http({
            method: 'POST',
            url: 'http://localhost/codeigniter-rest-api/index.php/api/products/getProductList'
        });
    };

    factory.createProduct = function($scope){
    return $http({
        method: 'POST',
        data: {
            'name' : $scope.name,
            'description' : $scope.description,
            'price' : $scope.price,
            'category_id' : 1
        },
        url: 'http://localhost/codeigniter-rest-api/index.php/api/products/createProduct.php'
    });
};
     
    // createProduct will be here
  // read one product
factory.readOneProduct = function(id){
    return $http({
        method: 'GET',
        url: 'http://localhost/api/product/read_one.php?id=' + id
    });
};   
    return factory;
});