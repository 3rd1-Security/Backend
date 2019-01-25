<?php 

class Feed {
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
      return false; 
    }
    // Current loggedin user
    $this->loggedInUser = $loggedInUser;
    // Database Connection
    $this->dbCon = $dbCon;
  }
    
  /**
   * Return the unseen posts of the current logged in user posted by friends
   * 
   * @param Relationship $rel
   * @return User $friend
   */
  public function getFeed($start = 0, $limit = 2000) {
    $id = (int)$this->loggedInUser->getUserId();
    
    $relation = new Relation($this->dbCon, $this->loggedInUser);
    $rels = $relation->getFriendsList();
    $friends = array();
    foreach ($rels as $key=>$value) {
    	$friends[$key] = $relation->getFriend($rels[$key]);
    }

    $follow = new Follow($this->dbCon, $this->loggedInUser);
    $followship = $follow->getFollowingList();
    $following = array();
    foreach ($followship as $key=>$value) {
    	$following[$key] = $relation->getFollower($followship[$key]);
    }
    $sql = 'SELECT * FROM `feeds` WHERE `by` IN ('. implode(', ', $friends) .', '. implode(', ', $following) .') ORDER BY timestamp DESC LIMIT '
    		. $limit . ' OFFSET '. $start;
    
    $resultObj = $this->dbCon->query($sql);
    
    $posts = array();
    
    while($row = $resultObj->fetch_assoc()) {
      $rel = new Post();
      $rel->arrToPost($row, $this->dbCon);
      $posts[] = $rel;
    }
    
    return $posts;
  }
  
  /**
   * Get all the friends list for the currently loggedin user
   * @return array Relationship Objects
   */
  public function searchFeed($keyword, $start = 0, $limit = 1000) {
    $id = (int)$this->loggedInUser->getUserId();
    
    $all_posts = new Post();
    $all_posts = $this->getFeed($start, $limit);

    $filtered_posts = array();
    $filter_pos = array();

    foreach ($all_posts as $key => $value) {
    	if($pos = stripos($all_posts[$key]->getMessage(), $keyword)) {
    		$filtered_posts[] = $all_posts[$key];
    		$filter_pos[] = $pos;
    	}
    }

    $result = array();
    $result['posts'] = $filtered_posts;
    $result['position'] = $filter_pos;

    return $filtered_posts;
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
}