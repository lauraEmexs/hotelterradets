<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\Admin_general_model;
use App\Models\Admin_language_model;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseAdminController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Helpers
        helper ('funcaux');
        helper('url');
        helper('form');
        helper('string');
        helper('text');

        // Settings
        if (in_array($_SERVER['HTTP_HOST'], config('App')->allowedDomains, TRUE)) {
            $this->domain = $_SERVER['HTTP_HOST'];
        } else {
            $this->domain = config('App')->defaultDomain;
        }
        define('THEME_PATH', 'assets/themes/'. $this->domain .'/');

        // Data
        $this->admin_general_model = new Admin_general_model ();
        $this->admin_language_model = new Admin_language_model ();

        $this->table = new \CodeIgniter\View\Table();

        $this->data['logged_user'] = \Config\Services::session()->get('userdata');

        $this->data['all_domain_preferences'] = [];
        foreach (config('App')->allowedDomains as $domain_menu) {
            $this->data['all_domain_preferences'][$domain_menu] = $this->admin_general_model->get_domain_preferences($domain_menu);
        }
    }

    public function search_filter($data) {
        $result = [];
        foreach ($data as $row) {
            foreach ($row as $value) {
                if ( $value && stripos($value, $_GET['q']) !== false ) {
                    $result[] = $row;
                    break;
                }
            }
        }
        return $result;
    }
}
