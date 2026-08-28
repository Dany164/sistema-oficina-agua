<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Rutas públicas
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// Rutas protegidas: agrupadas bajo el filtro 'auth'
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('main', 'Main::index');
    $routes->get('tablas', 'Main::tablas');
});