<?php

include_once('system/functions/createFilesList.fn.php');
include_once('system/functions/createCommentList.fn.php');

if ($template instanceof Template)
{
	if (!empty($_GET['pid']))
	{
		$project = new project($_GET['pid']);
		$projectTemplate = new Template(SYS_TEMPLATE_FOLDER . 'html/project.xhtml');
		$template->assign('#content', $projectTemplate);
		$template->addCSS('styles/css/color-' . $project->color . '.css');

		$projectTemplate->assign('name', $project->name);
		$projectTemplate->assign('icon', $project->pid . '.jpg');

		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => 'Author:', 'value1' => $project->owner, 'key2' => 'Date:', 'value2' => $project->upload_time));
		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => 'List:', 'value1' => $project->list, 'key2' => '', 'value2' => ''));
		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => '', 'value1' => '', 'key2' => '', 'value2' => ''));
		if ($project->info !== NULL)
		{
			$keys = array_keys($project->info);

			for ($i = 0; $i < count($keys); $i += 2)
			{
				$key1 = $keys[$i];
				$value1 = $project->info[$key1];

				if (empty($keys[$i + 1]))
					$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => $key1, 'value1' => $value1, 'key2' => '', 'value2' => ''));
				else
					$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => $key1, 'value1' => $value1, 'key2' => $keys[$i + 1], 'value2' => $project->info[$keys[$i + 1]]));
			}
		}

		for ($i = count($keys) / 2; $i <= 3; $i++)
			$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => '', 'value1' => '', 'key2' => '', 'value2' => ''));

		$projectTemplate->assign('description', str_replace('{newline}', '<br />', $project->description));
		createFilesList($project->viewContent(), $projectTemplate, '/', 'show');

		$commentList = new CommentList(CommentList::TYPE_PROJECT, $_GET['pid']);
		createCommentList($commentList->comments, $projectTemplate);
	}
	else
	{
		//@TODO: Add error handling;
	}
}
?>
