<?php
namespace App\Models;

use CodeIgniter\Model;

class Cms_block_model extends Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * Get all available blocks to  show it in page constructor panel
     * Blocks can be assigned in domain preferences page
     * @return array
     */
    public function get_available_blocks()
    {
        $blocks_to_output = [];
        $builder = $this->db->table('cms_block');
        $query   = $builder->whereIn('id', explode(',', $this->selected_domain_preferences->blocks))->get();
        $blocks = $query->getResult();
        foreach ($blocks as $block) {
            $block_params = json_decode($block->params);
            $block_model = $block->class;
            $this->load_model ($block_model);
            $blocks_to_output[$block_params->title .'_'. $block->id] = $this->$block_model->admin_form(null, $block);
        }
        ksort($blocks_to_output);

        return $blocks_to_output;
    }
	
    public function get_required_blocks()
    {
        $blocks_to_output = [];
        $builder = $this->db->table('cms_block');
        $query   = $builder->whereIn('id', explode(',', $this->selected_domain_preferences->blocks))->get();
        $blocks = $query->getResult();
		$sorted_blocks = [];
        foreach ($blocks as $block) {
            $block_params = json_decode($block->params);
			if (isset($block_params->required) && $block_params->required && $block_params->only_zero_position) {
				$sorted_blocks[] = $block;
			}
        }
        foreach ($blocks as $block) {
            $block_params = json_decode($block->params);
			if (isset($block_params->required) && $block_params->required && !$block_params->only_zero_position && !$block_params->only_last_position) {
				$sorted_blocks[] = $block;
			}
        }
        foreach ($blocks as $block) {
            $block_params = json_decode($block->params);
			if (isset($block_params->required) && $block_params->required && $block_params->only_last_position && !$block_params->only_zero_position) {
				$sorted_blocks[] = $block;
			}
        }
        foreach ($sorted_blocks as $block) {
            $block_params = json_decode($block->params);
			$block_model = $block->class;
			$this->load_model ($block_model);
			$blocks_to_output[$block_params->title .'_'. $block->id] = $this->$block_model->admin_form(null, $block).'<input type="hidden" name="'.$block->class.'[new][add]">';
        }
        return $blocks_to_output;
    }

    public function get_static_block_status($class, $domain)
    {
        $query = $this->db->get_where('cms_static_block', ['class' => $class, 'domain' => $domain]);
        $result = $query->row();

        return ($result) ? $result->status : null;
    }

    public function get_static_block_translations($class, $domain)
    {
        $query = $this->db->get_where('cms_static_block', ['class' => $class, 'domain' => $domain]);
        $result = $query->result();

        return ($result) ? $result : [];
    }

    public function get_static_block_translations_by_id($cms_block_id, $domain)
    {
        $builder    = $this->db->table('cms_static_block');
        $query = $builder->where(['cms_block_id' => $cms_block_id, 'domain' => $domain])->get();
        $result = $query->getResult();

        return ($result) ? $result : [];
    }

    public function get_assigned_blocks($page_id, $content_type, $language, $domain)
    {
        $builder    = $this->db->table('cms_page_block');
        $query  = $builder->where('page_id', $page_id)->where('content_type', $content_type)
                  ->where('language', $language)->where('domain', $domain)->get();
        $blocks_to_output = [];
        foreach ($query->getResult() as $block) {
            $position = json_decode($block->params_values);
            $position = ($position->position) ? $position->position : 0;
            while (isset($blocks_to_output[$position])) {
                $position++;
            }

            if (!$this->is_model_loaded($block->class)) {
                continue;
            }
            $blocks_to_output[$position] = $this->{$block->class}->admin_form($block, null, $language);
        }
        ksort($blocks_to_output);

        return $blocks_to_output;
    }

    public function save_page_blocks($page_id, $content_type, $request)
    {
        $this->request = $request;
        $original_language_blocks = $this->request->getPost('original_language_blocks');


        $builder = $this->db->table('cms_block');
        $query   = $builder->select('class')->groupBy("class")->get();

        foreach ($query->getResult() as $block) {
            $block_model = $block->class;
            $form_data[$block_model] = $this->request->getPost($block_model);

            if (isset($form_data[$block_model])) {
                $this->load_model($block_model);
                foreach ($form_data[$block_model] as $block_id => $block) {
                    if (stristr($block_id, 'new') !== FALSE) {
                        $data_filled = (isset($block['add'])) ? true : false;
                        if (!$data_filled) {
                            continue;
                        }
                    }
                    //Si existe el parametro copy ID = NULL
                    $this->$block_model->set_page_block([
                        'id' => (stristr($block_id, 'new') !== FALSE || isset($original_language_blocks) || isset($_POST['copy'])) ? 'null' : $block_id,
                        'original_id' => (isset($original_language_blocks)) ? $block_id : 'null',
                        'class' => $block_model,
                        'page_id' => $page_id,
                        'content_type' => $content_type,
                        'language' => $this->request->getPost('language'),
                        'domain' => $this->request->getPost('domain'),
                        'status' => $this->request->getPost('status'),
                        'form_data' => $block,
                        'form_id' => $block_id,
                    ],$this->selected_domain_preferences);
                }
            }
        }
    }

    public function delete_assigned_cms_page_blocks($page_id, $content_type, $domain)
    {
        $builder = $this->db->table('cms_page_block');
        $result   = $builder->where( ['page_id' => $page_id, 'content_type' => $content_type, 'domain' => $domain] )->delete();

        return ($result) ? true : false;
    }

    public function get_block_by_class($class)
    {
        $builder = $this->db->table('cms_block');
        $query   = $builder->where('class', $class)->get();
        $result = $query->getFirstRow();

        return ($result) ? $result : [];
    }

    public function get_block_by_id($id)
    {
        $builder = $this->db->table('cms_block');
         $query   = $builder->where('id', $id)->get();
        $result = $query->getFirstRow();

        return ($result) ? $result : [];
    }
    public function get_block_by_ids($id)
    {
        $builder = $this->db->table('cms_block');
        $query   = $builder->whereIn('id', $id)->get();
        $result = $query->getResult ();

        return ($result) ? $result : [];
    }

    /**
     * @param $class
     * @return bool
     */
    public function save_new_block_class($class)
    {
        if (file_exists(APPPATH .'Models/Block/'. ucfirst($class) .'.php')) {
            return false;
        }
        if (!is_dir(APPPATH .'Models/Block')) {
            mkdir(APPPATH . 'Models/Block', 0777);
        }
        if (!is_dir(APPPATH .'Views/block')) {
            mkdir(APPPATH . 'Views/block', 0777);
        }

        $fw = fopen(APPPATH .'Models/Block/'. ucfirst($class) .'.php','w');
        fwrite($fw, self::class_template($class));
        fclose($fw);
        if (!file_exists(APPPATH .'Models/Block/'. ucfirst($class) .'.php')){
            return false;
        }

        $fw = fopen(APPPATH .'Views/block/'. strtolower($class) .'.php','w');
        fwrite($fw, self::view_template($class));
        fclose($fw);
        if (!file_exists(APPPATH .'Views/block/'. strtolower($class) .'.php')) {
            return false;
        }

        return true;
    }

    /**
     * @param $class
     * @return string
     */
    private function class_template($class)
    {
        return '<?php '. PHP_EOL .
            'namespace App\Models\Block;'. PHP_EOL .
            'use App\Models\BaseBlock;'. PHP_EOL .
            'class '. ucfirst($class) .' extends BaseBlock'. PHP_EOL .
            '{'. PHP_EOL .
            '    function __construct() '. PHP_EOL .
            '    {'. PHP_EOL .
            '        parent::__construct(self::class);'. PHP_EOL .
            '    }'. PHP_EOL .
            '}'. PHP_EOL;
    }

    /**
     * @param $class
     * @return string
     */
    private function view_template($class)
    {
        return '<?php '. PHP_EOL .
            '/**'. PHP_EOL .
            ' * @var $'. strtolower($class) . PHP_EOL .
            ' */'. PHP_EOL .
            '?>'. PHP_EOL;
    }

    private function is_model_loaded($block_model) {
        return isset ($this->$block_model);
    }
    public function load_model($block_model) {
        $namespace_model = "App\\Models\\Block\\".$block_model;
        $this->$block_model = new $namespace_model ();
        $this->$block_model->domain = $this->domain;
        $this->$block_model->site_lang = \Config\Services::session()->get('site_lang');
        if(property_exists ($this,'site_domain'))$this->$block_model->site_domain = $this->site_domain;
        $this->$block_model->selected_domain = $this->selected_domain;
        $this->$block_model->selected_domain_preferences = $this->selected_domain_preferences;
        $this->$block_model->request = $this->request;
        if(property_exists ($this,'data'))$this->$block_model->data = $this->data;
        return $this->$block_model;
    }
    public function get_cms_blocks () {
        $builder    = $this->db->table('cms_block');
        return $builder->get()->getResult();
    }
    public function update_cms_block ($id, $data) {
        $builder    = $this->db->table('cms_block');
        return $builder->set($data)->where('id', $id)->update();
    }
    public function insert_cms_block ($id, $data) {
        $builder    = $this->db->table('cms_block');
        return $builder->set($data)->where('id', $id)->insert();
    }

    public function get_page_blocks($blocks_page_id, $content_table, $lang, $domain) {
        $builder    = $this->db->table('cms_page_block');
        return $builder->where(['page_id' => $blocks_page_id, 'content_type' => $content_table, 'language' => $lang, 'domain' => $domain, 'status' => 'ACTIVED'])->get()->getResult();
    }
    public function get_page_class_block($blocks_page_id, $class, $lang, $domain) {
        $builder    = $this->db->table('cms_page_block');
        return $builder->where(['page_id' => $blocks_page_id, 'class' => $class, 'language' => $lang, 'domain' => $domain, 'status' => 'ACTIVED'])->get()->getFirstRow();
    }
	/**
     * Get all available blocks to show it in page constructor panel
     * Blocks can be assigned in domain preferences blocks page
     * @return array
     */
    public function get_available_blocks_by_preferences($domain, $type)
    {
        $blocks_to_output = [];

        $builder = $this->db->table('preferences_blocks');
        $builder->join('cms_block', 'cms_block.id = preferences_blocks.cms_block_id');
        $builder->where('domain',$domain)->where('content_type',$type);
        $builder->where('preferences_blocks.status','ACTIVED');
        $builder->groupBy('preferences_blocks.cms_block_id');
        $query = $builder->get();
        $blocks = $query->getResult();

        foreach ($blocks as $block) {
            $block_params = json_decode($block->params);
            $block_model = $block->class;
            $this->load_model ($block_model);
            $blocks_to_output[$block_params->title .'_'. $block->id] = $this->$block_model->admin_form(null, $block);
        }
        ksort($blocks_to_output);

        return $blocks_to_output;
    }
}
