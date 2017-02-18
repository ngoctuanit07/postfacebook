<?php 
class fbaccount {
	
	private $userid = null;
	private $fbid = null;
	private $firstname = null;
	private $lastname = null;
	private $defaultApp = null;
	private $groups = null;
	private $pages = null;
	private $totalGroups = 0;
	
	// Userid setter and getter
	public function setUserId($userid){
		$this->userid = $userid;
	}
	
	public function getUserId(){
		return $this->userid;
	}

	// fbid  setter and getter
	public function setFbid($fbid){
		$this->fbid = $fbid;
	}

	public function getFbid(){
		return $this->fbid;
	}

	// Firstname setter and getter
	public function setFirstname($firstname){
		$this->firstname = $firstname;
	}

	public function getFirstname(){
		return $this->firstname;
	}

	// Lastname  setter and getter
	public function setLastname($lastname){
		$this->lastname = $lastname;
	}

	public function getLastname(){
		return $this->lastname;
	}

	// Groups  setter and getter
	public function setDefaultApp($defaultApp){
		$this->defaultApp = $defaultApp;
	}

	public function getDefaultApp(){
		return $this->defaultApp;
	}

	// Groups  setter and getter
	public function setGroups($groups){
		$this->groups = $groups;
	}

	// Pages  setter and getter
	public function setPages($pages){
		$this->pages = $pages;
	}
	
	public function getPages(){
		return $this->pages;
	}

	// Save current instance
	public function save(){
		try{

			if( $this->userid == null || $this->fbid == null ) 
				throw new Exception("User ID and facebook ID can not be empty.");
			

			$fields = array();

			$fields["user_id"] = $this->userid;
			$fields["fb_id"] = $this->fbid;
			$fields["firstname"] = $this->firstname;
			$fields["lastname"] =  $this->lastname;
			$fields["groups"] = $this->groups;
			$fields["pages"] = $this->pages;

			if($this->defaultApp)
				$fields["defaultApp"] = $this->defaultApp;

			DB::getInstance()->Insert("fb_accounts",$fields);

		}catch(Exception $e){
			throw new Exception($e);
		}
	}

