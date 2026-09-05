<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 * The default route after login is in the NoAuthCheck Filter
 * The default route if no current user is in AuthCheck Filter
 * No filter means public route 
 */

// Dashboard Menu routes
$routes->group('activity_designs', ['filter' => 'AuthCheckFilter'], function ($routes) {
    $controller = "Activity_designs";
    // HTML pages
    $routes->match(['get', 'post'], '/', $controller . '::index');
    $routes->get('create', $controller . '::create');
    $routes->post('save', $controller . '::save');
    $routes->post('update', $controller . '::update');
    $routes->get('view/(:any)', $controller . '::view/$1');
    $routes->get('edit/(:any)', $controller . '::edit/$1');
    $routes->get('printlist', $controller . '::printlist');
    $routes->get('exportlist', $controller . '::exportlist');

    $routes->get('replace_attachments/(:any)', $controller . '::replace_attachments/$1');
    $routes->post('replace_attachments_save', $controller . '::replace_attachments_save');

    $routes->post('review', $controller . '::review');
    $routes->post('approve', $controller . '::approve');
    $routes->post('cancel', $controller . '::cancel');
    $routes->post('decline', $controller . '::decline');

    $routes->get('search_employees', $controller . '::search_employees');
    $routes->get('get_section_employees', $controller . '::get_section_employees');
});
