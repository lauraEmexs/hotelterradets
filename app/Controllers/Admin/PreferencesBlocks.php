<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\Cms_block_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class PreferencesBlocks extends BaseAdminController
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

        $this->data['menu'] = 'management_blocks';
        $this->data['menu2'] = 'preferences_blocks';
        $this->data['page_title'] = 'Preferencias de Bloques';
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

    public function index()
    {
        $this->data['slug'] = 'preferences';
        $this->data['data'] =  $this->admin_general_model->get_domain_preferences($this->data['domain']);
        $this->data['title_section'] = 'Preferencias de Bloques';

        $blocks = $this->cms_block_model->get_cms_blocks ();

        $this->data['dynamic_types'] = config('App')->dynamicWithBlocks;

        foreach ($this->data['dynamic_types'] as $dynamic_type) {
            $this->data['blocks'] = [];
            foreach ($blocks as $block) {
                $block_params = json_decode($block->params);
                $this->data['blocks'][$block_params->title .'_'. $block->id][$block->id] = $block_params;
            }
            ksort($this->data['blocks']);
            $blocks_sorted = [];
            foreach ($this->data['blocks'] as $key => $block) {
                $blocks_sorted[key($block)] = current($block);
            }

            $this->data['blocks_' . $dynamic_type] = $blocks_sorted;

            $desactivated = array();
            foreach ($this->data['blocks'] as $id => $block) {
                $desactivated[] = $id;
            }

            $this->data['used_blocks_' . $dynamic_type] = $this->admin_general_model->get_preferences_blocks($this->data['selected_domain'], 'dynamic_' . $dynamic_type, true);
            $this->data['used_ids_' . $dynamic_type] = array();
            foreach ($this->data['used_blocks_' . $dynamic_type] as $block) {
                $block->params = json_decode($block->params);
                $this->data['used_ids_' . $dynamic_type][] = $block->cms_block_id;
            }

        }

        return view('admin/preferences_blocks', $this->data);
    }

    public function save()
    {
        $dynamics = config('App')->dynamicWithBlocks;
        foreach ($dynamics as $dynamic) {
            $blocks = $this->request->getPost('blocks_'. $dynamic);
            $desactivated = $this->request->getPost('desactivated_' . $dynamic);
            $blocks_array = explode(',', $blocks);

            foreach ($blocks_array as $id) {
                $data = array(
                    'domain' => $this->data['selected_domain'],
                    'cms_block_id' => $id,
                    'content_type' => 'dynamic_' . $dynamic,
                    'status' => 'ACTIVED'
                );
                $this->admin_general_model->save_preferences_blocks($this->data['selected_domain'], $data);
            }

            $desactivated = explode(',', $desactivated);
            foreach ($desactivated as $id) {
                $data = array(
                    'domain' => $this->data['selected_domain'],
                    'cms_block_id' => $id,
                    'content_type' => 'dynamic_' . $dynamic,
                    'status' => 'PAUSED'
                );
                $this->admin_general_model->save_preferences_blocks($this->data['selected_domain'], $data);
            }
        }

        \Config\Services::session()->setFlashdata('message', '<div class="alert alert-info">Preferencias guardadas</div>' );
        return redirect()->to('admin/preferencesblocks?domain='. $this->data['domain']);
    }

}
