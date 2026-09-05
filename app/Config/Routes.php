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
    $routes->group('contadores', ['filter' => 'role:administrador'], static function ($routes) {
        $routes->get('/', 'Contadores::index');
        $routes->get('crear', 'Contadores::crear');
        $routes->post('guardar', 'Contadores::guardar');
        $routes->get('editar/(:num)', 'Contadores::editar/$1');
        $routes->post('actualizar/(:num)', 'Contadores::actualizar/$1');
        $routes->post('retirar/(:num)', 'Contadores::retirar/$1');
        $routes->post('reactivar/(:num)', 'Contadores::reactivar/$1');
    });

    // Rutas de main =========================================================================
    $routes->get('/main', 'Main::index');

    // Rutas de Tarifas =========================================================================
    $routes->group('tarifas', ['filter' => 'role:administrador'], static function ($routes) {
        $routes->get('/', 'Tarifas::index');
        $routes->get('new', 'Tarifas::new');
        $routes->post('create', 'Tarifas::create');
        $routes->get('edit/(:num)', 'Tarifas::edit/$1');
        $routes->post('update/(:num)', 'Tarifas::update/$1');
    });

    // Rutas de Lecturas =========================================================================
    $routes->group('lecturas', static function ($routes) {
        $routes->get('/', 'Lecturas::index');
        $routes->get('new', 'Lecturas::new');
        $routes->post('create', 'Lecturas::create');

        // Corrección de lecturas: solo Administrador
        $routes->get('corregir/(:num)', 'Lecturas::corregir/$1');
        $routes->post('corregir/(:num)', 'Lecturas::actualizar/$1');

        $routes->get('recibo/(:num)', 'Lecturas::recibo/$1');
    });


    // Rutas de Clientes =========================================================================
    $routes->group('clientes', ['filter' => 'role:administrador,secretaria'], static function ($routes) {
        $routes->get('/', 'Clientes::index');
        $routes->get('new', 'Clientes::new');
        $routes->post('create', 'Clientes::create');
        $routes->get('edit/(:num)', 'Clientes::edit/$1');
        $routes->post('update/(:num)', 'Clientes::update/$1');
        $routes->post('delete/(:num)', 'Clientes::delete/$1');
    });

    // Rutas de Servicios =========================================================================
    $routes->group('servicios', ['filter' => 'role:administrador'], static function ($routes) {
        $routes->get('/', 'Servicios::index');
        $routes->get('new', 'Servicios::new');
        $routes->post('create', 'Servicios::create');
        $routes->get('edit/(:num)', 'Servicios::edit/$1');
        $routes->post('update/(:num)', 'Servicios::update/$1');
        $routes->post('delete/(:num)', 'Servicios::delete/$1');
    });

    // Rutas de Pagos =========================================================================
    $routes->group('pagos', ['filter' => 'role:administrador,secretaria'], static function ($routes) {
        $routes->get('/', 'Pagos::index');
        $routes->get('new', 'Pagos::new');
        $routes->post('create', 'Pagos::create');
        $routes->get('edit/(:num)', 'Pagos::edit/$1');
        $routes->post('update/(:num)', 'Pagos::update/$1');
        $routes->post('annul/(:num)', 'Pagos::annul/$1');
        $routes->get('receipt/(:num)', 'Pagos::receipt/$1');
    });

    $routes->group('usuarios', ['filter' => 'role:administrador'], static function ($routes) {
        $routes->get('/', 'Usuarios::index');
        $routes->get('new', 'Usuarios::new');
        $routes->post('create', 'Usuarios::create');
        $routes->get('edit/(:num)', 'Usuarios::edit/$1');
        $routes->post('update/(:num)', 'Usuarios::update/$1');
        $routes->post('delete/(:num)', 'Usuarios::delete/$1');
        $routes->post('toggle/(:num)', 'Usuarios::toggle/$1');
    });
});
