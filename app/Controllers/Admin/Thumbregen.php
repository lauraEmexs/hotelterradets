<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\Auth_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Thumbregen extends BaseAdminController
{
    public $selected_domain;
    public $selected_domain_preferences;
    public $domain_url_params;
    public $data;

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

        $this->data['slug'] = 'thumbregen';
        $this->data['menu'] = 'thumbregen';
        $this->data['menu2'] = 'thumbregen';

        $this->domain_url_params = '';
        if (isset($_GET['domain']) && in_array($_GET['domain'], config('App')->allowedDomains, TRUE)) {
            $this->data['domain'] = $_GET['domain'];
            $this->selected_domain = $_GET['domain'];
            if (count(config('App')->allowedDomains) > 1) {
                $this->domain_url_params = '?domain='. $_GET['domain'];
            }
        } else {
            $this->data['domain'] = $this->domain;
            $this->selected_domain = $this->domain;
        }

        $this->selected_domain_preferences = $this->admin_general_model->get_domain_preferences($this->selected_domain);
        $this->data['admin_menu_items'] = $this->admin_general_model->get_admin_menu_items();
    }

    public function index()
    {
        $this->data['page_title'] = 'Regenerar miniaturas';
        $this->data['title_section'] = 'Regenerar miniaturas';

        $this->data['images'] = $this->admin_general_model->get_images();

        return view('admin/thumbregen', $this->data);
    }

    public function regenerate()
    {
        $num = $this->request->getPost('num');

        if ($num == 0) {
            $this->regenerateBlockImages();
        }

        $image = $this->admin_general_model->get_images_by_num($num);

        $this->admin_general_model->generate_thumbnails($image->url, $this->selected_domain_preferences);

        echo json_encode(['OK']);
        exit(0);
    }

    public function regenerateBlockImages()
    {
        $result = $this->admin_general_model->get_assets_page_block();
        foreach ($result as $item) {
            $data = json_decode($item->params_values);
            foreach ($data as $datum) {
                $path_parts = pathinfo($datum);
                if (isset($path_parts['extension']) && in_array(mb_strtolower($path_parts['extension']), ['jpg', 'jpeg'])) {
                    $this->admin_general_model->generate_thumbnails($datum, $this->selected_domain_preferences);
                }
            }
        }
    }
}
