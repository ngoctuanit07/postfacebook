<?php  if (!defined('ABSPATH')) exit('No direct script access allowed');
class TemplateRender{
	private $vars = array();
	public function assign($key, $value){
		$this->vars[$key] = $value;
	}
	
	public function render($template_folder,$template_name,$fileExt = "php"){
		$path = $template_folder.'/'.$template_name . '.'.$fileExt;
		if(file_exists($path)){
			$contents = file_get_contents($path);
			foreach($this->vars as $key => $value){
				$contents = preg_replace('/\{{'.$key.'\}}/',$value,$contents);
			}
			eval(' ?>'.$contents.'<?php ');
		}else{
			exit("The File ".$path." Not found!");
		}
	}
}
?>