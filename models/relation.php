<?php

class Relation {
    
  /**
   * The user who is currently logged in
   * @var User
   */
  private $loggedInUser;
  
  /**
   * Database connection
   * @var mysqli
   */
  private $dbCon;
  
  /**
   * @param mysqli $dbCon
   * @param User $loggedInUser
   * @return boolean|Relation
   */
  public function __construct($dbCon, User $loggedInUser) {
    if ($dbCon == 'undefined') {
      return false; // or you could throw an exception
    }
    // Current loggedin user
    $this->loggedInUser = $loggedInUser;
    // Database Connection
    $this->dbCon = $dbCon;
  }
    
  /**
   * Return the friend of the current logged in user in the relationship object
   * 
   * @param Relationship $rel
   * @return User $friend
   */
  public function getFriend(Relationship $rel) {
    if ($rel->getUserOne()->getUserId() === $this->loggedInUser->getUserId()) {
      $friend = $rel->getUserTwo();
    } else {
      $friend = $rel->getUserOne();
    }
    
    return $friend;
  }
  
  /**
   * Get all the friends list for the currently loggedin user
   * 
   * @return array Relationship Objects
   */
  public function getFriendsList() {
    $id = (int)$this->loggedInUser->getUserId();
    
    $sql = 'SELECT * FROM `relationship` WHERE ' .
            '(`user_one_id` = ' . $id . ' OR `user_two_id` = '. $id .') ' .
            'AND `status` = 1';
            
    $resultObj = $this->dbCon->query($sql);
    
    $rels = array();
    
    while($row = $resultObj->fetch_assoc()) {
      $rel = new Relationship();
      $rel->arrToRelationship($row, $this->dbCon);
      $rels[] = $rel;
    }
    
    return $rels;
  }
  
  /**
   * Get the list of friend requests sent by the logged in user
   * 
   * @return array Relationship Objects
   */
  public function getSentFriendRequests() {
    $id = (int) $this->loggedInUser->getUserId();
    
    $sql = 'SELECT * FROM `relationship` WHERE ' . 
            '(`user_one_id` = ' . $id . ' OR `user_two_id` = ' . $id . ') ' . 
            'AND `status` = 0 '. 
            'AND `action_user_id` = ' . $id;
            
    $resultObj = $this->dbCon->query($sql);
    
    $rels = array();
    
    while($row = $resultObj->fetch_assoc()) {
      $rel = new Relationship();
      $rel->arrToRelationship($row, $this->dbCon);
      $rels[] = $rel;
    }
    
    return $rels;
  }
  
  /**
   * Get the list of friend requests for the logged in user
   * 
   * @return array Relationship Objects
   */
  public function getFriendRequests() {
    $id = (int) $this->loggedInUser->getUserId();
    
    $sql = 'SELECT * FROM `relationship` ' . 
            'WHERE (`user_one_id` = ' . $id . ' OR `user_two_id` = '. $id .')' . 
            ' AND `status` = 0 ' . 
            'AND `action_user_id` != ' . $id;
    
    $resultObj = $this->dbCon->query($sql);
    
    $rels = array();
    
    while($row = $resultObj->fetch_assoc()) {
      $rel = new Relationship();
      $rel->arrToRelationship($row, $this->dbCon);
      $rels[] = $rel;
    }
    
    return $rels;
  }
  
  /**
   * Get the list of friends blocked by the current user.
   * 
   * @return \Relationship array
   */
  public function getBlockedFriends() {
    $id = (int) $this->loggedInUser->getUserId();
    
    $sql = 'SELECT * FROM `relationship` ' . 
            'WHERE (`user_one_id` = ' . $id . ' OR `user_two_id` = '. $id .')' . 
            ' AND `status` = 3 ' . 
            'AND `action_user_id` = ' . $id;
    
    $resultObj = $this->dbCon->query($sql);
    
    $rels = array();
    
    while($row = $resultObj->fetch_assoc()) {
      $rel = new Relationship();
      $rel->arrToRelationship($row, $this->dbCon);
      $rels[] = $rel;
    }
    
    return $rels;
  }
  
  /**
   * Get the relatiohship for the friend and user
   * 
   * @param User $user
   * @return boolean|int - either false or the relationship status
   */
  public function getRelationship(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $user_two = (int) $user->getUserId();
    
    if ($user_one > $user_two) {
        $temp = $user_one;
        $user_one = $user_two;
        $user_two = $temp;
    }
    
    $sql = 'SELECT * FROM `relationship` ' .
            'WHERE `user_one_id` = ' . $user_one . 
            ' AND `user_two_id` = ' . $user_two;
    
    $resultObj = $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      $row = $resultObj->fetch_assoc();
      $relationship = new Relationship();
      $relationship->arrToRelationship($row, $this->dbCon);
      return $relationship;
    }
    
