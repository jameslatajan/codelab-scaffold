<?php
/*
 * Minimal metadata.php fixture for codelab-scaffold snapshot tests.
 * Contains only the insertion markers the metadata patch looks for.
 */

$systemName = 'Fixture System';

$module['main']['title'] = 'Fixture Module';

$subModules['existing_entry'] = 'Existing Entry';

/* ****START SUB MENU**** */
$sub1[$subModules['existing_entry']]['system']      = $systemName;
$sub1[$subModules['existing_entry']]['type']        = 'level1';
/* ****END SUB MENU***** */

/* 
 * CHECK ROLES
*/
