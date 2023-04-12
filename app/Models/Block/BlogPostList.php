<?php
namespace App\Models\Block;

use App\Models\BaseBlock;
use App\Models\User_general_model;

class BlogPostList extends BaseBlock
{
    function __construct()
    {
        parent::__construct(self::class);
    }

    public function prepare_view($block,$controllerData)
    {
        $data = parent::prepare_view($block,$controllerData);
        $this->user_general_model = new User_general_model ();
        $data['blogpostlist']->posts = $this->user_general_model->get_dynamic_table_data([
            'slug' => 'posts',
            'language' => $controllerData['site_lang'],
            'order' => 'id desc',
            'domain' => $controllerData['site_domain'],
            'conditions' => ['status' => 'actived'],
        ]);

        foreach ($data['blogpostlist']->posts as $post){
            $post->url = get_dynamic_container_url('posts', $post,$controllerData['site_lang'], $controllerData['site_domain']) . $post->slug;
        }

        return $data;
    }

    public function _view($block)
    {
        $this->cms_block_id = $block->cms_block_id;
        $this->block_model = self::get_block_by_id($block->cms_block_id);
        $params_values = self::get_params_values($block->id);
        $position = $params_values->position;
        $block_params = self::get_block_params($this->block_model);
        if (isset($block_params->static) && $block_params->static) {
            $params_values = self::get_static_params_values();
            /* Block was paused */
            if (empty($params_values)) {
                return [
                    'html' => null,
                    'position' => null,
                ];
            }
        }

        $container_page = $this->user_general_model->get_dynamic_table_data([
            'slug' => 'pages',
            'language' => $this->site_lang,
            'domain' => $this->site_domain,
            'conditions' => ['container_of ' => 'dynamic_posts'],
        ]);
        $root_blog_page = current($container_page);

        $posts = $this->user_general_model->get_dynamic_table_data([
            'slug' => 'posts',
            'language' => $this->site_lang,
            'order' => 'id desc',
            'domain' => $this->site_domain,
            'conditions' => ['status' => 'actived'],
        ]);

        $posts_output = [];
        foreach ($posts as $post) {
            $posts_output[] = (object) [
                'id' => $post->id,
                'text_title_web' => $post->text_title_web,
                'textarea_descripcion' => $post->textarea_descripcion,
                'textarea_descripcion_2' => $post->textarea_descripcion_2,
                'image_principal' => $post->image_principal,
                'updated_at' => $post->updated_at,
                'post_url' => get_page_href($root_blog_page) .'/'. $post->slug,
            ];
        }

        $params_values->posts = indexar_array($posts_output, 'id');

        $html = $this->load->view('block/'. strtolower(self::class), [
            strtolower(self::class) => $params_values
        ], true);

        return [
            'html' => $html,
            'position' => $position,
        ];
    }
}
