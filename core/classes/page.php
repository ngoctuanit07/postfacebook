<?php 
class Page{
	public static function Youtube($id){
		$content = file_get_contents("http://youtube.com/get_video_info?video_id=".$id);
		parse_str($content, $data);
		return $data;
	}
}
?>