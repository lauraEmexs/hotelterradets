<?php
namespace App\Controllers\Admin;


use App\Models\Auth_model;
use App\Controllers\Admin\BaseAdminController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Users extends BaseAdminController
{
    public $data;

    var $table_template = array (
        'table_open'          => '<table class="table table-striped table-bordered table-hover" id="users_list">',
        'heading_row_start'   => '<tr>',
        'heading_row_end'     => '</tr>',
        'heading_cell_start'  => '<th>',
        'heading_cell_end'    => '</th>',

        'row_start'           => '<tr>',
        'row_end'             => '</tr>',
        'cell_start'          => '<td>',
        'cell_end'            => '</td>',

        'row_alt_start'       => '<tr>',
        'row_alt_end'         => '</tr>',
        'cell_alt_start'      => '<td>',
        'cell_alt_end'        => '</td>',

        'table_close'         => '</table>'
    );

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->data['languages'] = $this->admin_language_model->get_all(array('actived'=>'1') );

        if (!\Config\Services::session()->get('userdata')) {
            header('Location: /admin/auth');
            die ();
        }

        $this->auth_model = new Auth_model ();

        $this->data['slug'] = 'users';
        $this->data['menu'] = 'management';
        $this->data['menu2'] = 'users';
        $this->data['page_title'] = 'Users';

        if (isset($_GET['domain']) && in_array($_GET['domain'], config('App')->allowedDomains, TRUE)) {
            $this->data['domain'] = $_GET['domain'];
            $this->data['selected_domain'] = $_GET['domain'];
        } else {
            $this->data['domain'] = config('App')->defaultDomain;
            $this->data['selected_domain'] = config('App')->defaultDomain;
        }

        $this->selected_domain_preferences = $this->admin_general_model->get_domain_preferences($this->data['selected_domain']);
        $this->data['admin_menu_items'] = $this->admin_general_model->get_admin_menu_items();
    }

    public function index(){
        $this->data['page_title'] = 'Users';
        $this->data['title_section'] = 'Users list';
        $this->table->setTemplate($this->table_template);
        $this->table->setHeading('id', 'Username', 'Group', 'Domain', 'Actions');
        $users=$this->auth_model->get_users();

        foreach($users as $user){
            $status=($user->status=='ACTIVED')?
                anchor('admin/users/upause/' . $user->id, '<input type="checkbox" checked
            data-toggle="toggle" data-size="mini" class="pause-toggle" data-on="<i class=\'fa fa-eye\'></i>" data-off="<i class=\'fa fa-eye-slash\'></i>" 
            data-onstyle="success" >', array('class' => '', 'onclick' => "return confirm('Do you want pause this user?')"))
                :
                anchor('admin/users/uresume/' . $user->id, '<input type="checkbox"
            data-toggle="toggle" data-size="mini" class="pause-toggle" data-on="<i class=\'fa fa-eye\'></i>" data-off="<i class=\'fa fa-eye-slash\'></i>" 
            data-onstyle="success" >', array('class' => '', 'onclick' => "return confirm('Do you want enable this user?')"));
            $this->table->addRow(
                $user->id,
                $user->username ,
                $user->group,
                $user->domain,
                anchor('admin/users/uedit/'. $user->id, '<i class="fa fa-pencil fa-fw"></i>', array('class' => ''))
                ." ".anchor('admin/users/udelete/'.$user->id, '<i class="fa fa-trash-o fa-fw"></i>', array('class' => 'dlist-delete-button', 'onclick' => "return confirm('Do you want delete this user?')"))
            //." ".$status." "
            );
        }

        $this->data['table'] = $this->table;
        return view('admin/users', $this->data);
    }

    public function uedit($id)
    {
        $this->data['title_section'] = 'Usuario';
        $this->data['id'] = $id;
        $this->data['user'] = $this->auth_model->get_user_by_id($id);
        return view('admin/uedit', $this->data);
    }

    public function usave($id)
    {
        $this->data['page_title'] = 'Users';
        $this->data['title_section'] = 'Users';

        if ($id && $id != 0) {
            $data = [
                'username' => trim($this->request->getPost('username')),
                'group' => $this->request->getPost('group'),
                'domain' => $this->request->getPost('domain'),
            ];
            if ($_POST['password'] != '')
                $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);
            $this->admin_general_model->update_user($id, $data);
        } else {
            $data = [
                'username' => trim($this->request->getPost('username')),
                'group' => $this->request->getPost('group'),
                'domain' => $this->request->getPost('domain'),
            ];
            $data['password'] = ($this->request->getPost('password') != '')
                ? password_hash($this->request->getPost('password'), PASSWORD_BCRYPT)
                : password_hash(substr(strtoupper(md5(mktime())),0,10), PASSWORD_BCRYPT);
            $this->admin_general_model->insert_user($data);
        }
        return redirect()->to(base_url('admin/users'));
    }

    public function uwellcome($id){
        $user=$this->auth_model->get_user_by_id(intval($id));
        if(!$user)
            return redirect()->to(base_url('admin/users'));

        $token=md5(mktime());
        $this->auth_model->set_token($user->id,$token);
        $data=array('token'=>$token, 'username'=>$user->username );
        $message = utf8_decode(view('mail/create_your_password', $data, TRUE));
        $this->load->library('email');
        $this->email->clear();
        $this->email->initialize(array('mailtype' => 'html', 'charset'=>'ISO-8859-1', 'crlf'=>"\n" ) );
        $this->email->set_newline("\n");
        $this->email->from('nuria.estape@archroma.com', 'Archroma Textile Portfolio Converter');
        $this->email->to($user->username);
        $this->email->subject("Project FIT � Launch of Archroma Digital Textile Portfolio Converter");
        $this->email->message($message);
        $this->email->send();

        return redirect()->to(base_url('admin/users'));
    }

    public function upause($id){
        $this->db->where('id', intval($id));
        $this->db->update('users', array('status'=>'PAUSED'));
        return redirect()->to(base_url('admin/users'));
    }

    public function uresume($id){
        $this->db->where('id', intval($id));
        $this->db->update('users', array('status'=>'ACTIVED'));
        return redirect()->to(base_url('admin/users'));
    }

    public function udelete($id){
        $this->admin_general_model->delete_user($id);
        return redirect()->to(base_url('admin/users'));
    }
}
