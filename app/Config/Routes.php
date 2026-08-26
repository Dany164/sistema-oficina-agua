<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('contadores', 'Contadores::index');
$routes->get('contadores/crear', 'Contadores::crear');
$routes->post('contadores/guardar', 'Contadores::guardar');
$routes->get('contadores/editar/(:num)', 'Contadores::editar/$1');
$routes->post('contadores/actualizar/(:num)', 'Contadores::actualizar/$1');
$routes->post('contadores/retirar/(:num)', 'Contadores::retirar/$1');
