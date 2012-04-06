<?php if ( ! defined('BASEPATH')) header("Location: /");

class Setup extends CI_Controller {
	
	// Class Constructor
	public function __construct()
	{
		parent::__construct():
	}

	// Base setup - user, pass, dbname, etc.
	public function base_setup()
	{
		$this->view->load('base_setup');
	}

	// Authentication method setup.
	public function auth_setup()
	{
		$this->view->load('auth_setup');
	}

	// World configuration.
	public function worlds_setup()
	{
		$this->view->load('worlds_setup');
	}

}

