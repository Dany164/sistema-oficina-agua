<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Rutas de Contadores =========================================================================
$routes->get('contadores', 'Contadores::index');
$routes->get('contadores/crear', 'Contadores::crear');
$routes->post('contadores/guardar', 'Contadores::guardar');
$routes->get('contadores/editar/(:num)', 'Contadores::editar/$1');
$routes->post('contadores/actualizar/(:num)', 'Contadores::actualizar/$1');
$routes->post('contadores/retirar/(:num)', 'Contadores::retirar/$1');

// Rutas de main =========================================================================
$routes->get('/main', 'Main::index');
$routes->get('/tablas', 'Main::tablas');
 feature/SCRUM-4-crud-clientes

$routes->group('clientes', static function ($routes) {
    $routes->get('/', 'Clientes::index');
    $routes->get('new', 'Clientes::new');
    $routes->post('create', 'Clientes::create');
    $routes->get('edit/(:num)', 'Clientes::edit/$1');
    $routes->post('update/(:num)', 'Clientes::update/$1');
    $routes->post('delete/(:num)', 'Clientes::delete/$1');
});

