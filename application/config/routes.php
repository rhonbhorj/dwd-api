<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * | -------------------------------------------------------------------------
 * | URI ROUTING
 * | -------------------------------------------------------------------------
 * | This file lets you re-map URI requests to specific controller functions.
 * |
 * | Typically there is a one-to-one relationship between a URL string
 * | and its corresponding controller class/method. The segments in a
 * | URL normally follow this pattern:
 * |
 * | example.com/class/method/id/
 * |
 * | In some instances, however, you may want to remap this relationship
 * | so that a different class/function is called than the one
 * | corresponding to the URL.
 * |
 * | Please see the user guide for complete details:
 * |
 * | https://codeigniter.com/userguide3/general/routing.html
 * |
 * | -------------------------------------------------------------------------
 * | RESERVED ROUTES
 * | -------------------------------------------------------------------------
 * |
 * | There are three reserved routes:
 * |
 * | $route['default_controller'] = 'welcome';
 * |
 * | This route indicates which controller class should be loaded if the
 * | URI contains no data. In the above example, the "welcome" class
 * | would be loaded.
 * |
 * | $route['404_override'] = 'errors/page_missing';
 * |
 * | This route will tell the Router which controller/method to use if those
 * | provided in the URL cannot be matched to a valid route.
 * |
 * | $route['translate_uri_dashes'] = FALSE;
 * |
 * | This is not exactly a route, but allows you to automatically route
 * | controller and method names that contain dashes. '-' isn't a valid
 * | class or method name character, so it requires translation.
 * | When you set this option to TRUE, it will replace ALL dashes in the
 * | controller and method URI segments.
 * |
 * | Examples: my-controller/index -> my_controller/index
 * | my-controller/my-method -> my_controller/my_method  v1/bsp-payment
 */

$route['404_override'] = 'custom404';

$route['default_controller'] = 'api';
$route['generate-token'] = 'api/generate_token';

$route['v1/council-list'] = 'detail/council_list';
$route['v1/district-list'] = 'detail/get_district_details';
$route['v1/sub-district-list'] = 'detail/get_sub_district_details';
$route['v1/school-list'] = 'detail/get_school';  
$route['v1/registration-form-list'] = 'detail/registration_form_list';


$route['v1/scout-list'] = 'scout/scout_list';
$route['v1/scout-payment-type'] = 'scout/scout_payment_type';
$route['v1/scout-payment-description'] = 'scout/scout_payment_description';

///postback
$route['postback/data'] = 'postback/data';


//manage  
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['v1/bsp-transaction'] = 'report/payment_data';


$route['v1/bsp-register'] = 'account/register';
$route['v1/bsp-login'] = 'auth/bsplogin';




//generate Qr
$route['v1/bsp-payment'] = 'payment/boy_scout';
$route['transaction-status'] = 'transaction/transaction_status'; 

//bsp v2  bsp-aur-generate
$route['v1/bsp-form-generate'] = 'generate/form';
$route['v1/bsp-update-status'] = 'generate/update_form_status';