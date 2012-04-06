<?php

class Auth {
	
	var $ci;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function create_user($username, $password, $level)
	{
		$passes = rand(1,10);
		$salt = substr(md5(uniqid(mt_rand(), true)), 0, 16);

		$password = $salt.$password;

		for($x = 0; $x < $passes; $x++)
		{
			$password = md5($password);
		}

		$data = array
		(
			'username' => $username,
			'password' => $password,
			'level' => $level,
			'salt' => $salt,
			'passes' => $passes
		);

		$this->CI->db->insert('x-auth', $data);
	}

	public function try_login($username, $password)
	{
		$data = $this->CI->db->get_where('x-auth', array('username' => $username));
		if(count($data) !== 1)
		{
			return FALSE
		}
		else
		{
			$data = $data->row();
			$passes = $data->passes;
			$salt = $data->salt;
			$hash = $data->password;
			$level = $data->hash;

			for($x = 0; $x < $passes; $x++)
			{
				$password = md5($password);
			}

			if($password === $hash)
			{
				// We've got a successful login! Fuck yeah!
				$this->CI->session->set_userdata(array('level' => $level));
				return TRUE;
			}
			return FALSE;
		}
	}

}