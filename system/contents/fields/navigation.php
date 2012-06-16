<?php
/*
 * Füllt die Navigation entsprechend dem Berechtigungslevel.
 *
 * @author Cédric Neukom
 */
$navigation = new Template(SYS_TEMPLATE_FOLDER . 'html/navigation.xhtml');
$template->assign('#navigation', $navigation);

$navigation->assignFromNew('links', 'navigationLink.xhtml', array(
	'page' => 'imprint',
	'label' => 'Imprint'
));

if(getUser() && getUser()->access_level == 0) // Admin Interface
	$navigation->assignFromNew('links', 'navigationLink.xhtml', array(
		'page' => 'editAdmin',
		'label' => 'Admin'
	));
else if(getUser()) // Benutzerseite
	$navigation->assignFromNew('links', 'navigationLink.xhtml', array(
		'page' => 'editUser',
		'label' => 'Profile'
	));

$navigation->assignFromNew('links', 'navigationLink.xhtml', array(
	'page' => 'home',
	'label' => 'Home'
));
