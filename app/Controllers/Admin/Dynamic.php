<?php

namespace App\Controllers\Admin;

use App\Models\Cms_block_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;

class Dynamic extends BaseAdminController
{
    public $selected_domain;
    public $selected_domain_preferences;
    public $domain_url_params;
    public $data;

    var $table_template = array(
        'table_open' => '<table class="table table-striped table-bordered table-hover" id="">',
        'heading_row_start' => '<tr>',
        'heading_row_end' => '</tr>',
        'heading_cell_start' => '<th>',
        'heading_cell_end' => '</th>',

        'row_start' => '<tr>',
        'row_end' => '</tr>',
        'cell_start' => '<td>',
        'cell_end' => '</td>',

        'row_alt_start' => '<tr>',
        'row_alt_end' => '</tr>',
        'cell_alt_start' => '<td>',
        'cell_alt_end' => '</td>',

        'table_close' => '</table>'
    );


    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        if (!\Config\Services::session()->get('userdata')) {
            header('Location: /admin/auth');
            die ();
        }

        $this->data['menu'] = 'contenidos';
        $this->data['menu2'] = '';
        $this->data['slug'] = 'dashboard';
        $this->data['page_title'] = 'Dashboard';
        $this->data['languages'] = $this->admin_language_model->get_all(['actived' => '1']);
        $this->data['idioma_original'] = $this->admin_language_model->get_default_language_id();
        $this->data['idioma_original_name'] = $this->admin_language_model->get_default_language();
        $this->data['dynamic_elements'] = $this->admin_general_model->get_dynamic_elements();
        #http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html#.toolbar_Basic
        $toolbar = array(
            array('Source'),
            array('Bold', 'Italic', 'Underline', 'Strike'),
            array('Cut', 'Copy', 'Paste'),
            array('Undo', 'Redo'),
            array('Styles'),
        );
        $toolbar_script = array(
            array('Source')
        );

        //$toolbar = 'Full';
        $this->data['config_mini'] = array(
            'toolbar' => $toolbar,
            'filebrowserBrowseUrl' => base_url() . "assets/themes/sbadmin2/ckeditor/kcfinder/browse.php",
            'filebrowserImageBrowseUrl' => base_url() . "assets/themes/sbadmin2/ckeditor/kcfinder/browse.php?type=images",
            'filebrowserUploadUrl' => base_url() . "assets/themes/sbadmin2/ckeditor/kcfinder/upload.php?type=files",
            'filebrowserImageUploadUrl' => base_url() . "assets/themes/sbadmin2/ckeditor/kcfinder/upload.php?type=images",
            'enterMode' => 'CKEDITOR.ENTER_P', // p | div | br
            'shiftEnterMode' => 'CKEDITOR.ENTER_DIV',  // p | div | br
            'height' => 200,
            'widht' => 800,
        );
        $this->data['config_script'] = array(
            'toolbar' => $toolbar_script,
            'filebrowserBrowseUrl' => base_url() . "assets/themes/sbadmin2/ckeditor/kcfinder/browse.php",
            'filebrowserImageBrowseUrl' => base_url() . "assets/themes/sbadmin2/ckeditor/kcfinder/browse.php?type=images",
            'filebrowserUploadUrl' => base_url() . "assets/themes/sbadmin2/ckeditor/kcfinder/upload.php?type=files",
            'filebrowserImageUploadUrl' => base_url() . "assets/themes/sbadmin2/ckeditor/kcfinder/upload.php?type=images",
            'enterMode' => 'CKEDITOR.ENTER_P', // p | div | br
            'shiftEnterMode' => 'CKEDITOR.ENTER_DIV',  // p | div | br
            'startupMode ' => 'source',
            'height' => 200,
            'widht' => 800,
        );

        $this->domain_url_params = '';
        if ($this->data['logged_user']->group == 'domain') {
            if (in_array($this->session->userdata('logged_user')->domain, (array) $this->config->item('allowed_domains'), TRUE)) {
                $this->data['domain'] = $this->session->userdata('logged_user')->domain;
                $this->data['selected_domain'] = $this->session->userdata('logged_user')->domain;
            } else {
                return redirect()->to('admin/dashboard');
            }
        } elseif (isset($_GET['domain']) && in_array($_GET['domain'], (array) config('App')->allowedDomains, TRUE)) {
            $this->data['domain'] = $_GET['domain'];
            $this->data['selected_domain'] = $_GET['domain'];
            if (count(config('App')->allowedDomains) > 1) {
                $this->domain_url_params = '?domain='. $_GET['domain'];
            }
        } else {
            $this->data['domain'] = $this->domain;
            $this->data['selected_domain'] = $this->domain;
        }

        //Website preferences short variable
        $this->selected_domain_preferences = $this->admin_general_model->get_domain_preferences($this->data['selected_domain']);
        $this->data['admin_menu_items'] = $this->admin_general_model->get_admin_menu_items();
        $this->data['selected_domain_preferences'] = $this->selected_domain_preferences;
        $this->data['domain_url_params'] = $this->domain_url_params;

        $this->cms_block_model = new Cms_block_model ();

