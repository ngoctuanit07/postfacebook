<?php
class scheduledposts {
	private $db = null;

	public function __construct(){
		$this->db = DB::getInstance();
	}
	
	// Get scheduled posts
	public function get($id = null){
		if($id){
			return $this->db->QueryGet("SELECT * FROM scheduledposts WHERE id = ? ",array($id))->first();
		}else{
			return $this->db->QueryGet("SELECT * FROM scheduledposts ORDER BY id DESC")->results();
		}
	}
	
	// Get user scheduled posts
	public function userPosts($id = null){
		$user = new User();
		if($id){
			return $this->db->QueryGet("SELECT * FROM scheduledposts WHERE id = ? AND userid = ? ",array($id,$user->data()->id))->first();
		}else{
			return $this->db->QueryGet("SELECT * FROM scheduledposts WHERE userid = ? ORDER BY id DESC",array($user->data()->id))->results();
		}
	}
	
	// Save scheduled posts
	public static function save($params){
		return DB::Getinstance()->Insert("scheduledposts",$params);
	}
	
	// Get posts that have status 0 (not completed) and pause = 0 and next post date <= current date
	public function post(){
		return $this->db->QueryGet("SELECT * FROM scheduledposts WHERE status = 0 AND pause = 0 ")->results();
	}

	// Delete scheduled posts
	public function delete($id){
		$user = new User();
		$this->db->Query("DELETE FROM scheduledposts WHERE id = ? AND  userid = ? ",array($id,$user->data()->id));
		$this->db->Query("DELETE FROM logs WHERE scheduledposts = ? AND  userid = ? ",array($id,$user->data()->id));
	}
	
}
?>