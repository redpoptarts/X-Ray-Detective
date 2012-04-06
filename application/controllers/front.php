<?php

class Front extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata('level'))
		{
			redirect('/login');
		}
		
		$this->load->model('config_model', 'config', TRUE);
		$this->load->model('logger_model', 'logger', TRUE);

	}

}