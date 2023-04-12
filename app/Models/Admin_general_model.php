<?php

namespace App\Models;

use CodeIgniter\Model;

class Admin_general_model extends Model {

	function __construct() {
		parent::__construct();
	}

	public function get_dynamic_elements() {
		$query = $this->db->query("SHOW TABLE STATUS LIKE 'dynamic_%';");
		$results = $query->getResult();
		if (empty($results))
			return array();
		$aRet = array();
		foreach ($results as $row) {
			$slug = str_replace('dynamic_', '', $row->Name);
			$aRet[] = array('name' => $row->Comment, 'slug' => $slug);
		}
		return $aRet;
	}

	public function get_related_elements() {
		$query = $this->db->query("SHOW TABLE STATUS LIKE 'related_%';");
		$results = $query->getResult();
		if (empty($results))
			return array();
		$aRet = array();
		foreach ($results as $row) {
			$slug = str_replace('related_', '', $row->Name);
			$aRet[] = array('name' => $row->Comment, 'slug' => $slug);
		}
		return $aRet;
	}

	public function get_dynamic_table_struct($slug, $idioma = null)
	{
		return $this->get_table_struct('dynamic_'. $slug, $idioma);
	}

	public function get_table_struct($table, $idioma = null, $parent_related_table = null)
	{
		if (!$this->table_exists($table))
			return array();
		$query = $this->db->query("SHOW FULL columns FROM " . $table . ";");
		$aRet = array();
		$results = $query->getResult();
		//print_r($results);

		foreach ($results as $k => $row) {
			$aRet[$k]['name'] = $row->Field;
			if (strpos($row->Field, 'dynamic_') !== FALSE) {
				$aRet[$k]['type'] = 'select';
				//list(, $related_slug, ) = explode("_", $row->Field); BUG FOUND 10.01.2019 Kirill
				$related_slug = str_replace('dynamic_', '', $row->Field);
				if ($parent_related_table && $row->Field == $parent_related_table) {
					return false;
				}
				//$aTemp = $this->get_dynamic_table_data($related_slug, $idioma);
				$aTemp = $this->get_dynamic_table_data([
					'slug' => $related_slug,
					'language' => $idioma,
					'parent_related_table' => $table,
				]);
				/* In case of when we need two selectors with same content */
				if (empty($aTemp)) {

					$related_slug = explode('_', $related_slug)[0];
					$aTemp = $this->get_dynamic_table_data([
						'slug' => $related_slug,
						'language' => $idioma,
						'parent_related_table' => $table,
					]);
				}

				$first_element_text = false;
				foreach ($aTemp as $rowb)
					foreach ($rowb as $kb => $v)
						if (strpos($kb, 'text_') !== FALSE && !$first_element_text)
							$first_element_text = $kb;
				if (!$first_element_text)
					foreach ($aTemp as $rowb)
						foreach ($rowb as $kb => $v)
							if (strpos($kb, 'textarea_') !== FALSE && !$first_element_text)
								$first_element_text = $kb;
				if ($first_element_text && !empty($aTemp)) {
					$aRet[$k]['content'] = [''];
					foreach ($aTemp as $rowb) {
						$aRet[$k]['content'][$rowb->id] = $rowb->$first_element_text;
					}
					asort($aRet[$k]['content']);
				}
				else
					$aRet[$k]['content'] = array();
			}elseif(strpos($row->Field, 'multiple_') !== FALSE){
				$aRet[$k]['type'] = 'multiselect';

				list(, $related_slug, ) = explode("_", $row->Field,2);
				//$aTemp = $this->get_dynamic_table_data($related_slug, $idioma);
				if ($parent_related_table && str_replace('multiple_', 'dynamic_', $row->Field) == $parent_related_table) {
					return false;
				}

				if (!$this->table_exists('dynamic_' . $related_slug)) {
					list(, $related_slug, ) = explode("_", $row->Field, 3);
				}

				$aTemp = $this->get_dynamic_table_data([
					'slug' => $related_slug,
					'language' => $idioma,
					'parent_related_table' => $table,
				]);
				$first_element_text = false;
				foreach ($aTemp as $rowb)
					foreach ($rowb as $kb => $v)
						if (strpos($kb, 'text_') !== FALSE && !$first_element_text)
							$first_element_text = $kb;
				if (!$first_element_text)
					foreach ($aTemp as $rowb)
						foreach ($rowb as $kb => $v)
							if (strpos($kb, 'textarea_') !== FALSE && !$first_element_text)
								$first_element_text = $kb;
				if ($first_element_text && !empty($aTemp)) {
					foreach ($aTemp as $rowb)
						$aRet[$k]['content'][$rowb->id] = $rowb->$first_element_text;
					asort($aRet[$k]['content']);
				}
				else
					$aRet[$k]['content'] = array();

			}elseif(strpos($row->Field, 'enum_') !== FALSE){
				$aRet[$k]['type'] = 'enum';
				$types=str_replace("'","",str_replace(')','',str_replace('enum(','',$row->Type)));
				$temp=explode(",",$types);
				foreach($temp as $val)
					$aRet[$k]['content'][$val]=$val;
			}elseif (strpos($row->Field, '_') !== FALSE)
				list($aRet[$k]['type']) = explode("_", $row->Field);
			else
				$aRet[$k]['type'] = 'hidden';
			$aRet[$k]['label'] = $row->Comment;
			$aRet[$k]['default'] = $row->Default;
		}
		return $aRet;
	}

	public function get_related_tables($slug) {
		$table = 'related_' . $slug;
		if (!$this->table_exists($table))
			return array();
		$query = $this->db->query("SHOW FULL columns FROM " . $table . ";");
		$aRet = array();
		$results = $query->result();
		foreach ($results as $k => $row) {
			$aRet[$k]['label'] = $row->Comment;
			$aRet[$k]['table'] = $row->Field;
		}
		return $aRet;
	}

	public function get_dynamic_table_data($params)
	{
		$params['slug'] = 'dynamic_'. $params['slug'];
		return $this->get_table_data($params);
	}

