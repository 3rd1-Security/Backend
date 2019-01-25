<?php

class Followship {
  
  // User one in the relationship
  // @var User
  public $userOne;
  
  // User two in the relationship
  // @var User
  public $userTwo;
  
  // Determines the status of the friendship
  // 1 - 1 follows 2
  // 2 - 2 follows 1
  // 3 - both follow each other
  // By default the status is set to 0
  public $status = 0;
  
  //##################### Accessor and Mutator Methods #########################
    
  public function getUserOne() {
    return $this->userOne;
  }
  
  public function setUserOne(User $userOne) {
    $this->userOne = $userOne;
  }
  
  public function getUserTwo() {
    return $this->userTwo;
  }
  
  public function setUserTwo(User $userTwo) {
    $this->userTwo = $userTwo;
  }
  
  public function getStatus() {
    return $this->status;
  }
  
  public function setStatus($status) {
    $this->status = $status;
  }
  
  //##################### End of Accessor and Mutator Methods ##################
  
  /* Set's the details of the relationship from the query result into the 
   * current relationship object instance.
   *
   * @param array $row
   * @param mysqli $dbCon
   */
  public function arrToFollowship($row, $dbCon) {
    if (!empty($row)) {
      if (isset($row['user_one_id']) && isset($row['user_two_id'])) {
        // Fetch the user details and create the user object set.
        $resultObj = $dbCon->query('SELECT * FROM `users` WHERE `users`.`user_id` IN (' . (int)$row['user_one_id'] . ', ' . (int)$row['user_two_id'] . ')');
        
        $usersArr = array();
        while($record = $resultObj->fetch_assoc()) {
          $usersArr[] = $record;
        }
        
        $userOne = new User();
        $userTwo = new User();
        
        // Check which user id is lesser.
        if ($row['user_one_id'] < $row['user_two_id']) {
          $userOne->arrToUser($usersArr[0]);
          $userTwo->arrToUser($usersArr[1]);
        } else {
          $userOne->arrToUser($usersArr[1]);
          $userTwo->arrToUser($usersArr[0]);
        }
        
        $this->setUserOne($userOne);
        $this->setUserTwo($userTwo);
      }
      
      isset($row['status']) ? $this->setStatus((int)$row['status']) : '';
    }
  }
}
