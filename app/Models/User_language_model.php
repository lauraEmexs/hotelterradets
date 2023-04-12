<?php

namespace App\Models;

use CodeIgniter\Model;

class User_language_model extends Model {
    function __construct() {
        parent::__construct();
    }

    function get_all($where = array()) {
        $builder = $this->db->table('languages');
        $query   = $builder->where($where)->orderBy('position', 'asc')->orderBy('default', 'desc')->orderBy('name', 'asc')->get();
        return $query->getResult();
    }

    function get_default_language() {
        $this->db->select('name');
        $this->db->from('languages');
        $this->db->where(array('default' => 1));
        $query = $this->db->get();
        $result = $query->result();
        return $result[0]->name;
    }

    function get_default_language_id() {
        $builder = $this->db->table('languages');
        $query   = $builder->orderBy('position', 'asc')->where('default', '1')->get();
        return $query->getFirstRow()->id;
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
        $this->db->select('name');
        $this->db->from('languages');
        $this->db->where(array('id' => $id));
        $query = $this->db->get();
        $result = $query->result();
        return $result[0]->name;
    }

}
