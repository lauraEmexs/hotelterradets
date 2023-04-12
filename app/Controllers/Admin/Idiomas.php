<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\Admin_dictionary_model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;

class Idiomas extends BaseAdminController
{
	public $data;


    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

		$this->admin_dictionary_model = new Admin_dictionary_model ();

        if (!\Config\Services::session()->get('userdata')) {
            header('Location: /admin/auth');
            die ();
        }

		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Pragma: no-cache"); // HTTP/1.0

		$this->data['slug'] = 'idiomas';
		$this->data['menu'] = 'idiomas';
		$this->data['menu2'] = 'idiomas';
		$this->data['page_title'] = 'Idiomas';
		$this->data['languages'] = $this->admin_language_model->get_all(array('actived'=>'1') );
		$this->data['selected_language'] =  'en';
		$this->data['dynamic_elements']=$this->admin_general_model->get_dynamic_elements();
		$this->data['related_elements']=$this->admin_general_model->get_related_elements();
		$this->data['admin_menu_items'] = $this->admin_general_model->get_admin_menu_items();

		$this->domain_url_params = '';
		if (isset($_GET['domain']) && in_array($_GET['domain'], $this->config->item('allowed_domains'), TRUE)) {
			$this->data['domain'] = $_GET['domain'];
			$this->selected_domain = $_GET['domain'];
			if (count($this->config->item('allowed_domains')) > 1) {
				$this->domain_url_params = '?domain='. $_GET['domain'];
			}
		} else {
			$this->data['domain'] = $this->domain;
			$this->selected_domain = $this->domain;
		}
	}

	public function change_default(){
		$this->data['page_title'] = 'Configuración de idiomas';
		$this->data['title_section'] = 'Cambiar idioma predeterminado';
		$this->data['menu2'] = 'idiomas_default';
		return view('admin/idiomas_default', $this->data);
	}

	public function update_default(){
		$this->admin_language_model->set_default_language($this->request->getPost('predeterminado'));
		return redirect()->to('admin/idiomas');
	}

	public function index()
	{
		return redirect()->to('/admin/idiomas/listado');
	}

	public function listado()
	{
		$this->data['slug'] = 'idiomas_activos';
		$this->data['page_title'] = 'Configuración de idiomas';
		$this->data['title_section'] = 'Idiomas activos';
		$this->data['menu2'] = 'idiomas_activos';
		$this->data['languages'] = $this->admin_language_model->get_all();
		$pages = $this->admin_general_model->get_dynamic_table_data([
			'slug' => 'pages',
			'language' => $this->admin_language_model->get_default_language_id(),
			'domain' => $this->data['domain'],
			'order' => 'text_title'
		]);
		$this->data['all_pages'] = ['0' => '-'];
		foreach ($pages as $page) {
			$this->data['all_pages'][$page->id] = $page->text_title;
		}

		return view('admin/idiomas_list', $this->data);
	}

	public function update_actived(){
		$this->admin_language_model->set_actived_language($this->request->getPost('actived'));
		$this->admin_language_model->set_actived_web_language($this->request->getPost('actived_web'));
		return redirect()->to('/admin/idiomas/listado');
	}

	public function dictionary()
	{
		$this->data['slug'] = 'idiomas_diccionarios';
		$this->data['page_title'] = 'Traducciones del diccionarios';
		$this->data['title_section'] = 'Diccionarios';
		$this->data['menu2'] = 'idiomas_diccionarios';

		$dictionary= $this->admin_dictionary_model->get_all($this->data['languages']);
		$all_keys=$this->admin_dictionary_model->get_all_keys($this->data['languages']);
		ksort($all_keys);

		$this->data['dictionary']=$dictionary;
		$this->data['all_keys']=$all_keys;

		//echo "<pre>".print_r($dictionary,1)."</pre>";
		//echo "<pre>".print_r($all_keys,1)."</pre>"; die();

		return view('admin/dictionary', $this->data);
	}

	public function dictionary_save(){
		$language = $this->request->getPost('klang');
		$key = $this->request->getPost('kkey');
		$value = $this->request->getPost('kvalue');

		/*
		$dictionary= $this->admin_dictionary_model->get_all($this->data['languages']);
		$all_keys=$this->admin_dictionary_model->get_all_keys($this->data['languages']);
		$dictionary[$language][$key]=$value;
		$this->admin_dictionary_model->actualizar($dictionary,$all_keys);
		*/

		$lang = new \stdClass();
		$lang->id = $language;

		$dictionary = $this->admin_dictionary_model->get_all(array($lang));
//        $all_keys = $this->admin_dictionary_model->get_all_keys(array($lang));
		$all_keys=$this->admin_dictionary_model->get_all_keys($this->data['languages']);
		$dictionary[$language][$key] = $value;
//        print_r($dictionary);die;
		$this->admin_dictionary_model->actualizar($dictionary, $all_keys);

		$status = array('status'=>'true');
		echo json_encode($status) ;
	}

	public function expimp()
	{
		if ($this->request->getPost('export')) {
			$dictionary = $this->admin_dictionary_model->get_all($this->data['languages']);
			$all_keys = $this->admin_dictionary_model->get_all_keys($this->data['languages']);
			ksort($all_keys);

			$spreadsheet = new Spreadsheet();

			foreach ($this->data['languages'] as $index => $language) {
				$myWorkSheet = new Worksheet($spreadsheet, ucfirst($language->name));
				$spreadsheet->addSheet($myWorkSheet, $index);
				$sheet = $spreadsheet->setActiveSheetIndex($index);
				$header = ['Key', 'String'];
				$sheet->fromArray($header, null, 'A1');

				$result_data = [];
				foreach($all_keys as $key => $kk) {
					if ($key == 'theme_' || $key == '') continue;
					$result_data[] = [$key, (isset($dictionary[$language->id][$key])) ? htmlspecialchars($dictionary[$language->id][$key]) : ''];
				}

				$sheet->fromArray($result_data, null, 'A2');
				$sheet->getColumnDimension('A')->setWidth(20);
				$sheet->getColumnDimension('B')->setWidth(100);
			}

			$writer = new Xlsx($spreadsheet);

			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="diccionario_'. date('Y-m-d h:i:s') .'.xlsx"');
			header('Cache-Control: max-age=0');
			$writer->save('php://output');
			exit();
		}

		if ($this->request->getPost('import')) {
			$file = $_FILES['metas']['tmp_name'];
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			$reader->setReadDataOnly(true);
			$spreadsheet = $reader->load($file);


			foreach ($spreadsheet->getAllSheets() as $worksheet) {
				$language_name = strtolower($worksheet->getTitle());
				foreach ($this->data['languages'] as $language) {
					if (strtolower($language->name) == $language_name) {
						break;
					}
				}
				if (!$language) {
					continue;
				}
				$data = [];
				foreach($worksheet->toArray() as $index => $item) {
					if ($index == 0) continue;
					$data[$language->id][$item[0]] = $item[1] ?? '';
				}

				$all_keys = $this->admin_dictionary_model->get_all_keys($this->data['languages']);
				$this->admin_dictionary_model->actualizar($data, $all_keys);
			}

			$this->session->set_flashdata('message', '<div class="alert alert-info">Diccionario importado correctamente</div>');
			return redirect()->to('admin/idiomas/expimp'. $this->domain_url_params);
		}

		$this->data['slug'] = 'expimp';
		$this->data['page_title'] = 'Exportación\importación diccionarios';
		$this->data['title_section'] = 'Exportación\importación diccionarios';

		return view('admin/dict_exp_imp', $this->data);
	}
}
