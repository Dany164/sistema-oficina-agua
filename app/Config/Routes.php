<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Rutas públicas
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// Rutas protegidas: agrupadas bajo el filtro 'auth'
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Rutas de Contadores =========================================================================
    $routes->get('contadores', 'Contadores::index');
    $routes->get('contadores/crear', 'Contadores::crear');
    $routes->post('contadores/guardar', 'Contadores::guardar');
    $routes->get('contadores/editar/(:num)', 'Contadores::editar/$1');
    $routes->post('contadores/actualizar/(:num)', 'Contadores::actualizar/$1');
    $routes->post('contadores/retirar/(:num)', 'Contadores::retirar/$1');

    // Rutas de main =========================================================================
    $routes->get('/main', 'Main::index');
    $routes->get('/tablas', 'Main::tablas'); //Solo era Ejemplo, TODO: Borrar ruta, vista y todo lo relacionado

    // Rutas de Tarifas =========================================================================
    $routes->group('tarifas', static function ($routes) {
        $routes->get('/', 'Tarifas::index');
        $routes->get('new', 'Tarifas::new');
        $routes->post('create', 'Tarifas::create');
        $routes->get('edit/(:num)', 'Tarifas::edit/$1');
        $routes->post('update/(:num)', 'Tarifas::update/$1');
    });



    // Rutas de Clientes =========================================================================
    $routes->group('clientes', static function ($routes) {
        $routes->get('/', 'Clientes::index');
        $routes->get('new', 'Clientes::new');
        $routes->post('create', 'Clientes::create');
        $routes->get('edit/(:num)', 'Clientes::edit/$1');
        $routes->post('update/(:num)', 'Clientes::update/$1');
        $routes->post('delete/(:num)', 'Clientes::delete/$1');
    });

    // Rutas de Servicios =========================================================================
    $routes->group('servicios', static function ($routes) {
        $routes->get('/', 'Servicios::index');
        $routes->get('new', 'Servicios::new');
        $routes->post('create', 'Servicios::create');
        $routes->get('edit/(:num)', 'Servicios::edit/$1');
        $routes->post('update/(:num)', 'Servicios::update/$1');
        $routes->post('delete/(:num)', 'Servicios::delete/$1');
    });

});

