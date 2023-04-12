<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\Cms_block_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Gallery extends BaseAdminController
{
    public $data;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        if (!\Config\Services::session()->get('userdata')) {
            header('Location: /admin/auth');
            die ();
        }

        $this->data['menu'] = 'management';
        $this->data['menu2'] = 'preferences';
        $this->data['page_title'] = 'Preferencias';
        $this->data['logo']="logo.png";

        $this->domain_url_params = '';
        if (isset($_GET['domain']) && in_array($_GET['domain'], config('App')->allowedDomains, TRUE)) {
            $this->data['domain'] = $_GET['domain'];
            $this->data['selected_domain'] = $_GET['domain'];
            if (count(config('App')->allowedDomains) > 1) {
                $this->domain_url_params = '?domain='. $_GET['domain'];
            }
        } else {
            $this->data['domain'] = $this->domain;
            $this->data['selected_domain'] = $this->domain;
        }
        $this->selected_domain_preferences = $this->admin_general_model->get_domain_preferences($this->data['selected_domain']);
        $this->data['admin_menu_items'] = $this->admin_general_model->get_admin_menu_items();
        $this->data['domain_url_params'] = $this->domain_url_params;


        $this->cms_block_model = new Cms_block_model ();

        $this->cms_block_model->domain = $this->data['domain'];
        $this->cms_block_model->selected_domain = $this->data['selected_domain'];
        $this->cms_block_model->selected_domain_preferences = $this->selected_domain_preferences;
        $this->cms_block_model->request = $this->request;
    }

    public function files()
    {
		$files=[];
		$path = func_get_args();
		$path =  FCPATH.'assets/themes/'. $this->data['domain'] .'/'.implode('/',$path);
		if (file_exists($path)) {
			$status = '200';
			$dir = scandir($path);
			foreach ($dir as $file) {
				$type = 'file';
				if ( is_dir ($path.'/'.$file) ) $type = 'folder';
				else if ( @is_array(getimagesize($path.'/'.$file)) || substr($file, -4,4) == '.svg') $type = 'image';
				else $type = 'document';
				if ($file != '.' && $file != '..' && $file == str_replace( ['thigh','tmedium','tlow','webp'], '', $file) ) {
					$files[] = [
						'name' => $file,
						'type' => $type,
					];
				}
			}
		} else {
			$status = '404';
		}
		
		header('Content-Type: application/json; charset=utf-8');
        echo json_encode ([
			'status'=>$status,
			'files'=>$files
		]);
    }

}
