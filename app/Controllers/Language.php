<?php
namespace App\Controllers;

use App\Models\Sitemap_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Language extends BaseController
{
    public $previous_language;
	
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        $this->redirect = parent::initController($request, $response, $logger);

		if ($this->redirect) return $this->redirect;
    }

    public function change()
    {
		if ($this->redirect) return $this->redirect;
		
        $slug_lang = ($this->site_lang == $this->data['language_default']) ? '' : $this->site_lang;

        if (\Config\Services::session()->get('REAL_REFERER')) {
            $referer = \Config\Services::session()->get('REAL_REFERER');
            \Config\Services::session()->set('REAL_REFERER', null);
        } else {
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
        }
		
        if (!$referer || $referer == base_url().'/' || stristr($referer, base_url().'/') === FALSE) {
            return redirect()->to(base_url($slug_lang .'/'));
        }

        $uri = str_replace(base_url().'/', '', $referer);
        $segments = explode("/", $uri);
        $segment   = $segments[0];
        $filter    = (isset($segments[1])) ? $segments[1] : '';

        if (array_key_exists($segment, $this->data['languages']) && $filter == '') {
            //Home url looks like www.com/es
            return redirect()->to(base_url($slug_lang .'/'));
        } elseif (array_key_exists($segment, $this->data['languages'])) {
            $this->previous_language = $segment;
            $previous_page_uri_string = implode('/', array_slice($segments, 1));
        } else {
            $this->previous_language = $this->data['language_default'];
            $previous_page_uri_string = implode('/', $segments);
        }
        if (str_replace($this->previous_language, '', $referer) == base_url().'/') {
            return redirect()->to(base_url($slug_lang .'/'));
        }

        $status = (\Config\Services::session()->get('userdata') ? '%' : 'ACTIVED');
		$previous_page = $this->user_general_model->get_previous ($status, urldecode($previous_page_uri_string), $this->previous_language, $this->site_domain);
		
        if ($previous_page) {
            $new = $this->user_general_model->get_dynamic_table_data([
                'slug' => 'pages',
                'language' => $this->site_lang,
                'id' => $previous_page->id,
                'domain' => $this->site_domain,
            ]);

            /* If there is no translation, redirects to home in new language */
            if (!$new) {
                return redirect()->to(base_url($slug_lang .'/'));
            }
            if (isset($new[0])) {
                $new = $new[0];
            }
            return redirect()->to(base_url($slug_lang .'/'. $new->uri_string));
        } else {
            /* Homepage without translation */
            if ($segment == '') {
                return redirect()->to(base_url());
            }

            /* Did`t found any page, try if it is a container */
            if ( ($dynamic_page_data = ($this->user_general_model->checkContainer($previous_page_uri_string, $this->previous_language, false, $this->site_domain))) !== false /*|| $this->user_general_model->checkRootRepresentative($this->uri_string) == true*/) {
                $new = $this->user_general_model->get_dynamic_table_data([
                    'slug' => str_replace('dynamic_', '', $dynamic_page_data['related_table']),
                    'language' => $this->site_lang,
                    'id' => $dynamic_page_data['object']->id,
                    'domain' => $this->site_domain,
                ]);
                /* If there is no translation, redirects to home in new language */
                if (!$new) {
                    return redirect()->to(base_url($slug_lang .'/'));
                }
                if (isset($new[0])) {
                    $new = $new[0];
                }
                $new_url = $this->user_general_model->get_dynamic_parent_url(str_replace('dynamic_', '', $dynamic_page_data['related_table']), $new, $this->site_lang, $this->site_domain);
                $new_url .= $new->slug;
				
                return redirect()->to(base_url($slug_lang .'/'. $new_url));
            }
        }

        /* Something else */
        return redirect()->to(base_url($slug_lang .'/'));

        return;
    }

}