	public function get_table_data($params)
	{
		$table    = (isset($params['slug'])) ? $params['slug'] : null;
		$language = (isset($params['language'])) ? $params['language'] : null;
		$id       = (isset($params['id'])) ? $params['id'] : null;
		$order    = (isset($params['order'])) ? $params['order'] : null;
		$domain   = (isset($params['domain'])) ? $params['domain'] : null;
		$parent_related_table = (isset($params['parent_related_table'])) ? $params['parent_related_table'] : null;

		$dynamic_tables = $this->get_dynamic_elements();
		foreach ($dynamic_tables as $dynamic_table) {
			if (isset($_GET['dynamic_'. $dynamic_table['slug']])) {
				$dynamic_filter_slug = $dynamic_table['slug'];
				$dynamic_filter['dynamic_'. $dynamic_filter_slug] = $_GET['dynamic_'. $dynamic_filter_slug];
			}
		}

		if (!$this->table_exists($table))
			return array();
		$struct = $this->get_table_struct($table, null, $parent_related_table);



        $builder = $this->db->table($table);

		if ($language != null)
			$builder->where('language', $language);

		if ($id != null) {
            $builder->where('id', $id);
		}
		if ($domain != null)
            $builder->where('domain', $domain);

		if (isset($params['filter']) && $params['filter']) {
			foreach ($struct as $item) {
				if (array_key_exists($item['name'], $params['filter']) && $params['filter'][$item['name']] != '') {
                    $builder->where($item['name'], $params['filter'][$item['name']]);
				}
			}
		}

		if ($order != null)
            $builder->orderBy($order, 'ASC');
		$query = $builder->get();

		return ($id) ? $query->getFirstRow() : $query->getResult();
	}

	public function get_related_table_data($slug, $tables, $id) {
		$aRet = array();
		$table_related = 'related_' . $slug;
		$table_info = $tables[0]['table'];
		$table_select = $tables[1]['table'];
		if (!$this->table_exists($table_related))
			return $aRet;
		if (!$this->table_exists($table_info))
			return $aRet;
		if (!$this->table_exists($table_select))
			return $aRet;
		#Informaci�n de las relaciones:
		$aRet['selected'] = array();
		$query = $this->db->query("SELECT * FROM " . $table_related . " WHERE " . $table_info . "=" . $id . ";");
		$results = $query->result();
		foreach ($results as $k => $row)
			$aRet['selected'][] = $row->$table_select;
		#Informaci�n del elemento:
		$data_struct = $this->admin_general_model->get_dynamic_table_struct(str_replace('dynamic_', '', $table_info));
		$first_element_text = $this->admin_general_model->get_first_element($data_struct);
		$query = $this->db->query("SELECT * FROM " . $table_info . " WHERE id=" . $id . ";");
		$results = $query->result();
		foreach ($results as $k => $row)
			$aRet['info'] = $row->$first_element_text;

		#Informaci�n del select:
		$data_struct = $this->admin_general_model->get_dynamic_table_struct(str_replace('dynamic_', '', $table_select));
		$first_element_text = $this->admin_general_model->get_first_element($data_struct);
		$query = $this->db->query("SELECT * FROM " . $table_select . " WHERE 1 ORDER BY 1;");
		$results = $query->result();
		foreach ($results as $k => $row)
			$aRet['list_select'][$row->id] = $row->$first_element_text;

		return $aRet;
	}

