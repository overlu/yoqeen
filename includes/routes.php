<?php
/*
// any, get, post, put, patch, delete
$route->any('/', function(){
    // Any method requests
    Yoqeen\Libs\Http::$yoqeenMod = 'mod';
    yoqeen\libs\http::$yoqeenCon = 'con';
    yoqeen\libs\http::$yoqeenAct = 'act';
});

$route->any('/?/?', function($a, $b){});

$route->any('/', 'mod@con@act');
$route->any('/', 'mod@act');
$route->any('/', ['mod', 'con', 'act']);
$route->any('/', ['mod', 'act']);

// can use multiple methods, just add _ between method names.
$route->get_post('/', function(){
    // Only GET and POST request
});

$route->group('/admin', function(){
    $this->any('/', function(){});
});

$route->get('/{username}:([0-9a-z_.-]+)/post/{id}:([0-9]+)',function($username, $id){
    echo "author $username post id $id";
});

$route->addPattern([
    'username' => '/([0-9a-z_.-]+)',
    'id' => '/([0-9]+)'
]);

$route->get('/{username}:username/post/{id}:id', function($username, $id){
    echo "author $username post id $id";
});

*/

// $route->get('/te','diary@index');

$route->addPattern([
    'id'                => '/([0-9]+)',
    'username'          => '/([a-z0-9_]+)',
    'action0'           => '/(view|del|edit|get)',
    'action1'           => '/(add|delete|addTopic|deleteTopic)',
    'code'              => '/([a-z0-9_]{8})',
]);

// backend route
$route->group('/admin', function(){

});

// $route->get_post('/mod', function(){
//     Yoqeen\Libs\Http::$yoqeenMod = 'mod';
//     Yoqeen\Libs\Http::$yoqeenCon = 'con';
//     Yoqeen\Libs\Http::$yoqeenFun = 'fun';
// });