<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Class BaseBlock
 */
class Auth_model extends Model {

    function __construct() {
        parent::__construct();
    }

    function login($username, $password, $remember = FALSE)
    {
        $builder = $this->db->table('users');
        $query = $builder->where(['username' => $username])->get();
        $user = $query->getFirstRow();
        if ($user) {
            return (password_verify($password, $user->password)) ? $user : false;
        }
        return false;
    }

    function get_user($id){
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $user = $query->row();
        return $user;
    }

    function get_users(){
        $builder = $this->db->table('users');
        $results= $builder->get()->getResult();
        return $results;
    }

    function get_user_by_id($id){
        $builder = $this->db->table('users');
        $builder->where('id', $id);
        $query = $builder->get();
        $user = $query->getFirstRow();
        return ($user) ? $user : false;
    }
}
