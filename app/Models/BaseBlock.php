<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Class BaseBlock
 */
#[\AllowDynamicProperties]
class BaseBlock extends Model
{
    public $block_model;
    public $cms_block_id;
    /** Values to pass to render view */
    public $params_values;
    private $related_table;
    private $block_class;

    /**
     * BaseBlock constructor.
     * @param $block_class
     * @param null $related_table
     * @param null $cms_block_id
     */
    function __construct($block_class, $related_table = null)
    {
        parent::__construct();
        $block_class_parts = explode ( '\\', $block_class);
        $this->block_class = array_pop($block_class_parts);
        $this->related_table = $related_table;
        $this->admin_general_model = new Admin_general_model();
        //$this->block_model = self::get_block_by_class();
    }

    /**
     * @param $block
     * @return array
     */
    public function prepare_view($block, $data)
    {
		$this->data = $data;
        $this->cms_block_id = $block->cms_block_id;
        $this->block_model = self::get_block_by_id($block->cms_block_id);
        $this->params_values = self::get_params_values($block->id);
        $position = $this->params_values->position;
        $block_params = self::get_block_params($this->block_model);
        if (isset($block_params->static) && $block_params->static) {
            $this->params_values = $this->get_static_params_values();
            if (!$this->params_values) {
                return [];
            }
            $this->params_values->position = $position;
            /* Block was paused */
            if (empty($this->params_values)) {
                return [
                    'html' => null,
                    'position' => null,
                ];
            }
        }
        $fields = self::get_fields($this->block_model);
        foreach ($fields as $field) {
            /* If field has defined related_table, try to get linked objects */
            if (isset($field->related_table) && $field->related_table != '') {
                if (isset($this->params_values->{$field->name})) {
                    $this->params_values->{$field->name} = self::prepare_dynamic_objects($this->params_values->{$field->name}, $field->related_table);
                } else {
                    $this->params_values->{$field->name} = [];
                }
            }
        }

        if (isset($data['child_page'])) {
            $this->params_values->dynamic_content = $data['child_page'];
        }

        return [strtolower($this->block_class) => $this->params_values];
    }

    public function render_view(array $data = [])
    {
		$data[strtolower($this->block_class)]->site_preferences = $this->selected_domain_preferences;
		$data[strtolower($this->block_class)]->site_lang = $this->site_lang;
		$data[strtolower($this->block_class)]->data = $this->data;
        return view('block/'. strtolower($this->block_class), $data, []);
    }

    /**
     * @param $block
     * @return array
     */
    public function view($block, $data)
    {
        $data = $this->prepare_view($block, $data);
		
        return [
            'html' => self::render_view($data),
            'position' =>  ($this->params_values) ? $this->params_values->position : null,
        ];
    }

    /**
     * Returns objects of passed dynamic content ids.
     * Function keeps order of linked objects
     * It is recursive, so we need to be careful when configuring BD
     * @param $ids - Ids of dynamic content, string separated
     * @param $table
     * @return array
     */
    public function prepare_dynamic_objects($ids, $table, $parent_related_table = null)
    {
        if (!$ids || $ids == '') {
            return [];
        }
		if(! property_exists ($this, 'site_lang') ) $this->site_lang = \Config\Services::session()->get('site_lang');
		if(! property_exists ($this, 'selected_domain') ) $this->selected_domain = \Config\Services::session()->get('site_domain');
        $dynamic_objects = self::get_related_data($table, $this->site_lang, $ids);
        foreach ($dynamic_objects as $dynamic_object) {
            foreach ($dynamic_object as $field => $value) {
                if (strpos($field, 'multiple_') !== FALSE || strpos($field, 'dynamic_') !== FALSE) {
                    if ($parent_related_table && str_replace('multiple_', 'dynamic_', $field) == $parent_related_table) {
                        continue;
                    }
                    $dynamic_object->{$field} = self::prepare_dynamic_objects($value, str_replace('multiple_', 'dynamic_', $field), $table);
                }
            }
        }
        $dynamic_objects = indexar_array($dynamic_objects, 'id');
        $dynamic_objects = (count($dynamic_objects) > 0) ? array_replace(array_flip(explode(',', $ids)), $dynamic_objects) : [];
        $dynamic_objects = array_filter($dynamic_objects, 'is_object');

        return $dynamic_objects;
    }

