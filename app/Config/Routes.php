<?php

namespace Config;

// Create a new instance of our RouteCollection class.
use App\Controllers\CustomerController;

$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

$routes->get('/customers', [CustomerController::class, 'list'], ['as' => 'customers.list']);
$routes->get('/customers/(:num)', [CustomerController::class, 'findById'], ['as' => 'customers.findById']);
$routes->post('/customers', [CustomerController::class, 'create'], ['as' => 'customers.create']);
$routes->put('/customers/(:num)', [CustomerController::class, 'update'], ['as' => 'customers.update']);
$routes->delete('/customers/(:num)', [CustomerController::class, 'delete'], ['as' => 'customers.delete']);

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
