<?php

class logger_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function log($level, $message)
	{
		$data = array
		(
			'level' => $level,
			'message' => $message,
			'created' => date('Y-m-d H:i:s')
		);

		$this->db->insert('x-log', $data);
	}

}