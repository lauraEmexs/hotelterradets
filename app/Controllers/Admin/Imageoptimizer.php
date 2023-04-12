<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\Auth_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Imageoptimizer extends BaseAdminController
{
    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->auth_model = new Auth_model ();

        if (!\Config\Services::session()->get('userdata')) {
            header('Location: /admin/auth');
            die ();
        }

        $this->data['slug'] = 'imageoptimizer';
        $this->data['menu'] = 'imageoptimizer';
        $this->data['menu2'] = 'imageoptimizer';

        $this->data['admin_menu_items'] = $this->admin_general_model->get_admin_menu_items();
    }

    public function index()
    {
        $this->data['page_title'] = 'Imageoptimizer';
        $this->data['title_section'] = 'Imageoptimizer';
        return view('admin/imageoptimizer', $this->data);
    }

    public function optimize()
    {
        $this->data['images_optimize'] = [];

        $rootDirectory = $this->request->getPost('rootDirectory');

        if (mb_strpos($rootDirectory,'/img/') === FALSE) {
            self::images_lookup($rootDirectory .'/uploads/img/');
            $rootDirectory .= '/img';
        }

        self::images_lookup($rootDirectory);

        echo json_encode($this->data['images_optimize']);
        exit(0);
    }

    private function images_lookup($rootDirectory)
    {
        foreach (glob($rootDirectory . '/*.{[jJ][pP][gG],[jJ][pP][eE][gG],[pP][nN][gG],[gG][iI][fF]}', GLOB_BRACE) as $filename) {
            $this->data['images_optimize'][] = $filename;
        }
        foreach (glob($rootDirectory .'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            self::images_lookup($dir);
        }
    }

    public function optimizeImage()
    {
        $img = $this->request->getPost('img');
        $this->admin_general_model->optimize_img ($img);

        echo json_encode(array("OK"));
        exit(0);
    }
}
