<?php
namespace App\Models;

use CodeIgniter\Model;

class Sitemap_model extends Model
{
    function generate_sitemap($default_language, $languages, $domain, $site_preferences)
    {
        $query = $this->db->query("SELECT * FROM dynamic_pages 
        WHERE uri_string NOT LIKE '//%' AND uri_string NOT like 'http://%' AND uri_string NOT LIKE 'https://%' 
        AND text_meta_robots LIKE '%index%' AND text_meta_robots NOT LIKE '%noindex%' AND status='ACTIVED' 
        AND domain = ? AND representative_of = ''", $domain);
        $results = $query->getResult();
        $pages = [];
        foreach ($results as $row) {
            $pages[$row->id][$row->language] = ($row->id != $site_preferences->home_page_id)
                ? $row->uri_string
                : '';
            /* If page is container */
            if ($row->container_of) {
				$builder = $this->db->table($row->container_of);
				$builder->where('language', $row->language);
				$builder->where('language', $domain);
				$builder->where('status','ACTIVED');
				$query = $builder->get();
                $children = $query->getResult();
                foreach ($children as $child) {
                    $pages[$row->container_of .'_'. $child->id][$child->language] = $pages[$row->id][$row->language] .'/'. $child->slug;
                }
            }
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>
                <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        foreach ($pages as $page_id => $translations) {
            foreach ($translations as $language => $translation) {
                echo "<url>\n";
                echo "<loc>". base_url((($language == $default_language) ? "" : $language . '/') . $translation) ."</loc>\n";
                echo "<changefreq>monthly</changefreq>\n";
                echo "<priority>". (($page_id == $site_preferences->home_page_id) ? "1" : "0.6") ."</priority>\n";

                foreach ($translations as $language => $alternate) {
                    if (!array_key_exists($language, $languages)) {
                        continue;
                    }
                    echo "<xhtml:link rel=\"alternate\" hreflang=\"". $language ."\" href=\"". base_url((($language == $default_language) ? "" : $language . '/')) .'/'. $alternate ."\"/>\n";
                }
                echo "</url>\n";
            }
        }

        echo '</urlset>';
    }

    /**
     * Generate canonical, alternate`s
     * @param $language
     * @param $default_language
     * @param $languages
     * @param $type
     * @param int $id
     * @return array
     */
    public function get_alternate($language, $default_language, $languages, $type, $id = 0, $home_page_id = 0, $site_domain = 0)
    {
        $return = ['canonical' => '', 'alternate' => []];
        $data = [];

        switch ($type) {
            case 'dynamic_pages':
                $builder    = $this->db->table('dynamic_pages');
                $query = $builder->where(['id' => $id])->get();
                $results = $query->getResult();
                foreach ($results as $row) {
                    $data[$row->language] = ($id != $home_page_id) ? $row->uri_string : '';
                }
                break;
            default:
                $this->user_general_model = new User_general_model ();
                $container_pages = $this->user_general_model->get_dynamic_table_data([
                    'slug' => 'pages',
                    'domain' => $site_domain,
                    'conditions' => ['container_of ' => $type],
                ]);
                if(empty($container_pages)) {
                    return $return;
                }
                $container_pages_translations = [];
                foreach ($container_pages as $container_page) {
                    //$container_pages_translations[$container_page->language] = $container_page->uri_string;
                    $container_pages_translations[$container_page->language] = $this->user_general_model->get_dynamic_container_url_v2(
                        substr($type, 8),
                        $container_page,
                        $container_page->language,
                        $site_domain
                    );
                }

                $builder    = $this->db->table($type);
                $query = $builder->where(['id' => $id])->get();
                $results = $query->getResult();
                foreach ($results as $row) {
                    if (isset($container_pages_translations[$row->language])) {
                        $data[$row->language] = $container_pages_translations[$row->language] . $row->slug;
                    }
                }
                break;
        }

        if (!isset($data[$language])) {
            return $return;
        }
        $return['canonical'] = base_url((($language != $default_language) ? $language . '/' : '') . $data[$language]);
        foreach ($languages as $lang) {
            /* Selected language should be last an it is fixed in layout_head */
            if (isset($data[$lang->id]) && $lang->id != $language) {
                $return['alternate'][$lang->id] = base_url((($lang->id != $default_language) ? $lang->id . '/' : '') . $data[$lang->id]);
            }
        }

        return $return;
    }
}
