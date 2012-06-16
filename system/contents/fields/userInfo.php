<?php
if($template instanceof Template)
	if(getUser())
		$template->assignFromNew('#userInfo', 'userInfo.xhtml', array(
			'USERNAME' => getUser()->data->name
		));
	else
		$template->assignFromNew('#userInfo', 'userLogin.xhtml');