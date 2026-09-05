<?php
/*
 * Minimal metadata.php fixture for codelab-scaffold snapshot tests.
 * Contains only the insertion markers the metadata patch looks for.
 */

$systemName = 'Fixture System';

$module['main']['title'] = 'Fixture Module';

$subModules['existing_entry'] = 'Existing Entry';
$subModules['Activity Design'] = 'Activity Design';

/* ****START SUB MENU**** */
$sub1[$subModules['existing_entry']]['system']      = $systemName;
$sub1[$subModules['existing_entry']]['type']        = 'level1';
/* ****END SUB MENU***** */

/* ****START SUB MENU***** */
$sub1[$subModules['Activity Design']]['system']      = $systemName;
$sub1[$subModules['Activity Design']]['main_module'] = $module['main']['title'];
$sub1[$subModules['Activity Design']]['menu_level1'] = $subModules['Activity Design'];
$sub1[$subModules['Activity Design']]['url']         = site_url('activity_designs');
$sub1[$subModules['Activity Design']]['icon']        = 'fa fa-file';
$sub1[$subModules['Activity Design']]['subitem']     = array();
$sub1[$subModules['Activity Design']]['type']        = 'level1';

/* 
	* Building menu 
*/
$module['sub'][$subModules['Activity Design']] = array(
	'system'       => $systemName,
	'sub_level2'   => '',
	'sub_level1'   => $sub1[$subModules['Activity Design']]['menu_level1'],
	'module_label' => $module['main']['title'],
	'menu_label'   => $subModules['Activity Design'],
	'description'  => 'Manage All ' . $subModules['Activity Design'],
	'icon'         => 'fa fa-file',
	'roles'        => array(
		$systemName . ' View ' . $subModules['Activity Design'],
		$systemName . ' Create ' . $subModules['Activity Design'],
		$systemName . ' Edit ' . $subModules['Activity Design'],
		$systemName . ' Review ' . $subModules['Activity Design'],
		$systemName . ' Approve ' . $subModules['Activity Design'],
		$systemName . ' Cancel ' . $subModules['Activity Design'],
		$systemName . ' Decline ' . $subModules['Activity Design'],
		$systemName . ' Export ' . $subModules['Activity Design'],
	)
);
/* ****END SUB MENU***** */

/* 
 * CHECK ROLES
*/
