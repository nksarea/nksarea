<?php
/**
 * @deprecated
 */
class Navigation {

	private $user;
	private $tmpl;

	public function __construct($user, $template) {
		if(get_class($user) !== 'User')
			throw new UnexpectedValueException('$user must be an instance of User');
		if(get_class($template) !== 'Template')
			throw new UnexpectedValueException('$template must be an instance of Template');
		$this->user = $user;
		$this->tmpl = $template;
		$this->create();
	}

	private function create() {
		if($this->user->loggedin)
			$this->tmpl->assign('NAVI', 'Du bist eingeloggt.');
		else {
			$lf = new Template('system/template/login.xml');
			$this->tmpl->assign('NAVI', $lf);
		}
	}

}