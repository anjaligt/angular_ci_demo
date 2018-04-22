<?php

class Common_Model extends CI_Model {

    public $_tablePrefix = "";
    public $suggested_users;

    function __construct() {
        parent::__construct();
        $this->_tableprefix = $this->db->dbprefix;
    }

    /* common function used to get all data from any table
     * @param String $select
     * @param String $table
     * @param Array/String $where
     */


    function JoinedInterestCount()
    {
        $this->db->select('CommunityMemberID');
        $this->db->distinct();
        $this->db->from(COMMUNITYMEMBER);
        $this->db->where('UserID',$this->session->userdata('UserID'));
        return $this->db->count_all_results();
        //echo $this->db->last_query();

    }

    function get_all_table_data($select = '*', $table, $where = "", $orderby = "", $order = "ASC") {
        $this->db->select($select);
        $this->db->from($table);
        if ($where != "") {
            $this->db->where($where);
        }
        if ($orderby != "") {
            $this->db->order_by($orderby, $order);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    function getCountries() {
        $this->db->select('country_id,country_code,country_name');
        $this->db->from(COUNTRYMASTER);
        $query = $this->db->order_by("country_name", "asc");
        $query = $this->db->get();
        return $query->result_array();
    }

    /* common function used to get single row from any table
     * @param String $select
     * @param String $table
     * @param Array/String $where
     */

    function get_single_row($select = '*', $table, $where = "") {
        $this->db->select($select);
        $this->db->from($table);
        if ($where != "") {
            $this->db->where($where);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * insert data in table
     *
     * @param $table_name
     * @param $data_array
     * @param String $return
     * @return mixed
     */
    function insert_data($table_name, $data_array, $return = 'id') {
        if ($table_name && is_array($data_array)) {
            $columns = $this->getTableFields($table_name);
            foreach ($columns as $coloumn_data)
                $column_name[] = $coloumn_data['Field'];

            foreach ($data_array as $key => $val) {
                if (in_array(trim($key), $column_name)) {
                    $data[$key] = trim($val);
                }
            }
            $this->db->insert($table_name, $data);
            $id = $this->db->insert_id();
            if ($return == 'id') {
                return $id;
            } else {
                $arr[$return] = $id;
                return $this->get_single_row('*', $table_name, array($arr));
            }
        }
    }

    function insert($table_name, $data, $return = 'id') {
        $this->db->insert($table_name, $data);
        $id = $this->db->insert_id();
        return $id;
    }

    /**
     * This method updates fields in my table.
     * @param String $fieldName
     * @param String $value
     * @param Integer $id
     */
    function update_field($table = "", $fieldName, $fieldValue, $where = "") {
        if (empty($fieldName)) {
            log_message('error', 'Got empty fieldName: ' . $fieldName);
            return FALSE;
        } else if ($table == "") {
            log_message('error', 'Got empty table name');
            return FALSE;
        } else if ($where == "") {
            return false;
        } else {
            $this->db->where($where);
            $query = $this->db->update($table, array($fieldName => $fieldValue));
        }
    }

    /**
     * Updates whole row [unlike update_field()]
     * @param Array $data
     * @param Integer $id
     */
    function update($table_name = "", $data_array, $where = "") {
        if ($table_name && is_array($data_array)) {
            $columns = $this->getTableFields($table_name);
            foreach ($columns as $coloumn_data)
                $column_name[] = $coloumn_data['Field'];

            foreach ($data_array as $key => $val) {
                if (in_array(trim($key), $column_name)) {
                    $data[$key] = trim($val);
                }
            }
            if (!is_array($data)) {
                log_message('error', 'Supposed to get an array!');
                return FALSE;
            } else if ($table_name == "") {
                log_message('error', 'Got empty table name');
                return FALSE;
            } else if ($where == "") {
                return false;
            } else {
                $this->db->where($where);
                $this->db->update($table_name, $data);
            }
        }
    }

    /**
     * Delete row 
     * @param String $table
     * @param Array/String $where
     */
    function delete($table = "", $where = "") {

        if ($table == "") {
            log_message('error', 'Got empty table name');
            return FALSE;
        } else if ($where == "") {
            return false;
        } else {
            $this->db->where($where);
            $this->db->delete($table);
        }
    }

    /**
     * getTableFields
     * @param String $table_name
     */
    function getTableFields($table_name) {
        $query = "SHOW COLUMNS FROM " . $this->db->dbprefix . "$table_name";
        $rs = $this->db->query($query);
        return $rs->result_array();
    }

    /*
      |--------------------------------------------------------------------------
      | check whether user login or not.
      |@Inputs: (Defined in user role DB Table)
      |--------------------------------------------------------------------------
     */

    function is_user_login() {

        if ($this->session->userdata('user_id') == '')
            return false;
        else
            return true;
    }

    /**
     * Function Name: set_remember_me
     * @param user_id
     * Description: Set remember me
     */
    function set_remember_me($user_id) {
        $cookie = array(
            'name' => 'remember_me',
            'value' => $user_id,
            'expire' => '7776000'  // 90 days expiration time
        );

        $this->input->set_cookie($cookie);
    }

    /**
     * Function Name: remember_me
     * Description: Set remember me
     */
    function remember_me() {
        if ($this->input->cookie('remember_me')) {
            $user_id = $this->input->cookie('remember_me');
            $result = $this->get_user_data($user_id);
            $user_session = array();
            if (!empty($result)) {
                if ($result['status'] == 0) {
                    $user_session = array('inactive_user_id' => $result['user_id'],
                        'inactive_name' => $result['name'],
                        'inactive_email' => $result['email']
                    );
                } else {

                    $user_session['user_id'] = $result['user_id'];
                    $user_session['unique_id'] = $result['unique_id'];
                    $user_session['avatar_id'] = $result['avatar_id'];
                    $user_session['name'] = $result['name'];
                    $user_session['email'] = $result['email'];
                    $user_session['image'] = $result['image'];
                }
                $this->session->set_userdata($user_session);
            }
        }
    }

    /*
      |--------------------------------------------------------------------------
      | Use to get error message by error code
      | @Inputs: errorcode
      |--------------------------------------------------------------------------
     */

    function getError($errorcode) {
        $row = array();
        /* Query to get ErrorCode Description - Starts */
        $data = array('ErrorCode' => $errorcode);
        $query = $this->db->get_where(ERRORCODES, $data, 1);

        /* Query to get ErrorCode Description - Ends */

        if ($query->num_rows() == 1) {
            $row = $query->row_array();
        } else {
            $row['Description'] = 'Invalid errorcode.';
        }
        return $row['Description'];
    }

    /*
      |--------------------------------------------------------------------------
      | Use to get slug for blog, post and article
      | @Inputs: tablename, title
      |--------------------------------------------------------------------------
     */

    function getRemoveSpecialCharectors($string) {
        return preg_replace('/[^A-Za-z0-9]/', '', $string);
    }

    /*
      |--------------------------------------------------------------------------
      | Use to get slug for blog, post and article
      | @Inputs: tablename, title
      |--------------------------------------------------------------------------
     */

    function getSlug($tablename, $title) {
        return $this->getRemoveSpecialCharectors($title);
    }

    /*
      |--------------------------------------------------------------------------
      | Use to get slug for blog, post and article
      | @Inputs: tablename, title
      |--------------------------------------------------------------------------
     */

    function getCategorySlug($title) {
        return $this->getRemoveSpecialCharectors($title);
    }

    /*
      |--------------------------------------------------------------------------
      | Use get DeviceTypeID
      |@Inputs: (Defined in devicetypes DB Table)
      |--------------------------------------------------------------------------
     */

    function GetDeviceTypeID($DeviceType) {
        $DeviceTypeID = '';
//$query=$this->db->query("SELECT  DeviceTypeID  FROM  `".DEVICETYPES."`  where Name='".$DeviceType."'   limit 1");


        $this->db->select('DeviceTypeID');
        $this->db->where('Name', $DeviceType);
        $query = $this->db->get(DEVICETYPES);

        if ($query->num_rows() > 0) {
            $Data = $query->row_array();
            $DeviceTypeID = $Data['DeviceTypeID'];
        } else {
            $DeviceType = DEFAULT_DEVICE_TYPE;
            $this->db->select('DeviceTypeID');
            $this->db->where('Name', $DeviceType);
            $query = $this->db->get(DEVICETYPES);

            if ($query->num_rows() > 0) {
                $Data = $query->row_array();
                $DeviceTypeID = $Data['DeviceTypeID'];
            }
        }
        return $DeviceTypeID;
    }

    /*
      |--------------------------------------------------------------------------
      | Use get SourceID
      |@Inputs: (Defined in sources DB Table)
      |--------------------------------------------------------------------------
     */

    function GetSourceID($SocialType) {
        $SourceID = '';
        $this->db->select('SourceID');
        $this->db->where('Name', $SocialType);
        $query = $this->db->get(SOURCES);

        if ($query->num_rows() > 0) {
            $Data = $query->row_array();
            $SourceID = $Data['SourceID'];
        }
        return $SourceID;
    }

    /*
      |--------------------------------------------------------------------------
      | Use get Resolution
      |@Inputs: (Defined in resolution DB Table)
      |--------------------------------------------------------------------------
     */

    function GetResolutionID($Resolution) {
        $ResolutionID = '';
        $this->db->select('ResolutionID');
        $this->db->where('Name', $Resolution);
        $query = $this->db->get(RESOLUTION);
        if ($query->num_rows() > 0) {
            $Data = $query->row_array();
            $ResolutionID = $Data['ResolutionID'];
        } else {
            $Resolution = DEFAULT_RESOLUTION;
            $this->db->select('ResolutionID');
            $this->db->where('Name', $Resolution);
            $query = $this->db->get(RESOLUTION);
            if ($query->num_rows() > 0) {
                $Data = $query->row_array();
                $ResolutionID = $Data['ResolutionID'];
            }
        }
        return $ResolutionID;
    }

    /**
     * Function Name: addEdit
     * @param table_name
     * @param data_array
     * @param where
     * Description: Add or update table row
     */
    function addEdit($table_name, $data_array, $where = array()) {

        if ($table_name && is_array($data_array)) {
            if (!empty($where)) {
                $query1 = $this->db->update_string($table_name, $data_array, $where);
            } else {
                $query1 = $this->db->insert_string($table_name, $data_array);
            }
            $this->db->db_debug = FALSE; 
            $result = $this->db->query($query1);
            if($this->db->_error_number()>=1){
                $result = $this->db->_error_message();
            }
            
            return $result;
        }
    }

    /**
     * Function Name: chk_membership
     * @param tablename
     * @param userid
     * @param GroupID
     * Description: Check if user is member or not
     */
    function chk_membership($tablename, $userid, $GroupID) {
        $sql = "select GroupMemeberID from " . $tablename . " WHERE UserID = " . $userid . " and GroupID = " . $GroupID . " ";
        $res = $this->db->query($sql);
        return $res->num_rows();
    }

    /*
      |--------------------------------------------------------------------------
      | Use get History of users
      |@Inputs: (Defined in payment history and session history DB Table)
      |--------------------------------------------------------------------------
     */

    function get_user_payment_history($UserId) {
        $this->db->select("*");
        $this->db->from(USER_PAYMENT_HISTORY);
        $this->db->where('UserID', $UserId);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Function Name: get_user_session_history
     * @param UserId
     * Description: Check user session history
     */
    function get_user_session_history($UserId) {
        $this->db->select("*");
        $this->db->from(USER_SESSION_HISTORY);
        $this->db->where('UserID', $UserId);
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
      |--------------------------------------------------------------------------
      | Use get follow users
      |@Inputs: (Defined in follow DB Table)
      |--------------------------------------------------------------------------
     */

    function follow($data, $FromFriend = '0') {


        if (constant('ALLOW_' . strtoupper($data['Type']) . '_FOLLOW') == 1) {//if follow is enabled for this entity
            
            $follow_details = $this->get_single_row('FollowID,StatusID', 'Follow', array('UserID' => $data['UserID'], 'TypeEntityID' => $data['TypeEntityID'], 'Type' => $data['Type'])); //get details to check if this already exists

            $result['request_sent'] = 0;

            if (count($follow_details) > 0) {//already following

                if ($follow_details['StatusID'] == 1) {

                            $this->db->where('UserID', $data['UserID'])
                            ->where('TypeEntityID', $data['TypeEntityID'])
                            ->where('Type', $data['Type'])
                            ->delete(FOLLOW);

                    $result['request_sent'] = 3;        
                    $result['msg'] = 'Request has been cancelled successfully';
                    $result['msg_type'] = lang('success');
                    $result['result'] = lang('success');
                } else {

                    //$sql = "delete from Follow where UserID=".$data['UserID']." and TypeEntityID =  ".$data['TypeEntityID']."  and Type = '".$data['Type']."'";
                    //$this->db->query("UPDATE ".ACTIVITY." SET StatusID='3' WHERE ActivityTypeID='3' AND UserID='".$data['UserID']."' AND EntityID='".$data['TypeEntityID']."'");
                    //$this->db->query($sql);

                    $this->db->where('UserID', $data['UserID'])
                            ->where('TypeEntityID', $data['TypeEntityID'])
                            ->where('Type', $data['Type'])
                            ->delete(FOLLOW);

                    if ($data['Type'] == 'user') {//generate activity and notification only if the follow is for user entity
                        $this->db->where('ActivityTypeID', '3')
                                ->where('UserID', $data['UserID'])
                                ->where('EntityID', $data['TypeEntityID'])
                                ->update(ACTIVITY, array('StatusID' => '3'));
                    }

                    $friendData = $this->get_user_data($data['TypeEntityID']);

                    $fname = $lname = '';
                    if(isset($friendData['FirstName'])){
                        $fname = stripcslashes($friendData['FirstName']);
                    }
                    if(isset($friendData['LastName'])){
                        $lname = stripcslashes($friendData['LastName']);
                    }

                    $result['msg'] = 'You have successfully unfollowed ' . $fname . ' ' . $lname;
                    $result['msg_type'] = lang('success');
                    $result['result'] = lang('success');
                }
            } else { //insert in follow and reeturn success message

                $privacyKey = 26;
                $privacy_status = self::getPrivacySettingStatus($privacyKey, $data['TypeEntityID']);  // Get Privacy Setting status for follow Request if 1 then request will be sent

                if ($privacy_status == 0) {

                    // if type = user then EntityOwnerID = TypeEntityID else we need to fetch owner id according to entitytype.


                    $this->insert_data('Follow', array('UserID' => $data['UserID'], 'TypeEntityID' => $data['TypeEntityID'], 'EntityOwnerID' => $data['TypeEntityID'], 'Type' => $data['Type']));


                    $friendData = $this->get_user_data($data['TypeEntityID']);

                    $fname = $lname = '';
                    if(isset($friendData['FirstName'])){
                        $fname = stripcslashes($friendData['FirstName']);
                    }
                    if(isset($friendData['LastName'])){
                        $lname = stripcslashes($friendData['LastName']);
                    }
                    
                    $result['msg'] = 'You are now following ' . $fname . ' ' . $lname;

                    // $result['msg'] = 'You are now following this '.$data['Type'].'.';
                    $result['msg_type'] = lang('success');
                    $result['result'] = lang('success');

                    if ($data['Type'] == 'user' && $FromFriend != 1) {//generate activity and notification only if the follow is for user entity
                        $activity['UserID'] = $data['UserID'];
                        $activity['EntityID'] = $data['TypeEntityID'];
                        $activity['EntityType'] = 'User';
                        
                        $activity1['UserID'] = $data['TypeEntityID'];
                        $activity1['EntityID'] = $data['UserID'];
                        $activity1['EntityType'] = 'User';

                        $this->activity_model->addActivity($activity, 'Follow', array($data['UserID'], $data['TypeEntityID']), 'User');
                        $this->activity_model->addActivity($activity1, 'FollowingYou', array($data['UserID'], $data['TypeEntityID']), 'User');

                        $parameters[0]['ReferenceID'] = $data['UserID'];
                        $parameters[0]['Type'] = 'User';
                        $this->notification_model->addNotification(5, $data['UserID'], array($data['TypeEntityID']), $data['UserID'], $parameters);
                    }
                } else {

                    $this->insert_data('Follow', array('UserID' => $data['UserID'], 'TypeEntityID' => $data['TypeEntityID'], 'EntityOwnerID' => $data['TypeEntityID'], 'Type' => $data['Type'], 'StatusID' => 1));
                    $result['msg'] = 'Your request has been sent to follow this ' . $data['Type'] . '.';
                    $result['msg_type'] = lang('success');
                    $result['result'] = lang('success');

                    if ($data['Type'] == 'user') {//generate notification only if the follow is for user entity and activity will be generated when user accepts the request 
                        $parameters[0]['ReferenceID'] = $data['UserID'];
                        $parameters[0]['Type'] = 'User';
                        $this->notification_model->addNotification(30,$data['UserID'],array($data['TypeEntityID']),$data['UserID'],$parameters);
                    }
               
                    $result['request_sent'] = 1;

                }
            }



            $followByYou    = $this->friend_model->checkFollowStatus($data['TypeEntityID'],$data['UserID']);
            $FollowByOther  = $this->friend_model->checkFollowStatus($data['UserID'],$data['TypeEntityID']);

            $result['ShowFollowBtn']     = 1;
            $result['ShowFriendsBtn']    = 0;

            if($followByYou==0 && $FollowByOther==0)
            {
               $result['ShowFollowBtn']     = 1;
               $result['ShowFriendsBtn']    = 1;
            }

            $privacyTypeID = $this->friend_model->getAddFriendPrivacy(6, $data['TypeEntityID']);
            if($followByYou==1 && $FollowByOther==1)
            {
               $result['ShowFriendsBtn']    = 0; 
               $result['ShowFollowBtn']    = 1;
            }
            elseif($privacyTypeID==34 && $followByYou!=0 && $FollowByOther!=0) // every one
            {
                $result['ShowFriendsBtn']    = 1;      
            }     
            elseif($privacyTypeID==35) // frnd of frnd
            {   
                $FOF = $this->friend_model->CheckFriendOfFriend($data['UserID'],$data['TypeEntityID']); // Get friend of friend ids
                
                if (!empty($FOF) && $followByYou==0 && $FollowByOther==0) {
                    $result['ShowFriendsBtn'] = 1;
                } else {
                    $result['ShowFriendsBtn'] = 0;
                }
            }
            elseif($privacyTypeID == 60)
            {
                 $result['ShowFriendsBtn']    = 0;      
            }



            return $result;
        }
    }


    function followWithFriend($data)
    {

       $follow_details = $this->get_single_row('FollowID,StatusID', 'Follow', array('UserID' => $data['UserID'], 'TypeEntityID' => $data['TypeEntityID'], 'Type' => $data['Type']));

       if(count($follow_details) > 0)
       {

            $this->db->where('UserID', $data['UserID'])
            ->where('TypeEntityID', $data['TypeEntityID'])
            ->where('Type', 'user')
            ->update(FOLLOW, array('StatusID' => '2'));
       }
       else
       {

          $this->insert_data('Follow', array('UserID' => $data['UserID'], 'TypeEntityID' => $data['TypeEntityID'], 'EntityOwnerID' => $data['TypeEntityID'], 'Type' => $data['Type']));

       }

    }

    /**
     * @Summary: Accept/Reject follow request 
     * @create_date: Wed, Dec 31, 2014
     * @last_update_date:
     * @access: public
     * @inputs:  (Defined in follow DB Table)
     * @return:
     */
    function action_request($data) {


        $follow_details = $this->get_single_row('FollowID,StatusID', 'Follow', array('UserID' => $data['RequesterID'], 'EntityOwnerID' => $data['EntityOwnerID'], 'Type' => $data['Type'], 'StatusID' => 1)); //get details to check if this already exists


        if (count($follow_details) > 0) { // if pending request exists

            if ($data['RequestType'] == 'approved') {
                //  $this->insert_data('Follow', array('UserID' =>$data['UserID'], 'TypeEntityID' =>$data['TypeEntityID'] ,'Type' =>$data['Type']));

                $this->db->where('FollowID', $follow_details['FollowID'])
                        ->update(FOLLOW, array('StatusID' => '2'));

                $result['msg'] = 'Approved successfully';
                $result['msg_type'] = lang('success');
                $result['result'] = lang('success');

                if ($data['Type'] == 'user') {//generate activity and notification only if the follow is for user entity
                    $activity['UserID'] = $data['UserID'];
                    $activity['EntityID'] = $data['TypeEntityID'];
                    $activity['EntityType'] = 'User';

                    $this->activity_model->addActivity($activity, 'Follow', array($data['UserID'], $data['TypeEntityID']), 'User');

                    $parameters[0]['ReferenceID'] = $data['UserID'];
                    $parameters[0]['Type'] = 'User';
                    $this->notification_model->addNotification(5, $data['UserID'], array($data['TypeEntityID']), $data['UserID'], $parameters);
                }
            } else {

                $this->db->where('FollowID', $follow_details['FollowID'])
                        ->delete(FOLLOW);

                if ($data['Type'] == 'user') {//generate activity and notification only if the follow is for user entity
                    $this->db->where('ActivityTypeID', '3')
                            ->where('UserID', $data['UserID'])
                            ->where('EntityID', $data['TypeEntityID'])
                            ->update(ACTIVITY, array('StatusID' => '3'));
                }

                $result['msg'] = 'Rejected successfully';
                $result['msg_type'] = lang('success');
                $result['result'] = lang('success');
            }
        } else { //insert in follow and reeturn success message
            $result['ResponseCode'] = 504;
            $result['msg'] = lang('record_not_found');
            $result['msg_type'] = lang('success');
            $result['result'] = lang('success');
        }


        return $result;
        // }
    }

    function cancelFollowRequest($data) {


        $follow_details = $this->get_single_row('FollowID,StatusID', 'Follow', array('UserID' => $data['UserID'], 'TypeEntityID' => $data['TypeEntityID'], 'Type' => $data['Type'])); //get details to check if this already exists


        if (count($follow_details) > 0) {//already following

            if ($follow_details['StatusID'] == 1) {


                //$sql = "delete from Follow where UserID=".$data['UserID']." and TypeEntityID =  ".$data['TypeEntityID']."  and Type = '".$data['Type']."'";
                //$this->db->query("UPDATE ".ACTIVITY." SET StatusID='3' WHERE ActivityTypeID='3' AND UserID='".$data['UserID']."' AND EntityID='".$data['TypeEntityID']."'");
                //$this->db->query($sql);

                $this->db->where('UserID', $data['UserID'])
                        ->where('TypeEntityID', $data['TypeEntityID'])
                        ->where('Type', $data['Type'])
                        ->delete(FOLLOW);

                if ($data['Type'] == 'user') {//generate activity and notification only if the follow is for user entity
                    $this->db->where('ActivityTypeID', '3')
                            ->where('UserID', $data['UserID'])
                            ->where('EntityID', $data['TypeEntityID'])
                            ->update(ACTIVITY, array('StatusID' => '3'));
                }

                $result['msg'] = 'Successfully deleted';
                $result['msg_type'] = lang('success');
                $result['result'] = lang('success');
            } else {
                $result['ResponseCode'] = 504;
                $result['msg'] = lang('record_not_found');
                $result['msg_type'] = lang('success');
                $result['result'] = lang('success');
            }
        } else {

            $result['ResponseCode'] = 504;
            $result['msg'] = lang('record_not_found');
            $result['msg_type'] = lang('success');
            $result['result'] = lang('success');
        }


        return $result;
    }

    /*
      |--------------------------------------------------------------------------
      | Use get unfollow users
      |@Inputs: (Defined in follow DB Table)
      |--------------------------------------------------------------------------
     */

    function unfollow($user_id, $type, $type_entity_id) {
        $this->db->from('Follow');
        $this->db->where(array('TypeEntityID' => $type_entity_id, 'UserID' => $user_id, 'Type' => $type)); //check for security i.e. if the user who was following is only removing the follow
        $query = $this->db->get();
        $result = $query->row_array();
        if (count($result) <= 0 || $result['UserID'] != $user_id || $result['Type'] != $type)
            $return = array("msg" => "Invalid Request", "msg_type" => "error");
        else {
            $this->db->delete("Follow", array("FollowID" => $result['FollowID'], 'Type' => $type));
            $return = array("msg" => "Successfully unfollowed.", "msg_type" => "success");
        }
        return $return;
    }

    /*
      |--------------------------------------------------------------------------
      | Remove Follower
      |@Inputs: (Defined in follow DB Table)
      |--------------------------------------------------------------------------
     */

    function remove_follower($data) {

        if (constant('ALLOW_' . strtoupper($data['Type']) . '_FOLLOW') == 1) {//if follow is enabled for this entity
            $follow_details = $this->get_single_row('FollowID,StatusID', 'Follow', array('UserID' => $data['UserID'], 'EntityOwnerID' => $data['EntityOwnerID'], 'Type' => $data['Type'])); //get details to check if this already exists


            if (count($follow_details) > 0) {

                $this->db->where('UserID', $data['UserID'])
                        ->where('EntityOwnerID', $data['EntityOwnerID'])
                        ->where('Type', $data['Type'])
                        ->delete(FOLLOW);


                $friendData = $this->get_user_data($data['UserID']);


                $result['msg'] = 'You have successfully removed ' . stripcslashes($friendData['FirstName']) . ' ' . stripcslashes($friendData['LastName']);


                //$result['msg']      = 'Successfully Removed';
                $result['msg_type'] = lang('success');
                $result['result'] = lang('success');
            } else { //insert in follow and reeturn success message

                $result['ResponseCode'] = 504;
                $result['msg'] = lang('record_not_found');
                $result['msg_type'] = lang('success');
                $result['result'] = lang('success');
            }


            return $result;
        }
    }

    /*
      |--------------------------------------------------------------------------
      | Use get right of the  roles
      |@Inputs: (Defined in user role DB Table)
      |--------------------------------------------------------------------------
     */

    function get_role_rights($RoleID) {
        //$query=$this->db->query("SELECT Roles.Name ,Rights.Name, Rights.Description FROM Roles join RoleRights on Roles.RoleID = RoleRights.RoleID join Rights on RoleRights.RightID = Rights.RightID where Roles.RoleID =".$RoleID." and Rights.IsActive = 1");
        $query = $this->db->select('Roles.Name ,Rights.Name, Rights.Description')
                ->from('Roles')
                ->join('RoleRights', 'Roles.RoleID=RoleRights.RoleID', 'left')
                ->join('Rights', 'RoleRights.RightID=Rights.RightID', 'right')
                ->where('Roles.RoleID', $RoleID)
                ->where('Rights.IsActive', '1')
                ->get();
        return $query->result_array();
    }

    /*
      |--------------------------------------------------------------------------
      | Use get role of the  roles
      |@Inputs: (Defined in user role DB Table)
      |--------------------------------------------------------------------------
     */

    function get_user_role($userid) {
        $query = $this->db->select('Roles.Name')
                ->from('Roles')
                ->join('UserRoles', 'UserRoles.RoleID=Roles.RoleID')
                ->where('UserRoles.UserID', $userid)
                ->get();
        return $query->result_array();
    }

    /**
     * Function Name: getUserData
     * @param userid
     * Description: Get user details
     */
    function getUserData($userid) {
        $return = array();

        $this->db->select('U.*,Ud.UserMotto');
        $this->db->from(USERS.' U');
        $this->db->join(USERDETAILS.' Ud','Ud.UserID=U.UserID','left');
        $this->db->where('U.UserID',$userid);
        $query = $this->db->get();

        //$query = $this->db->get_where(USERS, array('UserID' => $userid));
        
        if ($query->num_rows() > 0) {
            $return = $query->result_array();
        }


        return $return;
    }

    /**
     * Function Name: getMusicianUserUrl
     * @param userid
     * Description: Get user url
     */

    function getMusicianUserUrl($userid) {
        $return = array();

        $this->db->select('*');
        $this->db->where('UserID',$userid);
        $this->db->where('StatusID',5);
    
        $res = $this->db->get(USERURL);
        if($res->num_rows() > 0){
          $return = $res->num_rows();
        }
        return $return;
    }


    /**
     * Function Name: getUsersByRole
     * @param role
     * Description: List of users by role
     */
    function getUsersByRole($role) {
        if (!empty($role)) {
            $users = array();
            //$users="select * from USERS where UserID in ( SELECT UserID FROM ".USERROLES." where RoleID in ('".$role."'))";
            $users = $this->db->where('UserID IN (SELECT UserID FROM ".USERROLES." where RoleID in ("' . $role . '"))', NULL, FALSE)
                    ->get(USERS);
            $query = $this->db->query($users);
            if ($query->num_rows() > 0) {
                $users = $query->result_array();
            }
            return $users;
        }
    }

    /**
     * Function Name: UpdateProfilePercent
     * @param UserGUID
     * @param type
     * @param UserID
     * @param doRemove
     * Description: Update profile percent of user
     */
    function UpdateProfilePercent($UserGUID, $type, $UserID = '', $doRemove = '0') {
        switch ($type) {
            case '1' : //for profile my account setting
                $Perctage = 30;
                break;
            case '2' : // for profile image
                $Perctage = 20;
                break;
            case '3' : //interest
                $Perctage = 30;
                break;
            case '4' : // for profile description
                $Perctage = 20;
                break;
        }
        if ($UserID == '') {
            //$userdsql=$this->db->query("select UserID from ".USERS." where UserGUID='".$UserGUID."' limit 0,1 ");
            $userdsql = $this->db->select('UserID')
                    ->from(USERS)
                    ->where('UserGUID', $UserGUID)
                    ->limit(1)
                    ->get();
            $Userres = $userdsql->row_array();
            $UserID = $Userres['UserID'];
        }

        //$check=$this->db->query("select * from ".USERPROFILEPERCENTAGE." where UserID='".$UserID."' and EntityType='".$type."' limit 0,1 ");
        $check = $this->db->where('UserID', $UserID)
                ->where('EntityType', $type)
                ->limit(1)
                ->get(USERPROFILEPERCENTAGE);
        if ($check->num_rows() > 0) {
            $res = $check->row_array();

            if ($doRemove == 1) {
                //$this->db->query("DELETE FROM ".USERPROFILEPERCENTAGE." where PercentageID='".$res['PercentageID']."' limit 1");               
                $this->db->where('PercentageID', $res['PercentageID'])
                        ->delete(USERPROFILEPERCENTAGE);
            } else {
                $check = $this->db->where('PercentageID', $res['PercentageID'])
                        ->update(USERPROFILEPERCENTAGE, array('UserID' => $UserID, 'EntityType' => $type, 'Perctage' => $Perctage, 'ModifiedDate' => date('Y-m-d')));
            }
        } else {
            if ($doRemove == '0') {
                $this->db->insert(USERPROFILEPERCENTAGE, array('UserID' => $UserID, 'EntityType' => $type, 'Perctage' => $Perctage, 'CreatedDate' => date('Y-m-d')));
            }
        }
    }

    /**
     * Function Name: update_info
     * @param data
     * Description: Update user information
     */
    function update_info($data) {
        //$userdsql=$this->db->query("select UserID from ".USERS." where UserGUID='".$data->UserGUID."' limit 0,1 ");
        $userdsql = $this->db->select('UserID')
                ->from(USERS)
                ->where('UserGUID', $data->UserGUID)
                ->limit(1)
                ->get();
        $Userres = $userdsql->row_array();
        $userID = $Userres['UserID'];

        $res = $this->db->where('UserID', $userID)
                ->update(USERS, array('FirstName' => ($data->FirstName), 'LastName' => ($data->LastName), 'Location' => EscapeString($data->Location), 'Prifix' => $data->Prifix, 'PhoneNumber' => $data->Phone));


        if ($data->FirstName != '') {
            $DisplayName = $data->FirstName;
            if ($data->LastName != '') {
                $DisplayName.=" " . $data->LastName;
            }
        }

        $this->session->set_userdata('DisplayName', $DisplayName);


        //$sqlEmailUpdate = "update ".USERLOGINS." set LoginKeyword='".$data->Email."' where UserID='".$userID."' and SourceID=1 and LoginType=1";
        //$resEmailUpdate = $this->db->query($sqlEmailUpdate); 
        //$sqlUserNAmeUpdate = "update ".USERLOGINS." set LoginKeyword='".$data->UserName."' where UserID='".$userID."' and SourceID=1 and LoginType=2";
        //$resUserNAmeUpdate = $this->db->query($sqlUserNAmeUpdate); 

        $res_tz = $this->db->where('UserID', $userID)
                ->update(USERDETAILS, array('Zipcode' => $data->Zipcode, 'School' => $data->School, 'Address' => $data->Address, 'BirthDate' => $data->BirthDate));
        
        $SchoolData = GetData('SchoolMasterID', SCHOOLMASTER, array('SchoolName' => $data->School), '1');
        if(!$SchoolData){
            $this->db->insert(SCHOOLMASTER,array('SchoolName'=>$data->School));
        }

        if (($res)) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * Function Name: downgrade
     * @param data
     * Description: Downgrade user
     */
    function downgrade($data) {
        $sql = "update " . USERROLES . " set RoleID = " . $data['RoleId'] . "  where UserID = " . $data['UserId'] . "";
        $res = $this->db->query($sql);
        if ($res) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * Function Name: get_following_list
     * @param userid
     * Description: Get list of following users
     */
    function get_following_list($userid) {
        $rs = $this->db->select('TypeEntityID,Type')
                ->from(FOLLOW)
                ->where('UserID', $userid)
                ->get();
        return $rs->result_array();
    }

    /**
     * Function Name: get_follower_list
     * @param userid
     * Description: Get list of followers
     */
    function get_follower_list($userid) {
        $rs = $this->db->select('TypeEntityID,Type')
                ->from(FOLLOW)
                ->where('UserID', $userid)
                ->where('Type', 'user')
                ->get();
        return $rs->result_array();
    }

    /**
     * Function Name: chk_for_tag_existence
     * @param arr
     * @param blogid
     * Description: Check if tag is exist or not
     */
    function chk_for_tag_existence($arr, $blogid) {
        $ids = array();
        foreach ($arr as $k => $v) {
            $this->db->select('TagID');
            $this->db->from(BLOG_TAG_MASTER);
            $this->db->where(array('TagName' => $v));
            $query = $this->db->get();
            $result = $query->row_array();
            if (!isset($result['TagID']) && empty($result['TagID'])) {
                $rs = $this->db->insert(BLOG_TAG_MASTER, array('TagName' => EscapeString(ucwords($v)), 'TagSlug' => EscapeString(strtolower($v)), 'IsActive' => '1', 'CreatedAt' => date('Y-m-d H:i:s')));
                $result['TagID'] = $this->db->insert_id();
            }
            $ids[$k] = $result['TagID'];
            $rs = $this->db->insert(BLOG_TAG_RELATION, array('BlogID' => $blogid, 'TagID' => $ids[$k]));
        }
        return $rs;
    }

    /**
     * Function Name: chk_for_cat_existence
     * @param arr
     * @param blogid
     * Description: Check if category is exist or not
     */
    function chk_for_cat_existence($arr, $blogid) {
        $ids = array();
        foreach ($arr as $k => $v) {
            $this->db->select('CategoryID');
            $this->db->from(BLOGCATEGORY);
            $this->db->where(array('Title' => $v));
            $query = $this->db->get();
            $result = $query->row_array();
            if (!isset($result['TagID']) && empty($result['TagID'])) {
                $sql = "insert " . BLOGCATEGORY . " set Title = '" . EscapeString(ucwords($v)) . "', Slug = '" . EscapeString(strtolower($v)) . "', IsActive = 1 , CreatedAt = '" . date('Y-m-d H:i:s') . "',Description = '" . EscapeString(ucwords($v)) . " description here' , Image = 'image here' , CreatedBy = '4'";
                $rs = $this->db->query($sql);
                $result['CatID'] = $this->db->insert_id();
            }
            $ids[$k] = $result['CatID'];
            $sql = "insert " . BLOGCATEGORYRELATION . " set BlogID = " . $blogid . " , CategoryID = " . $ids[$k] . " ";
            $rs = $this->db->query($sql);
        }
        return $rs;
    }

    /**
     * Function Name: delete_comment
     * @param id
     * Description: Remove comment
     */
    function delete_comment($id) {
        $rs = $this->db->delete(COMMENTS, array('CommentID' => $id));
        return $rs;
    }

    /**
     * Function Name: setUserStatus
     * @param status
     * @param userid
     * Description: Set Status of User
     */
    function setUserStatus($status, $userid) {
        $query = $this->db->where('UserGUID', $userid)
                ->update(USERS, array('StatusID' => $status));
        return $query;
    }

    /**
     * Function Name: autosuggest_blog
     * @param key
     * Description: Autosuggest blog by key
     */
    function autosuggest_blog($key) {
        $sql = "SELECT " . BLOG . ".* FROM " . BLOG_TAG_MASTER . " inner join " . BLOG_TAG_RELATION . " on " . BLOG_TAG_MASTER . ".TagID = " . BLOG_TAG_RELATION . ".TagID inner join " . BLOG . " on " . BLOG . ".BlogID = " . BLOG_TAG_RELATION . ".BlogID where " . BLOG_TAG_MASTER . ".TagSlug like '%" . $key . "%'  ";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * Function Name: get_time_zone
     * Description: Get Timezone
     */
    function get_time_zone() {
        $this->db->select('TimeZoneID,StandardTime');
        $this->db->from(TIMEZONES);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Function Name: check_remember_me
     * Description: Check remember me
     */
    public function check_remember_me() {
        if ($this->input->cookie('remember_me')) {
            $user_id = $this->input->cookie('remember_me');
            $result = $this->get_user_data($user_id);
            $user_session = array();
            $LoginSessionKey = random_string('unique', 8);
            if (!empty($result)) {
                if ($result['StatusID'] == 0) {
                    die;
                    $user_session = array('inactive_user_id' => $result['UserID'], 'inactive_name' => $result['FirstName'], 'inactive_email' => $result['Email']);
                } else {
                    $user_session['UserID'] = $result['UserID'];
                    $user_session['LoginSessionKey'] = $LoginSessionKey;
                    $user_session['UserGUID'] = $result['UserGUID'];
                    $user_session['FirstName'] = $result['FirstName'];
                    $user_session['LastName'] = $result['LastName'];
                    $user_session['Email'] = $result['Email'];
                }

                $this->db->where(array('UserID' => $result['UserID']));
                $this->db->limit(1);
                $this->db->delete(ACTIVELOGINS);

                $this->db->insert(ACTIVELOGINS, array('UserID' => $result['UserID'], 'LoginSessionKey' => $LoginSessionKey, 'CreatedDate' => date('Y-m-d H:i:s')));
                $this->session->set_userdata($user_session);
            }
        }
    }

    /**
     * Function Name: get_user_data
     * @param user_id
     * Description: Get User Data
     */
    public function get_user_data($user_id) {
        $rs = $this->db->where('UserID', $user_id)
                ->get(USERS);
        return $rs->row_array();
    }

    /**
     * Function Name: check_member
     * @param Groupid
     * @param UserID
     * Description: Check if user is group member or not
     */
    function check_member($Groupid, $UserID) {
        $sql = $this->db->select('GroupID')
                ->from('GroupMembers')
                ->where('UserID', $UserID)
                ->where('GroupID', $Groupid)
                ->where('Presence', 'JOINED')
                ->get();
        if ($sql->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }


    function check_member_presence($Groupid, $UserID) {
        $sql = $this->db->select('Presence')
                ->from('GroupMembers')
                ->where('UserID', $UserID)
                ->where('GroupID', $Groupid)
                ->get();
        if ($sql->num_rows() > 0) {
             return $sql->row()->Presence;
        } else {
            return false;
        }
    }

    /**
     * Function Name: check_owner
     * @param Groupid
     * @param UserID
     * Description: Check if user is group owner or not
     */
    function check_owner($Groupid, $UserID) {
        $sql = $this->db->select('GroupID')
                ->from(GROUPS)
                ->where('CreatedBy', $UserID)
                ->where('GroupID', $Groupid)
                ->get();
        if ($sql->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Function Name: GetUserGUIDFromProfileName
     * @param ProfileName
     * Description: Get User GuID from Profile Name
     */
    function GetUserGUIDFromProfileName($ProfileName) {
        $query = $this->db->select('UserGUID')
                ->from(USERS)
                ->where('UserID=(select UserID from ' . USERDETAILS . ')', NULL, FALSE)
                ->where('ProfileName', $ProfileName)
                ->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $UserGUID = $row['UserGUID'];
        } else {
            $UserGUID = '';
        }
        return $UserGUID;
    }

    /**
     * Function Name: GetProfileLink
     * @param UserID
     * Description: Get User Profile Link
     */
    function GetProfileLink($UserID) {
        if ($UserID == '')
            return;
        $query = $this->db->select(USERS . '.UserGuID,' . USERDETAILS . '.ProfileName,'.USERROLES .'.RoleID')
                ->from(USERS)
                ->join(USERDETAILS, USERS . '.UserID=' . USERDETAILS . '.UserID', 'left')
                ->join(USERROLES, USERS . '.UserID=' . USERROLES . '.UserID', 'left')
                ->where(USERS . '.UserID', $UserID)
                ->get();
        $result = $query->row();
        if (empty($result))
            return;
        if($result->RoleID=='1')
             return '#';
      else  if ($result->ProfileName == '') {
            return site_url('user') . '/' . $result->UserGuID;
        } else {
           return site_url().$result->ProfileName;
            //return site_url('user') . '/' . $result->ProfileName;
        }
    }

    /**
     * Function Name: GetUserIDFromProfileName
     * @param ProfileName
     * Description: Get UserID From Profile Name
     */
    function GetUserIDFromProfileName($ProfileName) {
        //$fetch="select UserID from ".USERS." where UserID=(select UserID from ".USERDETAILS." where ProfileName='".$ProfileName."') ";
        //$query=$this->db->query($fetch);
        $query = $this->db->select('UserID')
                ->from(USERS)
                ->where('UserID=(select UserID from ' . USERDETAILS . ' where ProfileName="' . $ProfileName . '")', NULL, FALSE)
                ->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $UserGUID = $row['UserID'];
        } else {
            $UserGUID = '';
        }
        return $UserGUID;
    }

    /**
     * Function Name: GetUserIDFromGuID
     * @param UserGuID
     * Description: Get UserID From User GuID
     */
    function GetUserIDFromGuID($UserGuID) {
        //$fetch="select UserID from ".USERS." where UserGuID='".$UserGuID."'";
        //$query=$this->db->query($fetch);
        $query = $this->db->select('UserID')
                ->from(USERS)
                ->where('UserGuID', $UserGuID)
                ->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $UserGUID = $row['UserID'];
        } else {
            $UserGUID = '';
        }
        return $UserGUID;
    }

    /**
     * Function Name: GetUserGuIDFromID
     * @param UserID
     * Description: Get User GuID From UserID
     */
    function GetUserGuIDFromID($UserGuID) {
        //$fetch="select UserGuID from ".USERS." where UserID='".$UserGuID."'";
        //$query=$this->db->query($fetch);
        $query = $this->db->select('UserGuID')
                ->from(USERS)
                ->where('UserID', $UserGuID)
                ->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $UserGUID = $row['UserGuID'];
        } else {
            $UserGUID = '';
        }
        return $UserGUID;
    }

    /**
     * Function Name: CreteAutoWallPost
     * @param data
     * Description: Create Auto Wall Post
     */
    function CreteAutoWallPost($data) {
        $this->db->insert(POST, $data);
        $PostID = $this->db->insert_id();
        return $PostID;
    }

    /* function getSettings(){
      $settings = array();
      $result = $this->db->get(SETTINGS);
      if($result->num_rows($result)){
      foreach($result->result() as $r){
      $settings[$r->ModuleKey] = $r->IsActive;
      }
      }
      return $settings;
      } */

    /**
     * Function Name: getOffset
     * @param PageNo
     * @param Limit
     * Description: Get offset for pagination
     */
    function getOffset($PageNo, $Limit) {
        $offset = ($PageNo - 1) * $Limit;
        return $offset;
    }

    /**
     * Function Name: getRecords
     * @param table
     * @param fields
     * @param condition
     * @param orderby
     * @param single_row
     * Description: Get records from table
     */
    function getRecords($table, $fields = "", $condition = "", $orderby = "", $single_row = false) {
        if ($fields != "") {
            $this->db->select($fields);
        }
        if ($orderby != "") {
            $this->db->order_by($orderby);
        }
        if ($condition != "") {
            $rs = $this->db->get_where($table, $condition);
        } else {
            $rs = $this->db->get($table);
        }
        if ($single_row) {
            return $rs->row_array();
        }
        return $rs->result_array();
    }

    /**
     * Function Name: GetActivityID
     * @param ActivityGuID
     * Description: Get ActivityID From ActivityGuID
     */
    function GetActivityID($ActivityGuID,$Type=false) {
        //$SQL = "SELECT ActivityID FROM " . ACTIVITY . " WHERE ActivityGuID = '$ActivityGuID' LIMIT 1";
        //$query = $this->db->query($SQL);
        $query = $this->db->select('ActivityID,ActivityTypeID')
                ->from(ACTIVITY)
                ->where('ActivityGuID', $ActivityGuID)
                ->limit(1)
                ->get();
        if ($query->num_rows() == 0) {
            return FALSE;
        } else {
            $data = $query->row_array();
            if($Type){
                return $data['ActivityTypeID'];
            }
            return $data['ActivityID'];
        }
    }

    /**
     * Function Name: getActivityIdByPost
     * @param PostID
     * Description: Get ActivityID By PostID
     */
    function getActivityIdByPost($PostID,$ActivityTypeID) {
        $this->db->where('EntityID', $PostID);
        $this->db->where('ActivityTypeID', $ActivityTypeID);
        $Activity = $this->db->get(ACTIVITY);
        if ($Activity->num_rows()) {
            $ActivityID = $Activity->row();
            return $ActivityID->ActivityID;
        } else {
            return false;
        }
    }

    /**
     * Function Name: GetWallTypeID
     * @param WallType
     * Description: Get WallTypeID
     */
    function GetWallTypeID($WallType) {
        $this->db->where('WallName', $WallType);
        $Wall = $this->db->get(WALLTYPES);
        if ($Wall->num_rows()) {
            $Wall = $Wall->row();
            return $Wall->WallTypeID;
        } else {
            return false;
        }
    }

    /**
     * Function Name: getWeekDayID
     * @param Day
     * Description: Get WeekdayID
     */
    function getWeekDayID($Day) {
        $this->db->where('Name', $Day);
        $Week = $this->db->get(WEEKDAYS);
        if ($Week->num_rows()) {
            $WeekID = $Week->row();
            return $WeekID->WeekdayID;
        } else {
            return '';
        }
    }

    /**
     * Function Name: getExtID
     * @param ext
     * Description: Get Extension ID from Extension
     */
    function getExtID($ext) {
        $this->db->where('Name', $ext);
        $ext = $this->db->get(MEDIAEXTENSIONS);
        if ($ext->num_rows()) {
            $extID = $ext->row();
            return $extID->MediaExtensionID;
        } else {
            return 0;
        }
    }


    /**
     * Function Name: getMediaTypeID
     * @param ext
     * Description: Get Extension ID from Extension
     */
    function getMediaTypeID($ext) {
        $this->db->where('Name', $ext);
        $ext = $this->db->get(MEDIAEXTENSIONS);
        if ($ext->num_rows()) {
            $extID = $ext->row();
            return $extID->MediaTypeID;
        } else {
            return 0;
        }
    }

    /**
     * Function Name: isUserActivated
     * @param LoginSessionKey
     * Description: Check if user is activated or not
     */
    function isUserActivated($LoginSessionKey) {
        //$query = $this->db->query("SELECT * FROM ".USERS." LEFT JOIN ".ACTIVELOGINS." ON ".ACTIVELOGINS.".UserID=".USERS.".UserID WHERE ".ACTIVELOGINS.".LoginSessionKey='".$LoginSessionKey."' AND ".USERS.".StatusID='2'");
        $query = $this->db->select('*')
                ->from(USERS)
                ->join(ACTIVELOGINS, ACTIVELOGINS . '.UserID=' . USERS . '.UserID', 'left')
                ->where(ACTIVELOGINS . '.LoginSessionKey', $LoginSessionKey)
                ->where( "(".USERS.".StatusID =  '2' OR ".USERS.".StatusID =  '1')" ,NUll,FALSE)
                ->get();
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }



    
    function getUserStatus($UserID) {
        //$query = $this->db->query("SELECT * FROM ".USERS." LEFT JOIN ".ACTIVELOGINS." ON ".ACTIVELOGINS.".UserID=".USERS.".UserID WHERE ".ACTIVELOGINS.".LoginSessionKey='".$LoginSessionKey."' AND ".USERS.".StatusID='2'");
        $query = $this->db->select('StatusID')
                ->from(USERS)
                ->where(USERS . '.UserID', $UserID)
                ->get();
        if ($query->num_rows()) {
            $extID = $query->row();
            return $extID->StatusID;
        } else {
            return false;
        }
    }

    /**
     * Function Name: getTimeSlot
     * Description: Get current timeslot
     */
    function getTimeSlot() {
        $d = date('H i');
        $d = explode(" ", $d);
        $min = $d[1] / 60;
        $dec = $d[0] + $min;


        //$this->db->select('TimeSlotID');
        //$this->db->where("'"."".$dec."' > ", 'ValueRangeFrom');
        //$this->db->where("'$dec' BETWEEN ValueRangeFrom AND ValueRangeTo");
        //$query = $this->db->get(TIMESLOTS);
        $query = $this->db->query("SELECT TimeSlotID FROM TimeSlots WHERE '" . $dec . "' BETWEEN ValueRangeFrom AND ValueRangeTo");
        if ($query->num_rows()) {
            return $query->row()->TimeSlotID;
        } else {
            return '';
        }
    }

    /**
     * Function Name: getPrivacySettingStatus
     * @param privacyKey, userId
     * Description: Get Privacy Setting status for a particular privacyKey
     */
    function getPrivacySettingStatus($privacyKey, $userId) {

        $this->db->select('ID');
        $this->db->where('UserID', $userId);
        $this->db->where('NotificationTypeId', $privacyKey);
        $query = $this->db->get('UserNotificationSettings');

        if ($query->num_rows()) {
            return $query->row()->ID;
        } else {
            return 0;
        }
    }
    /**
     * Function Name: getPrivacySettingStatus
     * @param privacyType, userId
     * Description: Get Privacy Setting status for a particular privacyKey
     */
    function getAddFriendPrivacy($privacyType, $userId) {
        $this->db->select('NT.NotificationTypeID');
        $this->db->join(NOTIFICATIONTYPES.' NT','NT.NotificationTypeID=UNS.NotificationTypeId');
        $this->db->where('UNS.UserID', $userId);
        $this->db->where('NT.NotificationType', $privacyType);
        $query = $this->db->get('UserNotificationSettings UNS');
        if ($query->num_rows()) {
            return $query->row()->NotificationTypeID;
        } else {
            return 0;
        }
    }

    function getSliderImages()
    {
      $this->db->select('*');
      $this->db->from('HomePageSlider');
      $this->db->order_by('ImageID','RANDOM');
      $sql = $this->db->get();

      if ($sql->num_rows())
      {
        return $sql->result();
      }
      else
      {
        return false;
      }


    }


    function GetData($field,$table,$where,$row='0') {
        $this->db->select($field);
        $this->db->from($table);
        if($where)
        $this->db->where($where);
        $sql = $this->db->get();
        if ($sql->num_rows()) {
            $data=array();
             foreach($sql->result() as $item)
            {
                if($row)
                {
                    return $item;
                }
                    
                else{
                    $data[]=$item;
                }
            }
            return $data;
        } else {
            return '0';
        }
    }
    
     function getUserIdByGuid($UserGUID) {
        $sql = $this->db->query("select UserID from " . USERS . " WHERE UserGUID='" . $UserGUID . "' limit 0,1");
        if ($sql->num_rows() > 0) {
            $row = $sql->row_array();
            $userID = $row['UserID'];
        } else {
            $userID = 0;
        }
        return $userID;
    }
    
    function getPageGuidById($PageID){
    $result=$this->GetData('PageGUID',PAGES,array('PageID'=>$PageID),1) ;
    if($result)
        return $result->PageGUID;
        else
            return false;
}
    function getCommunityGuidById($CommunityID){
    $result=$this->GetData('CommunityGUID',COMMUNITY,array('CommunityID'=>$CommunityID),1) ;
    if($result)
        return $result->CommunityGUID;
        else
            return false;
    }
    
    function getCommunityIdByGUId($CommunityGUID){
    $result=$this->GetData('CommunityID',COMMUNITY,array('CommunityGUID'=>$CommunityGUID),1) ;
    if($result)
        return $result->CommunityID;
        else
            return false;
    }

    function check_jam_message($LoginUserID,$UserID)
    { 
        $this->db->select('M.MessageID');
        $this->db->from(MESSAGES.' M');
        $this->db->join(MESSAGERECEIVER.' MR','M.MessageID = MR.MessageID','left');
        $this->db->where('MR.ReceiverUserID',$UserID);
        $this->db->where('M.MessageType',3);
        $this->db->where('M.UserID',$LoginUserID);
        $Query = $this->db->get();

        if($Query->num_rows()>0)
        {
          return true;
        }
        else
        {
          return false;
        }
    }


}



// Class close