    return false;
  }
  
  /**
   * Insert a new friends request
   * 
   * @param User $user - User to which the friend request must be added with.
   * @return Boolean
   */
  public function addFriendRequest(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $action_user_id = $user_one;
    $user_two = (int) $user->getUserId();
    
    if ($user_one > $user_two) {
        $temp = $user_one;
        $user_one = $user_two;
        $user_two = $temp;
    }
    
    $sql = 'INSERT INTO `relationship` '
            . '(`user_one_id`, `user_two_id`, `status`, `action_user_id`) '
            . 'VALUES '
            . '(' . $user_one . ', '. $user_two .', 0, '. $action_user_id .')';
    


    $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      $follow = new Follow($this->dbCon, $this->loggedInUser);
      if($follow->addFollow($user)) {
        return true;
      }
    }
    
    return false;
  }
  
  /**
   * Accept a friend request
   * 
   * @param User $user - User to whome the friend request must be accepted with.
   * @return Boolean
   */
  public function acceptFriendRequest(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $action_user_id = $user_one;
    $user_two = $user->getUserId();
    
    if ($user_one > $user_two) {
      $temp = $user_one;
      $user_one = $user_two;
      $user_two = $temp;
    }
    
    $sql = 'UPDATE `relationship` '
            . 'SET `status` = 1, `action_user_id` = '. $action_user_id 
            .' WHERE `user_one_id` = '. $user_one 
            .' AND `user_two_id` = ' . $user_two;
    
    $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      return true;
    }
    
    return false;
  }
  
  /**
   * Decline a friend request for the user
   * 
   * @params User $user - The user whose request to be declined
   * @return Boolean
   */
  public function declineFriendRequest(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $action_user_id = $user_one;
    $user_two = $user->getUserId();
    
    if ($user_one > $user_two) {
      $temp = $user_one;
      $user_one = $user_two;
      $user_two = $temp;
    }
    
    $sql = 'UPDATE `relationship` '
            . 'SET `status` = 2, `action_user_id` = '. $action_user_id 
            .' WHERE `user_one_id` = '. $user_one 
            .' AND `user_two_id` = ' . $user_two;
            
    $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      return true;
    }
    
    return false;
  }
  
  /**
   * Cancel a friend request
   * 
   * @param User $user - The friend details
   * @return Boolean
   */
  public function cancelFriendRequest(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $user_two = (int) $user->getUserId();
    
    if ($user_one > $user_two) {
      $temp = $user_one;
      $user_one = $user_two;
      $user_two = $temp;
    }
    
    $sql = 'DELETE FROM `relationship` ' .
            'WHERE `user_one_id` = ' . $user_one . 
            ' AND `user_two_id` = ' . $user_two .
            ' AND `status` = 0';
    
    $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      return true;
    }
    
    return false;
  }
  
  /**
   * Remove a friend from the friends list
   * 
   * @param User $user - The friend details
   * @return Boolean
   */
  public function unfriend(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $user_two = (int) $user->getUserId();
    
    if ($user_one > $user_two) {
      $temp = $user_one;
      $user_one = $user_two;
      $user_two = $temp;
    }
    
    $sql = 'DELETE FROM `relationship` ' .
            'WHERE `user_one_id` = ' . $user_one . 
            ' AND `user_two_id` = ' . $user_two .
            ' AND `status` = 1';
    
    $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      return true;
    }
    
    return false;
  }
  
  /**
   * Block a particular user
   * 
   * @param User $user - The user to be blocked
   * @return Boolean
   */
  public function block(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $action_user_id = $user_one;
    $user_two = $user->getUserId();
    
    if ($user_one > $user_two) {
      $temp = $user_one;
      $user_one = $user_two;
      $user_two = $temp;
    }
    
    $sql = 'UPDATE `relationship` '
            . 'SET `status` = 3, `action_user_id` = '. $action_user_id 
            .' WHERE `user_one_id` = '. $user_one 
            .' AND `user_two_id` = ' . $user_two;
            
    $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      $sql = 'DELETE FROM `followship` ' .
              'WHERE `user_one_id` = ' . $user_one . 
              ' AND `user_two_id` = ' . $user_two;    
            
      $this->dbCon->query($sql);
      if ($this->dbCon->affected_rows > 0) {
        return true;
      }
    }
    
    return false;
  }
  
  /*
   * Unblock a friend who is blocked already. 
   * @param User $user
   * @return boolean
   */
  public function unblockFriend(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $user_two = (int) $user->getUserId();
    
    if ($user_one > $user_two) {
      $temp = $user_one;
      $user_one = $user_two;
      $user_two = $temp;
    }
    
    $sql = 'DELETE FROM `relationship` ' .
            'WHERE `user_one_id` = ' . $user_one . 
            ' AND `user_two_id` = ' . $user_two .
            ' AND `status` = 3';
    
    $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      return true;
    }
    
    return false;
  }

  public function 
}