    public function admin_form($block = null, $cms_block = null, $language = null)
    {
        if (isset($block) && $block->id) {
            $params_values = self::get_params_values($block->id);
            $this->cms_block_id = $block->cms_block_id;
        } elseif (isset($cms_block) && $cms_block->id) {
            $this->cms_block_id = $cms_block->id;
        }

        if ($this->cms_block_id) {
            $this->block_model = self::get_block_by_id($this->cms_block_id);
        } else {
            $this->block_model = self::get_block_by_class();

        }
        $block_params = self::get_block_params();
        $fields = self::get_fields();

        //$new_block_group = ($cms_block) ? 'new_'. $cms_block->id : 'new';
        $fields_group = (isset($block->id)) ? $block->id : 'new';//$new_block_group;
        $fields_options = [];

        $str = self::get_admin_block_layout($block, $block_params, $fields_group);
        $str .= '<div class="row">';

        $str .= '<input type="hidden" name="'. $this->block_class .'['. $fields_group .'][cms_block_id]" value="'. $this->cms_block_id .'">';

        foreach ($fields as $field) {
            //If it is a static block, draw only position field
            if (isset($block_params->static) && $block_params->static && $field->name != 'position') {
                continue;
            }
            /* For every input type=file we need to simplify its name, because of $_FILES array... */
            if ($field->type == 'image' || $field->type == 'document') {
                $form_field_name = $this->block_class .'__'. $fields_group .'__'. $field->name;
            } else {
                $form_field_name = $this->block_class .'['. $fields_group .']['. $field->name .']';
            }

            /* For every input type=multiselect we need to prepare related data */
            if ($field->type == 'select' || $field->type == 'multiselect' || $field->type == 'cmsimage') {
                if (isset($field->related_table) && $field->related_table != '') {
                    $related_blocks = self::get_related_data($field->related_table, $language);
                } else {
                    $related_blocks = self::get_related_data($this->related_table, $language);
                }

                if ($field->type == 'select') {
                    $fields_options[$field->name][''] = '-';
                }


                if ($field->type == 'cmsimage') {
                    foreach ($related_blocks as $info_block) {
                        $fields_options[$field->name][$info_block->id] = $info_block;
                    }
                } else {
                    foreach ($related_blocks as $info_block) {
                        $fields_options[$field->name][$info_block->id] = $info_block->text_title;
                    }
                }
            }

            //Si no existe el parametro copy fields = default
            $objects = [
                $form_field_name => [
                    'label' => $field->label,
                    'value' => isset($params_values->{$field->name}) && !isset($_GET['copy']) ? $params_values->{$field->name} : $field->default,
                    'default' => $field->default,
                    'content' => (isset($fields_options[$field->name])) ? $fields_options[$field->name] : [],
                    'related_table' => (isset($field->related_table) && $field->related_table != '') ? $field->related_table : false,
                ]
            ];

            $values = [];
            if (isset($field->values)) {
                foreach ($field->values as $value) {
                    $values = $values + get_object_vars($value);
                }
            }

            switch ($field->type) {
                case 'text' :
                    $str .= view('admin/input/text', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'htmlarea' :
                    $str .= view('admin/input/htmlarea', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'image' :
                    $str .= view('admin/input/image', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'cmsimage' :
                    $str .= view('admin/input/cmsimage', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'document' :
                    $str .= view('admin/input/document', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'textarea' :
                    $str .= view('admin/input/textarea', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'enum' :
                    $str .= view('admin/input/enum', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects, 'values' => $values]);
                    break;
                case 'multiselect' :
                    $str .= view('admin/input/multiselect', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'select' :
                    $str .= view('admin/input/select', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'hidden' :
                    $str .= view('admin/input/hidden', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'sys_html' :
                    $str .= $field->value;
            }
        }

        if (count($fields) <= 1) {
            $str .= '<div class="col-md-6"><p>No hay parámetros para este bloque</p></div>';
        }
        if (isset($block_params->static) && $block_params->static){
            $str .= '<div class="col-md-8"><p>Es un bloque estático, su parámetros están en el menú "Bloques estáticos"</p></div>';
        }

        $str .= '</div>';
        $str .= self::get_admin_block_layout_end();

        return $str;
    }

    /*
     * To determine exact block we already have class, and domain, we only need language
     * */
    public function admin_form_static($cms_block_id, $language = false)
    {
        $this->cms_block_id = $cms_block_id;

        $this->block_model = self::get_block_by_id($this->cms_block_id);
        $block_params = self::get_block_params();
        $fields = self::get_fields($this->block_model);

        if ($language) {
            $params_values = self::get_static_params_values($language);
            /* If we are creationg new translation, we need to show original language content */
            if (empty($params_values)) {
                $params_values = self::get_static_params_values($this->get_default_language_id());
                $language = null;
            }
        }

        $str = '<div class="row">';

        foreach ($fields as $field) {
            //If it is a static block, draw only position field (hidden)
            if (isset($block_params->static) && $block_params->static && $field->name == 'position') {
                continue;
            }
            $form_field_name = $this->block_class .'_'. $field->name;

            /* For every input type=multiselect we need to prepare related data */
            if ($field->type == 'select' || $field->type == 'multiselect' || $field->type == 'cmsimage') {
                if (isset($field->related_table) && $field->related_table != '') {
                    $related_blocks = self::get_related_data($field->related_table, $language);
                } else {
                    $related_blocks = self::get_related_data($this->related_table, $language);
                }

                if ($field->type == 'select') {
                    $fields_options[$field->name][''] = '-';
                }

                if ($field->type == 'cmsimage') {
                    foreach ($related_blocks as $info_block) {
                        $fields_options[$field->name][$info_block->id] = $info_block;
                    }
                } else {
                    foreach ($related_blocks as $info_block) {
                        $fields_options[$field->name][$info_block->id] = $info_block->text_title;
                    }
                }
            }

            $objects = [
                $form_field_name => [
                    'label' => $field->label,
                    'value' => isset($params_values->{$field->name}) ? $params_values->{$field->name} : $field->default,
                    'default' => $field->default,
                    'content' => (isset($fields_options[$field->name])) ? $fields_options[$field->name] : [],
                    'related_table' => (isset($field->related_table) && $field->related_table != '') ? $field->related_table : false,
                ]
            ];

            $values = [];
            if (isset($field->values)) {
                foreach ($field->values as $value) {
                    $values = $values + get_object_vars($value);
                }
            }

            switch ($field->type) {
                case 'text' :
                    $str .= view('admin/input/text', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'htmlarea' :
                    $str .= view('admin/input/htmlarea', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'image' :
                    $str .= view('admin/input/image', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'cmsimage' :
                    $str .= view('admin/input/cmsimage', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'document' :
                    $str .= view('admin/input/document', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'textarea' :
                    $str .= view('admin/input/textarea', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'enum' :
                    $str .= view('admin/input/enum', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects, 'values' => $values]);
                    break;
                case 'multiselect' :
                    $str .= view('admin/input/multiselect', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'select' :
                    $str .= view('admin/input/select', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'col' => (isset($field->cols) ? $field->cols : 12), 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'hidden' :
                    $str .= view('admin/input/hidden', ['selected_domain_preferences'=>$this->selected_domain_preferences, 'name' => $form_field_name, 'objects' => $objects]);
                    break;
                case 'sys_html' :
                    $str .= $field->value;
            }

        }
        $str .= '</div>';

        return $str;
    }

    /*
     * Function create or update attachement of a block to cms page
     * */
    public function set_page_block($data,$selected_domain_preferences)
    {
        if (isset($data['form_data']['delete'])) {
            $builder = $this->db->table('cms_page_block');
            $builder->where('id', $data['id'])->delete();
            return true;
        }
        /* image saving path */
        $theme_path =  'assets/themes/'. $data['domain'] .'/';
        $config['max_size'] = $selected_domain_preferences->max_upload_size;
        $config['overwrite'] = false;

        if (isset($cms_block) && $cms_block->id) {
            $this->block_model = self::get_block_by_id($cms_block->id);
        } else {
            $this->block_model = self::get_block_by_class();
        }

        $fields = self::get_fields();

        $insert_data['id']           = $data['id'];
        $insert_data['cms_block_id'] = (isset($data['form_data']['cms_block_id'])) ? $data['form_data']['cms_block_id'] : 0;
        $insert_data['class']        = $data['class'];
        $insert_data['page_id']      = $data['page_id'];
        $insert_data['content_type'] = $data['content_type'];
        $insert_data['language']     = $data['language'];
        $insert_data['domain']       = $data['domain'];
        $insert_data['status']       = (isset($data['form_data']['status'])) ? 'ACTIVED' : 'PAUSED';

        $params_values = [];
        foreach ($fields as $field) {

            switch ($field->type) {
                case 'image' :
                    if ($data['original_id'] != 'null') {
                        $input_file_fiend_name = $this->block_class .'__'. $data['original_id'] .'__'. $field->name;
                    } else {
                        $input_file_fiend_name = ($data['id'] != 'null') ? $this->block_class .'__'. $data['id'] .'__'. $field->name : $this->block_class .'__new__'. $field->name;
                    }

                    $config['upload_path'] = $theme_path .'img/'. strtolower($this->block_class) .'/';
                    if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
                    $config['allowed_types'] = 'pdf|gif|jpg|png|svg|jpeg';

                    $r_file = $this->request->getFile($input_file_fiend_name);

                    if ($r_file && $r_file->getName()) {
                        copy ($r_file->getPathName(), $config['upload_path'].'/'.time().'_'.$r_file->getName());
                        $params_values[$field->name] = $config['upload_path'].'/'.time().'_'.$r_file->getName();

                        /* Compress image */
                        if ($selected_domain_preferences->auto_shortpixel == 1) {
                            $this->admin_general_model->compress_image($params_values[$field->name]);
                        }
                        /* Generate thumbnails */
                        $this->admin_general_model->generate_thumbnails($params_values[$field->name], $selected_domain_preferences);
                        /* Generate webp */
                        if ($selected_domain_preferences->generate_webp_on_upload == 1) {
                            $path_parts = pathinfo($params_values[$field->name]);
                            foreach (['tlow', 'tmedium', 'thigh', 'tlowest'] as $size) {
                                $new_file_name = str_replace(
                                    $path_parts['filename'] . '.' . $path_parts['extension'],
                                    $path_parts['filename'] . '.' . $size . '.' . $path_parts['extension'],
                                    $params_values[$field->name]
                                );
                                if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($new_file_name, 'webp'))) {
                                    $this->admin_general_model->generate_webp($new_file_name);
                                }
                            }
                            $this->admin_general_model->generate_webp($params_values[$field->name]);
                        }
                    } elseif ($this->request->getPost($input_file_fiend_name .'_hidden') == "xDELETEx") { #Si se pide borrar se suprime
                        $params_values[$field->name] = '';
                        /* Will we delete file? unlink()*/
                    } elseif ($this->request->getPost($input_file_fiend_name .'_hidden')) { #Si no se envía mantenemos el valor anterior
                        $params_values[$field->name] = $this->request->getPost($input_file_fiend_name .'_hidden');
                    } else { #Si no se envía y es entrada nueva mantemos el valor de la entrada original.
                        $params_values[$field->name] = (!empty($data_original)) ? $data_original->{$field->name} : '';
                    }

                    break;
                case 'cmsimage' :
                    if (!isset($data['form_data'][$field->name])) {
                        break;
                    }
                    unset($_FILES['uploading_file'], $img_data);
                    /* File was uploaded */
                    if (!empty($_FILES[$this->block_class]['name'])) {
                        foreach ($_FILES[$this->block_class]['name'] as $file_index => $file) {
                            /* Another block */
                            if ($file_index != $data['form_id']) {
                                continue;
                            }
                            if ($_FILES[$this->block_class]['tmp_name'][$file_index][$field->name]['file'] == '') {
                                continue;
                            }

                            $_FILES['uploading_file']['name']      = $_FILES[$this->block_class]['name'][$file_index][$field->name]['file'];
                            $_FILES['uploading_file']['type']      = $_FILES[$this->block_class]['type'][$file_index][$field->name]['file'];
                            $_FILES['uploading_file']['tmp_name']  = $_FILES[$this->block_class]['tmp_name'][$file_index][$field->name]['file'];
                            $_FILES['uploading_file']['error']     = $_FILES[$this->block_class]['error'][$file_index][$field->name]['file'];
                            $_FILES['uploading_file']['size']      = $_FILES[$this->block_class]['size'][$file_index][$field->name]['file'];
                        }
                    }
                    /* If we uploading and already has image - delete it */
                    if ((isset($_FILES['uploading_file']) && isset($data['form_data'][$field->name]['id'])) || $data['form_data'][$field->name]['delete']) {
                        // Don't delete images 
						// $this->admin_general_model->delete_image_file($data['form_data'][$field->name]['id'], $insert_data['language'], $insert_data['domain'], null);
                        if ( isset($data['form_data'][$field->name]['id']) ) $this->admin_general_model->delete_dynamic_table_data('images', $data['form_data'][$field->name]['id'], $insert_data['domain']);
                        if ($data['form_data'][$field->name]['delete'] == 'xDELETEx' ) {
                            break;
                        }
                        
                    }
					
                    $config['upload_path'] = $theme_path .'img/'. strtolower($this->block_class) .'/';
                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }
                    $config['allowed_types'] = 'pdf|gif|jpg|png|svg|jpeg';

                    /* Upload file to server */
                    $r_file = $this->request->getFile('uploading_file');

                    if (isset($_FILES['uploading_file']) && $_FILES['uploading_file']['tmp_name'] || $data['form_data'][$field->name]['delete'] ) {
						
						if ($data['form_data'][$field->name]['delete'] ) {
							$file_url = $data['form_data'][$field->name]['delete'];
						} else {
							$file_url = $config['upload_path'].'/'.time().'_'.$_FILES['uploading_file']['name'];
							copy ($_FILES['uploading_file']['tmp_name'], $file_url);
						}


                        $img_data['url'] = $file_url;
                        $img_data['file_size'] = @filesize($file_url);
                        $sizes = @getimagesize($file_url);
                        $img_data['image_width'] =$sizes[0];
                        $img_data['image_height'] = $sizes[1];

                        /* Compress image */
                        if ($selected_domain_preferences->auto_shortpixel == 1) {
                            $this->admin_general_model->compress_image($file_url);
                        }
                        /* Generate thumbnails */
                        $this->admin_general_model->generate_thumbnails($file_url, $selected_domain_preferences);
                        /* Generate webp */
                        if ($selected_domain_preferences->generate_webp_on_upload == 1) {
                            $path_parts = pathinfo($file_url);
                            foreach (['tlow', 'tmedium', 'thigh', 'tlowest'] as $size) {
                                $new_file_name = str_replace(
                                    $path_parts['filename'] . '.' . $path_parts['extension'],
                                    $path_parts['filename'] . '.' . $size . '.' . $path_parts['extension'],
                                    $file_url
                                );
                                if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($new_file_name, 'webp'))) {
                                    $this->admin_general_model->generate_webp($new_file_name);
                                }
                            }
                            $this->admin_general_model->generate_webp($file_url);
                        }
                        unset($data['form_data'][$field->name]['id']);
                    }

                    $img_data['text_title'] = $data['form_data'][$field->name]['title'];
                    $img_data['text_alt'] = $data['form_data'][$field->name]['alt'];
                    $img_data['is_hide_seo'] = /*(isset($data['form_data'][$field->name]['image'])) ? 1 : */0;
                    $img_data['position'] = 0;
                    $img_data['language'] = $insert_data['language'];
                    $img_data['domain'] = $insert_data['domain'];

                    if (isset($data['form_data'][$field->name]['id']) && $data['form_data'][$field->name]['id'] != '') {
                        //If we are in new translation, we need to make a copy
                        $image = $this->admin_general_model->get_dynamic_table_data([
                            'slug' => 'images',
                            'language' => $insert_data['language'],
                            'domain' => $insert_data['domain'],
                            'id' => $data['form_data'][$field->name]['id'],
                        ]);
                        if (!$image) {
                            $image = $this->admin_general_model->get_dynamic_table_data([
                                'slug' => 'images',
                                'language' => $this->get_default_language_id(),
                                'domain' => $insert_data['domain'],
                                'id' => $data['form_data'][$field->name]['id'],
                            ]);
                            $img_data['id'] = $data['form_data'][$field->name]['id'];
                            $img_data['url'] = $image->url;
                            $img_data['file_size'] = $image->file_size;
                            $img_data['image_width'] = $image->image_width;
                            $img_data['image_height'] = $image->image_height;
                            $this->admin_general_model->set_image_record($img_data);
                        } else {
                            $this->admin_general_model->update_image_record($img_data, $data['form_data'][$field->name]['id'], $insert_data['language'], $insert_data['domain'], null);
                        }
                        $params_values[$field->name] = $data['form_data'][$field->name]['id'];
                    } elseif (isset($img_data['url'])) {

                        $img_data['id'] = $this->admin_general_model->get_next_insert_id('dynamic_images');
                        $this->admin_general_model->set_image_record($img_data);
                        $params_values[$field->name] = $img_data['id'];
                    }

                    break;
                case 'document' :
                    if ($data['original_id'] != 'null') {
                        $input_file_fiend_name = $this->block_class .'__'. $data['original_id'] .'__'. $field->name;
                    } else {
                        $input_file_fiend_name = ($data['id'] != 'null') ? $this->block_class .'__'. $data['id'] .'__'. $field->name : $this->block_class .'__new__'. $field->name;
                    }

                    $config['upload_path'] = $theme_path .'docs/'. strtolower($this->block_class) .'/';
                    if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
                    $config['allowed_types'] = 'pdf|doc|xls|ppt|docx|xlsx';
                    $config['overwrite'] = true;

                    $r_file = $this->request->getFile($input_file_fiend_name);
                    if ($r_file && $r_file->getName()) {
                        copy ($r_file->getPathName(), $config['upload_path'].'/'.time().'_'.$r_file->getName());
                        $params_values[$field->name] = $config['upload_path'].'/'.time().'_'.$r_file->getName();
                    } elseif ($this->request->getPost($input_file_fiend_name . "_delete") == "xDELETEx") { #Si se pide borrar se suprime
                        $params_values[$field->name] = '';
                    } elseif ($this->request->getPost($input_file_fiend_name . "_hidden")) { #Si no se envía mantenemos el valor anterior
                        $params_values[$field->name] = $this->request->getPost($input_file_fiend_name . "_hidden");
                    } else { #Si no se envía y es entrada nueva mantemos el valor de la entrada original.
                        $params_values[$field->name] = (!empty($data_original)) ? $data_original->{$field->name} : '';
                    }
                    break;
                default :
                    /* When it is multiselect, we need to implode */
                    if (isset($data['form_data'][$field->name]) && is_array($data['form_data'][$field->name])) {
                        $data['form_data'][$field->name] = implode(',', $data['form_data'][$field->name]);
                    }

                    $params_values[$field->name] = (isset($data['form_data'][$field->name])) ? $data['form_data'][$field->name] : false;
                    break;
            }
        }

        $insert_data['params_values'] = json_encode($params_values);
        $builder = $this->db->table('cms_page_block');
        if ($insert_data['id'] && $insert_data['id'] != 'null') {
            $builder->where('id', $insert_data['id']);
            $result = $builder->set($insert_data)->update();
        } else {
            $result = $builder->set($insert_data)->insert();
        }

        return ($result) ? true : false;
    }

    /*
     * Function create or update static block params
     * */
    public function set_static_block($data)
    {
        /* image saving path */
        $theme_path =  'assets/themes/'. $data['domain'] .'/uploads/';
        $config['upload_path'] = $theme_path .'img/'. strtolower($this->block_class) .'/';
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
        $config['allowed_types'] = 'pdf|gif|jpg|png|svg|jpeg';
        $config['max_size'] = $this->selected_domain_preferences->max_upload_size;
        $config['overwrite'] = false;

        /* end image saving path */

        $this->block_model = self::get_block_by_class();
        $fields = self::get_fields();

        $insert_data['class']        = $data['class'];
        $insert_data['cms_block_id'] = $data['cms_block_id'];
        $insert_data['language']     = $data['language'];
        $insert_data['domain']       = $data['domain'];

        $builder    = $this->db->table('cms_static_block');
        $block = $builder->where([
            'cms_block_id' => $insert_data['cms_block_id'],
            'class' => $insert_data['class'],
            'language' => $insert_data['language'],
            'domain' => $insert_data['domain']])->get()->getFirstRow();

        $insert_data['id'] = ($block) ? $block->id : null;

        $params_values = [];
        foreach ($fields as $field) {
            switch ($field->type) {
                case 'image' :
                    $input_file_fiend_name = $this->block_class .'_'. $field->name;
                    $r_file = $this->request->getFile($input_file_fiend_name);

                    if ($r_file->getName()) {
                        copy ($r_file->getPathName(), $config['upload_path'].'/'.time().'_'.$r_file->getName());
                        $params_values[$field->name] = $config['upload_path'].'/'.time().'_'.$r_file->getName();

                        /* Compress image */
                        if ($this->selected_domain_preferences->auto_shortpixel == 1) {
                            $this->admin_general_model->compress_image($params_values[$field->name]);
                        }
                        /* Generate thumbnails */
                        $this->admin_general_model->generate_thumbnails($params_values[$field->name], $this->selected_domain_preferences);
                        /* Generate webp */
                        if ($this->selected_domain_preferences->generate_webp_on_upload == 1) {
                            $path_parts = pathinfo($params_values[$field->name]);
                            foreach (['tlow', 'tmedium', 'thigh', 'tlowest'] as $size) {
                                $new_file_name = str_replace(
                                    $path_parts['filename'] . '.' . $path_parts['extension'],
                                    $path_parts['filename'] . '.' . $size . '.' . $path_parts['extension'],
                                    $params_values[$field->name]
                                );
                                if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($new_file_name, 'webp'))) {
                                    $this->admin_general_model->generate_webp($new_file_name);
                                }
                            }
                            $this->admin_general_model->generate_webp($params_values[$field->name]);
                        }
                    } elseif ($this->request->getPost($input_file_fiend_name .'_hidden') == "xDELETEx") { #Si se pide borrar se suprime
                        $params_values[$field->name] = '';
                        /* Will we delete file? unlink()*/
                    } elseif ($this->request->getPost($input_file_fiend_name .'_hidden')) { #Si no se envía mantenemos el valor anterior
                        $params_values[$field->name] = $this->request->getPost($input_file_fiend_name .'_hidden');
                    } else { #Si no se envía y es entrada nueva mantemos el valor de la entrada original.
                        $params_values[$field->name] = (!empty($data_original)) ? $data_original->{$field->name} : '';
                    }

                    break;
                case 'cmsimage' :

                    unset($_FILES['uploading_file'], $img_data);
                    /* File was uploaded */
                    if (!empty($_FILES[$this->block_class.'_'. $field->name]['name']['file'])) {
                        $_FILES['uploading_file']['name']      = $_FILES[$this->block_class.'_'. $field->name]['name']['file'];
                        $_FILES['uploading_file']['type']      = $_FILES[$this->block_class.'_'. $field->name]['type']['file'];
                        $_FILES['uploading_file']['tmp_name']  = $_FILES[$this->block_class.'_'. $field->name]['tmp_name']['file'];
                        $_FILES['uploading_file']['error']     = $_FILES[$this->block_class.'_'. $field->name]['error']['file'];
                        $_FILES['uploading_file']['size']      = $_FILES[$this->block_class.'_'. $field->name]['size']['file'];
                    }

                    /* If we uploading and already has image - delete it */
                    if ((isset($_FILES['uploading_file']) && isset($data['form_data'][$field->name]['id'])) || $data['form_data'][$field->name]['delete']) {
                        // Don't delete images 
						//$this->admin_general_model->delete_image_file($data['form_data'][$field->name]['id'], $insert_data['language'], $insert_data['domain'], null);
                        $this->admin_general_model->delete_dynamic_table_data('images', $data['form_data'][$field->name]['id'], $insert_data['domain']);
                        if ($data['form_data'][$field->name]['delete']) {
                            break;
                        }
                    }

                    $config['upload_path'] = $theme_path .'img/'. strtolower($this->block_class) .'/';
                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }
                    $config['allowed_types'] = 'pdf|gif|jpg|png|svg|jpeg';

                    /* Upload file to server */
                    $r_file = $this->request->getFile('uploading_file');

                    if (isset($_FILES['uploading_file']) && $_FILES['uploading_file']['tmp_name']) {
                        $file_url = $config['upload_path'].'/'.time().'_'.$_FILES['uploading_file']['name'];
                        copy ($_FILES['uploading_file']['tmp_name'], $file_url);


                        $img_data['url'] = $file_url;
                        $img_data['file_size'] = $_FILES['uploading_file']['size'];
                        $sizes = @getimagesize($file_url);
                        $img_data['image_width'] =@$sizes[0];
                        $img_data['image_height'] =@$sizes[1];

                        /* Compress image */
                        if ($this->selected_domain_preferences->auto_shortpixel == 1) {
                            $this->admin_general_model->compress_image($file_url);
                        }
                        /* Generate thumbnails */
                        $this->admin_general_model->generate_thumbnails($file_url, $this->selected_domain_preferences);
                        /* Generate webp */
                        if ($this->selected_domain_preferences->generate_webp_on_upload == 1) {
                            $path_parts = pathinfo($file_url);
                            foreach (['tlow', 'tmedium', 'thigh', 'tlowest'] as $size) {
                                $new_file_name = str_replace(
                                    $path_parts['filename'] . '.' . $path_parts['extension'],
                                    $path_parts['filename'] . '.' . $size . '.' . $path_parts['extension'],
                                    $file_url
                                );
                                if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($new_file_name, 'webp'))) {
                                    $this->admin_general_model->generate_webp($new_file_name);
                                }
                            }
                            $this->admin_general_model->generate_webp($config['upload_path'] . $uploaded_file['file_name']);
                        }
                        unset($data['form_data'][$field->name]['id']);
                    }

                    $img_data['text_title'] = $data['form_data'][$field->name]['title'];
                    $img_data['text_alt'] = $data['form_data'][$field->name]['alt'];
                    $img_data['is_hide_seo'] = /*(isset($data['form_data'][$field->name]['image'])) ? 1 : */0;
                    $img_data['position'] = 0;
                    $img_data['language'] = $insert_data['language'];
                    $img_data['domain'] = $insert_data['domain'];

                    if (isset($data['form_data'][$field->name]['id']) && $data['form_data'][$field->name]['id'] != '') {
                        //If we are in new translation, we need to make a copy
                        $image = $this->admin_general_model->get_dynamic_table_data([
                            'slug' => 'images',
                            'language' => $insert_data['language'],
                            'domain' => $insert_data['domain'],
                            'id' => $data['form_data'][$field->name]['id'],
                        ]);
                        if (!$image) {
                            $image = $this->admin_general_model->get_dynamic_table_data([
                                'slug' => 'images',
                                'language' => $this->get_default_language_id(),
                                'domain' => $insert_data['domain'],
                                'id' => $data['form_data'][$field->name]['id'],
                            ]);
                            $img_data['id'] = $data['form_data'][$field->name]['id'];
                            $img_data['url'] = $image->url;
                            $img_data['file_size'] = $image->file_size;
                            $img_data['image_width'] = $image->image_width;
                            $img_data['image_height'] = $image->image_height;
                            $this->admin_general_model->set_image_record($img_data);
                        } else {
                            $this->admin_general_model->update_image_record($img_data, $data['form_data'][$field->name]['id'], $insert_data['language'], $insert_data['domain'], null);
                        }
                        $params_values[$field->name] = $data['form_data'][$field->name]['id'];
                    } elseif (isset($img_data['url'])) {
                        $img_data['id'] = $this->admin_general_model->get_next_insert_id('dynamic_images');
                        $this->admin_general_model->set_image_record($img_data);
                        $params_values[$field->name] = $img_data['id'];
                    }

                    break;
                case 'document' :
                    $input_file_fiend_name = $this->block_class .'_'. $field->name;

                    $config['upload_path'] = $theme_path .'docs/'. strtolower($this->block_class) .'/';
                    if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
                    $config['allowed_types'] = 'pdf|doc|xls|ppt|docx|xlsx';
                    $config['overwrite'] = true;

                    $r_file = $this->request->getFile($input_file_fiend_name);
                    if ($r_file->getName()) {
                        copy ($r_file->getPathName(), $config['upload_path'].'/'.time().'_'.$r_file->getName());
                        $params_values[$field->name] = $config['upload_path'].'/'.time().'_'.$r_file->getName();
                    } elseif ($this->request->getPost($input_file_fiend_name . "_delete") == "xDELETEx") { #Si se pide borrar se suprime
                        $params_values[$field->name] = '';
                    } elseif ($this->request->getPost($input_file_fiend_name . "_hidden")) { #Si no se envía mantenemos el valor anterior
                        $params_values[$field->name] = $this->request->getPost($input_file_fiend_name . "_hidden");
                    } else { #Si no se envía y es entrada nueva mantemos el valor de la entrada original.
                        $params_values[$field->name] = (!empty($data_original)) ? $data_original->{$field->name} : '';
                    }
                    break;
                default :
                    if (isset($data['form_data'][$field->name]) && is_array($data['form_data'][$field->name])) {
                        $data['form_data'][$field->name] = implode(',', $data['form_data'][$field->name]);
                    }

                    $params_values[$field->name] = (isset($data['form_data'][$field->name])) ? $data['form_data'][$field->name] : false;
                    break;
            }
        }
        $insert_data['params_values'] = json_encode($params_values);

        $builder = $this->db->table('cms_static_block');
        if ($insert_data['id']) {
            $builder->where('id', $insert_data['id']);
            $result = $builder->set($insert_data)->update();
        } else {
            $result = $builder->set($insert_data)->insert();
        }

        return ($result) ? true : false;
    }

    protected function get_fields($block_model = false)
    {
        if ($block_model) {
            return ($block_model->fields) ? (json_decode($block_model->fields) ?? []) : [];
        } else {
            return ($this->block_model->fields) ? (json_decode($this->block_model->fields) ?? []) : [];
        }
    }

    protected function get_block_by_class()
    {
        $builder = $this->db->table('cms_block');
        $query   = $builder->where('class', $this->block_class)->get();
        $result = $query->getFirstRow();

        return ($result) ? $result : [];
    }

    protected function get_block_by_id($id)
    {
        $builder = $this->db->table('cms_block');
        $query   = $builder->where('id', $id)->get();
        $result = $query->getFirstRow();

        return ($result) ? $result : [];
    }

    protected function get_block_params($block_model = false)
    {
        if ($block_model) {
            return ($block_model->params) ? json_decode($block_model->params) : [];
        } else {
            return ($this->block_model->params) ? json_decode($this->block_model->params) : [];
        }
    }

    protected function get_page_block($id)
    {
        $builder    = $this->db->table('cms_page_block');
        $query  = $builder->where('id', $id)->get();
        $result =  $query->getFirstRow();

        return ($result) ? $result : [];
    }

    protected function get_params_values($id)
    {
        $page_block = self::get_page_block($id);

        return ($page_block) ? json_decode($page_block->params_values) : [];
    }

    protected function get_static_block($caller = false, $language = false)
    {
        $builder    = $this->db->table('cms_static_block');
        if ($caller && ($caller == 'view' || $caller == 'prepare_view' || $caller == 'process_post_data' || $caller == 'contact')) {
            $builder->where('language', $this->site_lang);
            $builder->where('domain', $this->site_domain);
            $builder->where('status', 'ACTIVED');
            $builder->where('cms_block_id', $this->cms_block_id);
        } else {
            if (!$language) {
                $language = $this->get_default_language_id();
            }
            $builder->where('language', $language);
            $builder->where('domain', $this->selected_domain);
            $builder->where('cms_block_id', $this->cms_block_id);
        }
        $builder->where('class', $this->block_class);

        $query = $builder->get();
        $result = $query->getFirstRow();

        return ($result) ? $result : [];
    }

    protected function get_static_params_values($language = false)
    {
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
        $caller = isset($dbt[1]['function']) ? $dbt[1]['function'] : null;

        $static_block = self::get_static_block($caller, $language);

        return ($static_block) ? json_decode($static_block->params_values) : [];
    }

    /**
     * @param $block
     * @param $block_params
     * @param $fields_group
     * @return string
     */
    protected function get_admin_block_layout($block, $block_params, $fields_group)
    {
        $fields_group = (isset($block->id)) ? $block->id : 'new';
        $status_toggle_value = 'checked';
        if ($block) {
            $block_data = self::get_page_block($block->id);
            //$static_block = self::get_static_block();
            $status_toggle_value = ($block_data->status == 'ACTIVED') ? 'checked' : '';
        }

        $toggle_button = '<input type="checkbox" '. $status_toggle_value .' name="'. $this->block_class .'['. $fields_group .'][status]" 
            data-toggle1="toggle" data-size="mini" class="status-toggle" data-on="<i class=\'fa fa-eye\'></i>" data-off="<i class=\'fa fa-eye-slash\'></i>" 
            data-onstyle="success" >';

        $required = (isset($block_params->required) && $block_params->required) ? 'required' : '';
        $only_zero_position = (isset($block_params->only_zero_position) && $block_params->only_zero_position) ? 'only_zero_position' : '';
        $only_last_position = (isset($block_params->only_last_position) && $block_params->only_last_position) ? 'only_last_position' : '';
        $repeatable = '';//(isset($block_params->repeatable) && $block_params->repeatable == 1) ? '' : 'display: none;';

        $class  = '';
        $class .= ' '. $only_zero_position;
        $class .= ' '. $only_last_position;
        $class .= (isset($block_params->repeatable) && $block_params->repeatable == 1) ? '' : ' no_repeatable';
        $class .= ' '. $this->block_class;
        $class .= ' '. $this->block_class .'_'. $fields_group;

        // Testing Mosaic Width
        /*$styleMosaic = '';
        if (strpos($this->block_class, 'GridItem') !== false) {
            $arrayBlockname = explode('GridItem', $this->block_class);
            $width = (int)$arrayBlockname[1];
            $styleMosaic = 'display: inline-block;width: ' . ($width - 1) . '%; ';
        }*/

        $str = '
            <div class="cms-block '. $class .'" style="' . $repeatable .'" id="'. $this->block_class .'_'. $fields_group .'" 
                data-cms-block-class="'. $this->block_class .'"
                data-cms-block-id="'. $this->cms_block_id .'"            
            >';
        $move_up_button = ($only_zero_position != '' || $only_last_position != '') ? ''
            : '<button type="button" class="btn btn-box-tool block-move-up" title="Mover arriba"><i class="fa fa-arrow-up"></i></button>';
        $move_down_button = ($only_zero_position != '' || $only_last_position != '') ? ''
            : '<button type="button" class="btn btn-box-tool block-move-down" title="Mover abajo"><i class="fa fa-arrow-down"></i></button>';
        $trash_button = ($required != '') ? ''
            : '<button type="button" class="btn btn-box-tool block-delete" title="Eliminar" ><i class="fa fa-trash"></i></button>';

        $str .= '<div class="box collapsed-box">
                <div class="box-header with-border">
                    <h3 class="box-title" data-widget="collapse">'. $block_params->title .'</h3>
                    <div class="box-tools pull-right">
                        '. $move_up_button .'
                        '. $move_down_button .'
                        '. $trash_button .'
                        <button type="button" class="btn btn-box-tool block-append" 
                            data-block-id="'. $this->block_class . '_' . $fields_group . '" title="Utilizar"><i class="fa fa-puzzle-piece"></i></button>
                        <button type="button" class="btn btn-box-tool" 
                            data-widget="collapse" title="Mostrar más"><i class="fa fa-plus"></i></button>
                        '. $toggle_button .'
                    </div>
                </div>
                <div class="box-body">
                    <p class="lead">'. $block_params->description .'</p>';
        if (isset($block_params->preview)) {
            $str .= '<a href="'. base_url($block_params->preview) .'" class="image_url image_preview">
                <img loading="lazy" class="thumbnail img-responsive" style="display:inline" src="'. base_url($block_params->preview) .'?'. time() .'" />
            </a>';
        }
        $str .= '<div class="admin-form well">';

        return $str;
    }

    protected function get_admin_block_layout_end()
    {
        return '</div><!-- end admin-form--></div><!-- end box-body--></div><!-- end box--></div><!-- end cms block-->';
    }

    protected function get_related_data($related_table, $language, $ids = null)
    {
        if (!$related_table || $related_table == '') {
            return [];
        }
        if (!$this->admin_general_model->table_exists($related_table)) {
            list(, $related_table, ) = explode("_", $related_table);
            $related_table = 'dynamic_'. $related_table;
        }

        $search_language = $language ?? $this->get_default_language_id();

        if (strpos($related_table, 'dynamic_gallery') !== false) {
            $related_table = 'dynamic_gallery';
        }
        if ($related_table == 'dynamic_gallery') {
            $search_language = self::get_default_language_id();
        }


        $builder    = $this->db->table($related_table);
        $builder->orderBy('text_title');
        $builder->select('*, "'. str_replace('dynamic_', '', $related_table) .'" as related_table');
        if ($ids) {
            $builder->whereIn('id', explode(',', $ids));
            $builder->where('language', $search_language);
            $builder->where('domain', $this->selected_domain);
        } else {
            $builder->where('language', $search_language);
            $builder->where('domain', $this->selected_domain);
        }
        $builder->where('status', 'ACTIVED');

        $query = $builder->get();

        return $query->getResult();
    }

    private function get_default_language_id()
    {
        $builder = $this->db->table('languages');
        $query   = $builder->select('id')->where('default', '1')->get();
        $results = $query->getResult();
        if (empty($results))
            return false;
        $row = $query->getFirstRow();
        return $row->id;
    }

    /**
     * @param $block
     * @param $post_data
     */
    public function process_post_data($block, $post_data)
    {

    }

    /**
     * @param $block
     * @param $ajax_data
     */
    public function ajax_data($block, $ajax_data)
    {

    }
}
