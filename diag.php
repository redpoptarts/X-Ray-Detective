<?php require_once( dirname(__FILE__) . '/inc/core_config_handler.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link href="styles/style_xray.css" rel="stylesheet" type="text/css" />
<link href="styles/style_weblinks_global.css" rel="stylesheet" type="text/css" />
<link href="styles/style_borders.css" rel="stylesheet" type="text/css" />
<link href="styles/style_backgrounds.css" rel="stylesheet" type="text/css" />
</head>

<body>
<table border="0" class="borderblack_greybg_light_thick">
  <tr>
    <td><table width="800" border="0">
      <tr>
        <th scope="row">PHP Version</th>
        <td><?php if (version_compare(PHP_VERSION, '5.3.0') >= 0) { ?>
          <span class="success">OK</span>
          <?php }else{ ?>
          <span class="error">FAIL</span>
          <?php } ?></td>
      </tr>
      <tr>
        <th scope="row">PHP Timezone Set</th>
        <td><?php $php_config_timezone = ini_get('date.timezone');
	if ( !empty($php_config_timezone) ) { ?>
          <span class="success">OK</span>
          <?php }else{ ?>
          <span class="error">FAIL</span>
          <?php } ?></td>
      </tr>
      <tr>
        <th scope="row">Writeable /config/ Directory</th>
        <td><?php if(is__writeable("config/")){?>
          <span class="success">OK</span>
          <?php }else{ ?>
          <span class="error">FAIL</span>
          <?php } ?></td>
      </tr>
      <tr>
        <th scope="row">Writeable /config/config_database.php</th>
        <td><?php if(is__writeable("config/config_database.php")){?>
          <span class="success">OK</span>
          <?php }else{ ?>
          <span class="error">FAIL</span>
          <?php } ?></td>
      </tr>
      <tr>
        <th scope="row">Writeable /config/config_settings.php</th>
        <td><?php if(is__writeable("config/config_settings.php")){?>
          <span class="success">OK</span>
          <?php }else{ ?>
          <span class="error">FAIL</span>
          <?php } ?></td>
      </tr>
      <tr>
        <th scope="row">&nbsp;</th>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th scope="row">&nbsp;</th>
        <td>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
</table>
</body>
</html>