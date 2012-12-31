<?php

class Auth 
{
    private $mysqli;
    
    /*
    * Initiates database connection
    */
    
    public function __construct()
    {
        include("config.php");
    
        $this->mysqli = new mysqli($db['host'], $db['user'], $db['pass'], $db['name']);
        unset($db['pass']);
    }
    
    /*
    * Logs a user in
    * @param string $username
    * @param string $password (MUST be already twice hashed with SHA1 : Ideally client side with JS)
    * @return array $return
    */
    
    public function login($username, $password)
    {
        $return = array();
        
        $ip = $this->getIp();
 
        if($this->isBlocked($ip))
        {
            $return['code'] = 0;
            return $return;
        }
        else
        {
            if(strlen($username) == 0) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($username) > 30) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($username) < 3) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($password) == 0) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($password) != 40) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            else
            {
                $plainpass = $password;
                $password = $this->getHash($password);
                
                if($userdata = $this->getUserData($username))
                {
                    if($password === $userdata['password'])
                    {
                        if($userdata['isactive'] == 1)
                        {
                            $sessiondata = $this->addNewSession($userdata['uid']);

                            $return['code'] = 4;
                            $return['session_hash'] = $sessiondata['hash'];
                            
                            $this->addNewLog($userdata['uid'], "LOGIN_SUCCESS", "User logged in. Session hash : " . $sessiondata['hash']);
                            
                            return $return;
                        }
                        else
                        {
                            $this->addAttempt($ip); 
                        
                            $this->addNewLog($userdata['uid'], "LOGIN_FAIL_NONACTIVE", "Account inactive");
                        
                            $return['code'] = 3;
                            
                            return $return;
                        }
                    }
                    else
                    {
                        $this->addAttempt($ip); 
                    
                        $this->addNewLog($userdata['uid'], "LOGIN_FAIL_PASSWORD", "Password incorrect : {$plainpass}");
                    
                        $return['code'] = 2;
                        
                        return $return;
                    }
                }
                else
                {
                    $this->addAttempt($ip); 
                
                    $this->addNewLog("", "LOGIN_FAIL_USERNAME", "Attempted login with the username : {$username} -> Username doesn't exist in DB");
                
                    $return['code'] = 2;
                    
                    return $return;
                }
            }
        }
    }
    
    /*
    * Creates a new user, adds them to database
    * @param string $email
    * @param string $username
    * @param string $password (MUST be already twice hashed with SHA1 : Ideally client side with JS)
    * @return array $return
    */
    
    public function register($email, $username, $password)
    {
        $return = array();
        
        $ip = $this->getIp();
        
        if($this->isBlocked($ip))
        {
            $return['code'] = 0;
            return $return;
        }
        else
        {
            if(strlen($email) == 0) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($email) > 100) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($email) < 3) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($username) == 0) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($username) > 30) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($username) < 3) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($password) != 40) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            else
            {
                $password = $this->getHash($password);
                
                if(!$this->isEmailTaken($email))
                {
                    if(!$this->isUsernameTaken($username))
                    {
                        $uid = $this->addUser($email, $username, $password);
                        
                        $this->addNewLog($uid, "REGISTER_SUCCESS", "Account created successfully, activation email sent.");
                        
                        $return['code'] = 4;
                        $return['email'] = $email;
                        return $return;
                        
                    }
                    else
                    {
                        $this->addAttempt($ip); 
                    
                        $this->addNewLog("", "REGISTER_FAIL_USERNAME", "User attempted to register new account with the username : {$username} -> Username already in use");
                    
                        $return['code'] = 3;
                        return $return;
                    }
                }
                else
                {
                    $this->addAttempt($ip); 
                
                    $this->addNewLog("", "REGISTER_FAIL_EMAIL", "User attempted to register new account with the email : {$email} -> Email already in use");
                
                    $return['code'] = 2;
                    return $return;
                }
            }
        }
    }
    
    /*
    * Activates a user's account
    * @param string $activekey
    * @return array $return
    */
    
    public function activate($activekey)
    {
        $return = array();
        
        $ip = $this->getIp();        
        
        if($this->isBlocked($ip))
        {
            $return['code'] = 0;
            return $return;
        }
        else
        {
            if(strlen($activekey) > 20) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($activekey) < 20) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            else
            {
                $query = $this->mysqli->prepare("SELECT uid, expiredate FROM activations WHERE activekey = ?");
                $query->bind_param("s", $activekey);
                $query->bind_result($uid, $expiredate);
                $query->execute();
                $query->store_result();
                $count = $query->num_rows;
                $query->fetch();
                $query->close();
                
                if($count == 0)
                {
                    $this->addAttempt($ip); 
                
                    $this->addNewLog("", "ACTIVATE_FAIL_ACTIVEKEY", "User attempted to activate an account with the key : {$activekey} -> Activekey not found in database");
                    
                    $return['code'] = 2;
                    return $return;
                }
                else
                {
                    if(!$this->isUserActivated($uid))
                    {
                        $expiredate = strtotime($expiredate);
                        $currentdate = strtotime(date("Y-m-d H:i:s"));
                    
                        if($currentdate < $expiredate)
                        {
                            $isactive = 1;
                        
                            $query = $this->mysqli->prepare("UPDATE users SET isactive = ? WHERE id = ?");
                            $query->bind_param("ii", $isactive, $uid);
                            $query->execute();
                            $query->close();
                            
                            $this->deleteUserActivations($uid);
                            
                            $this->addNewLog($uid, "ACTIVATE_SUCCESS", "Account activated -> Isactive : 1");
                            
                            $return['code'] = 5;
                            return $return;
                        }
                        else
                        {
                            $this->addAttempt($ip);
                        
                            $this->addNewLog($uid, "ACTIVATE_FAIL_EXPIRED", "User attempted to activate account with key : {$activekey} -> Key expired");
                        
                            $this->deleteUserActivations($uid);
                        
                            $return['code'] = 4;
                            return $return;
                        }
                    }
                    else
                    {
                        $this->addAttempt($ip); 
                    
                        $this->deleteUserActivations($uid);
                    
                        $this->addNewLog($uid, "ACTIVATE_FAIL_ALREADYACTIVE", "User attempted to activate an account with the key : {$activekey} -> Account already active. Set activekey : 0");
                    
                        $return['code'] = 3;
                        return $return;
                    }
                }
            }
        }            
    }
    
    /*
    * Creates a reset key for an email address and sends email
    * @param string $email
    * @return array $return
    */
    
    public function requestReset($email)
    {
        $return = array();
        
        $ip = $this->getIp();
        
        if($this->isBlocked($ip))
        {
            $return['code'] = 0;
            return $return;
        }
        else
        {
            if(strlen($email) == 0) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($email) > 100) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($email) < 3) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            else
            {
                $query = $this->mysqli->prepare("SELECT id FROM users WHERE email = ?");
                $query->bind_param("s", $email);
                $query->bind_result($uid);
                $query->execute();
                $query->store_result();
                $count = $query->num_rows;
                $query->fetch();
                $query->close();
                
                if($count == 0)
                {
                    $this->addAttempt($ip); 
                
                    $this->addNewLog("", "REQUESTRESET_FAIL_EMAIL", "User attempted to reset the password for the email : {$email} -> Email doesn't exist in DB");
                    
                    $return['code'] = 2;
                    return $return;
                }
                else
                {
                    if($this->addReset($uid, $email))
                    {
                        $this->addNewLog($uid, "REQUESTRESET_SUCCESS", "A reset request was sent to the email : {$email}");
                    
                        $return['code'] = 4;
                        $return['email'] = $email;

                        return $return;
                    }
                    else
                    {
                        $this->addAttempt($ip);
                    
                        $this->addNewLog($uid, "REQUESTRESET_FAIL_EXIST", "User attempted to reset the password for the email : {$email} -> A reset request already exists.");
                    
                        $return['code'] = 3;
                        return $return;
                    }
                }
            }
        }
    }
        
    /*
    * Logs out the session, identified by hash
    * @param string $hash
    * @return boolean
    */
    
    public function logout($hash)
    {
        include("config.php");
    
        if(strlen($hash) != 40) { return false; }
        
        $this->deleteSession($hash);
        
        setcookie($auth_conf['cookie_auth'], $hash, time() - 3600, $auth_conf['cookie_path'], $auth_conf['cookie_domain'], false, true);
        
        return true;
    }
    
    /*
    * Hashes string using multiple hashing methods, for enhanced security
    * @param string $string
    * @return string $enc
    */
    
    public function getHash($string)
    {
        include("config.php");
    
        if (strnatcmp(phpversion(),'5.5.0') >= 0) 
        {
            $enc = hash_pbkdf2("SHA512", base64_encode(str_rot13(hash("SHA512", str_rot13($auth_conf['salt_1'] . $string . $auth_conf['salt_2'])))), $auth_conf['salt_3'], 50000, 128);
        } 
        else 
        {
            $enc = hash("SHA512", base64_encode(str_rot13(hash("SHA512", str_rot13($auth_conf['salt_1'] . $string . $auth_conf['salt_2'])))));
        }
        
        return $enc;
    }
    
    /*
    * Gets user data for a given username and returns an array
    * @param string $username
    * @return array $data
    */
    
    private function getUserData($username)
    {
        $data = array();
    
        $data['username'] = $username;
    
        $query = $this->mysqli->prepare("SELECT id, password, email, salt, lang, isactive FROM users WHERE username = ?");
        $query->bind_param("s", $username);
        $query->bind_result($data['uid'], $data['password'], $data['email'], $data['salt'], $data['lang'], $data['isactive']);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->fetch();
        $query->close();
        
        if($count == 0)
        {
            return false;
        }
        else
        {
            return $data;
        }
    }
    
    /*
    * Creates a session for a specified user id
    * @param int $uid
    * @return array $data
    */
    
    private function addNewSession($uid)
    {
        include("config.php");
    
        $data = array();
    
        $query = $this->mysqli->prepare("SELECT salt, lang FROM users WHERE id = ?");
        $query->bind_param("i", $uid);
        $query->bind_result($data['salt'], $data['lang']);
        $query->execute();
        $query->store_result();
        $query->fetch();
        $query->close();
        $data['hash'] = sha1($data['salt'].microtime());
            
        $agent = $_SERVER['HTTP_USER_AGENT'];
        
        $this->deleteExistingSessions($uid);
        
        $ip = $this->getIp();
            
        $data['expire'] = date("Y-m-d H:i:s", strtotime("+1 month"));
        $data['cookie_crc'] = sha1 ($data['hash'].$auth_conf['sitekey']);
        
        
        $query = $this->mysqli->prepare("INSERT INTO sessions (uid, hash, expiredate, ip, agent, cookie_crc, lang) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $query->bind_param("issssss", $uid, $data['hash'], $data['expire'], $ip, $agent, $data['cookie_crc'], $data['lang']);
        $query->execute();
        $query->close();
        
        return $data;
    }
    
    /*
    * Removes all existing sessions for a given UID
    * @param int $uid
    * @return boolean
    */
    
    private function deleteExistingSessions($uid)
    {
        $query = $this->mysqli->prepare("DELETE FROM sessions WHERE uid = ?");
        $query->bind_param("i", $uid);
        $query->execute();
        $query->close();
        
        return true;
    }
    
    /*
    * Removes a session based on hash
    * @param string $hash
    * @return boolean
    */
    
    private function deleteSession($hash)
    {
        $query = $this->mysqli->prepare("DELETE FROM sessions WHERE hash = ?");
        $query->bind_param("s", $hash);
        $query->execute();
        $query->close();
        
        return true;
    }
    
    /*
    * Returns username based on session hash
    * @param string $hash
    * @return string $username
    */
    
    public function getUsername($hash)
    {
        $query = $this->mysqli->prepare("SELECT uid FROM sessions WHERE hash = ?");
        $query->bind_param("s", $hash);
        $query->bind_result($uid);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->fetch();
        $query->close();
        
        if($count == 0)
        {
            return false;
        }
        else
        {
            $query = $this->mysqli->prepare("SELECT username FROM users WHERE id = ?");
            $query->bind_param("i", $uid);
            $query->bind_result($username);
            $query->execute();
            $query->store_result();
            $count = $query->num_rows;
            $query->fetch();
            $query->close();
            
            if($count == 0)
            {
                return false;
            }
            else
            {
                return $username;
            }
        }
    }
    
    /*
    * Function to add data to log table
    * @param string $uid
    * @param string $action
    * @param string $info
    * @param return boolean
    */
    
    private function addNewLog($uid = 'UNKNOWN', $action, $info)
    {
        if(strlen($uid) == 0) { $uid = "UNKNOWN"; }
        elseif(strlen($action) == 0) { return false; }
        elseif(strlen($action) > 100) { return false; }
        elseif(strlen($info) == 0) { return false; }
        elseif(strlen($info) > 1000) { return false; }
        else
        {    
            $ip = $this->getIp();
         
            $query = $this->mysqli->prepare("INSERT INTO log (username, action, info, ip) VALUES (?, ?, ?, ?)");
            $query->bind_param("ssss", $uid, $action, $info, $ip);
            $query->execute();
            $query->close();
            
            return true;
        }
    }
    
    /*
    * Function to check if a session is valid
    * @param string $hash
    * @return boolean
    */
    
    public function checkSession($hash)
    {
        include("config.php");

        $ip = $this->getIp();
                   
        if($this->isBlocked($ip))
        {
            return false;
        }
        else
        {
            if(strlen($hash) != 40) { setcookie($auth_conf['cookie_auth'], $hash, time() - 3600, $auth_conf['cookie_path'], $auth_conf['cookie_domain'], false, true); return false; }
        
            $query = $this->mysqli->prepare("SELECT id, uid, expiredate, ip, agent, cookie_crc FROM sessions WHERE hash = ?");
            $query->bind_param("s", $hash);
            $query->bind_result($sid, $uid, $expiredate, $db_ip, $db_agent, $db_cookie);
            $query->execute();
            $query->store_result();
            $count = $query->num_rows;
            $query->fetch();
            $query->close();
            
            if($count == 0)
            {        
                setcookie($auth_conf['cookie_auth'], $hash, time() - 3600, $auth_conf['cookie_path'], $auth_conf['cookie_domain'], false, true);
                
                $this->addNewLog($uid, "CHECKSESSION_FAIL_NOEXIST", "Hash ({$hash}) doesn't exist in DB -> Cookie deleted");
                
                return false;
            }
            else
            {
                   
                if($ip != $db_ip)
                {
                    if($_SERVER['HTTP_USER_AGENT'] != $db_agent)
                    {
                        $this->deleteExistingSessions($uid);
                    
                        setcookie($auth_conf['cookie_auth'], $hash, time() - 3600, $auth_conf['cookie_path'], $auth_conf['cookie_domain'], false, true);
                    
                        $this->addNewLog($uid, "CHECKSESSION_FAIL_DIFF", "IP and User Agent Different ( DB : {$db_ip} / Current : " . $ip . " ) -> UID sessions deleted, cookie deleted");
                    
                        return false;
                    }
                    else
                    {
                        $expiredate = strtotime($expiredate);
                        $currentdate = strtotime(date("Y-m-d H:i:s"));
                    
                        if($currentdate > $expiredate)
                        {            
                            $this->deleteExistingSessions($uid);
                        
                            setcookie($auth_conf['cookie_auth'], $hash, time() - 3600, $auth_conf['cookie_path'], $auth_conf['cookie_domain'], false, true);
                        
                            $this->addNewLog($uid, "CHECKSESSION_FAIL_EXPIRE", "Session expired ( Expire date : {$db_expiredate} ) -> UID sessions deleted, cookie deleted");
                        
                            return false;
                        }
                        else 
                        {
                            $this->updateSessionIp($sid, $ip);
                        
                            return true;
                        }
                    }
                }
                else 
                {
                    $expiredate = strtotime($expiredate);
                    $currentdate = strtotime(date("Y-m-d H:i:s"));
                    
                    if($currentdate > $expiredate)
                    {            
                        $this->deleteExistingSessions($uid);
                        
                        setcookie($auth_conf['cookie_auth'], $hash, time() - 3600, $auth_conf['cookie_path'], $auth_conf['cookie_domain'], false, true);
                        
                        $this->addNewLog($uid, "AUTH_CHECKSESSION_FAIL_EXPIRE", "Session expired ( Expire date : {$db_expiredate} ) -> UID sessions deleted, cookie deleted");
                        
                        return false;
                    }
                    else 
                    {                            
                        $cookie_crc = sha1 ($hash.$auth_conf['sitekey']);
                        
                        if ($db_cookie == $cookie_crc) 
                        { 
                            return true;
                        } 
                        else 
                        {
                            $this->addNewLog($uid, "AUTH_COOKIE_FAIL_BADCRC", "Cookie Integrity failed");
                            
                            return false;
                        }
                    }
                }
            }
        }
    }
    
    /*
    * Updates the IP of a session (used if IP has changed, but agent has remained unchanged)
    * @param int $sid
    * @param string $ip
    * @return boolean
    */
    
    private function updateSessionIp($sid, $ip)
    {
        $query = $this->mysqli->prepare("UPDATE sessions SET ip = ? WHERE id = ?");
        $query->bind_param("si", $ip, $sid);
        $query->execute();
        $query->close();
        
        return true;
    }
    
    /*
    * Checks if an email is already in use
    * @param string $email
    * @return boolean
    */
    
    private function isEmailTaken($email)
    {
        $query = $this->mysqli->prepare("SELECT * FROM users WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->close();
        
        if($count == 0)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    /*
    * Checks if a username is already in use
    * @param string $username
    * @return boolean
    */
    
    private function isUsernameTaken($username)
    {
        $query = $this->mysqli->prepare("SELECT * FROM users WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->close();
        
        if($count == 0) {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    /*
    * Adds a new user to database
    * @param string $email
    * @param string $username
    * @param string $password
    * @return int $uid
    */
    
    private function addUser($email, $username, $password)
    {
        $username = htmlentities($username);
        $email = htmlentities($email);
        
        $salt = $this->getRandomKey(20);
        
        $query = $this->mysqli->prepare("INSERT INTO users (username, password, email, salt) VALUES (?, ?, ?, ?)");
        $query->bind_param("ssss", $username, $password, $email, $salt);
        $query->execute();
        $uid = $query->insert_id;
        $query->close();
        
        $this->addActivation($uid, $email);
        
        return $uid;
    }
    
    /*
    * Creates an activation entry and sends email to user
    * @param int $uid
    * @param string $email
    * @return boolean
    */
    
    private function addActivation($uid, $email)
    {
        include("config.php");
    
        $activekey = $this->getRandomKey(20);
                
        if($this->isUserActivated($uid))
        {
            return false;
        }
        else
        {
            $query = $this->mysqli->prepare("SELECT expiredate FROM activations WHERE uid = ?");
            $query->bind_param("i", $uid);
            $query->bind_result($expiredate);
            $query->execute();
            $query->store_result();
            $count = $query->num_rows;
            $query->fetch();
            $query->close();
            
            if($count > 0)
            {
                $expiredate = strtotime($expiredate);
                $currentdate = strtotime(date("Y-m-d H:i:s"));
                
                if($currentdate < $expiredate)
                {
                    return false;
                }
                else
                {
                    $this->deleteUserActivations($uid);
                }
            }
            
            $expiredate = date("Y-m-d H:i:s", strtotime("+1 day"));
            
            $query = $this->mysqli->prepare("INSERT INTO activations (uid, activekey, expiredate) VALUES (?, ?, ?)");
            $query->bind_param("iss", $uid, $activekey, $expiredate);
            $query->execute();
            $query->close();
        
            $mail_body = str_replace("{key}", $activekey, $auth_conf['activation_email']['body']);
                        
            @mail($email, $auth_conf['activation_email']['subj'], $mail_body, $auth_conf['activation_email']['head']);
                                
            return true;
        }
    }
    
    /*
    * Deletes all activation entries for a user
    * @param int $uid
    * @return boolean
    */
    
    private function deleteUserActivations($uid)
    {
        $query = $this->mysqli->prepare("DELETE FROM activations WHERE uid = ?");
        $query->bind_param("i", $uid);
        $query->execute();
        $query->close();
        
        return true;
    }
    
    /*
    * Checks if a user account is activated based on uid
    * @param int $uid
    * @return boolean
    */
    
    private function isUserActivated($uid)
    {
        $query = $this->mysqli->prepare("SELECT isactive FROM users WHERE id = ?");
        $query->bind_param("i", $uid);
        $query->bind_result($isactive);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->fetch();
        $query->close();
        
        if($count == 0)
        {
            return false;
        }
        else
        {
            if($isactive == 1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    
    /*
    * Creates a reset entry and sends email to user
    * @param int $uid
    * @param string $email
    * @return boolean
    */
    
    private function addReset($uid, $email)
    {
        include("config.php");
            
        $resetkey = $this->getRandomKey(20);    
        
        $query = $this->mysqli->prepare("SELECT expiredate FROM resets WHERE uid = ?");
        $query->bind_param("i", $uid);
        $query->bind_result($expiredate);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->fetch();
        $query->close();
            
        if($count == 0)
        {
            $expiredate = date("Y-m-d H:i:s", strtotime("+1 day"));
        
            $query = $this->mysqli->prepare("INSERT INTO resets (uid, resetkey, expiredate) VALUES (?, ?, ?)");
            $query->bind_param("iss", $uid, $resetkey, $expiredate);
            $query->execute();
            $query->close();
            
            $mail_body = str_replace("{key}", $resetkey, $auth_conf['reset_email']['body']);
                            
            @mail($email, $auth_conf['reset_email']['subj'], $mail_body, $auth_conf['reset_email']['head']);
                
            return true;
        }
        else
        {
            $expiredate = strtotime($expiredate);
            $currentdate = strtotime(date("Y-m-d H:i:s"));
                
            if($currentdate < $expiredate)
            {        
                return false;
            }
            else
            {
                $this->deleteUserResets($uid);
            }
            $expiredate = date("Y-m-d H:i:s", strtotime("+1 day"));
            
            $query = $this->mysqli->prepare("INSERT INTO resets (uid, resetkey, expiredate) VALUES (?, ?, ?)");
            $query->bind_param("iss", $uid, $resetkey, $expiredate);
            $query->execute();
            $query->close();
            
            $mail_body = str_replace("{key}", $resetkey, $auth_conf['reset_email']['body']);
                            
            @mail($email, $auth_conf['reset_email']['subj'], $mail_body, $auth_conf['reset_email']['head']);
                
            return true;
        }
    }
    
    /*
    * Deletes all reset entries for a user
    * @param int $uid
    * @return boolean
    */
    
    private function deleteUserResets($uid)
    {
        $query = $this->mysqli->prepare("DELETE FROM resets WHERE uid = ?");
        $query->bind_param("i", $uid);
        $query->execute();
        $query->close();
        
        return true;
    }
    
    /*
    * Checks if a reset key is valid
    * @param string $key
    * @return array $return
    */
    
    public function isResetValid($key)
    {
        $return = array();
        
        $ip = $this->getIp();
 
        if($this->isBlocked($ip))
        {
            $return['code'] = 0;
            return $return;
        }
        else
        {
            if(strlen($key) > 20) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($key) < 20) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            else
            {
                $query = $this->mysqli->prepare("SELECT uid, expiredate FROM resets WHERE resetkey = ?");
                $query->bind_param("s", $key);
                $query->bind_result($uid, $expiredate);
                $query->execute();
                $query->store_result();
                $count = $query->num_rows;
                $query->fetch();
                $query->close();
                
                if($count == 0)
                {
                    $this->addAttempt($ip); 
                
                    $return['code'] = 2;
                    return $return;
                }
                else
                {
                    $expiredate = strtotime($expiredate);
                    $currentdate = strtotime(date("Y-m-d H:i:s"));
                
                    if($currentdate > $expiredate)
                    {
                        $this->addAttempt($ip); 
                    
                        $this->deleteUserResets($uid);
                    
                        $return['code'] = 3;
                        return $return;
                    }
                    else
                    {
                        $return['code'] = 4;
                        $return['uid'] = $uid;
                        return $return;
                    }
                }
            }
        }
    }
    
    /*
    * After verifying key validity, changes user's password
    * @param string $key
    * @param string $password (Must be already twice hashed with SHA1 : Ideally client side with JS)
    * @return array $return
    */
    
    public function resetPass($key, $password)
    {
        $return = array();
        
             $ip = $this->getIp();        
        
        if($this->isBlocked($ip))
        {
            $return['code'] = 0;
            return $return;
        }
        else
        {
            if(strlen($password) != 40) { $return['code'] = 1; $this->addAttempt($ip); return $return; }

            $data = $this->isResetValid($key);
            
            if($data['code'] = 4)
            {
                $password = $this->getHash($password);
            
                $query = $this->mysqli->prepare("SELECT password FROM users WHERE id = ?");
                $query->bind_param("i", $data['uid']);
                $query->bind_result($db_password);
                $query->execute();
                $query->store_result();
                $count = $query->num_rows;
                $query->fetch();
                $query->close();
                
                if($count == 0)
                {
                    $this->addAttempt($ip); 
                
                    $this->deleteUserResets($data['uid']);
                    
                    $this->addNewLog($data['uid'], "RESETPASS_FAIL_UID", "User attempted to reset password with key : {$key} -> User doesn't exist !");
                    
                    $return['code'] = 3;
                    return $return;
                }
                else
                {
                    if($db_password == $password)
                    {
                        $this->addAttempt($ip); 
                    
                        $this->addNewLog($data['uid'], "RESETPASS_FAIL_SAMEPASS", "User attempted to reset password with key : {$key} -> New password matches previous password !");
                    
                        $this->deleteUserResets($data['uid']);
                    
                        $return['code'] = 4;
                        return $return;
                    }
                    else
                    {
                        $query = $this->mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $query->bind_param("si", $password, $data['uid']);
                        $query->execute();
                        $query->close();
                        
                        $this->addNewLog($data['uid'], "RESETPASS_SUCCESS", "User attempted to reset password with key : {$key} -> Password changed, reset keys deleted !");
                        
                        $this->deleteUserResets($data['uid']);
                        
                        $return['code'] = 5;
                        return $return;
                    }
                }
            }
            else
            {
                $this->addNewLog($data['uid'], "RESETPASS_FAIL_KEY", "User attempted to reset password with key : {$key} -> Key is invalid / incorrect / expired !");
            
                $return['code'] = 2;
                return $return;
            }
        }
    }
    
    /*
    * Recreates activation email for a given email and sends
    * @param string $email
    * @return array $return
    */
    
    public function resendActivation($email)
    {
        $return = array();
        
            $ip = $this->getIp();        
        
        if($this->isBlocked($ip))
        {
            $return['code'] = 0;
            return $return;
        }
        else
        {
            if(strlen($email) == 0) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($email) > 100) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($email) < 3) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            else
            {
                $query = $this->mysqli->prepare("SELECT id FROM users WHERE email = ?");
                $query->bind_param("s", $email);
                $query->bind_result($uid);
                $query->execute();
                $query->store_result();
                $count = $query->num_rows;
                $query->fetch();
                $query->close();
                
                if($count == 0)
                {
                    $this->addAttempt($ip); 
                
                    $this->addNewLog("", "RESENDACTIVATION_FAIL_EMAIL", "User attempted to resend activation email for the email : {$email} -> Email doesn't exist in DB !");
                
                    $return['code'] = 2;
                    return $return;
                }
                else
                {
                    if($this->isUserActivated($uid))
                    {
                        $this->addAttempt($ip); 
                    
                        $this->addNewLog($uid, "RESENDACTIVATION_FAIL_ACTIVATED", "User attempted to resend activation email for the email : {$email} -> Account is already activated !");
                    
                        $return['code'] = 3;
                        return $return;
                    }
                    else
                    {
                        if($this->addActivation($uid, $email))
                        {
                            $this->addNewLog($uid, "RESENDACTIVATION_SUCCESS", "Activation email was resent to the email : {$email}");
                        
                            $return['code'] = 5;
                            return $return;
                        }
                        else
                        {
                            $this->addAttempt($ip); 
                        
                            $this->addNewLog($uid, "RESENDACTIVATION_FAIL_EXIST", "User attempted to resend activation email for the email : {$email} -> Activation request already exists. 24 hour expire wait required !");
                            
                            $return['code'] = 4;
                            return $return;
                        }
                    }
                }
            }
        }
    }
    
    /*
    * Gets UID from Session hash
    * @param string $hash
    * @return int $uid
    */
    
    public function sessionUID($hash)
    {
        if(strlen($hash) != 40) { return false; }
        else
        {
            $query = $this->mysqli->prepare("SELECT uid FROM sessions WHERE hash = ?");
            $query->bind_param("s", $hash);
            $query->bind_result($uid);
            $query->execute();
            $query->store_result();
            $count = $query->num_rows;
            $query->fetch();
            $query->close();
            
            if($count == 0)
            {
                return false;
            }
            else
            {
                return $uid;
            }
        }
    }
    
    /*
    * Changes a user's password
    * @param int $uid
    * @param string $currpass
    * @param string $newpass
    * @return array $return
    */
    
    public function changePassword($uid, $currpass, $newpass)
    {
        $return = array();
        
            $ip = $this->getIp();
    
        if($this->isBlocked($ip))
        {
            $return['code'] = 0;
            return $return;
        }
        else
        {
            if(strlen($currpass) != 40) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($newpass) != 40) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            else
            {
                $currpass = $this->getHash($currpass);
                $newpass = $this->getHash($newpass);
            
                $query = $this->mysqli->prepare("SELECT password FROM users WHERE id = ?");
                $query->bind_param("i", $uid);
                $query->bind_result($db_currpass);
                $query->execute();
                $query->store_result();
                $count = $query->num_rows;
                $query->fetch();
                $query->close();
                
                if($count == 0)
                {
                    $this->addAttempt($ip); 
                
                    $this->addNewLog($uid, "CHANGEPASS_FAIL_UID", "User attempted to change password for the UID : {$uid} -> UID doesn't exist !");
                
                    $return['code'] = 2;
                    return $return;
                }
                else
                {
                    if($currpass != $newpass)
                    {
                        if($currpass == $db_currpass)
                        {
                            $query = $this->mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $query->bind_param("si", $newpass, $uid);
                            $query->execute();
                            $query->close();
                            
                            $this->addNewLog($uid, "CHANGEPASS_SUCCESS", "User changed the password for the UID : {$uid}");
                            
                            $return['code'] = 5;
                            return $return;
                        }
                        else
                        {
                            $this->addAttempt($ip); 
                        
                            $this->addNewLog($uid, "CHANGEPASS_FAIL_PASSWRONG", "User attempted to change password for the UID : {$uid} -> Current password incorrect !");
                        
                            $return['code'] = 4;
                            return $return;
                        }
                    }
                    else
                    {
                        $this->addAttempt($ip);
                    
                        $this->addNewLog($uid, "CHANGEPASS_FAIL_PASSMATCH", "User attempted to change password for the UID : {$uid} -> New password matches current password !");
                    
                        $return['code'] = 3;
                        return $return;
                    }
                }
            }
        }
    }
    
    /*
    * Gets a user's email address by UID
    * @param int $uid
    * @return string $email
    */
    
    public function getEmail($uid)
    {
        $query = $this->mysqli->prepare("SELECT email FROM users WHERE id = ?");
        $query->bind_param("i", $uid);
        $query->bind_result($email);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->fetch();
        $query->close();
        
        if($count == 0)
        {
            return false;
        }
        else
        {
            return $email;
        }
    }
    
    /*
    * Changes a user's email
    * @param int $uid
    * @param string $currpass
    * @param string $newpass
    * @return array $return
    */
    
    public function changeEmail($uid, $email, $password)
    {
        $return = array();
        
        $ip = $this->getIp();        
        
        if($this->isBlocked($ip))
        {
            $return['code'] = 0;
            return $return;
        }
        else
        {
            if(strlen($email) == 0) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($email) > 100) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($email) < 3) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            elseif(strlen($password) != 40) { $return['code'] = 1; $this->addAttempt($ip); return $return; }
            else
            {    
                $password = $this->getHash($password);
            
                $query = $this->mysqli->prepare("SELECT password, email FROM users WHERE id = ?");
                $query->bind_param("i", $uid);
                $query->bind_result($db_password, $db_email);
                $query->execute();
                $query->store_result();
                $count = $query->num_rows;
                $query->fetch();
                $query->close();
                
                if($count == 0)
                {
                    $this->addAttempt($ip); 
                
                    $this->addNewLog($uid, "CHANGEEMAIL_FAIL_UID", "User attempted to change email for the UID : {$uid} -> UID doesn't exist !");
                
                    $return['code'] = 2;
                    return $return;
                }
                else
                {
                    if($password == $db_password)
                    {
                        if($email == $db_email)
                        {
                            $this->addAttempt($ip); 
                        
                            $this->addNewLog($uid, "CHANGEEMAIL_FAIL_EMAILMATCH", "User attempted to change email for the UID : {$uid} -> New Email address matches current email !");
                        
                            $return['code'] = 4;
                            return $return;
                        }
                        else
                        {
                            $query = $this->mysqli->prepare("UPDATE users SET email = ? WHERE id = ?");
                            $query->bind_param("si", $email, $uid);
                            $query->execute();
                            $query->close();
                            
                            $this->addNewLog($uid, "CHANGEEMAIL_SUCCESS", "User changed email address for UID : {$uid}");
                            
                            $return['code'] = 5;
                            return $return;
                        }                    
                    }
                    else
                    {
                        $this->addAttempt($ip); 
                    
                        $this->addNewLog($uid, "CHANGEEMAIL_FAIL_PASS", "User attempted to change email for the UID : {$uid} -> Password is incorrect !");
                    
                        $return['code'] = 3;
                        return $return;
                    }
                }
            }
        }
    }
    
    /*
    * Informs if a user is locked out
    * @param string $ip
    * @return boolean
    */
    
    public function isBlocked($ip)
    {
        $query = $this->mysqli->prepare("SELECT count, expiredate FROM attempts WHERE ip = ?");
        $query->bind_param("s", $ip);
        $query->bind_result($attcount, $expiredate);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->fetch();
        $query->close();
        
        if($count == 0)
        {
            return false;
        }
        else
        {
            if($attcount == 5)
            {
                $expiredate = strtotime($expiredate);
                $currentdate = strtotime(date("Y-m-d H:i:s"));
            
                if($currentdate < $expiredate)
                {
                    return true;
                }
                else
                {
                    $this->deleteAttempts($ip);
                    return false;
                }
            }
            else
            {
                $expiredate = strtotime($expiredate);
                $currentdate = strtotime(date("Y-m-d H:i:s"));
            
                if($currentdate < $expiredate)
                {
                    return false;
                }
                else
                {
                    $this->deleteAttempts($ip);
                    return false;
                }
            
                return false;
            }
        }
    }
    
    /*
    * Deletes all attempts for a given IP from database
    * @param string $ip
    * @return boolean
    */
    
    private function deleteAttempts($ip)
    {
        $query = $this->mysqli->prepare("DELETE FROM attempts WHERE ip = ?");
        $query->bind_param("s", $ip);
        $query->execute();
        $query->close();
    
        return true;
    }
    
    /*
    * Adds an attempt to database for given IP
    * @param string $ip
    * @return boolean
    */
    
    private function addAttempt($ip)
    {
        $query = $this->mysqli->prepare("SELECT count FROM attempts WHERE ip = ?");
        $query->bind_param("s", $ip);
        $query->bind_result($attempt_count);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->fetch();
        $query->close();
        
        if($count == 0)
        {        
            $attempt_expiredate = date("Y-m-d H:i:s", strtotime("+30 minutes"));
            $attempt_count = 1;
            
            $query = $this->mysqli->prepare("INSERT INTO attempts (ip, count, expiredate) VALUES (?, ?, ?)");
            $query->bind_param("sis", $ip, $attempt_count, $attempt_expiredate);
            $query->execute();
            $query->close();
            
            return true;
        }
        else 
        {
            // IP Already exists in attempts table, add 1 to current count
            
            $attempt_expiredate = date("Y-m-d H:i:s", strtotime("+30 minutes"));
            $attempt_count = $attempt_count + 1;
            
            $query = $this->mysqli->prepare("UPDATE attempts SET count=?, expiredate=? WHERE ip=?");
            $query->bind_param("iss", $attempt_count, $attempt_expiredate, $ip);
            $query->execute();
            $query->close();
            
            return true;
        }
    }
    
    /*
    * Returns a random string, length can be modified
    * @param int $length
    * @return string $key
    */
    
    public function getRandomKey($length = 20)
    {
        $chars = "_" . "A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6" . "_" . "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6" . "_";
        $key = "";
        
        for($i = 0; $i < $length; $i++)
        {
            $key .= $chars{mt_rand(0, strlen($chars) - 1)};
        }
        
        return $key;
    }
    
    /*
    * Returns ip address
    * @return string $ip
    */
    
    private function getIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
 
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } 
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        return $ip;
    }
    
    /*
    * Gets a user's level by UID
    * @param int $uid
    * @return int $level
    */

    public function getLevel($uid)
    {
        $query = $this->mysqli->prepare("SELECT level FROM users WHERE id = ?");
        $query->bind_param("i", $uid);
        $query->bind_result($level);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->fetch();
        $query->close();

        if($count == 0)
        {
            return false;
        }
        else
        {
            return $level;
        }
    }
    
    /*
    * Puts a user's level by UID
    * @param string $hash
    * @param int $uid
    * @param int $uid
    * @return boolean
    */

    public function putLevel($hash, $uid, $level)
    {
        include("config.php");
    
        $admin_uid = $this->sessionUID($hash);
        $admin_level = $this->getLevel($admin_uid);
        
        if ($admin_level >= $auth_conf['admin_level'])
        {
            return false;
        }
        else
        {
            $query = $this->mysqli->prepare("UPDATE users SET level = ? WHERE id = ?");
            $query->bind_param("ii", $level, $uid);
            $query->execute();
            $count = $query->affected_rows;
            $query->close();
            
            if($count == 0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }    
    
    /*
    * Returns language based on session hash
    * @param string $hash
    * @return string $language
    */

    public function getLang($hash)
    {
        $query = $this->mysqli->prepare("SELECT lang FROM sessions WHERE hash = ?");
        $query->bind_param("s", $hash);
        $query->bind_result($lang);
        $query->execute();
        $query->store_result();
        $count = $query->num_rows;
        $query->fetch();
        $query->close();

        if($count == 0)
        {
            return "en";
        }
        else
        {
            return $lang;    
        }
    }

    /*
    * Puts a user's language based on session hash
    * @param string $hash
    * @param string $lang
    * @return string $language
    */

    public function putLang($hash, $lang)
    {
        $query = $this->mysqli->prepare("UPDATE sessions SET lang = ? WHERE hash = ?");
        $query->bind_param("ss", $lang, $hash);
        $query->execute();
        $query->close();
    
        if($count == 0)
        {
            return false;
        }
        else
        {
            $uid = $this->sessionUID($hash);
            $query = $this->mysqli->prepare("UPDATE users SET lang = ? WHERE id = ?");
            $query->bind_param("si", $lang, $uid);
            $query->execute();
            $count = $query->affected_rows;
            $query->close();
    
            if($count == 0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }
}

?>