	public function update_dynamic_table_data($slug, $data)
	{
		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table))
			return false;
		if (!isset($data['id']) || !$data['id']) {
			$data['id'] = $this->get_next_insert_id($table);
            $builder = $this->db->table($table);
			$result = $builder->insert($data);
			return ($result) ? $data['id'] : false;
		} else {
			/*
			 * We about to update different tables with different Primary keys, so we need to do like this...
			 * */
            $builder = $this->db->table($table);
			$update_string = $builder->set($data)->getCompiledUpdate();
			$update_string = mb_substr($update_string, mb_strpos($update_string, 'SET') + mb_strlen('SET'));
            $builder = $this->db->table($table);
            $insert_string = $builder->set($data)->getCompiledInsert();
			$query = $insert_string . ' ON DUPLICATE KEY UPDATE ' . $update_string;

			$result = $this->db->query($query);
			return ($result) ? $data['id'] : false;
		}
	}

	public function update_related_table_data($slug, $data_update) {
		$table = 'related_' . $slug;
		foreach ($data_update as $data)
			$result = $this->db->insert($table, $data);
		return ($result) ? true : false;
	}

	public function exist_element($slug, $id, $idioma) {
		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table))
			return false;
		if (!$id)
			return false;
		if (!$idioma)
			return false;

        $builder = $this->db->table($table);
        $query   = $builder->where('id', $id)->where('language', $idioma)->get();
        $results = $query->getResult();

		return (!empty($results)) ? true : false;
	}

	public function delete_dynamic_table_data($slug, $id, $domain = false)
	{
		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table))
			return false;
		if (!$id)
			return false;

        $builder = $this->db->table($table);
        $builder->where('id', $id);

		if ($domain) {
            $builder->where('domain', $domain);
		}
		$builder->delete();

		return $this->db->affectedRows();
	}

	public function delete_dynamic_table_data_lang($slug, $id, $lang, $domain = false)
	{
		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table))
			return false;
		if (!$id)
			return false;

        $builder = $this->db->table($table);
        $builder->where('id', $id);
		$builder->where('language', $lang);

        if ($domain) {
			$builder->where('domain', $domain);
		}
		$builder->delete();

		return $this->db->affectedRows();
	}

	public function truncate_dynamic_table_data($slug, $domain = false)
	{
		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table))
			return false;
		if ($domain) {
			$this->db->where('domain', $domain);
		}
		$this->db->delete($table);

		return $this->db->affected_rows();
	}

	public function delete_related_table_data($slug, $index, $id) {
		$table = 'related_' . $slug;
		if (!$this->table_exists($table))
			return false;
		if (!$index)
			return false;
		if (!$id)
			return false;
		$this->db->where($index, $id);
		$this->db->delete($table);
		return $this->db->affected_rows();
	}

	public function update_position($slug, $id, $data, $domain = false) {
		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table))
			return false;
		if (!$id)
			return false;
        $builder = $this->db->table($table);
        $builder->where('id', $id);
		if ($domain) {
			$builder->where('domain', $domain);
		}
		$result = $builder->update($data);

		return ($result) ? true : false;
	}

	public function table_exists($table) {
		$query = $this->db->query("SHOW TABLES LIKE '" . $table . "'");
		$results = $query->getResult();
		return (!empty($results)) ? true : false;
	}

	public function get_next_insert_id($table) {
		$query = $this->db->query("SELECT MAX(id)+1 as nextid FROM " . $table . ";");
		return ($query->getFirstRow()->nextid) ? $query->getFirstRow()->nextid : 1;
	}

	public function get_first_element($data_struct) {
		$first_element_text = false;
		foreach ($data_struct as $row) {
			if ($row['type'] == 'text' && !$first_element_text)
				$first_element_text = $row['name'];
		}
		if (!$first_element_text)
			foreach ($data_struct as $row)
				if ($row['type'] == 'textarea' && !$first_element_text)
					$first_element_text = $row['name'];
		return $first_element_text;
	}

	public function get_ordenar($data_struct) {
		$ordenar = false;
		foreach ($data_struct as $row) {
			if ($row['name'] == 'position')
				$ordenar = true;
		}
		return $ordenar;
	}

	public function pause_dynamic_table_data($slug, $id, $domain = false) {
		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table) || !$id)
			return false;
		$data=array('status'=>'PAUSED');
        $builder = $this->db->table($table);
        $builder->where('id', $id);
		if ($domain) {
            $builder->where('domain', $domain);
		}
		$data['updater_id'] = \Config\Services::session()->get('userdata')->id;
		$data['updated_at'] = date('Y-m-d H:i:s', time());

		$result = $builder->update($data);

		return ($result) ? true : false;
	}

	public function resume_dynamic_table_data($slug, $id, $domain = false) {
		$table = 'dynamic_' . $slug;
		if (!$this->table_exists($table) || !$id)
			return false;
		$data=array('status'=>'ACTIVED');
        $builder = $this->db->table($table);
        $builder->where('id', $id);
        if ($domain) {
            $builder->where('domain', $domain);
        }
        $data['updater_id'] = \Config\Services::session()->get('userdata')->id;
		$data['updated_at'] = date('Y-m-d H:i:s', time());

        $result = $builder->update($data);

		return ($result) ? true : false;
	}

	/**
	 * recursive delete folder
	 * @param $dir
	 * @return bool
	 */
	public function delFolder($dir)
	{
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? self::delFolder("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	/**
	 * delete images by gallery id
	 * @param $id
	 * @return bool
	 */
	public function delImagesByGalleryId($id)
    {
        $array = array('dynamic_gallery_id' => $id);
        $builder = $this->db->table('dynamic_images');
        $builder->where($array);
        return $builder->delete();
    }
	/**
	 * delete updated_images by gallery dir
	 * @param $dir
	 * @return bool
	 */
	public function delUpdatedImagesByGalleryDir($dir)
    {
		$sql = "delete from updated_images where text_title like '". $dir ."%'";
		$this->db->query($sql);
		return true;
    }

	/**
	 * Dynamically get possible admin menu items
	 * @return array
	 */
	public function get_admin_menu_items()
	{
		$dynamic_elements = $this->get_dynamic_elements();
		/*
		 * Array 'slug' - Part of table name after "dynamic_", 'name' - Table comment from DB
		 * */
		$menu_items = [];
		$dynamic_exceptions = ['images'];
		/* Dynamic menu items by dynamic_* tables */
		foreach ($dynamic_elements as $element) {
			if (in_array($element['slug'], $dynamic_exceptions)) {
				continue;
			}
			$menu_items = $menu_items + [
					'dynamic/dlist/'. $element['slug'] => ($element['name']) ? $element['name'] : $element['slug'],
				];
		}

		/* Static (manual) menu items */
		return $menu_items + [
				'cmsBlock/static_blocks' => 'Bloques estáticos',
				'formSubmits' => 'Registro de formularios',
			];
	}

	/**
	 * Dynamically get tables with "page" fields (slug, seo, etc)
	 * @return array
	 */
	public function get_dynamic_pages_tables()
	{
		$dynamic_elements = $this->get_dynamic_elements();
		$result[''] = '---';

		foreach ($dynamic_elements as $element) {
			if ($element['slug'] == 'pages') {
				continue;
			}
			$query = $this->db->query("SHOW FULL columns FROM dynamic_". $element['slug']);
			$columns = $query->getResult();

			foreach ($columns as $column) {
				if ($column->Field == 'slug') {
					$result['dynamic_'. $element['slug']] = $element['name'];
					continue;
				}
			}
		}

		return $result;
	}

	public function get_last_edited_objects()
	{
		$tables = $this->get_dynamic_elements();
		$sql = 'select tt.id, tt.domain, tt.language, tt.updated_at, tt.text_title, tt.slug, tt.name, username, 
                    tt.language as language_id, languages.name as language, tt.status from (';
		foreach ($tables as $table) {
			if ($table['slug'] == 'images' || $table['slug'] == 'images_backup') {
				continue;
			}
			$sql .= '(select id, domain, language, text_title, status, updater_id, max(updated_at) as updated_at, "'. $table['name'] .'" as name,
              "'. $table['slug'] .'" as slug from dynamic_'. $table['slug'] .' group by id, domain, language) union ';
		}
		$sql = substr($sql, 0, -6);
		$sql .= ') as tt inner JOIN users on updater_id = users.id inner join languages on tt.language = languages.id ORDER BY `updated_at` DESC LIMIT 100';
		$query = $this->db->query($sql);

		return $query->getResult();
	}

	/**
	 * @param $upload_path
	 * @param $old_filename
	 * @param $new_filename
	 * @return bool
	 */
	public function rename_uploaded_file($upload_path, $old_filename, $new_filename)
	{
		if ($old_filename == $new_filename) {
			return true;
		}

		$source = $upload_path .'/'. $old_filename;
		$dest =$upload_path .'/'. $new_filename;
		rename($source, $dest);

		/* Rename thumbnails */
		$old_path_parts = pathinfo($old_filename);
		$new_path_parts = pathinfo($new_filename);

		$thumb_file_name = str_replace(
			$old_path_parts['filename'] .'.'. $old_path_parts['extension'],
			$old_path_parts['filename'] .'.thigh' .'.'. $old_path_parts['extension'],
			$upload_path .'/'. $old_filename
		);
		if (file_exists(FCPATH . $thumb_file_name)) {
			$new_thumb_file_name = str_replace(
				$new_path_parts['filename'] .'.'. $new_path_parts['extension'],
				$new_path_parts['filename'] .'.thigh' .'.'. $new_path_parts['extension'],
				$upload_path .'/'. $new_filename
			);
			rename(FCPATH . $thumb_file_name, FCPATH . $new_thumb_file_name);
		}
		$thumb_file_name = str_replace(
			$old_path_parts['filename'] .'.'. $old_path_parts['extension'],
			$old_path_parts['filename'] .'.tmedium' .'.'. $old_path_parts['extension'],
			$upload_path .'/'. $old_filename
		);
		if (file_exists(FCPATH . $thumb_file_name)) {
			$new_thumb_file_name = str_replace(
				$new_path_parts['filename'] .'.'. $new_path_parts['extension'],
				$new_path_parts['filename'] .'.tmedium' .'.'. $new_path_parts['extension'],
				$upload_path .'/'. $new_filename
			);
			rename(FCPATH . $thumb_file_name, FCPATH . $new_thumb_file_name);
		}
		$thumb_file_name = str_replace(
			$old_path_parts['filename'] .'.'. $old_path_parts['extension'],
			$old_path_parts['filename'] .'.tlow' .'.'. $old_path_parts['extension'],
			$upload_path .'/'. $old_filename
		);
		if (file_exists(FCPATH . $thumb_file_name)) {
			$new_thumb_file_name = str_replace(
				$new_path_parts['filename'] .'.'. $new_path_parts['extension'],
				$new_path_parts['filename'] .'.tlow' .'.'. $new_path_parts['extension'],
				$upload_path .'/'. $new_filename
			);
			rename(FCPATH . $thumb_file_name, FCPATH . $new_thumb_file_name);
		}
		$thumb_file_name = str_replace(
			$old_path_parts['filename'] .'.'. $old_path_parts['extension'],
			$old_path_parts['filename'] .'.tlowest' .'.'. $old_path_parts['extension'],
			$upload_path .'/'. $old_filename
		);
		if (file_exists(FCPATH . $thumb_file_name)) {
			$new_thumb_file_name = str_replace(
				$new_path_parts['filename'] .'.'. $new_path_parts['extension'],
				$new_path_parts['filename'] .'.tlowest' .'.'. $new_path_parts['extension'],
				$upload_path .'/'. $new_filename
			);
			rename(FCPATH . $thumb_file_name, FCPATH . $new_thumb_file_name);
		}

		return true;
	}

	/**
	 * @param $id
	 * @param $language
	 * @param $domain
	 * @param $dynamic_gallery_id
	 */
	public function delete_image_file($id, $language, $domain, $dynamic_gallery_id = null)
	{
        $builder = $this->db->table('dynamic_images');
		$builder->where('id', $id);
		$builder->where('language', $language);
		$builder->where('domain', $domain);
		if ($dynamic_gallery_id) {
			$builder->where('dynamic_gallery_id', $dynamic_gallery_id);
		}

		$query = $builder->get();
		$result = $query->getFirstRow();

		if ($result && $result->url) {
			if (file_exists( $result->url)) {
				unlink($result->url);
			}

			/* Delete thumbnails */
			$path_parts = pathinfo($result->url);
			$thumb_file_name = str_replace(
				$path_parts['filename'] .'.'. $path_parts['extension'],
				$path_parts['filename'] .'.thigh' .'.'. $path_parts['extension'],
				$result->url
			);
			if (file_exists(FCPATH . $thumb_file_name)) {
				unlink(FCPATH . $thumb_file_name);
			}
			$thumb_file_name = str_replace(
				$path_parts['filename'] .'.'. $path_parts['extension'],
				$path_parts['filename'] .'.tmedium' .'.'. $path_parts['extension'],
				$result->url
			);
			if (file_exists(FCPATH . $thumb_file_name)) {
				unlink(FCPATH . $thumb_file_name);
			}
			$thumb_file_name = str_replace(
				$path_parts['filename'] .'.'. $path_parts['extension'],
				$path_parts['filename'] .'.tlow' .'.'. $path_parts['extension'],
				$result->url
			);
			if (file_exists(FCPATH . $thumb_file_name)) {
				unlink(FCPATH . $thumb_file_name);
			}
			$thumb_file_name = str_replace(
				$path_parts['filename'] .'.'. $path_parts['extension'],
				$path_parts['filename'] .'.tlowest' .'.'. $path_parts['extension'],
				$result->url
			);
			if (file_exists(FCPATH . $thumb_file_name)) {
				unlink(FCPATH . $thumb_file_name);
			}
		}
	}

	/**
	 * @param $src
	 * @return bool
	 */
	public function delete_image_file_by_src($src)
	{
		if (file_exists(FCPATH . $src)) {
			unlink(FCPATH . $src);
		}
		$builder = $this->db->table('updated_images');
		$builder->where('text_title', $src);
		$builder->delete();

		/* Delete thumbnails */
		$path_parts = pathinfo($src);
		$thumb_file_name = str_replace(
			$path_parts['filename'] .'.'. $path_parts['extension'],
			$path_parts['filename'] .'.thigh' .'.'. $path_parts['extension'],
			$src
		);
		if (file_exists(FCPATH . $thumb_file_name)) {
			unlink(FCPATH . $thumb_file_name);
		}
		$builder = $this->db->table('updated_images');
		$builder->where('text_title', $thumb_file_name);
		$builder->delete();
		$thumb_file_name = str_replace(
			$path_parts['filename'] .'.'. $path_parts['extension'],
			$path_parts['filename'] .'.tmedium' .'.'. $path_parts['extension'],
			$src
		);
		if (file_exists(FCPATH . $thumb_file_name)) {
			unlink(FCPATH . $thumb_file_name);
		}
		$builder = $this->db->table('updated_images');
		$builder->where('text_title', $thumb_file_name);
		$builder->delete();
		$thumb_file_name = str_replace(
			$path_parts['filename'] .'.'. $path_parts['extension'],
			$path_parts['filename'] .'.tlow' .'.'. $path_parts['extension'],
			$src
		);
		if (file_exists(FCPATH . $thumb_file_name)) {
			unlink(FCPATH . $thumb_file_name);
		}
		$builder = $this->db->table('updated_images');
		$builder->where('text_title', $thumb_file_name);
		$builder->delete();

		$thumb_file_name = str_replace(
			$path_parts['filename'] .'.'. $path_parts['extension'],
			$path_parts['filename'] .'.tlowest' .'.'. $path_parts['extension'],
			$src
		);
		if (file_exists(FCPATH . $thumb_file_name)) {
			unlink(FCPATH . $thumb_file_name);
		}
		$builder = $this->db->table('updated_images');
		$builder->where('text_title', $thumb_file_name);
		$builder->delete();


		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function set_image_record($data)
	{
        $builder = $this->db->table('dynamic_images');
		return $builder->insert($data);
	}

	/**
	 * @param $update_data
	 * @param $id
	 * @param $language
	 * @param $domain
	 * @param $dynamic_gallery_id
	 * @return bool
	 */
	public function update_image_record($update_data, $id, $language, $domain, $dynamic_gallery_id)
	{
        $builder = $this->db->table('dynamic_images');
		$builder->where([
			'id' => $id,
			'language' => $language,
			'domain' => $domain,
			'dynamic_gallery_id' => $dynamic_gallery_id,
		]);
		$result = $builder->update($update_data);

		return $result;
	}

	/**
	 * @param $old_path
	 * @param $new_path
	 * @param $domain
	 * @param $dynamic_gallery_id
	 * @return bool
	 */
	public function rename_gallery_folder($old_path, $new_path, $domain, $dynamic_gallery_id)
	{
		$sql = "UPDATE dynamic_images
                SET url = CONCAT('". $new_path ."', '/', SUBSTRING_INDEX(url, '/', -2))
                WHERE domain = ? AND dynamic_gallery_id = ? ";
		$result = $this->db->query($sql, [$domain, $dynamic_gallery_id]);

		$source = str_replace('system/..', '', BASEPATH .'../'. $old_path);
		$dest = str_replace('system/..', '', BASEPATH .'../'. $new_path);
		if (is_dir($source)) {
			rename($source, $dest);
		}

		return $result;
	}

	public function fix_image_paraments($url, $image_id, $domain, $dynamic_gallery_id)
	{
		if (file_exists(FCPATH . $url)) {
			$image_size = @getimagesize(FCPATH . $url);
			$file_size = @filesize(FCPATH . $url);
		} else {
			return false;
		}
		if (!$image_size) {
			return false;
		}

		$update_data = [
			'file_size' => round(($file_size/1024)),
			'image_width' => $image_size[0],
			'image_height' => $image_size[1]
		];

        $builder = $this->db->table('dynamic_images');
        $builder->where([
			'id' => $image_id,
			'domain' => $domain,
			'dynamic_gallery_id' => $dynamic_gallery_id,
		]);
        return $builder->update($update_data);
		
	}

	/**
	 * Recursively update childs (path, uri_string) of any page
	 * @param $id
	 * @param $domain
	 * @param $language
	 * @param $parent_uri_string
	 * @param $parent_path
	 * @param $parent_position
	 */
	public function update_page_childs($id, $domain, $language, $parent_uri_string, $parent_path)
	{
		$sql = 'UPDATE dynamic_pages 
            SET uri_string = CONCAT(?, \'/\', slug), path = CONCAT(?, \'.\', SUBSTRING_INDEX(path, \'.\', -1))
            WHERE parent_id = ? AND domain = ? AND language = ?
            ';
		$this->db->query($sql, [$parent_uri_string, $parent_path, $id, $domain, $language]);


		$sql = 'SELECT *
                FROM dynamic_pages 
                WHERE parent_id = ? AND domain = ? AND language = ?
                ';
		$query = $this->db->query($sql, [$id, $domain, $language]);
		$pages = $query->getResult();
		foreach ($pages as $page) {
			$this->update_page_childs($page->id, $page->domain, $page->language, $page->uri_string, $page->path);
		}
	}

	/**
	 * We need to calculate path variable depends on parents
	 * $new_path - can be false, when we choose parent_page from page edition.
	 * When we making movements in dorder_pages, always have $new_path
	 * @param $id
	 * @param $parent_id
	 * @param $domain
	 * @param $language_original
	 * @param $slug
	 * @param $new_path
	 * @return array
	 */
	public function get_page_positions($id, $parent_id, $domain, $language_original, $slug, $new_path = false)
	{
		if ($parent_id == 0) {
			$sql = 'SELECT CONVERT(path, UNSIGNED INTEGER) as path, id, 
                        (select max(q2.position) from dynamic_pages q2 WHERE q2.domain = q1.domain) as position
                    FROM dynamic_pages q1
                    WHERE parent_id = 0 AND domain = ? 
                    '. /*AND language = ?*/ '
                    ORDER BY CONVERT(path, UNSIGNED INTEGER) DESC LIMIT 1
                    ';
			$query = $this->db->query($sql, [$domain/*, $language_original*/]);
			$page = $query->getFirstRow();


			$new_position = 1;
			if ($new_path == false) {
				if (!$page || !$page->path) {
					$new_path = 1;
				} elseif ($page->id == $id) {
					/* If it is just translation */
					$new_path = $page->path;
					$new_position = $page->position;
				} else {
					$new_path = $page->path + 1;
					$new_position = $page->position + 1;
				}
			}

			$data['position']   = $new_position;
			$data['path']       = $new_path;
			$data['uri_string'] = $slug;
		} else {
			/* Any child level */
			$sql = 'SELECT CONVERT(SUBSTRING_INDEX(b.path, ".", -1), UNSIGNED INTEGER) path, b.id, a.uri_string as parent_uri_string, a.path as parent_path, 
                            b.position, a.position as parent_position
                        FROM dynamic_pages a 
                        LEFT JOIN dynamic_pages b ON a.id = b.parent_id AND a.language = b.language AND a.domain = b.domain
                        WHERE a.id = ? AND a.domain = ? AND a.language = ? 
                        ORDER BY CONVERT(SUBSTRING_INDEX(b.path, ".", -1), UNSIGNED INTEGER) DESC LIMIT 1 
                        ';
			$query = $this->db->query($sql, [$parent_id, $domain, $language_original]);
			$page = $query->getFirstRow();
			$new_position = 1;
			if ($new_path == false) {
				if ($page->id == $id) {
					$new_path = $page->path;
					$new_position = $page->position;
				} else {
					$new_path = ($page->path) ? $page->path + 1 : 1;
					$new_position = ($page->position) ? $page->position + 1 : $page->parent_position + 1;
				}
			}
			/* There is no parent page in this translation */
			if(!is_object($page)) {
				return [];
			}

			$data['position'] = $new_position;
			$data['path'] = $page->parent_path .'.'. $new_path;
			$data['uri_string'] = ($page->parent_uri_string != '') ? $page->parent_uri_string .'/'. $slug : $slug;
		}

		return [
			'position'   => $data['position'],
			'path'       => $data['path'],
			'uri_string' => $data['uri_string'],
		];
	}

	/**
	 *
	 * @param $page_id
	 * @param $page_position
	 * @param $domain
	 * @return mixed
	 */
	public function update_pages_positions($domain, $language_original)
	{
		$last_position = false;
		$sql = 'SELECT * FROM dynamic_pages 
                    WHERE domain = ? AND language = ? 
                    ORDER BY POSITION ASC';
		$query = $this->db->query($sql, [$domain, $language_original]);
		$pages = $query->getResult();
		$last_id = null;
		foreach ($pages as $page) {
			if ($page->position != 0 && $page->position == $last_position) {
				/*
				 * +1
				 * */
				$sql = 'UPDATE dynamic_pages set position = position + 1 WHERE position >= ? AND domain = ? AND id != ?';
				$query = $this->db->query($sql, [$page->position, $domain, $last_id]);

				self::update_pages_positions($domain, $language_original);
				break;
			} elseif($page->position != 0 && $page->position > $last_position + 1) {
				/*
				 * -1
				 * */
				$sql = 'UPDATE dynamic_pages set position = position - 1 WHERE position >= ? AND domain = ? ';
				$query = $this->db->query($sql, [$page->position, $domain]);

				self::update_pages_positions($domain, $language_original);
				break;
			}

			$last_position = $page->position;
			$last_id = $page->id;
		}

		return true;
	}


	/**
	 * Generate thumbnails for images.
	 * If original width is bigger, image will be same.
	 * If original image was optimized, it will be lost, result can be bigger than original.
	 * We need to optimize thumbnails manually
	 * @param $source
	 * @param $preferences
	 * @return bool
	 * @throws \ShortPixel\ClientException
	 */
	public function generate_thumbnails($source, $preferences)
	{
		ini_set("memory_limit","256M");
		ini_set("gd.jpeg_ignore_warning", 1);

		if (!file_exists(FCPATH . $source)) {
			return false;
		}
		$path_parts = pathinfo($source);
		if (strtolower($path_parts['extension']) == 'svg') {
			return false;
		}

		$image_size = getimagesize(FCPATH . $source);
		$orig_image_width = $image_size[0];
		$orig_image_height = $image_size[1];
		$aspect_ratio = $orig_image_width / $orig_image_height;

		//Thumb Alta
		if ($preferences->thumbn_size_high) {
			$config['source_image'] = FCPATH . $source;
			$new_file_name = str_replace(
				$path_parts['filename'] .'.'. $path_parts['extension'],
				$path_parts['filename'] .'.thigh' .'.'. $path_parts['extension'],
				$source
			);
			$width = min($preferences->thumbn_size_high, $orig_image_width);
			if (!\Config\Services::image()
				->withFile(FCPATH . $source)
				->resize($width, round($width / $aspect_ratio), true, 'height')
				->save(FCPATH . $new_file_name)) {
				echo "Image resize error";
				print_r($config);
				die();
			}

			/* Compress image */
			if ($preferences->auto_shortpixel == 1) {
				/* We cannot do it here, because it is very slow */
				//$this->compress_image($new_file_name);
			}
		}

		//Thumb Media
		if ($preferences->thumbn_size_medium) {
			$new_file_name = str_replace(
				$path_parts['filename'] .'.'. $path_parts['extension'],
				$path_parts['filename'] .'.tmedium' .'.'. $path_parts['extension'],
				$source
			);
			$width = min($preferences->thumbn_size_medium, $orig_image_width);
			if (!\Config\Services::image()
				->withFile(FCPATH . $source)
				->resize($width, round($width / $aspect_ratio), true, 'height')
				->save(FCPATH . $new_file_name)) {
				echo "Image resize error";
				print_r($config);
				die();
			}

			/* Compress image */
			if ($preferences->auto_shortpixel == 1) {
				/* We cannot do it here, because it is very slow */
				//$this->compress_image($new_file_name);
			}
		}

		//Thumb Baja
		if ($preferences->thumbn_size_low) {
			$new_file_name = str_replace(
				$path_parts['filename'] .'.'. $path_parts['extension'],
				$path_parts['filename'] .'.tlow' .'.'. $path_parts['extension'],
				$source
			);
			$width = min($preferences->thumbn_size_low, $orig_image_width);
			if (!\Config\Services::image()
				->withFile(FCPATH . $source)
				->resize($width, round($width / $aspect_ratio), true, 'height')
				->save(FCPATH . $new_file_name)) {
				echo "Image resize error";
				print_r($config);
				die();
			}

			/* Compress image */
			if ($preferences->auto_shortpixel == 1) {
				/* We cannot do it here, because it is very slow */
				//$this->compress_image($new_file_name);
			}
		}

		//Thumb extra Baja
		if ($preferences->thumbn_size_lowest) {
			$new_file_name = str_replace(
				$path_parts['filename'] .'.'. $path_parts['extension'],
				$path_parts['filename'] .'.tlowest' .'.'. $path_parts['extension'],
				$source
			);
			$new_file_name = str_replace(
				$path_parts['filename'] .'.'. $path_parts['extension'],
				$path_parts['filename'] .'.tlow' .'.'. $path_parts['extension'],
				$source
			);
			$width = min($preferences->thumbn_size_lowest, $orig_image_width);
			if (!\Config\Services::image()
				->withFile(FCPATH . $source)
				->resize($width, round($width / $aspect_ratio), true, 'height')
				->save(FCPATH . $new_file_name)) {
				echo "Image resize error";
				print_r($config);
				die();
			}

			/* Compress image */
			if ($preferences->auto_shortpixel == 1) {
				/* We cannot do it here, because it is very slow */
				//$this->compress_image($new_file_name);
			}
		}

		return true;
	}

	/**
	 * Generate Webp format for image.
	 * @param $source
	 * @return bool
	 */
	public function generate_webp($source)
	{
		if (!file_exists(FCPATH . $source)) {
			return false;
		}
		if (!in_array(strtolower(pathinfo($source)['extension']), ['png', 'jpg', 'jpeg'])) {
			return false;
		}
		$destination = self::replace_extension($source, 'webp');
		$options = [
			'quality' => 90,
		];
		try {
			\WebPConvert\WebPConvert::convert($source, $destination, $options);
		} catch (\WebPConvert\Convert\Exceptions\ConversionFailedException $e) {
			return false;
		}

		return true;
	}

	/**
	 * Use shortpixel to compress uploaded image
	 * @param $image
	 * @return bool
	 * @throws \ShortPixel\ClientException
	 */
	public function compress_image($image)
	{
		$builder    = $this->db->table('updated_images');
        $query      = $builder->where(['text_title' => $image, 'zipstatus' => '1'])->get();
	
		if (count( $query->getResult()) == 0) {
			\ShortPixel\setKey(config('app')->shortpixelKey);
			$str = str_replace("\n", '', base_url($image));
			//\ShortPixel\fromUrls($str)->wait(800)->toFiles(FCPATH . pathinfo($image)['dirname']);
			$str_compr = str_replace("\n", '', FCPATH . $image);
			\ShortPixel\fromFiles($str_compr)->toFiles(FCPATH . pathinfo($image)['dirname']);
			$builder    = $this->db->table('updated_images');
			$query      = $builder->where(['text_title'=> $image])->update(['text_title' => $image, 'zipstatus' => '1']);
		}

		return true;
	}

	public function get_table_title($table)
	{
		$query = $this->db->query("SHOW TABLE STATUS LIKE ?", $table);
		$results = $query->getResult();
		if (!empty($results)) {
			return current($results)->Comment;
		} else {
			return false;
		}
	}

	public function get_form_data_tables()
	{
		$query = $this->db->query("SHOW TABLE STATUS LIKE 'form_data_%'");
		$results = $query->getResult();
		if (empty($results))
			return array();
		$aRet = array();
		foreach ($results as $row) {
			$slug = str_replace('form_data_', '', $row->Name);
			$aRet[] = array('name' => $row->Comment, 'slug' => $slug);
		}
		return $aRet;
	}

	public static function replace_extension($filename, $new_extension)
	{
		$info = pathinfo($filename);
		return ($info['dirname'] ? $info['dirname'] . DIRECTORY_SEPARATOR : '')
			. $info['filename']
			. '.'
			. $new_extension;
	}

    /**
     * POSSIBLY WE NEED TO CACHE THIS FUNCTION!
     *
     * @param $slug
     * @param $object
     * @param bool $url_return
     * @return bool|string
     */
    public function get_dynamic_parent_url($slug, $object, $lang, $domain, $url_return = false)
    {
        $sql = 'SELECT id, container_of, representative_of, uri_string, path FROM dynamic_pages 
                    WHERE container_of = ? AND language = ? AND domain = ?';
        $query = $this->db->query($sql, ['dynamic_'. $slug, $lang, $domain]);
        $result = $query->getResult();
        foreach ($result as $row) {
            /**
             * Page is representative, it means that its url is not correct and we need to find url from dynamic content
             */
            if ($row->representative_of) {
                /**
                 * If objects was preloaded with @prepare_related_data we just take one
                 */
                if (is_array($object->{$row->representative_of}) && count($object->{$row->representative_of}) == 1) {
                    $object = current($object->{$row->representative_of});
                } else {
                    $object = ($this->get_dynamic_table_data([
                        'slug' => str_replace('dynamic_', '', $row->representative_of),
                        'id' => $object->{$row->representative_of},
                        'language' => $lang,
                        'domain' => $domain,
                    ]));
                }

                $url_return_2 = $this->get_dynamic_parent_url(str_replace('dynamic_', '', $row->representative_of), $object, $lang, $domain);

                //return implode('/', [$url_return_2, $object->slug]);
                return $url_return_2 . (!empty($object) && property_exists($object, "slug")?$object->slug:'') . '/';
            } else {
                return implode('/', [$row->uri_string, $url_return]);
            }
        }

        return $url_return;
    }

    /**
     * POSSIBLY WE NEED TO CACHE THIS FUNCTION!
     *
     * @param $slug
     * @param $object
     * @param bool $url_return
     * @return bool|string
     */
    public function get_dynamic_container_url($slug, $object, $lang, $domain, $url_return = false)
    {
        $sql = 'SELECT id, container_of, representative_of, uri_string, path FROM dynamic_pages 
                    WHERE representative_of = ? AND language = ? AND domain = ?';
        $query = $this->db->query($sql, ['dynamic_'. $slug, $lang, $domain]);
        $result = $query->getResult();

        $object1 = current($result);
        if (!$object1) {
            return false;
        }

        return $this->get_dynamic_parent_url(str_replace('dynamic_', '', $object1->representative_of), $object, $lang, $domain);
    }

    public function get_domain_preferences($domain_menu)
    {
        $builder    = $this->db->table('preferences');
        $query  = $builder->where('domain', $domain_menu)->get();
        return $query->getFirstRow();
    }
    public function get_preferences ()
    {
        $builder    = $this->db->table('preferences');
        $query  = $builder->get();
        return $query->getResult();
    }

    public function get_users()
    {
        $builder    = $this->db->table('users');
        $query      = $builder->get();
        return $query->getFirstRow();
    }
    public function get_images()
    {
        $builder    = $this->db->table('dynamic_images');
        $query      = $builder->get();
        return $query->getResult();
    }

    public function get_assets_page_block ()
    {
        $builder    = $this->db->table('cms_page_block');
        $builder->like('params_values', 'assets');
        $query = $builder->get();
        return $query->getResult();
    }
    public function get_images_by_num ($num)
    {
        $builder    = $this->db->table('dynamic_images');
        $builder->limit(1, $num);
        $query = $builder->get();
        return $query->getFirstRow();
    }
    public function get_assets_page_block_by_num ($num)
    {
        $builder    = $this->db->table('cms_page_block');
        $builder->like('params_values', 'assets');
        $builder->limit(1, $num);
        $query = $builder->get();
        return $query->getResult();
    }

    public function get_page_by_id_domain ($id, $domain) {
        $builder    = $this->db->table('dynamic_pages');
        $query      = $builder->where('id',$id)->where('domain',$domain)->get();
        return $query->getFirstRow();
    }
    public function get_pages_by_id_domain ($id, $domain) {
        $builder    = $this->db->table('dynamic_pages');
        $query      = $builder->where('id',$id)->where('domain',$domain)->get();
        return $query->getResult();
    }
    public function get_pages_by_parentid_domain ($id, $domain) {
        $builder    = $this->db->table('dynamic_pages');
        $query      = $builder->where('parent_id',$id)->where('domain',$domain)->get();
        return $query->getResult();
    }
    public function get_images_by_galleryid_domain ($id, $domain) {
        $builder    = $this->db->table('dynamic_images');
        $query      = $builder->where('dynamic_gallery_id',$id)->where('domain',$domain)->orderBy('position')->get();
        return $query->getResult();
    }
    public function get_submits_by_form_domain ($form, $domain) {
        $builder    = $this->db->table('form_data_' . $form);
        $builder->where('domain', $domain);
        $builder->orderBy('id DESC');
        $query = $builder->get();
        return  $query->getResult();
    }
    public function delete_submits_by_id_form_domain ($id, $form, $domain) {
        $builder    = $this->db->table('form_data_' . $form);
        $builder->where('domain', $domain);
        $builder->where('id', intval($id));
        $builder->delete();
    }
    public function save_preferences ($domain, $data) {
        $builder    = $this->db->table('preferences');
        $count      = $builder->where('domain',$domain)->countAllResults();
        if ( $count ) {
            $builder    = $this->db->table('preferences');
            $query      = $builder->set($data)->where('domain',$domain)->update();
        } else {
            $builder    = $this->db->table('preferences');
            $query      = $builder->set($data)->insert();
        }
    }
    public function update_user ($id, $data) {
        $builder    = $this->db->table('users');
        $builder->set($data)->where('id',$id)->update();
    }
    public function insert_user ( $data) {
        $builder    = $this->db->table('users');
        $builder->set($data)->insert();
    }
    public function delete_user ( $id) {
        $builder    = $this->db->table('users');
        $builder->where('id',$id)->delete();
    }

    public function optimize_img ( $img)
    {

        $builder    = $this->db->table('updated_images');
        $query = $builder->getWhere(['text_title' => $img, 'zipstatus' => '1'])->getFieldCount();
        if ($query == 0) {
            \ShortPixel\setKey(config('app')->shortpixelKey);
            $str = str_replace("\n", '', base_url($img));
            $udata['text_title'] = $img;
            $udata['zipstatus'] = '1';

            $builder    = $this->db->table('updated_images');
            $builder->where('text_title', $img);
            $builder->replace($udata);
            //\ShortPixel\fromUrls($str)->wait(800)->toFiles(FCPATH . pathinfo($img)['dirname']);
            $str_compr = str_replace("\n", '', FCPATH . $img);
            \ShortPixel\fromFiles($str_compr)->toFiles(FCPATH . pathinfo($img)['dirname']);
        }
    }
    public function get_form_data($columns, $form)
    {
        $builder    = $this->db->table('form_data_' . $form);
        $builder->select($columns);
        return $builder->get()->getResult();
    }

    public function save_preferences_blocks ($domain, $data) {
        $builder = $this->db->table('preferences_blocks');
        $count = $builder->where('domain',$domain)->where('content_type', $data['content_type'])->where('cms_block_id', $data['cms_block_id'])->countAllResults();
        if ($count) {
            $builder = $this->db->table('preferences_blocks');
            $query = $builder->set($data)->where('domain',$domain)->where('cms_block_id',$data['cms_block_id'])->where('content_type',$data['content_type'])->update();
        } else {
            $builder = $this->db->table('preferences_blocks');
            $query = $builder->set($data)->insert();
        }
    }

    public function get_preferences_blocks ($domain, $type, $status = false) {
        $builder = $this->db->table('preferences_blocks');
        $builder->join('cms_block', 'cms_block.id = preferences_blocks.cms_block_id');
        $builder->where('domain',$domain)->where('content_type',$type);
        if ($status) {
            $builder->where('preferences_blocks.status','ACTIVED');
            $builder->groupBy('preferences_blocks.cms_block_id');
        }

        $query = $builder->get();

        return $query->getResult();
    }
}
