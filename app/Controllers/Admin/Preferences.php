<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\Cms_block_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Preferences extends BaseAdminController
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

    public function index()
    {
        $this->data['slug'] = 'preferences';
        $this->data['data'] =  $this->admin_general_model->get_domain_preferences($this->data['domain']);
        $this->data['title_section'] = 'Preferencias generales';
        //$this->data['title_section'] .=  ($this->data['domain']) ? ' <small>('. $this->data['domain'] .')</small>' : '';
        $blocks = $this->cms_block_model->get_cms_blocks ();

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

        //Selector de idioma por dominio
        $this->data['selected_langs'] = indexar_array($this->admin_language_model->get_all(array('actived' => 1)), 'id');
        $selected_lang = explode(',',$this->data['data']->selected_languages);
        $actived = array();
        foreach ($this->data['selected_langs'] as $language) {
            if (in_array($language->id, $selected_lang, TRUE)){
                $actived[$language->id] = $language;
            }
        }
        $this->data['used_langs'] = indexar_array($actived, 'id');

        $this->data['blocks'] = $blocks_sorted;

        $this->data['used_blocks'] = (isset($this->data['data']) && $this->data['data']->blocks != '') ? explode(',', $this->data['data']->blocks) : [];

        $this->data['used_admin_menu'] = (isset($this->data['data']) && $this->data['data']->admin_menu != '') ? explode(',', $this->data['data']->admin_menu) : [];

        $pages = $this->admin_general_model->get_dynamic_table_data([
            'slug' => 'pages',
            'language' => $this->admin_language_model->get_default_language_id(),
            'domain' => $this->data['domain'],
            'order' => 'text_title'
        ]);
        $this->data['all_pages'] = ['0' => '-'];
        foreach ($pages as $page) {
            $this->data['all_pages'][$page->id] = $page->text_title;
        }

        return view('admin/preferences', $this->data);
    }

    public function save()
    {
        $this->admin_general_model->save_preferences($this->data['selected_domain'], [
            'domain'             => $this->data['selected_domain'],
            'custom_css'         => $this->request->getPost('custom_css'),
            'custom_js'          => $this->request->getPost('custom_js'),
            'scripts_head'       => $this->request->getPost('scripts_head'),
            'scripts_footer'     => $this->request->getPost('scripts_footer'),
			'scripts_dashboard'     => $this->request->getPost('scripts_dashboard'),
            'google_tag_manager' => $this->request->getPost('google_tag_manager'),
            'gmap_api_key'       => $this->request->getPost('gmap_api_key'),
            'recaptcha_version'  => $this->request->getPost('recaptcha_version'),
            'recaptcha_public_key'=> $this->request->getPost('recaptcha_public_key'),
            'recaptcha_private_key'=> $this->request->getPost('recaptcha_private_key'),
            'google_maps_url'    => $this->request->getPost('google_maps_url'),
            'hotel_phone'        => $this->request->getPost('hotel_phone'),
            'hotel_email'        => $this->request->getPost('hotel_email'),
            'restaurant_mail'    => $this->request->getPost('restaurant_mail'),
            'hotel_address'      => $this->request->getPost('hotel_address'),
            'hotel_latitude'     => $this->request->getPost('hotel_latitude'),
            'hotel_longitude'    => $this->request->getPost('hotel_longitude'),
            'header_image_404'   => $this->request->getPost('header_image_404'),
            'menu_logo'          => $this->request->getPost('menu_logo'),
            'menu_mobile_logo'   => $this->request->getPost('menu_mobile_logo'),
            'footer_logo'        => $this->request->getPost('footer_logo'),
            'booking_engine_url' => $this->request->getPost('booking_engine_url'),
            'booking_engine_id'  => $this->request->getPost('booking_engine_id'),
            'instagram'          => $this->request->getPost('instagram'),
            'instagram_user'     => $this->request->getPost('instagram_user'),
            'facebook'           => $this->request->getPost('facebook'),
            'twitter'            => $this->request->getPost('twitter'),
            'pinterest'          => $this->request->getPost('pinterest'),
            'tripadvisor'        => $this->request->getPost('tripadvisor'),
            'youtube'            => $this->request->getPost('youtube'),
            'spotify'            => $this->request->getPost('spotify'),
            'linkedin'           => $this->request->getPost('linkedin'),
            'blocks'             => $this->request->getPost('blocks'),
            'selected_languages' => $this->request->getPost('selected_languages'),
            'admin_menu'         => $this->request->getPost('admin_menu'),
            'home_page_id'       => $this->request->getPost('home_page_id'),
            'error_page_id'     => $this->request->getPost('error_page_id'),
            'mail_from_mail'     => $this->request->getPost('mail_from_mail'),
            'mail_from_name'     => $this->request->getPost('mail_from_name'),
            'mailchimp_api_key'  => $this->request->getPost('mailchimp_api_key'),
            'mailchimp_list_id'  => $this->request->getPost('mailchimp_list_id'),
            'max_upload_size'    => $this->request->getPost('max_upload_size'),
            'og_site_name'       => $this->request->getPost('og_site_name'),
            'og_image'           => $this->request->getPost('og_image'),
            'thumbn_size_high'   => $this->request->getPost('thumbn_size_high') ?? 1200,
            'thumbn_size_medium' => $this->request->getPost('thumbn_size_medium') ?? 1024,
            'thumbn_size_low'    => $this->request->getPost('thumbn_size_low') ?? 575,
			'thumbn_size_lowest' => $this->request->getPost('thumbn_size_lowest') ?? 250,
            'auto_shortpixel'    => $this->request->getPost('auto_shortpixel'),
            'favicon'            => $this->saveImage('favicon'),
        ]);

        \Config\Services::session()->setFlashdata('message', '<div class="alert alert-info">Preferencias guardadas</div>' );
        return redirect()->to('admin/preferences?domain='. $this->data['domain']);
    }
    private function saveImage($field_name)
    {
        $theme_path =  'assets/themes/'. $this->data['domain'] .'/';
        $config['upload_path'] = $theme_path .'img/preferences/';
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);

        $config['max_size'] = $this->selected_domain_preferences->max_upload_size;
        $config['overwrite'] = false;
        $config['allowed_types'] = 'pdf|gif|jpg|png|svg|jpeg|ico';

        $r_file = $this->request->getFile($field_name);
        if ($r_file->getName()) {
            copy ($r_file->getPathName(), $config['upload_path'].'/'.$r_file->getName());
            return $config['upload_path'] . $r_file->getName();
        } elseif ($this->request->getPost($field_name . "_hidden") == "xDELETEx") { #Si se pide borrar se suprime
            return '';
        } elseif ($this->request->getPost($field_name . "_hidden")) { #Si no se envía mantenemos el valor anterior
            return $this->request->getPost($field_name . "_hidden");
        }

        return '';
    }

}
