<?php

class User {
  
  // The Unique id of the user
  // @var Int
  private $userId;

  // Name of the user
  // @var String
  private $name;
  
  // Username of the user
  // @var String
  private $userName;
  
  // User email id
  // @var String
  private $email;

  // The catagory of user 
  //  1 : Govt. official
  //  2 : Aspirant
  //  3 : Investor  
  // @var Int
  private $catagory;

  // Gender of the user(M/F/O)
  // @var String
  private $gender;
  
  // Occupation of the user
  // @var String  
  private $occupation;

  // Employment status(1/0)
  // @var Int
  private $employ;

  // Educaional Qualification
  // @var String
  private $qualif;
  
  // Aim of the user
  // @var String
  private $aim;
  
  // User password
  // @var String
  private $password;
  
  // User password
  // @var String
  private $lastLogout;

  //##################### Accessor and Mutator Methods #########################
  
  public function getUserId() {
    return $this->userId;
  }
  
  public function setUserId($userId) {
    $this->userId = $userId;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function setName($name) {
    $this->name = $name;
  }
  
  public function getUsername() {
    return $this->userName;
  }
  
  public function setUsername($userName) {
    $this->userName = $userName;
  }
  
  public function getEmail() {
    return $this->email;
  }
  
  public function setEmail($email) {
    $this->email = $email;
  }
  
  public function getPassword() {
    return $this->password;
  }
  
  public function setPassword($password) {
    $this->password = $password;
  }
  
  public function getLastlog() {
    return $this->lastLogout;
  }
  
  public function setLastlog($log) {
    $this->lastLogout = $log;
  }

  public function getCat() {
    return $this->catagory;
  }
  
  public function setCat($cat) {
    $this->catagory = $cat;
  }

  public function getGender() {
    return $this->gender;
  }
  
  public function setGender($gen) {
    $this->gender = $gen;
  }

  public function getOccup() {
    return $this->occupation;
  }
  
  public function setOccup($occup) {
    $this->occupation = $occup;
  }

  public function getEmploy() {
    return $this->employ;
  }
  
  public function setEmploy($emp) {
    $this->employ = $emp;
  }

  public function getQual() {
    return $this->qualif;
  }
  
  public function setQual($qual) {
    $this->qualif = $qual;
  }

  public function getAim() {
    return $this->aim;
  }
  
  public function setAim($Aim) {
    $this->aim = $Aim;
  }
  //##################### End of Accessor and Mutator Methods ##################
  
  /**
   * Returns the User Object provided the id of the user.
   * 
   * @param mysqli $db
   * @param int $id
   * @return \User
   */
  public function getUser($db, $id) {
    $resultObj = $db->query('SELECT * FROM `users` ' . 
                'WHERE `users`.`user_id` = ' . (int) $id);
    $user_details = $resultObj->fetch_assoc();
    $user = new User();
    $user->arrToUser($user_details);
    return $user;
  }
  
  /**
   * Set's the user details returned from the query into the current object.
   * 
   * @param array $userRow
   */
  public function arrToUser($userRow) {
    if (!empty($userRow)) {
      isset($userRow['user_id']) ? 
        $this->setUserId($userRow['user_id']) : '';
      isset($userRow['name']) ? 
        $this->setName($userRow['name']) : '';
      isset($userRow['username']) ? 
        $this->setUsername($userRow['username']) : '';
      isset($userRow['email']) ? 
        $this->setEmail($userRow['email']) : '';
      isset($userRow['catagory']) ? 
        $this->setCat($userRow['catagory']) : '';
      isset($userRow['gender']) ? 
        $this->setGender($userRow['gender']) : '';
      isset($userRow['occupation']) ? 
        $this->setOccup($userRow['occupation']) : '';
      isset($userRow['employ']) ? 
        $this->setEmploy($userRow['employ']) : '';
      isset($userRow['qualif']) ? 
        $this->setQual($userRow['qualif']) : '';
      isset($userRow['aim']) ? 
        $this->setAim($userRow['aim']) : '';
      isset($userRow['password']) ? 
        $this->setPassword($userRow['password']) : '';
      isset($userRow['lastLogout']) ? 
        $this->setLastlog($userRow['lastLogout']) : '';      
    }
  }

  public function postToJson(){
    $ret = array();
    $ret['user_id'] = $this->user_id;
    $ret['name'] = $this->name;
    $ret['username'] = $this->username;
    $ret['email'] = $this->email;
    $ret['catagory'] = $this->catagory;
    $ret['gender'] = $this->gender;
    $ret['occupation'] = $this->occupation;
    $ret['employ'] = $this->employ;
    $ret['qualif'] = $this->qualif;
    $ret['aim'] = $this->aim;
    $ret['lastLogout'] = $this->lastLogout;
    
    return json_encode($ret);   
  }
}
