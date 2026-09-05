<?php
/*
 * Minimal metadata.php fixture for codelab-scaffold snapshot tests.
 * Contains only the insertion markers the metadata patch looks for.
 */

$systemName = 'Fixture System';

$module['main']['title'] = 'Fixture Module';

$subModules['existing_entry'] = 'Existing Entry';
$subModules['cloud_attachments'] = 'Cloud Attachments';

/* ****START SUB MENU**** */
$sub1[$subModules['existing_entry']]['system']      = $systemName;
$sub1[$subModules['existing_entry']]['type']        = 'level1';
/* ****END SUB MENU***** */

/* ****START SUB MENU***** */
$sub1[$subModules['cloud_attachments']]['system']      = $systemName;
$sub1[$subModules['cloud_attachments']]['main_module'] = $module['main']['title'];
$sub1[$subModules['cloud_attachments']]['menu_level1'] = $subModules['cloud_attachments'];
$sub1[$subModules['cloud_attachments']]['url']         = site_url('cloud_attachments');
$sub1[$subModules['cloud_attachments']]['icon']        = 'fa fa-paperclip';
$sub1[$subModules['cloud_attachments']]['subitem']     = array();
$sub1[$subModules['cloud_attachments']]['type']        = 'level1';

/* 
	* Building menu 
*/
$module['sub'][$subModules['cloud_attachments']] = array(
	'system'       => $systemName,
	'sub_level2'   => '',
	'sub_level1'   => $sub1[$subModules['cloud_attachments']]['menu_level1'],
	'module_label' => $module['main']['title'],
	'menu_label'   => $subModules['cloud_attachments'],
	'description'  => 'Manage All ' . $subModules['cloud_attachments'],
	'icon'         => 'fa fa-paperclip',
	'roles'        => array(
		$systemName . ' View ' . $subModules['cloud_attachments'],
		$systemName . ' Create ' . $subModules['cloud_attachments'],
		$systemName . ' Edit ' . $subModules['cloud_attachments'],
		$systemName . ' Export ' . $subModules['cloud_attachments'],
	)
);
/* ****END SUB MENU***** */

/* 
 * CHECK ROLES
*/
