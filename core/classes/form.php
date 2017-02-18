<?php if (!defined('ABSPATH')) exit('No direct script access allowed');
class Form {	
	
	// <Form>		-------------------------------------------------------------------------------
		public static function formOpen($attr = null){
			$form = "<form ";
			foreach($attr as $key => $value){
				$form .= $key."='".$value."' ";
			}
			$form .= " />";
			echo $form;
		}
		
		public static function formClose(){
			echo "</form>";
		}
	
	// <Input>		-------------------------------------------------------------------------------
	public static function input($attr = null){
		$input = "<input ";
		foreach($attr as $key => $value){
			$input .= $key."='".$value."' ";
		}
		$input .= " />";
		
		echo $input;
	}
	
	// <Select>		-------------------------------------------------------------------------------
		public static function selectOpen($selectAttr = null){
			//	<select Attributes>
			$select = "<select ";
			foreach($selectAttr as $key => $value){
				$select .= $key."='".$value."' ";
			}
			$select .= ">";
		
			echo $select;
		}
		
		public static function selectClose(){
			echo "</select>";
		}
		
		
		public static function selectOption($selectOptAttr = array('value'=>''),$selectOptContent = null){
			// <option Attributes>
			$option = "<option ";
			foreach($selectOptAttr as $key => $value){
				$option .= $key."='".$value."' ";
			}
			$option .= ">".$selectOptContent."</option>";
			
			echo $option;
		}
	
	// <Textarea>	-------------------------------------------------------------------------------
	public static function textarea($attrs = null,$content = null){
		$textarea = "<textarea ";
		foreach($attrs as $key => $value){
			$textarea .= $key."='".$value."'";	
		}
		$textarea .= ">".$content."</textarea>";
		
		echo $textarea;
	}
	
	// <button>		-------------------------------------------------------------------------------
	public static function button($attrs = null,$content){
		$button = "<button ";
		foreach($attrs as $key => $value){
			$button .= $key."='".$value."'";	
		}
		$button .= ">".$content."</button>";
		
		echo $button;
	}
	
}
?>