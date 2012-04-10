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

	public function index()
	{
		$this->view->load('front');
	}

	public function update_stats()
	{
		// Here, we update the stats.
	}

	public function clear_xrd_db()
	{
		// Here, we clear the DB for new stats
	}

	public function settings()
	{
		// Here, we change the XRD configuration.
	}

}