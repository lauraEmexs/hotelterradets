<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->match(['get', 'post'],'/', 'Hotel::remap');

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
 
/* Sitemap */
$routes->match(['get', 'post'],'/sitemap.xml', 'Sitemaps::index');

 /* Admin */
$routes->match(['get', 'post'],'/admin/', 'Admin\Dashboard::index');
$routes->match(['get', 'post'],'/admin/dynamic', 'Admin\Dynamic::index');
$routes->match(['get', 'post'],'/admin/dynamic/(:segment)', 'Admin\Dynamic::$1');
$routes->match(['get', 'post'],'/admin/dynamic/(:segment)/(:segment)', 'Admin\Dynamic::$1/$2');
$routes->match(['get', 'post'],'/admin/dynamic/(:segment)/(:segment)/(:segment)', 'Admin\Dynamic::$1/$2/$3');
$routes->match(['get', 'post'],'/admin/dynamic/(:segment)/(:segment)/(:segment)/(:num)', 'Admin\Dynamic::$1/$2/$3/$4');
$routes->match(['get', 'post'],'/admin/cmsBlock', 'Admin\CmsBlock::index');
$routes->match(['get', 'post'],'/admin/cmsBlock/(:segment)', 'Admin\CmsBlock::$1');
$routes->match(['get', 'post'],'/admin/cmsBlock/(:segment)/(:segment)', 'Admin\CmsBlock::$1/$2');
$routes->match(['get', 'post'],'/admin/cmsBlock/(:segment)/(:segment)/(:segment)', 'Admin\CmsBlock::$1/$2/$3');
$routes->match(['get', 'post'],'/admin/preferences', 'Admin\Preferences::index');
$routes->match(['get', 'post'],'/admin/preferences/(:segment)', 'Admin\Preferences::$1');
$routes->match(['get', 'post'],'/admin/idiomas', 'Admin\Idiomas::index');
$routes->match(['get', 'post'],'/admin/idiomas/(:segment)', 'Admin\Idiomas::$1');
$routes->match(['get', 'post'],'/admin/users', 'Admin\Users::index');
$routes->match(['get', 'post'],'/admin/users/(:segment)', 'Admin\Users::$1');
$routes->match(['get', 'post'],'/admin/users/(:segment)/(:segment)', 'Admin\Users::$1/$2');
$routes->match(['get', 'post'],'/admin/auth', 'Admin\Auth::index');
$routes->match(['get', 'post'],'/admin/auth/(:segment)', 'Admin\Auth::$1');
$routes->match(['get', 'post'],'/admin/imageoptimizer', 'Admin\Imageoptimizer::index');
$routes->match(['get', 'post'],'/admin/imageoptimizer/(:segment)', 'Admin\Imageoptimizer::$1');
$routes->match(['get', 'post'],'/admin/thumbregen', 'Admin\Thumbregen::index');
$routes->match(['get', 'post'],'/admin/thumbregen/(:segment)', 'Admin\Thumbregen::$1');
$routes->match(['get', 'post'],'/admin/webpregen', 'Admin\Webpregen::index');
$routes->match(['get', 'post'],'/admin/webpregen/(:segment)', 'Admin\Webpregen::$1');
$routes->match(['get', 'post'],'/admin/formSubmits', 'Admin\FormSubmits::index');
$routes->match(['get', 'post'],'/admin/formSubmits/(:segment)', 'Admin\FormSubmits::$1');
$routes->match(['get', 'post'],'/admin/formSubmits/(:segment)/(:segment)', 'Admin\FormSubmits::$1/$2');
$routes->match(['get', 'post'],'/admin/formSubmits/(:segment)/(:segment)/(:segment)', 'Admin\FormSubmits::$1/$2/$3');
$routes->match(['get', 'post'],'/admin/gallery', 'Admin\Gallery::index');
$routes->match(['get', 'post'],'/admin/gallery/(:segment)', 'Admin\Gallery::$1');
$routes->match(['get', 'post'],'/admin/gallery/(:segment)/(:any)', 'Admin\Gallery::$1/$2');

$routes->match(['get', 'post'],'/admin/preferencesblocks', 'Admin\PreferencesBlocks::index');
$routes->match(['get', 'post'],'/admin/preferencesblocks/(:segment)', 'Admin\PreferencesBlocks::$1');

/* Languages */
$routes->match(['get', 'post'],'/change-language', 'Language::change');
$routes->match(['get', 'post'],'(:segment)/change-language', 'Language::change');

/* Frontend */
$routes->match(['get', 'post'],'/(:segment)', 'Hotel::remap/$1');
$routes->match(['get', 'post'],'/(:segment)/(:segment)', 'Hotel::remap/$1/$2');
$routes->match(['get', 'post'],'/(:segment)/(:segment)/(:segment)', 'Hotel::remap/$1/$2/$3');
$routes->match(['get', 'post'],'/(:segment)/(:segment)/(:segment)/(:segment)', 'Hotel::remap/$1/$2/$3/$4');
$routes->match(['get', 'post'],'/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)', 'Hotel::remap/$1/$2/$3/$5');
$routes->match(['get', 'post'],'/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)', 'Hotel::remap/$1/$2/$3/$5/$6');




if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
