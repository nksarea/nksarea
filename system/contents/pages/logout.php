<?php
if(getUser())
	getUser()->logout();

include(SYS_FIELD_DIR.'navigation.php');
include(SYS_FIELD_DIR.'userInfo.php');