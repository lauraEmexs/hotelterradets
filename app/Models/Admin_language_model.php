<?php

namespace App\Models;

use CodeIgniter\Model;

class Admin_language_model extends Model {

	function __construct() {
		parent::__construct();
	}

	function get_all($where = array()) {
		$builder = $this->db->table('languages');
		$query   = $builder->where($where)->orderBy('position', 'asc')->orderBy('default', 'desc')->orderBy('name', 'asc')->get();
		return $query->getResult();
	}

	function insertar($data) {
		$this->db->insert('languages', $data);
		return $this->db->insert_id();
	}

	function actualizar($id, $data) {
		$this->db->where('id', $id);
		return $this->db->update('languages', $data);
	}

	function eliminar($id) {
		$this->db->where('id', $id);
		$this->db->delete('languages');
	}

	function set_default_language($id) {
		$data = array('default' => 0);
		$this->db->update('languages', $data);

		$data = array('default' => 1);
		$this->db->where('id', $id);
		$this->db->update('languages', $data);
	}

	function get_default_language() {
        $builder = $this->db->table('languages');
        $query   = $builder->select('name')->where('default', '1')->get();
        $results = $query->getResult();
        if (empty($results))
            return false;
        $row = $query->getFirstRow();
		return $row->name;
	}

	function get_default_language_id() {
        $builder = $this->db->table('languages');
        $query   = $builder->select('id')->where('default', '1')->get();
        $results = $query->getResult();
		if (empty($results))
			return false;
		$row = $query->getFirstRow();
		return $row->id;
	}

	function is_actived($id) {
		$this->db->select('id');
		$this->db->from('languages');
		$this->db->where('id', $id);
		$this->db->where('actived', 1);
		$query = $this->db->get();
		$results = $query->result();
		return (!empty($results)) ? true : false;
	}

	function get_idioma($id) {
        $builder = $this->db->table('languages');
        $query   = $builder->select('name')->where('id', $id)->get();
        return $query->getFirstRow()->name;
	}

	function set_actived_language($list_id) {
		$data = array('actived' => 0);
        $builder = $this->db->table('languages');
		$builder->update($data);
		$data = array('actived' => 1);
        $builder = $this->db->table('languages');
        $builder->whereIn('id', $list_id)->update($data);
	}

	function set_actived_web_language($list_id) {
        $data = array('actived_web' => 0);
        $builder = $this->db->table('languages');
        $builder->update($data);
        if (!$list_id) return;
        $data = array('actived_web' => 1);
        $builder = $this->db->table('languages');
        $builder->whereIn('id', $list_id)->update($data);
	}
}
