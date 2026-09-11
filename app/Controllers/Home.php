<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
{
    if (session()->get('logged_in')) {
        return redirect()->to(base_url('main'));
    }
        
    return redirect()->to(base_url('login'));
}
}
