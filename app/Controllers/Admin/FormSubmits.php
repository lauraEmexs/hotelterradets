<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class FormSubmits extends BaseAdminController
{
	public $data;
	public $form;
	public $selected_domain;
	public $selected_domain_preferences;
	public $domain_url_params;

	const ROWS_TO_SKIP = 3;
	const COLUMNS_TO_SHOW = 4;

	var $table_template = array(
		'table_open' => '<table class="table table-striped table-bordered table-hover" id="">',
		'heading_row_start' => '<tr>',
		'heading_row_end' => '</tr>',
		'heading_cell_start' => '<th>',
		'heading_cell_end' => '</th>',

		'row_start' => '<tr>',
		'row_end' => '</tr>',
		'cell_start' => '<td>',
		'cell_end' => '</td>',

		'row_alt_start' => '<tr>',
		'row_alt_end' => '</tr>',
		'cell_alt_start' => '<td>',
		'cell_alt_end' => '</td>',

		'table_close' => '</table>'
	);

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);


        if (!\Config\Services::session()->get('userdata')) {
            header('Location: /admin/auth');
            die ();
        }
		$this->data['filter_info'] = '';
		$this->data['idioma_original'] = $this->admin_language_model->get_default_language_id();
		$this->data['idioma_original_name'] = $this->admin_language_model->get_default_language();
        $this->data['domain_url_params'] = $this->domain_url_params;


		$this->data['slug'] = '';
		$this->data['menu'] = 'management';
		$this->data['menu2'] = '';

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
		$this->data['page_title'] = 'Registros de formularios enviados';
		$this->data['title_section'] = 'Registros de formularios enviados';

		$this->table->setTemplate($this->table_template);
		$this->table->setHeading(['Registro', 'Accion',]);
		$data = $this->admin_general_model->get_form_data_tables();

        if(isset($_GET['q']) && !empty($_GET['q'])) $data = $this->search_filter ($data);

        $this->data['count'] = count($data);
        $this->data['page'] = 1;
        if(isset($_GET['page'])) {
            $this->data['page'] = $_GET['page'];
        }
        $this->data['perpage'] = 10;
        if(isset($_GET['perpage'])) {
            $this->data['perpage'] = $_GET['perpage'];
        }
        $this->data['perpages'] = [10,25,50,100];

        $data = array_slice ( $data, ($this->data['page']-1)*$this->data['perpage'], $this->data['perpage'] );

		foreach ($data as $item) {
			$this->table->addRow(
				$item['name'],
				anchor('admin/formSubmits/list/'. $item['slug'] . $this->domain_url_params, 'Ver', '')
			);
		}

        $this->data['table'] = $this->table;

		return view('admin/form-submits-index', $this->data);
	}

	public function list($form)
	{
		$title = $this->admin_general_model->get_table_title('form_data_'. $form);
		$data_struct = $this->admin_general_model->get_table_struct('form_data_'. $form);
		$this->data['page_title'] = $title;
		$this->data['title_section'] = $title;
		$this->data['no_add_button'] = true;
		$this->data['ordenar'] = false;
		$this->data['slug'] = $form;
		$this->data['export'] = true;

		$heading = [['data' => 'Id', 'class' => 'col-md-1']];
		$i = 0;
		$columns = [];
		foreach ($data_struct as $item) {
			$i++;
			if ($i <= self::ROWS_TO_SKIP) {
				continue;
			} elseif ($i > self::ROWS_TO_SKIP + self::COLUMNS_TO_SHOW || $item['name'] == 'status') {
				break;
			}

			array_push($heading, $item['label']);
			$columns[] = $item['name'];
		}
		array_push($heading, ['data' => 'Idioma', 'class' => 'col-md-1']);
		array_push($heading, ['data' => 'Acciones', 'class' => 'col-md-1']);
		array_push($heading, ['data' => 'Ultima edicion', 'class' => 'col-md-2']);

		$this->table->setTemplate($this->table_template);
		$this->table->setHeading($heading);

		$data = $this->admin_general_model->get_submits_by_form_domain( $form, $this->data['domain']);


        if(isset($_GET['q']) && !empty($_GET['q'])) $data = $this->search_filter ($data);

        $this->data['count'] = count($data);
        $this->data['page'] = 1;
        if(isset($_GET['page'])) {
            $this->data['page'] = $_GET['page'];
        }
        $this->data['perpage'] = 10;
        if(isset($_GET['perpage'])) {
            $this->data['perpage'] = $_GET['perpage'];
        }
        $this->data['perpages'] = [10,25,50,100];

        $data = array_slice ( $data, ($this->data['page']-1)*$this->data['perpage'], $this->data['perpage'] );

		foreach ($data as $item) {
			$row = [
				$item->id,
			];
			foreach ($columns as $column) {
				$row = array_merge($row, [htmlentities($item->{$column})]);
			}
			$row = array_merge($row, [
				$item->text_user_language,
				anchor('admin/formSubmits/view/'. $form .'/'. $item->id, '<i class="fa fa-eye fa-fw"></i>',
					array('class' => 'dlist-view-button')) .' '.
				anchor('admin/formSubmits/delete/'. $form .'/'. $item->id, '<i class="fa fa-trash-o fa-fw"></i>',
					array('class' => 'dlist-delete-button', 'onclick' => "return confirm('Do you want delete this entry?')")),
				$item->updated_at,
			]);
			$this->table->addRow($row);
		}

        $this->data['table'] = $this->table;
		return view('admin/dlist', $this->data);
	}

	public function view($form, $id)
	{
		$title = $this->admin_general_model->get_table_title('form_data_'. $form);
		$this->data['page_title'] = $title;
		$this->data['title_section'] = $title;
		$this->data['no_add_button'] = true;
		$this->data['ordenar'] = false;
		$this->data['id'] = $id;

		$this->data['data_struct'] = $this->admin_general_model->get_table_struct('form_data_'. $form);
		$this->data['data'] = $this->admin_general_model->get_table_data([
			'slug' => 'form_data_'. $form,
			'id' => $id,
			'domain' => $this->data['domain'],
		]);

		return view('admin/dview', $this->data);
	}

	public function delete($form, $id){

        $this->admin_general_model->delete_submits_by_id_form_domain( $id, $form, $this->data['domain']);
		return redirect()->to(base_url() .'/admin/formSubmits/list/'. $form);
	}

	public function export($form)
	{
		$i = 0;
		$data_struct = $this->admin_general_model->get_table_struct('form_data_'. $form);
		foreach ($data_struct as $item) {
			$i++;
			if ($i <= self::ROWS_TO_SKIP) {
				continue;
			}
			if (in_array($item['name'], ['id', 'language', 'domain', 'status', 'position', 'updater_id'])) {
				continue;
			}
			$columns[] = $item['name'];
		}

		$result = $this->admin_general_model->get_form_data ($columns, $form);

		$this->download_send_headers($form .'_export_' . date("Y-m-d") . '.csv');
		echo $this->array2csv($result);

		exit();
	}

	private function array2csv(array &$array)
	{
		if (count($array) == 0) {
			return null;
		}
		ob_start();
		$df = fopen("php://output", 'w');
		fputcsv($df, array_keys((array) reset($array)));
		foreach ($array as $row) {
			fputcsv($df, (array) $row);
		}
		fclose($df);
		return ob_get_clean();
	}

	private function download_send_headers($filename) {
		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}
}
