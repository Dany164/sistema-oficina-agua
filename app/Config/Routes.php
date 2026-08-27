<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('/main', 'Main::index');
$routes->get('/tablas', 'Main::tablas');

$routes->group('clientes', static function ($routes) {
    $routes->get('/', 'Clientes::index');
    $routes->get('new', 'Clientes::new');
    $routes->post('create', 'Clientes::create');
    $routes->get('edit/(:num)', 'Clientes::edit/$1');
    $routes->post('update/(:num)', 'Clientes::update/$1');
    $routes->post('delete/(:num)', 'Clientes::delete/$1');
});