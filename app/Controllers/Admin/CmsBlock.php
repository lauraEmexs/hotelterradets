<?php
namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\Cms_block_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CmsBlock extends BaseAdminController
{
    public $data;

    var $table_template = array (
        'table_open'          => '<table class="table table-striped table-bordered table-hover datatable" id="">',
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

        $this->cms_block_model = new Cms_block_model ();

        $this->data['languages'] = $this->admin_language_model->get_all(array('actived' => '1'));

        $this->data['menu'] = 'cmsBlock';
        $this->data['menu2'] = '';
        $this->data['page_title'] = 'Cms Bloques';

        if (isset($_GET['domain']) && in_array($_GET['domain'], config('App')->allowedDomains, TRUE)) {
            $this->data['domain'] = $_GET['domain'];
            $this->data['selected_domain'] = $_GET['domain'];
        } else {
            $this->data['domain'] = config('App')->defaultDomain;
            $this->data['selected_domain'] = config('App')->defaultDomain;
        }

        $this->selected_domain_preferences = $this->admin_general_model->get_domain_preferences($this->data['selected_domain']);
        $this->data['admin_menu_items'] = $this->admin_general_model->get_admin_menu_items();

        $this->cms_block_model->domain = $this->data['domain'];
        $this->cms_block_model->selected_domain = $this->data['selected_domain'];
        $this->cms_block_model->selected_domain_preferences = $this->selected_domain_preferences;
        $this->cms_block_model->request = $this->request;
    }

    public function static_blocks()
    {
        $this->data['menu2'] = 'static_blocks';
        $this->data['slug'] = 'static_blocks';
        $this->data['page_title'] = 'Bloques estáticos';
        $this->data['title_section'] = 'Bloques estáticos';

        $this->table->setTemplate($this->table_template);
        $flags = '';
        foreach ($this->data['languages'] as $language) {
            $flags .= '<img src="' . base_url('assets/themes/adminlte/flags/blank.png') . '" style="margin-left:5px" class="flag flag-' . (($language->id == 'en') ? 'us' : $language->id) . '" alt="' . $language->name . '" />';
        }
        $this->table->setHeading([['data' => 'Block class', 'class' => 'col-md-1'], 'Titulo', 'Descripción', ['data' => $flags, 'class' => 'col-md-1'], ['data' => 'Acciones', 'class' => 'col-md-1']]);

        //$blocks = $this->cms_block_model->get_block_by_ids(explode(',', $this->selected_domain_preferences->blocks));
        $blocks = $this->data['used_blocks_pages'] = $this->admin_general_model->get_preferences_blocks($this->data['selected_domain'], 'dynamic_pages', true);

        foreach ($blocks as $block) {
            $block_params = json_decode($block->params);
            if (!isset($block_params->static) || !$block_params->static) {
                continue;
            }
            $status = '';
            $translations = [];
            $block_translations = $this->cms_block_model->get_static_block_translations_by_id($block->id, $this->data['selected_domain']);
            foreach ($block_translations as $block_translation) {
                $status = $block_translation->status;
                $translations[$block_translation->language] = $block_translation->id;
            }

            $translation_actions = '';
            foreach ($this->data['languages'] as $language) {
                $class = (isset($translations[$language->id])) ? '<i class="fa fa-pencil fa-fw"></i>' : '<i class="fa fa-plus fa-fw"></i>';
                $translation_actions .= anchor('admin/cmsBlock/edit_static_block/'. $block->id .'/'. $language->id .'?domain='. $this->data['domain'], $class, '') . ' ';
            }

            $status = ($status == 'ACTIVED') ?
                anchor('admin/cmsBlock/change_state_static_block/' . $block->id .'/pause?domain='. $this->data['domain'], '<input type="checkbox" checked
            data-toggle="toggle" data-size="mini" class="pause-toggle" data-on="<i class=\'fa fa-eye\'></i>" data-off="<i class=\'fa fa-eye-slash\'></i>" 
            data-onstyle="success" >', ['class' => '', 'onclick' => "return confirm('¿Quieres pausar este bloque?')"])
                :
                anchor('admin/cmsBlock/change_state_static_block/' . $block->id .'/activate?domain='. $this->data['domain'], '<input type="checkbox" 
            data-toggle="toggle" data-size="mini" class="pause-toggle" data-on="<i class=\'fa fa-eye\'></i>" data-off="<i class=\'fa fa-eye-slash\'></i>" 
            data-onstyle="success" >', ['class' => '', 'onclick' => "return confirm('¿Quieres activar este bloque?')"]);
            if (empty($translations)) {
                $status = '';
            }

            $this->table->addRow(
                $block->class ,
                $block_params->title,
                $block_params->description,
                '<div style="white-space:nowrap;">'. $translation_actions .'</div>',
                $status
            );
        }

        $this->data['table'] = $this->table;

        return view('admin/cms-block/static_blocks', $this->data);
    }

    public function edit_static_block($cms_block_id, $language)
    {
        $block = $this->cms_block_model->get_block_by_id($cms_block_id);
        $block_model = $block->class;
        $this->data['menu2'] = 'static_blocks';
        $this->data['slug'] = 'static_blocks';
        $this->data['language'] = $language;
        $this->data['language_name'] = $this->admin_language_model->get_idioma($language);
        $this->data['language_original'] = $this->admin_language_model->get_default_language_id();
        $this->data['language_original_name'] = $this->admin_language_model->get_default_language();
        $this->data['block_model'] = $block_model;
        $this->data['cms_block_id'] = $cms_block_id;

        $this->$block_model = $this->cms_block_model->load_model ($block_model);

        $this->data['block_form'] = $this->$block_model->admin_form_static($cms_block_id, $language);
        $block_params = json_decode($block->params);

        $this->data['title_section'] = 'Editar bloque estático '. $block_params->title;

        return view('admin/cms-block/edit_static_block', $this->data);
    }

    public function save_static_block($cms_block_id)
    {
        $block = $this->cms_block_model->get_block_by_id($cms_block_id);
        $block_model = $block->class;
        $this->$block_model = $this->cms_block_model->load_model ($block_model);
        $form_data = [];
        foreach ($this->request->getPost() as $name => $value) {
            if (stristr($name, $block_model) !== FALSE) {
                $form_data[str_replace($block_model .'_', '', $name)] = $value;
            }
        }

        $result = $this->$block_model->set_static_block([
            'class'        => $block_model,
            'cms_block_id' => $cms_block_id,
            'language'     => $this->request->getPost('language'),
            'domain'       => $this->request->getPost('domain'),
            'form_data'    => $form_data,
        ]);

        \Config\Services::session()->setFlashdata('message', ($result)
            ? '<div class="alert alert-info">Entrada insertada/actualizada correctamente</div>'
            : '<div class="alert alert-warning">Se ha producido un error, inténtalo nuevamente.</div>');

        return redirect()->to(base_url('admin/cmsBlock/static_blocks').'?domain='. $this->data['domain']);
    }

    public function change_state_static_block($cms_block_id, $action)
    {
        switch ($action) {
            case 'activate' :
                $status = 'ACTIVED';
                break;
            case 'pause' :
                $status = 'PAUSED';
                break;
        }

        $this->db->update('cms_static_block', ['status' => $status], ['cms_block_id' => $cms_block_id]);

        return redirect()->to(base_url('admin/cmsBlock/static_blocks').'?domain='. $this->data['domain']);
    }

    /*
     * All registered blocks, main configuring
     * */
    public function cms_blocks()
    {
        $this->data['menu2'] = 'cms_blocks';
        $this->data['slug'] = 'cms_blocks';
        $this->data['page_title'] = 'CMS Bloques';
        $this->data['title_section'] = 'CMS Bloques';

        $this->table->setTemplate($this->table_template);
        $this->table->setHeading('Block class', 'Título', 'Descripción', 'Acciones');

        $blocks = $this->cms_block_model->get_cms_blocks ();

        foreach ($blocks as $block) {
            $block_params = json_decode($block->params);

            /*$status = '';
            $status = ($status == 'ACTIVED') ?
                anchor('admin/cmsBlock/change_state_general_block/' . $block->class .'/pause', '<i class="fa fa-pause fa-fw"></i>', ['class' => 'btn bg-black btn-flat', 'onclick' => "return confirm('¿Quieres pausar este bloque?')"])
                :
                anchor('admin/cmsBlock/change_state_general_block/' . $block->class .'/activate', '<i class="fa fa-play fa-fw"></i>', ['class' => 'btn bg-black btn-flat', 'onclick' => "return confirm('¿Quieres activar este bloque?')"]);
            */
            $edit = anchor('admin/cmsBlock/edit_cms_block/' . $block->id, '<i class="fa fa-pencil fa-fw"></i>', ['class' => '']);

            $this->table->addRow(
                $block->class ,
                $block_params->title,
                $block_params->description,
                $edit// .' '.$status
            );
        }

        $this->data['table'] = $this->table;

        return view('admin/cms-block/cms_blocks', $this->data);
    }

    public function edit_cms_block($id)
    {
        $block = $this->cms_block_model->get_block_by_id($id);
        $block_params = json_decode($block->params);

        $this->data['id'] = $id;
        $this->data['menu2'] = 'cms_blocks';
        $this->data['slug'] = 'cms_blocks';
        $this->data['title_section'] = 'Cms bloque: <strong>'. $block_params->title .'</strong>';
        $this->data['block'] = $block;
        $this->data['block_params'] = $block_params;
        $this->data['fields'] = $block->fields;

        return view('admin/cms-block/cms_block_form', $this->data);
    }

    public function create_cms_block()
    {
        $this->data['menu2'] = 'cms_blocks';
        $this->data['slug'] = 'cms_blocks';
        $this->data['title_section'] = 'Nuevo Cms bloque</strong>';
        $this->data['id'] = 'new';
        $this->data['block_params'] = null;

        return view('admin/cms-block/cms_block_form', $this->data);
    }

    public function save_cms_block($id)
    {
        /* preview upload config */
        $theme_path =  'assets/themes/'. $this->data['domain'] .'/';
        $config['upload_path'] = $theme_path .'img/cms_block/';
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
        $config['allowed_types'] = 'pdf|gif|jpg|png|svg|jpeg';
        $config['max_size'] = $this->selected_domain_preferences->max_upload_size;
        $config['overwrite'] = true;

        $r_file = $this->request->getFile('preview');
        if ( $r_file->getName()) {
            copy ($r_file->getPathName(), $config['upload_path'].'/'.$r_file->getName());
            $preview_src = $config['upload_path'] . $r_file->getName();
        } elseif ($this->request->getPost("preview_hidden") == "xDELETEx") { #Si se pide borrar se suprime
            $preview_src = '';
        } else { #Si no se envía mantenemos el valor anterior
            $preview_src = $this->request->getPost("preview_hidden");
        }
        /* end preview*/

        if ($id == 'new') {
            //1. Check, class name must bu uniq
            $blocks = $this->cms_block_model->get_cms_blocks();
            foreach ($blocks as $row) {
                if (strtolower($row->class) == strtolower($this->request->getPost('classname'))){
                    $this->session->set_flashdata('message', '<div class="alert alert-warning">Se ha producido un error, inténtalo nuevamente 1.</div>');
                    return redirect()->to->to(base_url('admin/cmsBlock/cms_blocks'));
                }
            }
            //2. Create class file and view
            $result = $this->cms_block_model->save_new_block_class($this->request->getPost('classname'));
            if (!$result) {
                \Config\Services::session()->setFlashdata('message', '<div class="alert alert-warning">Se ha producido un error, inténtalo nuevamente 2.</div>');
                return redirect()->to(base_url('admin/cmsBlock/cms_blocks'));
            }
            //3. Save to
            $data = [
                'params' => json_encode($this->request->getPost('params')),
                'fields' => trim($this->request->getPost('fields')),
                'class' => ucfirst($this->request->getPost('classname')),
                'status' => 'ACTIVED',
            ];
            $result = $this->cms_block_model->insert_cms_block ($id, $data);
        } elseif(null !== $this->request->getPost('duplicate')) {
            $block = $this->cms_block_model->get_block_by_id ($id);
            $data = [
                'params' => json_encode($this->request->getPost('params')),
                'fields' => $block->fields,
                'class' => $block->class,
                'status' => 'ACTIVED',
            ];
            $result = $this->cms_block_model->insert_cms_block ($id, $data);
        } else {
            $params = $this->request->getPost('params');
            $params['preview'] = $preview_src;
            $data = [
                'params' => json_encode($params),
                'fields' => $this->request->getPost('fields')
            ];
            $result = $this->cms_block_model->update_cms_block ($id, $data);
        }

        \Config\Services::session()->setFlashdata('message', ($result)
            ? '<div class="alert alert-info">Entrada insertada/actualizada correctamente</div>'
            : '<div class="alert alert-warning">Se ha producido un error, inténtalo nuevamente.</div>');

        return redirect()->to(base_url('/admin/cmsBlock/cms_blocks'));
    }
}
