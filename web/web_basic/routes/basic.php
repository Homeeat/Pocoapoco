<?php

/**
 * Basic router.
 * ----------------------------------------------
 * File name must be called router.php.
 * Check and execute sequentially from top to bottom,
 * therefore, it is recommended to place the commonly used ones up to reduce the response time.
 */

use Ntch\Pocoapoco\WebRestful\Routing\Router;

$router = new Router();


/**
 * Router to controller.
 * 【 param 】
 *  uri：client request, Parameter usage ":" means
 *  path：the path from the controller folder
 *  class：file name, extension must be php
 *  method：function to call, default is index
 */
$router->controller('/uri/:parameter', '/path', 'class', 'method');

/**
 * Router to view.
 * 【 param 】
 *  uri：client request, Parameter usage ":" means
 *  path：the path from the view folder
 *  class：file name, extension must be php
 *  data：parameters that need to be brought in
 */
$router->view('/uri/:parameter', '/path', 'class', ["pocoapoco" => "framework"]);

/**
 * Router to public.
 * 【 param 】
 *  path：the path from the public folder
 *  class：file name
 */
$router->public('/path', 'index.html');

/**
 * Router to controller and import the required mods.
 * ----------------------------------------------
 * Model is not recommended to import the entire database,
 * it is more efficient to fetch the required table.
 *
 * 【 param 】
 *  uri：client request, Parameter usage ":" means
 *  mods：array type, fill in what you need, value $name refer to the name filled in setting
 *  [
 *      'controller' => [$path, $class, $method],
 *      'libraries' => [$name],
 *      'mail' => [$name],
 *      'aws' => [$name],
 *      'oracle' => [$name],
 *      'mysql' => [$name],
 *      'mssql' => [$name],
 *      'postgre' => [$name],
 *  [
 */
$router->mvc('/uri/:parameter',
    [
        'controller' => ['/path', 'class', 'method'],
        'libraries' => ['name'],
        'mail' => ['name'],
        'aws' => ['name'],
        'oracle' => ['server_name', 'tb_name'],
        'mysql' => ['server_name', 'tb_name'],
        'mssql' => ['server_name', 'tb_name'],
        'postgre' => ['server_name', 'tb_name'],
    ]);