        $this->cms_block_model->domain = $this->data['domain'];
        $this->cms_block_model->selected_domain = $this->data['selected_domain'];
        $this->cms_block_model->selected_domain_preferences = $this->selected_domain_preferences;
        $this->cms_block_model->request = $this->request;

    }

    public function index()
    {
        return redirect()->to('admin/dashboard');
    }

    /*
     * @desc dlist Carga el contenido de base de datos de todo el contenido para listarlo.
     * @param $slug identificador de la tabla de datos, por ejemplo, dynamic_xxxxx => slug=xxxxx
     * @param $id identificador de la entrada, en el caso de ser 0 se interpretará como una nueva y se devolverán datos vacios.
     * @note se mostrará en el idioma default de base de datos.
     */
    public function dlist($slug)
    {
        $this->data['menu2'] = $slug;
        $this->data['slug'] = $slug;

        foreach ($this->data['dynamic_elements'] as $element) {
            if ($element['slug'] == $slug) {
                $this->data['title_section'] = $element['name'];
            }
        }

        #Elementos estructurales
        $data_struct = $this->admin_general_model->get_dynamic_table_struct($slug);
        $first_element_text = $this->admin_general_model->get_first_element($data_struct);
        $this->data['ordenar'] = $this->admin_general_model->get_ordenar($data_struct);

        $this->data['filter_info'] = '';
        if (isset($_GET['filter'])) {
            foreach ($data_struct as $item) {
                if (array_key_exists($item['name'], $_GET['filter']) && $_GET['filter'][$item['name']] != '') {
                    $this->data['filter_info'] .= '| <span>Filtrado por campo: <b>'. $item['label'] .'</b>, con valor: <b>'. $_GET['filter'][$item['name']] .'</b></span>';
                }
            }
        }

        $this->data['metas_xls'] = 0;
        foreach ($data_struct as $item) {
            if (in_array($item['name'], ['slug', 'text_page_title', 'order_images', 'orden_real'])) {
                $this->data['metas_xls']++;
            }
        }
        $this->data['metas_xls'] = $this->data['metas_xls'] == 2;

        #Elementos del contenido:
        $data = $this->admin_general_model->get_dynamic_table_data([
            'slug' => $slug,
            'language' => $this->data['idioma_original'],
            'order' => 'position',
            'domain' => $this->data['domain'],
            'filter' => (isset($_GET['filter'])) ? $_GET['filter'] : false,
        ]);

        if(isset($_GET['q']) && !empty($_GET['q'])) $data = $this->search_filter ($data);

        $this->data['count'] = count($data);
        $this->data['page'] = 1;
        if(isset($_GET['page'])) {
            $this->data['page'] = $_GET['page'];
        }
        $this->data['perpage'] = 10;
        if(isset($_GET['perpage'])) {
            $this->data['perpage'] = $_GET['perpage'];
        }
        $this->data['perpages'] = [10,25,50,100];

        $data = array_slice ( $data, ($this->data['page']-1)*$this->data['perpage'], $this->data['perpage'] );

        $this->table->setTemplate($this->table_template);
        $banderas = '';
        foreach ($this->data['languages'] as $language) {
            $banderas .= '<img src="' . base_url('assets/themes/adminlte/flags/blank.png') . '" style="margin-left:5px" class="flag flag-' . (($language->id == 'en') ? 'us' : $language->id) . '" alt="' . $language->name . '" />';
        }

        $heading = [['data' => 'id', 'class' => 'col-md-1'], 'Título', ['data' => $banderas, 'class' => 'col-md-1'], ['data' => 'Acciones', 'class' => 'col-md-1', 'style'=>'width: 110px;']];
        //we give permission to translate gallery titles
        if (false && $slug === "gallery") {
            $heading = [['data' => 'id', 'class' => 'col-md-1'], 'Título', ['data' => 'Editar', 'class' => 'col-md-1'], ['data' => 'Acciones', 'class' => 'col-md-1']];
        }

        if ($this->data['logged_user']->group == 'admin') {
            $heading[] = ['data' => 'Última edición', 'class' => 'col-md-2'];
            $users = $this->admin_general_model->get_users();
            $users = indexar_array($users, 'id');
        }

        $this->table->setHeading($heading);

        foreach ($data as $item) {
            $row = [];
            $traducciones = null;
            $clonar = null;
            foreach ($this->data['languages'] as $language) {
                $class = ($this->admin_general_model->exist_element($slug, $item->id, $language->id)) ? '<i class="fa fa-pencil fa-fw"></i>' : '<i class="fa fa-plus fa-fw"></i>';
                $traducciones .= anchor('admin/dynamic/dedit/' . $slug . '/' . $language->id . '/' . $item->id .''. $this->domain_url_params, $class, '') . ' ';
            }

            //Crear parametro copy para saber que sera un clon
            if ($slug == 'pages') {
                foreach ($this->data['languages'] as $language) {
                    if ($language->id == $this->data['idioma_original']) {
                        $class = ($this->admin_general_model->exist_element($slug, $item->id, $language->id)) ? '<i class="fa fa-copy fa-fw"></i>' : '<i class="fa fa-plus fa-fw"></i>';
                        $clonar .= anchor('admin/dynamic/dedit/' . $slug . '/' . $language->id . '/' . $item->id . '' . (!empty($this->domain_url_params) ? $this->domain_url_params . '&copy=1' : '?copy=1'), $class, '') . ' ';
                    }
                }
            }

            $status = '';
            $ids_not_for_pause = [];
            if (isset($item->status) && !($slug == 'pages' && in_array($item->id, $ids_not_for_pause))) {
                $status = ($item->status == 'ACTIVED')
                    ? anchor('admin/dynamic/dpause/' . $slug . '/' . $item->id .''. $this->domain_url_params,
                        '<input type="checkbox" checked data-size="mini" class="pause-toggle" data-on="<i class=\'fa fa-eye\'></i>" data-off="<i class=\'fa fa-eye-slash\'></i>" 
                                    data-onstyle="success" data-confirm-text="¿Quieres pausar esta entrada?" data-style="toggle-switch">')
                    : anchor('admin/dynamic/dresume/' . $slug . '/' . $item->id .''. $this->domain_url_params,
                        '<input type="checkbox" data-size="mini" class="pause-toggle" data-on="<i class=\'fa fa-eye\'></i>" data-off="<i class=\'fa fa-eye-slash\'></i>" 
                                    data-onstyle="success"  data-confirm-text="¿Quieres activar esta entrada?" data-style="toggle-switch">');
            }

            //we give permission to translate gallery titles
            if (false && $slug == "gallery") {
                $row = [
                    $item->id,
                    ($first_element_text) ? substr(trim(strip_tags($item->{$first_element_text})), 0, 155) : $slug,
                    "<div style='white-space:nowrap;'>" .
                    anchor('admin/dynamic/dedit/' . $slug . '/'. $this->data['idioma_original'] .'/' . $item->id.''. $this->domain_url_params, '<i class="fa fa-pencil fa-fw"></i>', '') . ' ' .
                    "</div>",
                    anchor('admin/dynamic/ddelete/' . $slug . '/' . $item->id.'?domain='. $this->data['domain'], '<i class="fa fa-trash-o fa-fw"></i>', array('class' => 'dlist-delete-button', 'onclick' => "return confirm('¿Quieres eliminar esta entrada?')")) . " " . $status
                ];
            } else {
                if ($slug == "pages") {
                    $indent = substr_count($item->path, '.');
                    $title = str_repeat("- ", $indent) . ' '. $item->text_title;
                } else {
                    $title = ($first_element_text) ? substr(trim(strip_tags($item->text_title)), 0, 155) : $slug;
                }
                $delete_button = anchor('admin/dynamic/ddelete/' . $slug . '/' . $item->id .''. $this->domain_url_params, '<i class="fa fa-trash-o fa-fw"></i>', array('class' => 'dlist-delete-button', 'onclick' => "return confirm('¿Quieres eliminar esta entrada?')"));
                if ($slug == 'pages' && $item->id == $this->selected_domain_preferences->home_page_id || ($slug == 'pages' && $item->id == $this->selected_domain_preferences->error_page_id)){
                    $delete_button = '';
                    $status = '';
                    $clonar = '';
                }
                $row = [
                    $item->id,
                    $title,
                    "<div style='white-space:nowrap;'>" .
                    $traducciones .
                    "</div>",
                    $delete_button . " " . $status . " " . $clonar
                ];
            }

            if ($this->data['logged_user']->group == 'admin') {
                $str = (isset($users[$item->updater_id])) ? ucfirst($users[$item->updater_id]->username) : '';
                $str .= ' '. $item->updated_at;
                $row[] = ['data' => $str];
            }

            $this->table->addRow ($row);
        }

        $this->data['table'] = $this->table;

        return view('admin/dlist', $this->data);
    }

    /*
     * @desc dedit Carga el contenido de base de datos de una entrada para poder editarlo, en el caso de id=0 será una nueva entrada y el contenido estará vacio.
     * @param $slug identificador de la tabla de datos, por ejemplo, dynamic_xxxxx => slug=xxxxx
     * @param $idioma identificador del idioma
     * @param $id identificador de la entrada, en el caso de ser 0 se interpretará como una nueva y se devolverán datos vacios.
     */
    public function dedit($slug, $idioma, $id)
    {
        $this->data['menu2'] = $slug;
        $this->data['slug'] = $slug;
        $this->data['id'] = $id;

        $this->data['idioma'] = $idioma;
        $this->data['idioma_name'] = $this->admin_language_model->get_idioma($idioma);

        /*
         * CMS Blocks
         * */

        //$this->data['new_blocks'] = $this->cms_block_model->get_available_blocks();
        $this->data['new_blocks'] = $this->cms_block_model->get_available_blocks_by_preferences($this->data['domain'], 'dynamic_'. $slug);
        $this->data['blocks'] = $this->cms_block_model->get_assigned_blocks($id, 'dynamic_'. $slug, $idioma, $this->data['domain']);
        /* If we are creating new translation, we need to load original content */
        if (empty($this->data['blocks']) && $idioma != $this->data['idioma_original']) {
            $this->data['blocks'] = $this->cms_block_model->get_assigned_blocks($id, 'dynamic_'. $slug, $this->data['idioma_original'], $this->data['domain']);
            $this->data['original_language_blocks'] = true;
        }
		if (!$id) {
			$this->data['blocks'] = $this->cms_block_model->get_required_blocks();
		}
        /*
         * End CMS Blocks
         * */

        $this->data['data_struct'] = $this->admin_general_model->get_dynamic_table_struct($slug, $this->data['idioma_original']);

        $this->data['data'] = $this->admin_general_model->get_dynamic_table_data([
            'slug' => $slug,
            'language' => $idioma,
            'id' => $id,
            'domain' => $this->data['domain'],
        ]);
        #Sino se ha traducido cojo el original.
        if ($idioma != $this->data['idioma_original'] && empty($this->data['data'])) {
            $this->data['data'] = $this->admin_general_model->get_dynamic_table_data([
                'slug' => $slug,
                'language' => $this->data['idioma_original'],
                'id' => $id,
                'domain' => $this->data['domain'],
            ]);

            /* We need to clear some old variables when translating */
            $this->data['data']->slug = '';
            $this->data['data']->uri_string = '';
        }

        /* To choose parent page, we need to get all the list */
        /* Not used at the moment, but maybe another day
        if ($slug == 'pages') {
            $pages = $this->admin_general_model->get_dynamic_table_data([
                'slug' => $slug,
                'language' => $this->data['idioma_original'],
                'domain' => $this->data['domain'],
                'order' => 'position'
            ]);
            $this->data['all_pages'] = ['0' => '-'];
            foreach ($pages as $page) {
                if ($page->id == $id || $page->slug == '' || $page->parent_id == $id) {
                    continue;
                }
                $indent = substr_count($page->path, '.');
                $this->data['all_pages'][$page->id] = str_repeat("- ", $indent) . ' '. $page->text_title;
            }
        }
        */
        $has_slug = false;
        foreach ($this->data['data_struct'] as $struct_item){
            $this->data['objects'][$struct_item['name']] = $struct_item;
            if ($struct_item['name'] == 'slug') {
                $has_slug = true;
            }
        }

        if ($has_slug && $slug != 'pages' && $id != 0) {
            $this->data['preview_link'] = $idioma . '/'. $this->admin_general_model->get_dynamic_container_url(
                    $slug,
                    $this->data['data'],
                    $idioma,
                    $this->data['domain']
                ) . $this->data['data']->slug;
        } else {
            $this->data['preview_link'] = false;
        }

        foreach ($this->data['data_struct'] as $struct_item){
            $this->data['objects'][$struct_item['name']] = $struct_item;
        }

        foreach ($this->data['dynamic_elements'] as $element)
            if ($element['slug'] == $slug)
                $this->data['title_section'] = $element['name'];

        //$galleries = indexar_array($this->user_general_model->get_dynamic_table_data('gallery', $idioma_original, array(), 'id ASC'), 'id');
        $this->data['galleries'] = $this->admin_general_model->get_dynamic_table_data([
            'slug' => 'gallery',
            'language' => $this->data['idioma_original'],
            'domain' => $this->data['domain'],
        ]);
        $this->data['galleries'] = indexar_array($this->data['galleries'], 'id');
        foreach ($this->data['galleries'] as $idg => $gal) {
            $this->data['content_galleries'][$idg] = array('title' => $gal->text_title, 'img' => base_url());// . $gal->imagethn_principal);
        }

        $this->data['galleries_content'][''] = '-';
        foreach ($this->data['galleries'] as $key => $value) {
            $this->data['galleries_content'][$value->id] = $value->text_title;
        }

        /* Default values */
        if ($id == 0) {
            $this->data['objects']['text_meta_robots']['value'] = 'index, follow';
        }

        if ($slug == 'gallery') {
            $images = $this->admin_general_model->get_images_by_galleryid_domain ($id, $this->data['domain']);

            $gallery_images = [];
            foreach ($images as $image) {
                $gallery_images[$image->id][$image->language] = $image;
                if (!$image->file_size) {
                    $this->admin_general_model->fix_image_paraments($image->url, $image->id, $this->data['domain'], $id);
                    $need_to_reload = true;
                }
            }
            if (isset($need_to_reload)) {
                return redirect()->to('admin/dynamic/dedit/'. $slug .'/'. $this->data['idioma_original'] .'/'. $id .'?domain='. $this->data['domain']);
            }

            $this->data['gallery_images'] = $gallery_images;

            /* Temporary patch */
            if ($id != 0 && $this->data['data']->hidden_folder_path == '') {
                $image = current($this->data['gallery_images']);
                if (is_object($image)) {
                    $url_parts = explode('/', $image[$this->data['idioma_original']]->url);
                    $upload_path = array_slice($url_parts, 0, count($url_parts) -2);
                    $this->data['data']->hidden_folder_path = implode('/', $upload_path);
                } else {
                    $this->data['data']->hidden_folder_path = '';
                }
            }
        }
        //Igualar ID a 0 para realizar el duplicado
        if (isset($_GET['copy'])) $this->data['data']->id = "0";

        $this->data['dynamic_pages_tables'] = $this->admin_general_model->get_dynamic_pages_tables();

        if (file_exists(__DIR__ .'/../../Views/admin/dedit_'. $slug .'.php')) {
            return view('admin/dedit_'. $slug, $this->data);
        } else {
            return view('admin/dedit_universal', $this->data);
        }

    }

    /*
     * @desc dupdate Actualiza, inserta entradas de una sección y sus traducciones. Cada tabla se calcula a partir del slug y cada tabla tiene una estructura diferente
     * @param $slug identificador de la tabla de datos, por ejemplo, dynamic_xxxxx => slug=xxxxx
     * @param $id identificador de la entrada, en el caso de ser 0 se interpretará como una nueva y se calcula es siguiente id libre en base de datos.
     * @note Si no se envía el language se cogerá el marcado en base de datos como "default".
     */
    /**
     * @param $slug
     */
    public function dupdate($slug)
    {
        // $this->load->library('image_lib');

        $id = $this->request->getPost('id');

        /* Delete translation */
        if (null !== $this->request->getPost('delete_translation')) {
            $this->admin_general_model->delete_dynamic_table_data_lang($slug, $id, $this->request->getPost('language'), $this->data['domain']);
            return redirect()->to('admin/dynamic/dlist/' . $slug .''. $this->domain_url_params);
        }

        $data_struct = $this->admin_general_model->get_dynamic_table_struct($slug);
        $data_original = $this->admin_general_model->get_dynamic_table_data([
            'slug' => $slug,
            'language' => $this->admin_language_model->get_default_language_id(),
            'id' => $id,
            'domain' => $this->data['domain'],
        ]);

        $theme_path =  'assets/themes/'. $this->data['domain'] .'/';
        $config['upload_path'] = $theme_path .'img/'. $slug .'/';
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);

        $config['max_size'] = $this->selected_domain_preferences->max_upload_size;
        $config['overwrite'] = false;
        // $this->load->library('upload', $config);

        foreach ($data_struct as $row) {
            if ($row['type'] == 'image') {
                $config['allowed_types'] = 'pdf|gif|jpg|png|svg|jpeg';
                $r_file = $this->request->getFile($row['name']);
                if ($r_file && $r_file->getName()) {
                    $archivo = time().'_'.$r_file->getName();
                    copy ($r_file->getPathName(), $config['upload_path'].$archivo);
                    $data[$row['name']] = $config['upload_path'].$archivo;

                    // Compress image
                    if ($this->selected_domain_preferences->auto_shortpixel == 1) {
                        $this->admin_general_model->compress_image($data[$row['name']]);
                    }
                    // Generate thumbnails
                    $this->admin_general_model->generate_thumbnails($data[$row['name']], $this->selected_domain_preferences);
                    // Generate webp
                    if ($this->selected_domain_preferences->generate_webp_on_upload == 1) {
                        $path_parts = pathinfo($data[$row['name']]);
                        foreach (['tlow', 'tmedium', 'thigh', 'tlowest'] as $size) {
                            $new_file_name = str_replace(
                                $path_parts['filename'] . '.' . $path_parts['extension'],
                                $path_parts['filename'] . '.' . $size . '.' . $path_parts['extension'],
                                $data[$row['name']]
                            );
                            if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($new_file_name, 'webp'))) {
                                $this->admin_general_model->generate_webp($new_file_name);
                            }
                        }
                        $this->admin_general_model->generate_webp($data[$row['name']]);
                    }
                } elseif ($this->request->getPost($row['name'] . "_hidden") == "xDELETEx") { #Si se pide borrar se suprime
                    // Here we about to use function to delete file...but same file can be used in another translations, so...
                    // $this->admin_general_model->delete_image_file_by_src($data_original->{$row['name']});
                    $data[$row['name']] = '';
                } elseif ($this->request->getPost($row['name'] . "_hidden")) { #Si no se envía mantenemos el valor anterior
                    $data[$row['name']] = $this->request->getPost($row['name'] . "_hidden");
                } else { #Si no se envía y es entrada nueva mantemos el valor de la entrada original.
                    $data[$row['name']] = (!empty($data_original) && is_object($data_original)) ? $data_original->{$row['name']} : '';
                }
            } elseif ($row['type'] == 'document') {
                //$config['upload_path'] = './assets/docs/' . $slug . '/';
                $config['upload_path'] = $theme_path .'docs/' . $slug . '/';
                if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
                $config['allowed_types'] = 'pdf|doc|xls|ppt|docx|xlsx|mp4';
                $config['overwrite'] = true;
                $r_file = $this->request->getFile($row['name']);
                if ($r_file && $r_file->getName()) {
                    $archivo = time().'_'.$r_file->getName();
                    copy ($r_file->getPathName(), $config['upload_path'].'/'.$archivo);
                    $data[$row['name']] = $config['upload_path'] . $archivo;
                } elseif ($this->request->getPost($row['name'] . "_delete") == "xDELETEx") { #Si se pide borrar se suprime
                    $data[$row['name']] = '';
                } elseif ($this->request->getPost($row['name'] . "_hidden")) { #Si no se envía mantenemos el valor anterior
                    $data[$row['name']] = $this->request->getPost($row['name'] . "_hidden");
                } else { #Si no se envía y es entrada nueva mantemos el valor de la entrada original.
                    $data[$row['name']] = (!empty($data_original) && is_object($data_original)) ? $data_original->{$row['name']} : '';
                }
            } elseif ($row['name'] == 'slug') {
                $title = ($this->request->getPost('text_title_menu')) ? $this->request->getPost('text_title_menu') : $this->request->getPost('text_title');
                $data['slug'] = (!$this->request->getPost('slug') && $this->request->getPost('type') != 'homepage') ? url_semantica($title) : url_semantica($this->request->getPost('slug'));
            } elseif ($row['type'] == 'multiselect' && $row['name'] != 'multiple_gallery') {
                if (!is_array($this->request->getPost($row['name']))) {
                    $data[$row['name']] = "";
                }else{
                    $data[$row['name']] = implode(",", $this->request->getPost($row['name']));
                }
            } elseif ($row['type'] != 'imagethn') {
                // OTHERS
                $data[$row['name']] = ($this->request->getPost($row['name']) == '') ? '' : $this->request->getPost($row['name']);
            }
        }

        $data['updater_id'] = $this->data['logged_user']->id;
        $data['updated_at'] = date('Y-m-d H:i:s', time());

        /* Special logic for pages */
        if ($slug == 'pages') {
            if (!$data['id']) {
                /* If we add new page */
                $result = $this->admin_general_model->get_page_positions(
                    $data['id'],
                    0,
                    $this->data['selected_domain'],
                    $this->data['idioma_original'],
                    $data['slug']
                );
                $data['position']   = $result['position'];
                $data['path']       = $result['path'];
                $data['uri_string'] = $result['uri_string'];
            } elseif ($data_original->slug != $data['slug']) {
                /* If we changed slug we will look, if page have parent, we change last uri_string part */
                $parent_uri_string = implode('/', array_slice(explode('/', $data_original->uri_string), 0, count(explode('/', $data_original->uri_string))-1));
                $data['uri_string'] = ($data['parent_id']) ? $parent_uri_string .'/'. $data['slug'] : $data['slug'];
            } else {
                $data['uri_string'] = isset($data_original->uri_string) ? $data_original->uri_string : '';
            }

            /* If we update existing page, we need to update every childs uri_string, only for this language */
            if (isset($data['id']) && $data['id']) {
                $this->admin_general_model->update_page_childs($data['id'], $this->data['selected_domain'], $data['language'], $data['uri_string'], $data['path']);
            }
        }

        $id = $this->admin_general_model->update_dynamic_table_data($slug, $data);
        $data['id'] = $id;


        if ($slug == "gallery" && $this->data['idioma_original'] == $this->request->getPost('language')) {
            $theme_path =  'assets/themes/'. $this->data['domain'] .'/';
            $gallery_folder = $theme_path .'img/'. $slug .'/'. $this->string_check_and_replace(trim($this->request->getPost('text_title')));

            $config['allowed_types'] = 'pdf|gif|jpg|png|svg|jpeg';
            $config['max_size']     = $this->selected_domain_preferences->max_upload_size;
            $dynamic_gallery_id = $id;

            if ($this->request->getPost('hidden_folder_path') != '' && $this->request->getPost('hidden_folder_path') != $gallery_folder) {
                $this->admin_general_model->rename_gallery_folder(
                    $this->request->getPost('hidden_folder_path'),
                    $gallery_folder,
                    $this->request->getPost('domain'),
                    $dynamic_gallery_id
                );
                $data['hidden_folder_path'] = $gallery_folder;
                $this->admin_general_model->update_dynamic_table_data($slug, $data);
            }

            $fileData = [];
            if (!empty($_FILES['image']['name'])) {
				
				$files = $this->request->getFiles();
				$files_new_index = 0;
				
				if (isset ($files['multiple-images']) && count($files['multiple-images'])) {
					foreach ( $files['image'] as $file_index => $r_file ) {
						if (stristr($file_index, 'new') !== FALSE) {
							$file_index = str_replace ( 'new_','', $file_index )*1;
							if ($file_index > $files_new_index ) $files_new_index = $file_index;
						}
					}
					foreach ( $files['multiple-images'] as $file_index => $r_file ) {
						$files_new_index++;
						$files['image']['new_'.$files_new_index] = $r_file;
					}
					unset ($files['multiple-images']);
				}
					
                foreach ($files['image'] as $file_index => $r_file) {
                    if (!$r_file->getPath ()) {
                        continue;
                    }
                    /* if we changing file, we need to keep old name */
                    if (stristr($file_index, 'new') === FALSE) {
                        foreach ($this->data['languages'] as $language) {
						// Don't delete images
                        // $this->admin_general_model->delete_image_file($file_index, $language->id, $this->request->getPost('domain'), $dynamic_gallery_id);
                        }
                    }
					
                    /* Every image we duplicate by different language */
                    foreach ($this->data['languages'] as $language) {
                        $config['upload_path'] = $gallery_folder .'/'. $language->id .'/';
                        if (!is_dir($config['upload_path'])) {
                            mkdir($config['upload_path'], 0777, true);
                        }
                        /* Manual name from input, for every uploaded file */
                        if ($this->request->getPost('filename['. $file_index .']['. $language->id .']')) {
                            $config['file_name'] = $this->string_check_and_replace(trim($this->request->getPost('filename['. $file_index .']['. $language->id .']')));
                        } else {
                            unset($config['file_name']);
                        }

                        // $this->upload->initialize($config);

                        /* Upload file to server */
						copy ($r_file->getPathName(), $config['upload_path'].'/'.time().'_'.$r_file->getName());
						$fileData[$file_index][$language->id] = [];
						$fileData[$file_index][$language->id]['file_name'] = time().'_'.$r_file->getName();
						$fileData[$file_index][$language->id]['upload_path'] = $config['upload_path'];
						$fileData[$file_index][$language->id]['file_size'] = $r_file->getSize();
						$sizes = @getimagesize($config['upload_path'].'/'.time().'_'.$r_file->getName());
						$fileData[$file_index][$language->id]['image_width'] = $sizes[0];
						$fileData[$file_index][$language->id]['image_height'] = $sizes[1];
						/* Compress image */
						if ($this->selected_domain_preferences->auto_shortpixel == 1) {
							$this->admin_general_model->compress_image($fileData[$file_index][$language->id]['upload_path'] . $fileData[$file_index][$language->id]['file_name']);
						}
						/* Generate thumbnails */
						$this->admin_general_model->generate_thumbnails($fileData[$file_index][$language->id]['upload_path'] . $fileData[$file_index][$language->id]['file_name'], $this->selected_domain_preferences);
						/* Generate webp */
						if ($this->selected_domain_preferences->generate_webp_on_upload == 1) {
							$file_name = $fileData[$file_index][$language->id]['upload_path'] . $fileData[$file_index][$language->id]['file_name'];
							$path_parts = pathinfo($file_name);
							foreach (['tlow', 'tmedium', 'thigh', 'tlowest'] as $size) {
								$new_file_name = str_replace(
									$path_parts['filename'] . '.' . $path_parts['extension'],
									$path_parts['filename'] . '.' . $size . '.' . $path_parts['extension'],
									$file_name
								);
								if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($new_file_name, 'webp'))) {
									$this->admin_general_model->generate_webp($new_file_name);
								}
							}
							$this->admin_general_model->generate_webp($file_name);
						}
                    }
                }
            }
            /* First save\update uploaded files */
            foreach ($fileData as $file_index => $data) {
                $img_data['id'] = (stristr($file_index, 'new') === FALSE) ? $file_index : $this->admin_general_model->get_next_insert_id("dynamic_images");
                $img_data['dynamic_gallery_id'] = $dynamic_gallery_id;
                $img_data['domain'] = $this->request->getPost('domain');
                foreach ($data as $language_id => $datum) {
                    $img_data['language'] = $language_id;
                    $img_data['url'] = $datum['upload_path'] . $datum['file_name'];
                    $img_data['text_title'] = $this->request->getPost('title['. $file_index.']['. $language_id .']');
                    $img_data['text_alt'] = $this->request->getPost('alt['. $file_index.']['. $language_id .']');
                    $img_data['file_size'] = round($datum['file_size']);
                    $img_data['image_width'] = $datum['image_width'];
                    $img_data['image_height'] = $datum['image_height'];
                    $img_data['is_hide_seo'] = ($this->request->getPost('seo['. $file_index.']') !== null) ? 1 : 0;
                    $img_data['position'] = $this->request->getPost('img_position['. $file_index.']');

                    /* If it was not a new image record and we are changing image file we need to delete old */
                    if (stristr($file_index, 'new') === FALSE) {
                        $this->admin_general_model->update_image_record($img_data, $file_index, $language_id, $this->request->getPost('domain'), $dynamic_gallery_id);
                    } else {
                        $this->admin_general_model->set_image_record($img_data);
                    }
                }
            }

            /* Second update existing files */
            $old_images = $this->request->getPost('image') ? $this->request->getPost('image') : [];
            foreach ($old_images as $file_index => $value) {
                /* Skip new uploads */
                if (stristr($file_index, 'new') !== FALSE && isset($fileData[$file_index])) {
                    continue;
                }
                /* Image was marked for deleting */
                if ($value == 'xDELETEx') {
                    foreach ($this->data['languages'] as $language) {
						// Don't delete images
                        // $this->admin_general_model->delete_image_file($file_index, $language->id, $this->request->getPost('domain'), $dynamic_gallery_id);
                    }
                    $this->admin_general_model->delete_dynamic_table_data('images', $file_index, $this->request->getPost('domain'));
                }
				
				$db_id = (stristr($file_index, 'new') === FALSE) ? $file_index : $this->admin_general_model->get_next_insert_id("dynamic_images");

                /* Updating record by input values */
                foreach ($this->data['languages'] as $language) {
                    /* We need to check if filename was changed and rename file */
                    $old_url_parts = explode('/', $this->request->getPost('old_url['. $file_index.']['. $language->id .']'));
                    $upload_path = array_slice($old_url_parts, 0, count($old_url_parts) -1);
                    $old_file_name = array_values(array_slice($old_url_parts, -1))[0];
                    $update_data = [];
                    if ($old_file_name != $this->request->getPost('filename['. $file_index.']['. $language->id .']')) {
                        /* Do rename */
                        $this->admin_general_model->rename_uploaded_file(
                            implode('/', $upload_path),
                            $old_file_name,
                            $this->request->getPost('filename['. $file_index.']['. $language->id .']')
                        );

                        /* New field value */
                        $update_data['url'] = implode('/', $upload_path) . '/'. $this->request->getPost('filename['. $file_index.']['. $language->id .']');
                    } else {
                        unset($update_data['url']);
                    }
					if (basename($value) != $old_file_name && !isset($update_data['url']) && $value != 'xDELETEx') {
						$update_data['id'] = $db_id;
						$update_data['dynamic_gallery_id'] = $dynamic_gallery_id;
						$update_data['domain'] = $this->request->getPost('domain');
						$update_data['url'] = $value;
						$update_data['language'] = $language->id;
					}

                    $update_data['text_title'] = $this->request->getPost('title['. $file_index.']['. $language->id .']');
                    $update_data['text_alt'] = $this->request->getPost('alt['. $file_index.']['. $language->id .']');
                    $update_data['is_hide_seo'] = ($this->request->getPost('seo['. $file_index.']') !== null) ? 1 : 0;
                    $update_data['position'] = $this->request->getPost('img_position['. $file_index.']');
					
                    /* If it was not a new image record and we are changing image file we need to delete old */
                    if (stristr($file_index, 'new') === FALSE) {
                        $this->admin_general_model->update_image_record($update_data, $file_index, $language->id, $this->request->getPost('domain'), $dynamic_gallery_id);
                    } elseif(isset($update_data['url'])) {
                        $this->admin_general_model->set_image_record($update_data);
                    }
                }
            }

        }

        /*
         * CMS Block
         * */
        $this->cms_block_model->save_page_blocks($id, 'dynamic_'. $slug, $this->request);
        /*
         * End CMS Block
         * */

        \Config\Services::session()->setFlashdata('message', ($id) ? '<div class="alert alert-info">Entrada insertada/actualizada correctamente</div>' : '<div class="alert alert-warning">Se ha producido un error, inténtalo nuevamente.</div>');

        if (null !== $this->request->getPost('save_view')) {
            return redirect()->to('admin/dynamic/dedit/'. $slug .'/'. $this->request->getPost('language') .'/'. $id .''. $this->domain_url_params);
        } else {
            return redirect()->to('admin/dynamic/dlist/'. $slug .''. $this->domain_url_params);
        }
    }

    /*
     * @desc dorder Carga el contenido de base de datos de todas las entradas para poder ordenarlos
     * @param $slug identificador de la tabla de datos, por ejemplo, dynamic_xxxxx => slug=xxxxx
     * @param $idioma identificador del idioma
     */
    public function dorder($slug, $idioma)
    {
        $this->data['menu2'] = $slug;
        $this->data['slug'] = $slug;
        foreach ($this->data['dynamic_elements'] as $element)
            if ($element['slug'] == $slug)
                $this->data['title_section'] = $element['name'];

        #Elementos estructurales
        $data_struct = $this->admin_general_model->get_dynamic_table_struct($slug);
        $this->data['first_element_text'] = $this->admin_general_model->get_first_element($data_struct);

        $this->data['data'] = $this->admin_general_model->get_dynamic_table_data([
            'slug' => $slug,
            'language' => $idioma,
            'id' => null,
            'domain' => $this->data['domain'],
            'order' => 'position',
        ]);

        if (file_exists(__DIR__ .'/../../Views/admin/dorder_'. $slug .'.php')) {
            return view('admin/dorder_'. $slug, $this->data);
        } else {
            return view('admin/dorder', $this->data);
        }
    }
    /*
     * @desc ddelete Elimina una entrada de la sección identificada mediante el slug.
     * @param $slug identificador de la tabla de datos, por ejemplo, dynamic_xxxxx => slug=xxxxx
     * @param $id identificador de la entrada.
     * @note Se borrará la entrada y todas las traducciones correspondientes a la misma.
     */
    public function ddelete($slug, $id)
    {
        if ($slug == 'gallery') {
            $data_original = $this->admin_general_model->get_dynamic_table_data([
                'slug' => $slug,
                'language' => $this->admin_language_model->get_default_language_id(),
                'id' => $id,
                'domain' => $this->data['domain'],
            ]);
            $theme_path =  'assets/themes/'. $this->data['domain'] .'/';

            $this->admin_general_model->delImagesByGalleryId($id);

            #delete gallery folder if exist
            $dir = $theme_path .'img/'. $slug .'/'. $this->string_check_and_replace($data_original->text_title);
            if (is_dir($dir)) {
                $this->admin_general_model->delFolder($dir);
            }
			$this->admin_general_model->delUpdatedImagesByGalleryDir ($dir);
        }
        /* We need to save "parent" page data before delete */
        if ($slug == 'pages') {
            $deleted_page_data = $this->admin_general_model->get_page_by_id_domain ($id, $this->data['selected_domain']);
        }

        /* Delete images */
        $data_struct = $this->admin_general_model->get_dynamic_table_struct($slug);
        foreach ($this->data['languages'] as $language) {
            $data = $this->admin_general_model->get_dynamic_table_data([
                'slug' => $slug,
                'id' => $id,
                'language' => $language->id,
                'domain' => $this->data['domain'],
            ]);
            if (!$data) {
                continue;
            }
            foreach ($data_struct as $field) {
                if ($field['type'] != 'image') {
                    continue;
                }

                if ($data->{$field['name']} != '') {
                    // Don't delete images
                    // $this->admin_general_model->delete_image_file_by_src($data->{$field['name']});
                }
            }
        }
        /* End delete images */
        $exito = $this->admin_general_model->delete_dynamic_table_data($slug, $id, $this->data['domain']);

        /* To properly calculate path, position we need to do if after deletion parent page */
        if ($slug == 'pages') {
            /* We need to delete assigned blocks */
            $this->cms_block_model->delete_assigned_cms_page_blocks($id, 'dynamic_'. $slug, $this->data['domain']);

            /* Search for deleting page childs */
            $pages = $this->admin_general_model->get_pages_by_parentid_domain ($id, $this->data['selected_domain']);
            foreach ($pages as $page) {
                $data['id'] = $page->id;

                $result = $this->admin_general_model->get_page_positions(
                    $data['id'],
                    0, /* We drop child's to 0 */
                    $this->data['selected_domain'],
                    $this->data['idioma_original'],
                    $page->slug
                );
                $data['position']   = $result['position'];
                $data['path']       = $result['path'];
                $data['uri_string'] = $result['uri_string'];

                $update_data = [
                    'parent_id' => $deleted_page_data->parent_id,
                    'path' => $data['path'],
                    'uri_string' => $data['uri_string'],
                    'position' => $data['position'],
                ];

                $this->db->where(['id' => $data['id'], 'domain' => $this->data['selected_domain']]);
                $this->db->update('dynamic_pages', $update_data);

                $this->admin_general_model->update_page_childs($data['id'], $this->data['selected_domain'], $page->language, $data['uri_string'], $data['path'], $data['position']);
            }
            /*
             * Lets re order all positions
             * */
            $this->admin_general_model->update_pages_positions($this->data['selected_domain'], $this->data['idioma_original']);
        }

        \Config\Services::session()->setFlashdata('message', ($exito) ? '<div class="alert alert-info">Entrada eliminada correctamente</div>' : '<div class="alert alert-warning">Se ha producido un error, inténtalo nuevamente.</div>');
        return redirect()->to('admin/dynamic/dlist/' . $slug .''. $this->domain_url_params);
    }

    /*
     * @desc dpause Pausa una entrada
     * @param $slug identificador de la tabla de datos, por ejemplo, dynamic_xxxxx => slug=xxxxx
     * @param $id identificador de la entrada.
     * @note Se borrará la entrada y todas las traducciones correspondientes a la misma.
     */
    public function dpause($slug, $id)
    {
        $exito = $this->admin_general_model->pause_dynamic_table_data($slug, $id);
        \Config\Services::session()->setFlashdata('message', ($exito) ? '<div class="alert alert-info">Entrada pausada correctamente</div>' : '<div class="alert alert-info">Se ha producido un error, inténtalo nuevamente.</div>');
        return redirect()->to('admin/dynamic/dlist/' . $slug .''. $this->domain_url_params);
    }

    /*
     * @desc dresume Activa una entrada
     * @param $slug identificador de la tabla de datos, por ejemplo, dynamic_xxxxx => slug=xxxxx
     * @param $id identificador de la entrada.
     * @note Se borrará la entrada y todas las traducciones correspondientes a la misma.
     */

    public function dresume($slug, $id)
    {
        if ($slug == 'modals') {
            $data = $this->admin_general_model->get_dynamic_table_data([
                'slug' => $slug,
                'language' => $this->admin_language_model->get_default_language_id(),
                'order' => 'position',
                'domain' => $this->selected_domain,
            ]);

            foreach ($data as $datum) {
                $this->admin_general_model->pause_dynamic_table_data($slug, $datum->id);
            }
        }

        $exito = $this->admin_general_model->resume_dynamic_table_data($slug, $id);
        \Config\Services::session()->setFlashdata('message', ($exito) ? '<div class="alert alert-info">Entrada activada correctamente</div>' : '<div class="alert alert-info">Se ha producido un error, inténtalo nuevamente.</div>');
        return redirect()->to('admin/dynamic/dlist/' . $slug .''. $this->domain_url_params);
    }

    /*
     * @desc dorder_update Actualiza las posiciones de todas las entradas, mediante petición ajax.
     * @param $slug identificador de la tabla de datos, por ejemplo, dynamic_xxxxx => slug=xxxxx
     * @note Se borrará la entrada y todas las traducciones correspondientes a la misma.
     */
    public function dorder_update($slug)
    {
        $elements = json_decode($this->request->getPost('orden'));
        foreach ($elements[0] as $new_posicion => $element) {
            $data['position'] = $new_posicion + 1;
            $this->admin_general_model->update_position($slug, $element->id, $data, $this->request->getGet('domain'));
        }
        $status = array('status' => 'true');
        echo json_encode($status);
    }

    /*
     * Ajax function, save order from "admin/dynamic/dorder/pages"
     */
    public function dorder_update_pages()
    {
        $new_structure = [];
        foreach ($this->request->getPost('data') as $index => $updated_page) {
            $new_parent_id = (isset($updated_page['parent_id'])) ? $updated_page['parent_id'] : 0;
            $updated_page['position'] = $index;
            $new_structure[$new_parent_id][] = $updated_page;
        }

        foreach ($new_structure as $parent_id => $new_structure_pages) {
            foreach ($new_structure_pages as $index => $page) {
                /* We need to update hierarchy in every language */
                $pages_translations = $this->admin_general_model->get_pages_by_id_domain ($page['id'], $this->data['selected_domain']);
                foreach ($pages_translations as $pages_translation) {
                    $result = $this->admin_general_model->get_page_positions(
                        $page['id'],
                        $parent_id,
                        $this->data['selected_domain'],
                        $pages_translation->language,
                        $pages_translation->slug,
                        $index + 1
                    );
                    /* If there was no translated parent page */
                    if (empty($result)) {
                        $result = $this->admin_general_model->get_page_positions(
                            $page['id'],
                            $parent_id,
                            $this->data['selected_domain'],
                            $this->admin_language_model->get_default_language_id(),
                            $pages_translation->slug,
                            $index + 1
                        );
                    }

                    $data['id']         = $page['id'];
                    $data['position']   = $page['position']; //Important $page not $result!
                    $data['path']       = $result['path'];
                    $data['uri_string'] = $result['uri_string'];
                    $data['domain']     = $this->data['selected_domain'];
                    $data['parent_id']  = $parent_id;
                    $data['language']   = $pages_translation->language;

                    $this->admin_general_model->update_dynamic_table_data('pages', $data);
                }
            }
        }

        echo 'true';
    }

    /*
     * @desc export\import metas
     * @param $slug identificador de la tabla de datos, por ejemplo, dynamic_xxxxx => slug=xxxxx
     */
    public function metas($slug)
    {
        $data_slug = ($slug == 'gallery') ? 'images' : $slug;

        if ($this->request->getPost('export')) {
            $data = $this->admin_general_model->get_dynamic_table_data([
                'slug' => $data_slug,
                'order' => 'position',
                'domain' => $this->data['domain'],
            ]);

            $gallery = $this->admin_general_model->get_dynamic_table_data([
                'slug' => 'gallery',
                'domain' => $this->data['domain'],
            ]);
            $gallery = indexar_array($gallery, 'id');

            $result_data = [];
            foreach ($data as $datum) {
                if ($data_slug == 'images') {
                    if (!$datum->dynamic_gallery_id) {
                        continue;
                    }
                    $path_parts = pathinfo($datum->url);
                    $new_file_name = str_replace(
                        $path_parts['filename'] . '.' . $path_parts['extension'],
                        $path_parts['filename'] . '.tlowest.' . $path_parts['extension'],
                        $datum->url
                    );


                    $result_data[] = [
                        'id' => $datum->id,
                        'language' => $datum->language,
                        'gallery' => $gallery[$datum->dynamic_gallery_id]->text_title,
                        'image' => FCPATH . $new_file_name,
                        'text_title' => $datum->text_title,
                        'text_alt' => $datum->text_alt,
                    ];
                } else {
                    $result_data[] = [
                        'id' => $datum->id,
                        'language' => $datum->language,
                        'title' => $datum->text_title,
                        'slug' => $datum->slug,
                        'text_page_title' => $datum->text_page_title,
                        //'text_meta_keywords' => $datum->text_meta_keywords,
                        'text_meta_description' => $datum->text_meta_description,
                        'text_meta_robots' => $datum->text_meta_robots,
                    ];
                }
            }
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            if ($data_slug == 'images') {
                $header = ['Id', 'Language', 'Gallery', 'Image', 'Title', 'Alt',];
            } else {
                $header = ['Id', 'Language', 'Admin title', 'Slug', 'Meta title', /*'Meta keywords', */'Meta description', 'Meta robots'];
            }

            $sheet->fromArray($header, null, 'A1');
            $sheet->fromArray($result_data, null, 'A2');
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(5);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(30);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setWidth(30);
            //$sheet->getColumnDimension('H')->setWidth(30);

            if ($data_slug == 'images') {
                ini_set("memory_limit","256M");
                ini_set("gd.jpeg_ignore_warning", 1);
                $row_num = 1;
                foreach ($result_data as $item) {
                    $row_num++;
                    $sheet->setCellValue('D'. $row_num, pathinfo($item['image'])['filename'] .'.'. pathinfo($item['image'])['extension']);
                    if (!in_array(pathinfo($item['image'])['extension'], ['png', 'jpeg', 'jpg'])) {
                        continue;
                    }
                    $sheet->getRowDimension($row_num)->setRowHeight(100);
                    $drawing = new MemoryDrawing();
                    $gdImage = (pathinfo($item['image'])['extension'] == 'png') ? imagecreatefrompng($item['image']) : imagecreatefromjpeg($item['image']);
                    $drawing->setName('Image');
                    $drawing->setDescription('Image');
                    $drawing->setResizeProportional(true);
                    $drawing->setImageResource($gdImage);
                    $drawing->setRenderingFunction((pathinfo($item['image'])['extension'] == 'png') ? MemoryDrawing::RENDERING_PNG : MemoryDrawing::RENDERING_JPEG);
                    $drawing->setMimeType((pathinfo($item['image'])['extension'] == 'png') ? MemoryDrawing::MIMETYPE_DEFAULT : MemoryDrawing::MIMETYPE_JPEG);
                    $drawing->setWidth(100);

                    $drawing->setHeight(100);
                    /*$drawing->setOffsetX(5);
                    $drawing->setOffsetY(30);*/
                    $drawing->setCoordinates('D'. $row_num);
                    $drawing->setWorksheet($spreadsheet->getActiveSheet());

                    unset($drawing, $gdImage);
                }
            }

            $writer = new Xlsx($spreadsheet);

            header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="metas_'. $slug .'_'. date('Y-m-d h:i:s') .'.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit();
        }

        if ($this->request->getPost('import')) {
            $table = 'dynamic_' . $data_slug;

            $file = $_FILES['metas']['tmp_name'];

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file);

            $worksheet = $spreadsheet->getActiveSheet();
            foreach($worksheet->toArray() as $item) {
                $id = $item[0];
                $language = $item[1];
                if ($data_slug == 'images') {
                    $data = [
                        'text_title' => $item[4],
                        'text_alt' => $item[5],
                    ];
                } else {
                    $data = [
                        'slug' => $item[3].'',
                        'text_page_title' => $item[4].'',
                        //'text_meta_keywords' => $item[5].'',
                        'text_meta_description' => $item[5].'',
                        'text_meta_robots' => $item[6].'',
                    ];
                }

                if (!$id || $id == '' || !$language || $language == '') {
                    continue;
                }

                $data['id'] = $id;
                $data['language'] = $language;
                if ($this->data['domain']) {
                    $data['domain'] = $this->data['domain'];
                }

                $this->admin_general_model->update_dynamic_table_data($data_slug, $data);

                unset($id, $language);
            }

            \Config\Services::session()->setFlashdata('message', '<div class="alert alert-info">Metas importadas correctamente</div>');
            return redirect()->to('admin/dynamic/metas/' . $slug .''. $this->domain_url_params);
        }

        $this->data['title_section'] = 'Exportar\importar Metas';
        $this->data['menu2'] = $slug;
        $this->data['slug'] = $slug;

        return view('admin/metas', $this->data);
    }

    /*
     * @desc export\import all metas/except galleries
     */
    public function allmetas()
    {
        $slugs = [];
        $dynamic_tables = $this->admin_general_model->get_dynamic_elements();
        foreach ($dynamic_tables as $dynamic_table) {
            $data_struct = $this->admin_general_model->get_dynamic_table_struct($dynamic_table['slug']);
            foreach ($data_struct as $item) {
                if (in_array($item['name'], ['slug', 'text_page_title'])) {
                    $slugs[$dynamic_table['slug']] = $dynamic_table['name'];
                }
            }
        }
        if ($this->request->getPost('export')) {
            $result_data = [];
            foreach ($slugs as $slug => $title) {
                $data = $this->admin_general_model->get_dynamic_table_data([
                    'slug' => $slug,
                    'order' => 'position',
                    'domain' => $this->data['domain'],
                ]);
                foreach ($data as $datum) {
                    $result_data[$slug][] = [
                        'type' => $slug,
                        'id' => $datum->id,
                        'language' => $datum->language,
                        'title' => $datum->text_title,
                        'slug' => $datum->slug,
                        'text_page_title' => $datum->text_page_title,
                        //'text_meta_keywords' => $datum->text_meta_keywords,
                        'text_meta_description' => $datum->text_meta_description,
                        'text_meta_robots' => $datum->text_meta_robots,
                    ];
                }
            }

            $header = ['Type', 'Id', 'Language', 'Admin title', 'Slug', 'Meta title', /*'Meta keywords', */'Meta description', 'Meta robots'];
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);
            $i = 0;
            foreach ($slugs as $slug => $title) {
                if (!isset($result_data[$slug])) {
                    continue;
                }
                $spreadsheet->createSheet();
                $spreadsheet->setActiveSheetIndex($i);

                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle($title);

                $sheet->fromArray($header, null, 'A1');
                $sheet->fromArray($result_data[$slug], null, 'A2');
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(5);
                $sheet->getColumnDimension('C')->setWidth(5);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(30);
                $sheet->getColumnDimension('F')->setWidth(30);
                $sheet->getColumnDimension('G')->setWidth(30);
                $sheet->getColumnDimension('H')->setWidth(30);
                $i++;
            }

            $writer = new Xlsx($spreadsheet);

            header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="metas_'. $slug .'_'. date('Y-m-d h:i:s') .'.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit();
        }

        if ($this->request->getPost('import')) {

            $file = $_FILES['metas']['tmp_name'];

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file);
            $sheetCount = $spreadsheet->getSheetCount();
            for ($i = 0; $i < $sheetCount; $i++) {
                $worksheet = $spreadsheet->getSheet($i);
                foreach($worksheet->toArray() as $item) {
                    $slug = $item[0];
                    $id = $item[1];
                    $language = $item[2];
                    $data = [
                        'slug' => $item[4].'',
                        'text_page_title' => $item[5].'',
                        //'text_meta_keywords' => $item[6],
                        'text_meta_description' => $item[6].'',
                        'text_meta_robots' => $item[7].'',
                    ];
                    if (!$slug || $slug == '' || !$id || $id == '' || !$language || $language == '') {
                        continue;
                    }

                    $data['id'] = $id;
                    $data['language'] = $language;
                    if ($this->data['domain']) {
                        $data['domain'] = $this->data['domain'];
                    }

                    $this->admin_general_model->update_dynamic_table_data($slug, $data);


                    unset($slug, $id, $language);
                }
            }
            \Config\Services::session()->setFlashdata('message', '<div class="alert alert-info">Metas importadas correctamente</div>');
            return redirect()->to('admin/dynamic/allmetas/'. $this->domain_url_params);
        }

        $this->data['title_section'] = 'Exportar\importar todos Metas';
        $this->data['menu2'] = 'allmetas';
        $this->data['slug'] = 'allmetas';

        return view('admin/allmetas', $this->data);
    }

    public function string_check_and_replace($string){
        $keyReplace="-";
        $minuscula=true;

        $coDau=array("à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă",
            "ằ","ắ","ặ","ẳ","ẵ",
            "è","é","ẹ","ẻ","ẽ","ê","ề" ,"ế","ệ","ể","ễ",
            "ì","í","ị","ỉ","ĩ",
            "ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ"
        ,"ờ","ớ","ợ","ở","ỡ",
            "ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ",
            "ỳ","ý","ỵ","ỷ","ỹ",
            "đ",
            "À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă"
        ,"Ằ","Ắ","Ặ","Ẳ","Ẵ",
            "È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ",
            "Ì","Í","Ị","Ỉ","Ĩ",
            "Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ"
        ,"Ờ","Ớ","Ợ","Ở","Ỡ",
            "Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ",
            "Ỳ","Ý","Ỵ","Ỷ","Ỹ",
            "Đ","ê","ù","à",
            "Ñ","ñ");

        $khongDau=array("a","a","a","a","a","a","a","a","a","a","a"
        ,"a","a","a","a","a","a",
            "e","e","e","e","e","e","e","e","e","e","e",
            "i","i","i","i","i",
            "o","o","o","o","o","o","o","o","o","o","o","o"
        ,"o","o","o","o","o",
            "u","u","u","u","u","u","u","u","u","u","u",
            "y","y","y","y","y",
            "d",
            "A","A","A","A","A","A","A","A","A","A","A","A"
        ,"A","A","A","A","A",
            "E","E","E","E","E","E","E","E","E","E","E",
            "I","I","I","I","I",
            "O","O","O","O","O","O","O","O","O","O","O","O"
        ,"O","O","O","O","O",
            "U","U","U","U","U","U","U","U","U","U","U",
            "Y","Y","Y","Y","Y",
            "D","e","u","a",
            "N","n");
        $string=  str_replace($coDau,$khongDau,$string);

        $cyrillicPattern  = array('а','б','в','г','д','e', 'ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у',
            'ф','х','ц','ч','ш','щ','ъ','ь', 'э', 'ы', 'ю','я','А','Б','В','Г','Д','Е', 'Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У',
            'Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ь', 'Э', 'Ы', 'Ю','Я' );

        $latinPattern = array( 'a','b','v','g','d','e','jo','zh','z','i','y','k','l','m','n','o','p','r','s','t','u',
            'f' ,'h' ,'ts' ,'ch','sh' ,'sht', '', '`', 'je','ji','yu' ,'ya','A','B','V','G','D','E','Jo','Zh',
            'Z','I','Y','K','L','M','N','O','P','R','S','T','U',
            'F' ,'H' ,'Ts' ,'Ch','Sh','Sht', '', '`', 'Je' ,'Ji' ,'Yu' ,'Ya' );

        $string = str_replace($cyrillicPattern, $latinPattern, $string);
        $string     =  str_replace(" ","_",$string);
        $string    =    str_replace("--","_",$string);
        $string    =    str_replace("--","_",$string);
        $string    =    str_replace("--","_",$string);
        $string    =    str_replace($keyReplace,"_",$string);
        $string    =    ($minuscula)?strtolower($string):$string;

        return $string;
    }
}
