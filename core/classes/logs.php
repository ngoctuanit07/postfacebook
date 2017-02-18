<?php
class Logs {
	
	public static function save($scheduledPostsId,$content){
		$date = new DateTime();
		DB::getInstance()->insert("logs",
				array(
				'scheduledposts' => $scheduledPostsId,
				'content' => $content,
				'date' => $date->format('Y-m-d H:i')
				)
			);
	}
	
	// Get logs
	public function get($id = null){
		$user = new user();
		if($id){
			return DB::getInstance()->QueryGet("SELECT * FROM logs WHERE scheduledposts = ? AND scheduledposts in (SELECT id FROM scheduledposts WHERE userid = ?)  ORDER BY `date` DESC",array($id,$user->data()->id))->results();
		}else{
			return DB::getInstance()->QueryGet("SELECT * FROM logs WHERE scheduledposts in (SELECT id FROM scheduledposts WHERE userid = ?) ORDER BY `date` DESC",array($user->data()->id))->results();
		}
	}
	
	public static function Clear(){
		$user = new user();
		return DB::GetInstance()->Query("DELETE FROM logs WHERE scheduledposts = (SELECT id FROM scheduledposts WHERE userid = ?)",array($user->data()->id));
	}
}
?>