<?php

date_default_timezone_set('America/New_York');

function Dump($data = array())
{
	echo "<pre>";
	var_dump($data); 
	echo "</pre>";
}

class dbClass
{
	
	private $conn;
	private $dbh;
	private $error;
	private $stmt;

	public function __construct()
	{

		require("config.php");

		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
		$this->hostname = $hostname;
        $this->server = $server;

		//AUTH KEY FOR ENCRYPT/DECRYPT
        $this->skey = $cf_authkey;

		// Set options
        $options = array(
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
        );

		// Create a new PDO instanace
		try
		{
			$this->conn = new PDO("mysql:host=$hostname;dbname=$database", $user, $password, $options);
                        $this->conn->exec("set names utf8");
		}
		// Catch any errors
		catch(PDOException $e)
		{
            $this->error = $e->getMessage();
        }

	}

	public function query($query){
		$this->stmt = $this->conn->prepare($query);
	}

	public function bind($param, $value, $type = null){
	    if (is_null($type)) {
	        switch (true) {
	            case is_int($value):
	                $type = PDO::PARAM_INT;
	                break;
	            case is_bool($value):
	                $type = PDO::PARAM_BOOL;
	                break;
	            case is_null($value):
	                $type = PDO::PARAM_NULL;
	                break;
	            default:
	                $type = PDO::PARAM_STR;
	        }
	    }
	    $this->stmt->bindValue($param, $value, $type);
	}

	public function execute(){
	    return $this->stmt->execute();
	}

	public function resultset(){
	    $this->execute();
	    return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function single(){
	    $this->execute();
	    return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function rowCount(){
	    return $this->stmt->rowCount();
	}

	public function lastInsertId(){
	    return $this->conn->lastInsertId();
	}

	public function debugDumpParams(){
	    return $this->stmt->debugDumpParams();
	}

	public function beginTransaction(){
	    return $this->conn->beginTransaction();
	}

	public function endTransaction(){
	    return $this->conn->commit();
	}

	public function cancelTransaction(){
	    return $this->conn->rollBack();
	}

	public function safe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    public function safe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function encode($value, $key = null){
        if(!$value){return false;}
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $curKey = $this->skey;
        if(isset($key))
            $curKey = $key;
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $curKey, $text, MCRYPT_MODE_ECB, $iv);
        return trim($this->safe_b64encode($crypttext));
    }

    public function decode($value, $key = null){
        if(!$value){return false;}
        $crypttext = $this->safe_b64decode($value);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $curKey = $this->skey;
        if(isset($key))
            $curKey = $key;
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $curKey, $crypttext, MCRYPT_MODE_ECB, $iv);
        return trim($decrypttext);
    }

    public function setAppCookie($cookieName, $userName, $displayName,$email,$permission, $hour, $key = null){
        $content = array();
        $content['user_name'] = $userName;
        $content['display_name'] = $displayName;
        $content['email'] = $email;
        $content['permission'] = $permission;
        $curTime = time();
        $content['time'] = $curTime; 
        setcookie($cookieName, $this->encode(json_encode($content),$key), $curTime+60*60*$hour, '/', 'researchnow.com'); //2hours
    }

    public function getAppCookie($cookieName, $key=null){
        $cookieStr = $_COOKIE[$cookieName];

        if(isset($cookieStr))
        {
            $cookieStr = stripslashes($cookieStr);
            $decodedStr = $this->decode($cookieStr,$key);
            if($decodedStr){
                $cookie = json_decode($decodedStr, true);
                if(isset($cookie['time'])){
                    return $cookie;
                }
            }
        }
        return false;
    }
    public function hasPermission($userPermission,$requiredPermission){

        $userPerms = explode(',', $userPermission);
        $rePerms = explode(',', $requiredPermission);
        $arrLen = count($rePerms);
        $permitted = false;
        for($i=0; $i<$arrLen; $i++){
            if(in_array($rePerms[$i], $userPerms)){
                $permitted = true;
                break;
            }
        }
        return $permitted;
    }

}


?>
