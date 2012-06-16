<?php

if ($template instanceof Template)
{
	$projectTemplate = new Template(SYS_TEMPLATE_FOLDER . 'html/imprint.xhtml');
	$template->assign('#content', $projectTemplate);
}
