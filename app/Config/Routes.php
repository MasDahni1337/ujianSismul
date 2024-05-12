<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'ProductController::index');
$routes->post('/list', 'ProductController::list');
$routes->post('/save', 'ProductController::save');
$routes->post('/update', 'ProductController::update');
$routes->get('/getProduct/(:any)', 'ProductController::getProduct/$1');
$routes->post('/singleDelete/(:any)', 'ProductController::singleDelete/$1');
$routes->post('/batchDelete', 'ProductController::batchDelete');