	// Update current instance
	public function update(){
		try{

			if( $this->userid == null || $this->fbid == null ) 
				throw new Exception("User ID and facebook ID can not be empty.");

			DB::getInstance()->Query("UPDATE fb_accounts SET 
				firstname = ? ,
				lastname = ? ,
				groups = ? ,
				pages = ? 
				WHERE user_id = ? AND fb_id = ?
			",array(
				$this->firstname,
				$this->lastname,
				$this->groups,
				$this->pages,
				$this->userid,
				$this->fbid)
			);

		}catch(Exception $e){
			throw new Exception($e);
		}
	}


	public function get($fbid,$userid = null){

		if($userid == null){
			$user = new User();
			$userid = $user->data()->id;
		}

		$f = new fbaccount();

		$fbAccount = DB::GetInstance()->QueryGet("SELECT * FROM fb_accounts WHERE user_id = ? AND fb_id = ? ",array($userid,$fbid));

		if($fbAccount->count()){
			
			$f->userid = $fbAccount->first()->user_id;
			$f->fbid = $fbAccount->first() ->fb_id;
			$f->firstname = $fbAccount->first() ->firstname;
			$f->lastname = $fbAccount->first() ->lastname;
			$f->groups = $fbAccount->first() ->groups;
			$f->pages = $fbAccount->first() ->pages;
			$f->defaultApp = $fbAccount->first() ->defaultApp;

		}

		return $f;
	}


	public function  getAll($userid = null){

		if($userid == null){
			$user = new User();
			$userid = $user->data()->id;
		}

		$fba = new ArrayObject();	
		
		$fbAccount = DB::GetInstance()->QueryGet("SELECT user_id,fb_id,firstname,lastname FROM fb_accounts WHERE user_id = ? ",array($userid));

		if($fbAccount->count()){
			foreach($fbAccount->results() as $f){
				$tempfb = new fbaccount();
				$tempfb->userid = $f->user_id;
				$tempfb->fbid = $f->fb_id;
				$tempfb->firstname = $f->firstname;
				$tempfb->lastname = $f->lastname;
				$fba[] = $tempfb;
			}
		}

		return $fba;
	}

	public function delete($id){
		
		$user = new User();
		
		// Delete the facebook account is exists
		if($this->exists($id)){
			
			// Delete the account
			DB::GetInstance()->query("DELETE FROM fb_accounts WHERE fb_id = ? AND user_id = ? ",array(
				$id,
				$user->data()->id
			));

			// Delete the account apps
			DB::GetInstance()->query("DELETE FROM user_fbapp WHERE fb_id = ? AND userid = ? ",array(
				$id,
				$user->data()->id
			));

			// Remove the account from user options if it is the default account
			if($this->UserDefaultFbAccount() == $id){
				$user->UpdateOptions(array("default_Fb_Account"=>""));
			}

			return true;
		}
		throw new Exception(lang("FB_ACCOUNT_NOT_EXISTS"));
	}

	public function exists($fbid,$userid = null){

		if($userid == null){
			$user = new User();
			$userid = $user->data()->id;
		}

		$fbAccount = DB::GetInstance()->QueryGet("SELECT * FROM fb_accounts WHERE user_id = ? AND fb_id = ? ",array($userid,$fbid));

		return $fbAccount->count();
	}


	public function UserDefaultFbAccount(){
		$user = new User();

		if(isset($user->Options()->default_Fb_Account)){
			if(trim($user->Options()->default_Fb_Account)){
				return $user->Options()->default_Fb_Account;
			}
		}

		return false;
	}

	public function UserFbAccountDefaultApp(){
		$user = new User();

		if(isset($user->Options()->default_Fb_Account)){
			if(trim($user->Options()->default_Fb_Account)){
				$query = "SELECT defaultApp FROM fb_accounts WHERE user_id = ? AND fb_id = ? ";
				$result = DB::GetInstance()->QueryGet($query,array($user->data()->id,$user->Options()->default_Fb_Account));
				if($result->first()){
					return $result->first()->defaultApp;
				}
			}
		}

		return false;
	}


	// Update current facebook default app
	public function updateDefaultApp($app){

		try{

			if($this->UserDefaultFbAccount()){
				$user = new user();
				$query = "UPDATE fb_accounts SET defaultApp = ? WHERE user_id = ? AND fb_id = ? ";
				DB::getInstance()->Query($query,
					array(
						$app,
						$user->data()->id,
						$this->UserDefaultFbAccount()
					)
				);
			}

		}catch(Exception $e){
			throw new Exception($e);
		}
	}
	/*
	|--------------------------------------------------------------------------
	| get the list of categories
	|--------------------------------------------------------------------------
	|
	|
	*/ 
	public function GetGroupCategories($fbAccount){
		$user = new User();
		return DB::GetInstance()->QueryGet("SELECT id,category_name FROM groups_category WHERE fb_id = ? AND user_id = ? ",array($fbAccount,$user->data()->id))->results();
		
	}
	/*
	|--------------------------------------------------------------------------
	| get the list of groups of the current user
	|--------------------------------------------------------------------------
	|
	|
	*/ 
	public function GetGroups($category = null){
		$user = new User();
		$fbAccount = $this->UserDefaultFbAccount();
		$groups = null;

		if($category){
			$groups = DB::GetInstance()->QueryGet("SELECT groups FROM groups_category WHERE id = ? AND fb_id = ? AND user_id = ? ",array(
					$category,
					$fbAccount,
					$user->data()->id
				));
		}else{
			if(Session::exists("groupscategory")){
				if(Session::get("groupscategory") != -1){
					if( $this->currentFbAccountHasCat(Session::get("groupscategory")) ){
						$groups = DB::GetInstance()->QueryGet("SELECT groups FROM groups_category WHERE id = ? AND fb_id = ? AND user_id = ? ",array(
							Session::get("groupscategory"),
							$fbAccount,
							$user->data()->id
						));
					}else{
						$groups = DB::GetInstance()->QueryGet("SELECT groups FROM fb_accounts WHERE fb_id = ? AND user_id = ? ",array($fbAccount,$user->data()->id));
					}
					
				}else{
					$groups = DB::GetInstance()->QueryGet("SELECT groups FROM fb_accounts WHERE fb_id = ? AND user_id = ? ",array($fbAccount,$user->data()->id));
				}

			}else{
				$groups = DB::GetInstance()->QueryGet("SELECT groups FROM fb_accounts WHERE fb_id = ? AND user_id = ? ",array($fbAccount,$user->data()->id));
			}
		}

		if(isset($groups->first()->groups)){

			$listGroups = json_decode($groups->first()->groups,true);

			// Check show open group only option is on unset non open groups
			if(isset($user->Options()->openGroupOnly)){
				if($user->Options()->openGroupOnly){
					$i = 0;
					foreach ($listGroups as $group) {
						if(isset($group['privacy'])){
							if($group['privacy'] != 'OPEN') {
								unset($listGroups[$i]);
							}
						}
						$i++;
					}
				}
			}
			
			$this->totalGroups = count($listGroups);
			return $listGroups;

		}
			
		return false;
	}
	/*
	|--------------------------------------------------------------------------
	| Count number of groups
	|--------------------------------------------------------------------------
	|
	*/
	public function GroupsCount(){
		if($this->totalGroups){
			return $this->totalGroups;
		}
		return 0;
	}
	

	/*
	|--------------------------------------------------------------------------
	| Add new group category
	|--------------------------------------------------------------------------
	|
	*/
	public function addGroupCategory($name){
		$user = new User();
		$fbAccount = $this->UserDefaultFbAccount();

		if(trim($name) == ""){
			return lang('CATEGORY_NAME_CAN_NOT_BE_EMPTY');
		}

		try{
			DB::getInstance()->Insert("groups_category",array(
				'user_id' 	=> $user->data()->id,
				'fb_id' 	=> $fbAccount,
				'category_name'=> $name
			)); 
		}catch(Exception $ex){
			throw new Exception($ex->GetMessage());
		}

		return false;
	}

	/*
	|--------------------------------------------------------------------------
	| Remove group from category
	|--------------------------------------------------------------------------
	|
	*/
	public function removeGroupFromCategory($groupId){
		try{

			if(Session::exists("groupscategory") && Session::get("groupscategory") != -1){
				$groups = $this->GetGroups(Session::get("groupscategory"));
			}else{
				$groups = $this->GetGroups();
			}

			if(!$groups){
				throw new Exception("List of groups of the current category can not be loaded");
				return false;
			}

			$groups = array_values($groups);

			$i = 0; $removed = false;
			foreach ($groups as $g) {
				if($g['id'] == $groupId) {
					unset($groups[$i]);
					$removed = true;
					break;
				}
				$i++;
			}

			if(!$removed){
				throw new Exception("The group with ID '".$groupId."' not found!");
				return false;
			}
			
			$user = new User();
			
			if(Session::exists("groupscategory") && Session::get("groupscategory") != -1){
				DB::getInstance()->query("UPDATE groups_category SET 'groups' = ? WHERE id = ? AND user_id = ? ",array(
					json_encode($groups),
					Session::get("groupscategory"),
					$user->data()->id,
					)
				);
			}else{
				
				$fbAccount = $this->UserDefaultFbAccount();
		
				DB::getInstance()->query("UPDATE fb_accounts SET 'groups' = ? WHERE user_id = ? AND fb_id = ? ",array(
					json_encode($groups),
					$user->data()->id,
					$fbAccount,	
					)
				); 

			}
			
		}catch(Exception $ex){
			throw $ex;
		}

	}
	/*
	|--------------------------------------------------------------------------
	| Add group to category
	|--------------------------------------------------------------------------
	|
	*/
	public function addGroupToCategory($group,$category){
		$user = new User();

		if(!$this->isGroupCategoryExists($category)){
			return "Category not Exists";
		}

		if($category){
			$groups = $this->GetGroups($category);
		}else{
			$groups = $this->GetGroups();
		}

		$exists = false;


		if($groups){
			$groups = array_values($groups);
			$i = 0;
			foreach ($groups as $g) {
				if($g['id'] == $group['id']) {
					$exists = true;
					break;
				}
				$i++;
			}
		}

		if($exists){
			return lang('GROUP_ALREADY_EXISTS_IN_THIS_CATEGORY');
		}else{
			$groups[] = $group;
		}

		try{
			DB::getInstance()->query("UPDATE groups_category SET 'groups' = ? WHERE id = ? AND user_id = ? ",array(
					json_encode($groups),
					$category,
					$user->data()->id,
					)
				);
		}catch(Exception $ex){
			throw new Exception($ex->GetMessage());
		}

		return "true";
	}
	/*
	|--------------------------------------------------------------------------
	| Get group category
	|--------------------------------------------------------------------------
	|
	*/
	public function isGroupCategoryExists($category){
		$user = new User();
		return DB::GetInstance()->QueryGet("SELECT id FROM groups_category WHERE id = ? AND user_id = ? ",array($category,$user->data()->id))->count() == 0 ? false : true;
		
	}

	/*
	|--------------------------------------------------------------------------
	| Current fb account has category
	|--------------------------------------------------------------------------
	|
	*/
	public function currentFbAccountHasCat($category){
		$user = new User();
		$fbAccount = $this->UserDefaultFbAccount();
		return DB::GetInstance()->QueryGet("SELECT id FROM groups_category WHERE id = ? AND user_id = ? AND fb_id = ? ",array(
			$category,
			$user->data()->id,
			$fbAccount)
		)->count() == 0 ? false : true;
	}

	/*
	|--------------------------------------------------------------------------
	| Delete category
	|--------------------------------------------------------------------------
	|
	*/
	public function deleteCategory($category){
		$user = new User();
		return DB::GetInstance()->QueryGet("DELETE FROM groups_category WHERE id = ? AND user_id = ? ",array($category,$user->data()->id));
		
	}
}
?>