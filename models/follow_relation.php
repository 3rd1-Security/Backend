<?php

class Follow {
    
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
    
  /*
   * Return the follower of the current logged in user in the folowship object
   * @param Relationship $rel
   * @return User $friend
   */
  public function getFollower(Followship $rel) {
    if ($rel->getUserOne()->getUserId() === $this->loggedInUser->getUserId()) {
      $follow = $rel->getUserTwo();
    } else {
      $follow = $rel->getUserOne();
    }
    
    return $follow;
  }
  
  /*
   * Get all the followers list for the currently loggedin user 
   * @return array followship Objects
   */
  public function getFollowersList() {
    $id = (int)$this->loggedInUser->getUserId();
    
    $sql = 'SELECT * FROM `followship` WHERE ' . '(`user_one_id` = ' . $id . ' AND `status` IN(2,3)) OR (`user_two_id` = '. $id .' AND `status` IN (1,3) )';
            
    $resultObj = $this->dbCon->query($sql);
    
    $rels = array();
    
    while($row = $resultObj->fetch_assoc()) {
      $rel = new Followship();
      $rel->arrToFollowship($row, $this->dbCon);
      $rels[] = $rel;
    }
    
    return $rels;
  }

  public function getFollowingList() {
    $id = (int)$this->loggedInUser->getUserId();
    
    $sql = 'SELECT * FROM `followship` WHERE ' . '(`user_one_id` = ' . $id . ' AND `status` IN(1,3)) OR (`user_two_id` = '. $id .' AND `status` IN (2,3) )';
            
    $resultObj = $this->dbCon->query($sql);
    
    $rels = array();
    
    while($row = $resultObj->fetch_assoc()) {
      $rel = new Followship();
      $rel->arrToFollowship($row, $this->dbCon);
      $rels[] = $rel;
    }
    
    return $rels;
  }
  
  /**
   * Get the relatiohship for the follower and user or vice-versa
   * @param User $user
   * @return boolean|int - either false or the relationship status
   */
  public function getFollowship(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $user_two = (int) $user->getUserId();
    
    if ($user_one > $user_two) {
        $temp = $user_one;
        $user_one = $user_two;
        $user_two = $temp;
    }
    
    $sql = 'SELECT * FROM `followship` ' .
            'WHERE `user_one_id` = ' . $user_one . 
            ' AND `user_two_id` = ' . $user_two;
    
    $resultObj = $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      $row = $resultObj->fetch_assoc();
      $followship = new Followship();
      $followship->arrToFollowship($row, $this->dbCon);
      return $followship;
    }
    
    return false;
  }
  
  /**
   * Insert a new friends request
   * @param User $user - User to which the friend request must be added with.
   * @return Boolean
   */
  public function addFollow(User $user) {
    $user_one = (int) $this->loggedInUser->getUserId();
    $status = 1;
    $user_two = (int) $user->getUserId();
    
    if ($user_one > $user_two) {
        $temp = $user_one;
        $user_one = $user_two;
        $user_two = $temp;
        $status = 2;
    }
    
    $sql = 'INSERT INTO `followship` '
            . '(`user_one_id`, `user_two_id`, `status`) '
            . 'VALUES '
            . '(' . $user_one . ', '. $user_two .', '. $status .')';
    
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
  public function Unfollow(User $user) {
    $followship = new Followship();
    $followship = $this->getFollowship($user);
    
    if ($followship==false || $followship=='undefined') {
      return false;
    }

    $old_status = $followship->getStatus();
    $user_one = $followship->getUserOne();
    $user_two = $followship->getUserTwo();
    
    if ($old_status==1 && $user_two==$user->getUserId()) {
        $status = 0;    
    } else if ($old_status==2 && $user_one==$user->getUserId()) {
        $status = 0;
    } else if ($old_status==3) {
        if ($user_one==$user->getUserId()) {
            $status = 1;
        } else {
            $status = 2;
        }
    } else {
        return true;
    }
    if($status==0){
        $sql = 'DELETE FROM `followship` ' .
                'WHERE `user_one_id` = ' . $user_one . 
                ' AND `user_two_id` = ' . $user_two;    
    } else {
        $sql = 'UPDATE `relationship` '
                . 'SET `status` = '. $status .', `action_user_id` = '. $action_user_id 
                .' WHERE `user_one_id` = '. $user_one 
                .' AND `user_two_id` = ' . $user_two;
            
    $this->dbCon->query($sql);
    
    if ($this->dbCon->affected_rows > 0) {
      return true;
    }
    
    return false;
  }

}
