<?php

namespace App\Controllers;

use App\Models\User_general_model;
use App\Models\User_language_model;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Helpers
        helper ('funcaux');
        helper ('mobiledetect');
        helper('url');
        helper('form');
        helper('string');
        helper('text');

        // Models
        $this->user_general_model = new User_general_model ();
        $this->user_language_model = new User_language_model ();

        // Settings
        if (in_array($_SERVER['HTTP_HOST'], config('App')->allowedDomains, TRUE)) {
            $this->domain = $_SERVER['HTTP_HOST'];
        } else {
            $this->domain = config('App')->defaultDomain;
        }
        define('THEME_PATH', 'assets/themes/'. $this->domain .'/');


        /* It was made in .htaccess */
        if (mb_strpos( $_SERVER['REQUEST_URI'],'/index.php') !== FALSE) {
            return redirect()->to(base_url() . str_replace('/index.php','', $_SERVER['REQUEST_URI']));
        }

        $this->mobile_detect = new \Mobile_Detect();
        $this->data['mobile_detect'] = $this->mobile_detect;

        $this->form_validation = \Config\Services::validation();
        /*
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        */

        $this->data['language_default'] = $this->user_language_model->get_default_language_id();
        $this->data['languages'] = indexar_array($this->user_language_model->get_all(array('actived' => 1)), 'id');

        //Website domain short variable
        $this->data['site_domain'] = $this->site_domain = $this->domain;
        \Config\Services::session()->set('site_domain', $this->site_domain);
		
        //Website preferences short variable
        $this->data['site_preferences'] = $this->site_preferences = $this->user_general_model->get_domain_preferences($this->site_domain);

        $new_array_lang = explode(',', $this->site_preferences->selected_languages);

        $empty_array = array();

        foreach($this->data['languages'] as $lang) {
            if(in_array($lang->id, $new_array_lang, true)) {
                $empty_array[$lang->id] = $lang;
            }
        }

        $this->data['languages'] = $empty_array;

        $urlLang = $this->request->uri->getSegment(1);

        $this->uri_string = implode('/',$this->request->uri->getSegments());

        # Si pasan el slug del idioma principal se redirecciona a la misma url sin el idioma (exepts language switcher)
        if ($urlLang == $this->data['language_default'] && get_called_class() != 'Language') {
            $segments = $this->request->uri->getSegments();
            unset($segments[0]);
            $this->uri_string = ($this->uri_string == $this->data['language_default']) ? '' : implode('/', $segments);
            return redirect()->to(base_url($this->uri_string));
        }

        # Detect user language
        $user_locale = locale_accept_from_http(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : $this->data['language_default']);
        $user_lang = substr($user_locale, 0, 2);

        # Datos del idioma
        if (isset($this->data['languages'][$urlLang])) {
            if (!\Config\Services::session()->get('site_lang') && ($urlLang != $user_lang)) {
                if (isset($this->data['languages'][$user_lang])) {
                    \Config\Services::session()->set('REAL_REFERER', current_url());
                    return redirect()->to(base_url($this->data['languages'][$user_lang]->id .'/change-language'));
                }
            }

            \Config\Services::session()->set('slug_lang', $urlLang . "/");
            \Config\Services::session()->set('site_lang', $this->data['languages'][$urlLang]->id);
            \Config\Services::session()->set('locale_lang',  $this->data['languages'][$urlLang]->locale);
            \Config\Services::language()->setLocale( $this->data['languages'][$urlLang]->text_lang);
            $segments = $this->request->uri->getSegments();
            unset($segments[0]);
            $this->uri_string = ($this->uri_string == $this->data['language_default']) ? '' : implode('/', $segments);
        } else {
            if (!\Config\Services::session()->get('site_lang') && ($this->data['language_default'] != $user_lang)) {
                if (isset($this->data['languages'][$user_lang])) {
                    \Config\Services::session()->set('REAL_REFERER', current_url());
                    return redirect()->to(base_url($this->data['languages'][$user_lang]->id .'/change-language'));
                }
            }

            \Config\Services::session()->set('slug_lang', '');
            \Config\Services::session()->set('site_lang', $this->data['language_default']);
            \Config\Services::session()->set('locale_lang', $this->data['languages'][$this->data['language_default']]->locale);
            \Config\Services::language()->setLocale( $this->data['languages'][$this->data['language_default']]->text_lang);
        }

        //User language short variable
        $this->data['site_lang'] = $this->site_lang = \Config\Services::session()->get('site_lang');
        $this->slug_lang = \Config\Services::session()->get('slug_lang');

        //Website domain short variable


        if ($urlLang && mb_strlen($urlLang) == 0 && $this->site_lang != $this->data['language_default']) {
            return redirect()->to(base_url($this->site_lang));
        }

        setlocale(LC_TIME, str_replace('-', '_', $this->data['languages'][$this->site_lang]->locale));

        $status = (\Config\Services::session()->get('userdata')) ? [] : ['status' => 'ACTIVED'];
        $this->data['pages'] = $this->user_general_model->get_dynamic_table_data([
            'slug' => 'pages',
            'language' => $this->site_lang,
            'order' => 'position ASC',
            'domain' => $this->site_domain,
            'conditions' => $status,
        ]);
        $this->data['pages'] = indexar_array($this->data['pages'], 'id');

        $this->data['dynamic_images'] = $this->user_general_model->get_dynamic_table_data([
            'slug' => 'images',
            'language' => $this->site_lang,
            'order' => 'position ASC',
            'domain' => $this->site_domain,
        ]);
        $this->data['dynamic_images'] = indexar_array($this->data['dynamic_images'], 'id');
    }

    public function content_replace($html, $gallery = [])
    {
        #search and replace images: set image url, alt, width, height
        if (mb_strpos($html,'##URL_IMG_') !== FALSE) {
            foreach($this->data['dynamic_images'] as $image_id => $image) {
                $url = '"'. base_url($image->url) .'"';
                if ($image->is_hide_seo != 1) {
                    if ($image->image_width && $image->image_height) {
                        $url .= ' width="'. $image->image_width .'" ';
                        $url .= ' height="'. $image->image_height .'" ';
                    }
                    $url .= ' alt="'. $image->text_alt .'" ';
                    $url .= ' title="'. $image->text_title .'" ';
                }

                $html = str_replace('"##URL_IMG_'. (strval($image->id)) .'##"', $url, $html);
                $html = str_replace('##URL_IMG_'. (strval($image->id)) .'##', $url, $html);
            }
        }

        #search and replace urls: set page url
        if (mb_strpos($html,'##URL_PAG_') !== FALSE) {
            foreach($this->data['pages'] as $key => $page_data) {
                $url  = ($this->site_lang != $this->data['language_default']) ? base_url($this->site_lang) .'/' : base_url();
                $url .= (isset($this->site_preferences->home_page_id) && $page_data->id == $this->site_preferences->home_page_id) ? '' : $page_data->uri_string;
                $html = str_replace('##URL_PAG_'. (strval($page_data->id)) .'##', $url, $html);
            }
        }

        #search and replace motor url
        $reserve_link = '#';
        if (isset($this->site_preferences->booking_engine_url)) {
            $reserve_link = $this->site_preferences->booking_engine_url;
            if (isset($this->data['languages'][$this->site_lang])) {
                $reserve_link .= '&locale='. str_replace('_', '-', $this->data['languages'][$this->site_lang]->locale);
            }
        }
        if (mb_strpos($html,'"##MOTOR##"') !== FALSE) {
            $html = str_replace('"##MOTOR##"','"' . $reserve_link . '"' . " target = \"_blank\"", $html);
        } else {
            $html = str_replace('##MOTOR##', $reserve_link, $html);
        }

        $html = str_replace('##EMAIL_HOTEL##', $this->site_preferences->hotel_email, $html);
        $html = str_replace('##EMAIL_RESTAURANTE##', $this->site_preferences->restaurant_mail, $html);
        $html = str_replace('##SITE_LANG##', $this->site_lang, $html);

        return $html;
    }

    #add custom meta data
    protected function add_custom_meta($text_page_title, $text_meta_keywords, $text_meta_description, $text_meta_robots, $image_principal, $custom_html)
    {
        if (!isset($this->data['page']) || !is_object($this->data['page']))
            $this->data['page'] = new \stdClass();
        $this->data['page']->text_page_title = $text_page_title;
        $this->data['page']->text_title = $text_page_title;
        $this->data['page']->text_meta_keywords = $text_meta_keywords;
        $this->data['page']->text_meta_description = $text_meta_description;
        $this->data['page']->text_meta_robots = $text_meta_robots;
        $this->data['page']->image_principal = $image_principal;
        $this->data['page']->custom_html = $custom_html;
    }
}
