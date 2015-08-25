<?php
include('passwordClass.php');
class UserClass extends PasswordClass{

    private $_db;

    function __construct($db){
    	parent::__construct();
    
    	$this->_db = $db;
    }

	private function fetch_user_password($username){

		try {
			$stmt = $this->_db->prepare('SELECT password FROM members WHERE username = :username AND active="Yes" ');
			$stmt->execute(array('username' => $username));
			
			$row = $stmt->fetch();
			return $row['password'];

		} catch(PDOException $e) {
		    echo '<p class="bg-danger">'.$e->getMessage().'</p>';
		}
	}

	public function user_login($username,$password){
        //check user login and set session for user

		$hashed = $this->fetch_user_password($username);
		
		if($this->password_match($password,$hashed) == 1){
		    
		    $_SESSION['userLoggedIn'] = true;
		    return true;
		} 	
	}
		
	public function user_logout(){
        //will destroy all sessions
		session_destroy();
	}

	public function is_logged_in(){
		if(isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn'] == true){
			return true;
		}		
	}
	
}


?>