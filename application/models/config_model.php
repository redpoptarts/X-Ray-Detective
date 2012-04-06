<?php

class config_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function get_config_option($key)
	{
		$data = $this->db->get_where('x-config', array('conf_key' => $key));
		return $data->row();
	}

	public function set_config_option($key, $value)
	{
		$this->db->query("INSERT INTO `x-config` VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE `x-auth` SET `conf_val` = '$value' WHERE `conf_key = '$key'");
	}

	public function get_all_config_options()
	{
		$data = $this->db->get('x-config');
		return $data->result();
	}

	public function get_config_app_version()
	{
		$data = $this->db->get_where('x-config', array('conf_key' => 'xrd_version'));
		return $data->row();
	}

}