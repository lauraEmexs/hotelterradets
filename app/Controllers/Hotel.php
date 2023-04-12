<?php

namespace App\Controllers;

use App\Models\Cms_block_model;
use App\Models\Sitemap_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Hotel extends BaseController
{

    const STATIC_ROUTES = ['show_error', 'ajax'];
    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        $this->redirect = parent::initController($request, $response, $logger);

		if ($this->redirect) return $this->redirect;

        $this->sitemap_model = new Sitemap_model();
        $this->cms_block_model = new Cms_block_model ();

        $this->cms_block_model->domain = $this->domain;
        $this->cms_block_model->selected_domain = $this->domain;
        $this->cms_block_model->selected_domain_preferences = $this->site_preferences;
        $this->cms_block_model->request = $this->request;
        $this->cms_block_model->site_lang = $this->site_lang;
        $this->cms_block_model->site_domain = $this->site_domain;
    }

    public function remap()
    {
        if($this->redirect) return $this->redirect;

        $segment = $this->request->uri->getSegment(1);
        /* In translations */
        if (mb_strlen($segment) == 2 && $this->site_lang == $segment) {
            $segment = $this->request->uri->getSegment(2);
        }

        if (in_array($segment, self::STATIC_ROUTES)) {
            return self::$segment();
        }

        $status = (\Config\Services::session()->get('userdata')) ? '%' : 'ACTIVED';
        $row = $this->user_general_model->get_home ($segment, $this->site_preferences->home_page_id, $status, urldecode($this->uri_string), $this->site_lang, $this->site_domain);
        if ($row) {
            return $this->show_by_blocks($row->id);
        } else {
            /* Homepage without translation */
            if ($segment == '') {
                return redirect()->to(base_url());
            }

            /* Did`t found any page, try if it is a container of dynamic content */
            if ( ($dynamic_page_data = ($this->user_general_model->checkContainer($this->uri_string, $this->site_lang, false, $this->site_domain))) !== false /*|| $this->user_general_model->checkRootRepresentative($this->uri_string) == true*/) {
                return $this->container_content(
                    $dynamic_page_data['related_table'],
                    $dynamic_page_data['object'],
                    $dynamic_page_data['path']
                );
            } else {
                //If we came here...404
                if ($this->site_preferences->error_page_id) {
                    return $this->show_by_blocks($this->site_preferences->error_page_id);
                }
                else {
                    return $this->show_error();
                }
            }
        }

        return true;
    }

    /**
     * This function shows every single page by its configured blocks
     * @param $page_id
     * @param string $content_table
     */
    protected function show_by_blocks($page_id, $content_table = 'dynamic_pages')
    {
        $this->data['page'] = $this->data['pages'][$page_id];
        $image_seo = $this->data['page']->image_seo;

        if (isset($this->data['child_page']) && in_array(str_replace('dynamic_', '', $content_table), config('App')->dynamicWithBlocks)) {
            $blocks_page_id = $this->data['child_page']->id;
        } elseif (isset($this->data['child_page']) && !in_array(str_replace('dynamic_', '', $content_table), config('App')->dynamicWithBlocks)) {
            $content_table_seo = $content_table;
            $content_table = 'dynamic_pages';
            $blocks_page_id = $page_id;
        } else {
            $content_table = 'dynamic_pages';
            $blocks_page_id = $page_id;
        }

        $blocks = [];

        $cms_page_block = $this->cms_block_model->get_page_blocks($blocks_page_id, $content_table, $this->site_lang, $this->site_domain);

        foreach ($cms_page_block as $block) {
            $block_model = $block->class;
            $this->$block_model = $this->cms_block_model->load_model ($block_model);
            /* Process some post data of blocks */
            if ($this->request->getPost($block_model)) {
                $result = $this->$block_model->process_post_data($block, $this->request->getPost($block_model));
            }
            /* Get view of block to output it later */
            $block_data = $this->$block_model->view($block,$this->data);
            /* Replace contents like ##MOTOR## etc */
            if (\Config\Services::session()->get('userdata')) {
                $block_data['html'] = '<!-- BLOCK_ID: '. $block->id .' BLOCK_CLASS: '. $block->class .' -->
                '. $block_data['html'];

            }
            $block_data['html'] = $this->content_replace($block_data['html']);
            $blocks[] = $block_data;

            /*
             * We need to process all blocks by its order and take any background_image
             * */
            if ($block_model == 'Header' && $block->params_values) {
                $params = json_decode($block->params_values);
                $main_image = isset($params->logo_image) ? $params->logo_image : '';
            }
        }
        $this->data['blocks'] = $blocks;


        if (isset($this->data['child_page'])) {
            $this->data['page'] = $this->data['child_page'];
            //$this->data['page']->image_seo = $this->data['page']->image_principal;
        }

        if (!$image_seo && isset($this->data['child_page'])) {
            $this->data['page']->image_seo = $this->data['page']->image_principal;
        }

        if ($this->data['page']->image_seo) {
            $main_image = $this->data['page']->image_seo;
        }

        $this->data['alternate'] = $this->sitemap_model->get_alternate($this->site_lang, $this->data['language_default'], $this->data['languages'],
            isset($content_table_seo) ? $content_table_seo : $content_table, $this->data['page']->id, $this->site_preferences->home_page_id,$this->site_domain);
        $this->add_custom_meta(
            $this->data['page']->text_page_title,
            $this->data['page']->text_meta_keywords,
            $this->data['page']->text_meta_description,
            $this->data['page']->text_meta_robots,
            (isset($main_image)) ? $main_image : null,
            $this->data['page']->textarea_custom_html
        );

        return view('by_blocks', $this->data);
    }

    /**
     * A static route, to get\put any data and process it in block model
     */
    protected function ajax()
    {
        $page_id = $this->request->getGet('page_id');
        $block_model = $this->request->getGet('class');
        $output_type = $this->request->getGet('type');

        $data = [];
        $block = $this->cms_block_model->get_page_class_block($page_id, $block_model, $this->site_lang, $this->site_domain);

        if ($cms_page_block = $block) {

            $block_model = $cms_page_block->class;
            $this->$block_model = $this->cms_block_model->load_model ($block_model);
            /* Process ajax request */
            if (empty($this->request->getPost())) {
                $data = $this->request->getGet();
            } else {
                $data = $this->request->getPost();
            }
            $data = $this->$block_model->ajax_data($cms_page_block, $data);
        }

        if ($output_type == 'html') {
            return $this->response
                ->setContentType('text/html')
                ->setBody($data);
        } else {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(json_encode($data, JSON_UNESCAPED_SLASHES));
        }
    }
    protected function show_error()
    {
        $this->data['page'] = new \StdClass;
        $this->data['site_lang'] = $this->site_lang;

        $this->data['sections'] = [
            '404',
        ];
        $this->add_custom_meta(
            'Page not found',
            '',
            '',
            'noindex nofollow',
            '',
            ''
        );
        return view('by_blocks', $this->data);
    }

    /**
     * If the container is a representative of dynamic content,
     * it is necessary to associate a representative with child content not only by Slug, but also by Id
     *
     * @param $related_table
     * @param $dynamic_object
     * @param $path
     * @return bool
     */
    protected function container_content($related_table, $dynamic_object, $path)
    {
        $this->user_general_model->prepare_related_data($dynamic_object);
        $this->data['child_page'] = $dynamic_object;
        $this->data['child_page']->related_table = $related_table;

        $representative_page = $this->user_general_model->get_dynamic_table_data([
            'slug' => 'pages',
            'language' => $this->site_lang,
            'domain' => $this->site_domain,
            'conditions' => [
                'representative_of ' => $related_table
            ],
            'like'  => [
                'path' => $path .'%'
            ],
        ]);
        if (empty($representative_page)) {
            return $this->show_error();
        }

        return $this->show_by_blocks(current($representative_page)->id, $related_table);
    }

}
