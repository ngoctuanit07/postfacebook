<?php
class Template
{
	private $_db,
			$_template,
			$_templateFolder,
			$_templateFolderPath;
	
	public function __construct(){
		$this->_db = DB::getInstance();
		$this->_template = new TemplateRender();
		$this->_templateFolder = Options::Get("siteurl").'/theme/default';
		$this->_templateFolderPath = ABSPATH.'/theme/default';
	} 
	
	// header Template
	public function header($title,$extraContent = null){
		$this->_template->assign('templateFolder',$this->_templateFolder);
		$this->_template->assign('title',$title);
		$this->_template->render($this->_templateFolderPath,'header');
	}

	public function signUp(){
		$this->_template->assign('templateFolder',$this->_templateFolder);
		$this->_template->render($this->_templateFolderPath,'signup');
	}
	
	public function signIn(){
		$this->_template->assign('templateFolder',$this->_templateFolder);
		$this->_template->render($this->_templateFolderPath,'signin');
	}


	//footer Template 
	public function footer(){
		//$this->_template->assign('','');
		$this->_template->render($this->_templateFolderPath,'footer');
	}

}


?>