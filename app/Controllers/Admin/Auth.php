<?php

namespace App\Controllers\Admin;

use App\Models\Auth_model;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Auth extends Controller
{
    public $data;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Helpers
        helper('url');
        helper('form');
        $this->form_validation = \Config\Services::validation();
        $this->auth_model = new Auth_model ();
    }

    public function index() {
        if (!\Config\Services::session()->get('userdata'))
            return redirect()->to('/admin/auth/login');
        else
            return redirect()->to('/admin/');
    }

    public function login()
    {
        ## Url del logo
        $this->form_validation->setRule('username', 'Usuario', 'required');
        $this->form_validation->setRule('password', 'Password', 'required');

        $this->data['username'] = array('name' => 'username',
            'id' => 'username',
            'type' => 'text',
            'value' => $this->request->getPost('username'),
            'placeholder' => 'Nombre de usuario',
            'autofocus' => 'autofocus',
            'class' => 'form-control',
        );
        $this->data['password'] = array('name' => 'password',
            'id' => 'password',
            'type' => 'password',
            'placeholder' => 'Contraseña',
            'class' => 'form-control',
        );
        $this->data['enviar'] = array(
            'name' => 'submit',
            'value' => 'Login',
            'class' => 'btn btn-success btn-login',
        );
        $this->data['remember'] = array(
            'id' => 'remember_pass',
            'class' => 'btn-warning',
            'type' => 'button',
            'data-toggle' => 'modal',
            'data-target' => '#forgot_pass',
            'content' => 'Recordar contraseña'
        );

        if ($this->form_validation->withRequest($this->request)->run() === TRUE) {
            $remember = (bool) $this->request->getPost('remember');

            $user = $this->auth_model->login($this->request->getPost('username'), $this->request->getPost('password'), $remember);
            if ($user) {
                \Config\Services::session()->set('userdata', $user);
                return redirect()->to('admin/');
            } else {
                \Config\Services::session()->setFlashdata('message', '<div class="alert alert-warning">Usuario o contraseña incorrectos</div>');
                return redirect()->to('admin/auth/login');
            }
        } else
            return view('admin/login', $this->data);
    }

    function logout() {
        \Config\Services::session()->remove('userdata');
        \Config\Services::session()->setFlashdata('message', '<div class="alert alert-info">Logout correctamente</div>');
        return redirect()->to('admin/auth/login');
    }

}
