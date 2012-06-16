<?php
if(isset($_POST['username']) && isset($_POST['password']))
	if(getUser($_POST['username'], $_POST['password']))
		include(SYS_FIELD_DIR.'navigation.php');

include(SYS_FIELD_DIR.'userInfo.php');