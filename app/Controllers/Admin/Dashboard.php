<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Dashboard extends BaseAdminController
{
    public $data;

    var $table_template = array (
        'table_open'          => '<table class="table table-striped table-bordered table-hover" id="last_changes">',
        'heading_row_start'   => '<tr>',
        'heading_row_end'     => '</tr>',
        'heading_cell_start'  => '<th>',
        'heading_cell_end'    => '</th>',

        'row_start'           => '<tr>',
        'row_end'             => '</tr>',
        'cell_start'          => '<td>',
        'cell_end'            => '</td>',

        'row_alt_start'       => '<tr>',
        'row_alt_end'         => '</tr>',
        'cell_alt_start'      => '<td>',
        'cell_alt_end'        => '</td>',

        'table_close'         => '</table>'
    );


    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        if (!\Config\Services::session()->get('userdata')) {
            header('Location: /admin/auth');
            die ();
        }

        // $this->load->library('table');
        $this->data['languages'] = $this->admin_language_model->get_all(array('actived'=>'1') );
        $this->data['selected_language'] = $this->admin_language_model->get_default_language_id();

        $this->data['menu'] = 'dashboard';
        $this->data['menu2'] = '';
        $this->data['slug'] = 'dashboard';
        $this->data['page_title'] = 'Dashboard';
        $this->data['title_section'] = 'Dashboard';
        $this->data['dynamic_elements'] = $this->admin_general_model->get_dynamic_elements();
        $this->data['admin_menu_items'] = $this->admin_general_model->get_admin_menu_items();
        $this->data['domain'] = $this->domain;
    }

    public function index()
    {
        $this->table->setTemplate($this->table_template);
        $this->table->setHeading(['Objeto', 'Título', 'Idioma', 'Dominio', 'Estado', 'Usuario', 'Cuando']);
        $this->data['last_edited_objects'] = $this->admin_general_model->get_last_edited_objects();
        foreach ($this->data['last_edited_objects'] as $last_edited_object) {
             $label_class = ($last_edited_object->status != 'ACTIVED') ? 'danger' : 'success';
             $this->table->addRow(
                 $last_edited_object->name,
                 anchor('admin/dynamic/dedit/' . $last_edited_object->slug . '/' . $last_edited_object->language_id . '/' . $last_edited_object->id .'?domain='. $last_edited_object->domain, $last_edited_object->text_title, '') . ' ',
                 $last_edited_object->language,
                 $last_edited_object->domain,
                 '<span class="label label-'. $label_class .'">'. ucfirst(strtolower($last_edited_object->status)) .'</span>',
                 $last_edited_object->username,
                 $last_edited_object->updated_at
             );
        }

        $this->data['table'] = $this->table;
        $this->data['site_preferences'] = $this->admin_general_model->get_preferences ();

        return view('admin/dashboard', $this->data);
    }

}
