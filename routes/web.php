<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//Auth
$router->post("/user-login", "AuthController@userLogin");
$router->post("/user-create", "AuthController@userCreate");
$router->post("/user-change-password", ['middleware' => 'if_userlogin', 'uses' => "AuthController@userChangePassword"]);
$router->post("/search", "SearchEngineController@searchEngineResults");
$router->post("/dropdown-search-engine-lists","SearchEngineController@searchEngineList");

//Search Engine List
$router->post("/search-engine-lists", ['middleware' => 'if_userlogin', 'uses' => "SearchEngineController@searchengine_lists"]);
$router->post("/search-engine-list/show/{id}", ['middleware' => 'if_userlogin', 'uses' => "SearchEngineController@searchengine_listShow"]);
$router->post("/search-engine-list/create", ['middleware' => 'if_userlogin', 'uses' => "SearchEngineController@searchengine_listCreate"]);
$router->post("/search-engine-list/update/{id}", ['middleware' => 'if_userlogin', 'uses' => "SearchEngineController@searchengine_listUpdate"]);
$router->post("/search-engine-list/delete/{id}", ['middleware' => 'if_userlogin', 'uses' => "SearchEngineController@searchengine_listDelete"]);
