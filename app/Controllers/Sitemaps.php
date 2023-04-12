<?php

namespace App\Controllers;

use App\Models\Sitemap_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Sitemaps extends BaseController
{
    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
	}

    public function index()
    {
        header('Content-Type: application/xml');
        //echo $this->getContent('https://dashboard.bouquetdata.com/sitemap/get_sitemap_by_url?url=' . base_url());

        $this->sitemap_model = new Sitemap_model();
        $this->sitemap_model->generate_sitemap($this->data['language_default'], $this->data['languages'], $this->site_domain, $this->site_preferences);

        return true;
    }

    function getContent($url){

        try {
            $crl = curl_init($url);
            curl_setopt_array($crl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_POST => 1,
                    CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
                    CURLOPT_FOLLOWLOCATION => 1
                )
            );
            if(!($html = curl_exec($crl))) throw new Exception();
            curl_close($crl);
            return $html;

        } catch(Exception $e){
            return FALSE;
        }
    }

}
