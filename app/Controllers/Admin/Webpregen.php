<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\Auth_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Webpregen extends BaseAdminController
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

        $this->data['slug'] = 'webpregen';
        $this->data['menu'] = 'webpregen';
        $this->data['menu2'] = 'webpregen';

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
        $this->data['page_title'] = 'Regenerar webp';
        $this->data['title_section'] = 'Regenerar webp';

        $query = $this->admin_general_model->get_images();
        $this->data['images_cnt'] = count($query);

        $query = $this->admin_general_model->get_assets_page_block();
        $this->data['blocks_cnt'] = count($query);

        $tables = $this->admin_general_model->get_dynamic_elements();
        $this->data['dynamic_cnt'] = 0;
        foreach ($tables as $table) {
            $columns = $this->admin_general_model->get_dynamic_table_struct($table['slug']);
            foreach ($columns as $column) {
                if (stristr($column['name'], 'image_') === FALSE) {
                    continue;
                }
                $objects = $this->admin_general_model->get_dynamic_table_data(['slug' => $table['slug']]);
                foreach ($objects as $object) {
                    $this->data['dynamic_cnt']++;
                }
            }
        }

        return view('admin/webpregen', $this->data);
    }

    public function regenerate()
    {
        $num = $this->request->getPost('num');
        $type = $this->request->getPost('type');
        $onlyNew = $this->request->getPost('onlyNew') == 1;

        switch ($type) {
            case 'default' :
                $this->regenerateCmsImages($num, $onlyNew);
                break;
            case 'block' :
                $this->regenerateBlockImages($num, $onlyNew);
                break;
            case 'dynamic' :
                $this->regenerateDynamicElementsImages($num, $onlyNew);
                break;
        }

        echo json_encode(['OK']);
        exit(0);
    }

    public function regenerateCmsImages($num, $onlyNew)
    {
        ini_set('max_execution_time', 0);

        $image = $this->admin_general_model->get_images_by_num($num);

        if (!$image) return;

        echo 'Default: image_id=' . $image->id .'<br>';

        $path_parts = pathinfo($image->url);
        if (isset($path_parts['extension']) && in_array(mb_strtolower($path_parts['extension']), ['png', 'jpg', 'jpeg'])) {
            if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($image->url, 'webp')) || $onlyNew == false) {
                $this->admin_general_model->generate_webp($image->url);
            }
            //Resize thumbnails
            foreach (['tlow', 'tmedium', 'thigh', 'tlowest'] as $size) {
                $new_file_name = str_replace(
                    $path_parts['filename'] . '.' . $path_parts['extension'],
                    $path_parts['filename'] . '.' . $size . '.' . $path_parts['extension'],
                    $image->url
                );
                if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($new_file_name, 'webp')) || $onlyNew == false) {
                    $this->admin_general_model->generate_webp($new_file_name);
                }
            }
        }
    }

    public function regenerateBlockImages($num, $onlyNew)
    {
        ini_set('max_execution_time', 0);

        $result = $this->admin_general_model->get_assets_page_block_by_num($num);
        foreach ($result as $item) {
            echo 'Block: id=' . $item->id .'<br>';
            $data = json_decode($item->params_values);
            foreach ($data as $datum) {
                $path_parts = pathinfo($datum);
                if (!isset($path_parts['extension']) || !in_array(mb_strtolower($path_parts['extension']), ['png', 'jpg', 'jpeg'])) {
                    continue;
                }
                if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($datum, 'webp')) || $onlyNew == false) {
                    $this->admin_general_model->generate_webp($datum);
                }
                //Resize thumbnails
                foreach (['tlow', 'tmedium', 'thigh', 'tlowest'] as $size) {
                    $new_file_name = str_replace(
                        $path_parts['filename'] .'.'. $path_parts['extension'],
                        $path_parts['filename'] .'.'. $size .'.'. $path_parts['extension'],
                        $datum
                    );
                    if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($new_file_name, 'webp')) || $onlyNew == false) {
                        $this->admin_general_model->generate_webp($new_file_name);
                    }
                }
            }
        }
    }

    public function regenerateDynamicElementsImages($num, $onlyNew)
    {
        ini_set('max_execution_time', 0);

        $cnt = 0;
        $tables = $this->admin_general_model->get_dynamic_elements();
        foreach ($tables as $table) {
            $columns = $this->admin_general_model->get_dynamic_table_struct($table['slug']);
            foreach ($columns as $column) {
                if (stristr($column['name'], 'image_') === FALSE) {
                    continue;
                }
                $objects = $this->admin_general_model->get_dynamic_table_data(['slug' => $table['slug']]);
                foreach ($objects as $object) {
                    if ($cnt != $num) {
                        $cnt++;
                        if ($cnt > $num) {
                            break;
                        }
                        continue;
                    }
                    /*
                     * Need to iterate every ->image_*
                     * */
                    $path_parts = pathinfo($object->{$column['name']});
                    if (!isset($path_parts['extension']) || !in_array(mb_strtolower($path_parts['extension']), ['png', 'jpg', 'jpeg'])) {
                        continue;
                    }
                    echo 'Dynamic: slug=' . $table['slug'] .', id='. $object->id .'<br>';
                    if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($object->{$column['name']}, 'webp')) || $onlyNew == false) {
                        $this->admin_general_model->generate_webp($object->{$column['name']});
                    }
                    //Resize thumbnails
                    foreach (['tlow', 'tmedium', 'thigh', 'tlowest'] as $size) {
                        $new_file_name = str_replace(
                            $path_parts['filename'] .'.'. $path_parts['extension'],
                            $path_parts['filename'] .'.'. $size .'.'. $path_parts['extension'],
                            $object->{$column['name']}
                        );
                        if (!file_exists(FCPATH . $this->admin_general_model->replace_extension($new_file_name, 'webp')) || $onlyNew == false) {
                            $this->admin_general_model->generate_webp($new_file_name);
                        }
                    }

                    $cnt++;
                }
            }
        }
    }
}
