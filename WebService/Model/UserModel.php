<?php

/**
 * First Global Data Corp.
 *
 * @category DEX_API
 * @package  Api\WebServiceBundle\Tests\Controller
 * @author   Anit Shrestha Manandhar <ashrestha@firstglobalmoney.com>
 * @license  http://firstglobalmoney.com/license description
 * @version  v1.0.0
 * @link     (remittanceController, http://firsglobaldata.com)
 */

namespace  Model;

use  Model\TransactionModel as Transaction;
use Model\MainModel as Main;
use \Library\PasswordHash as Password;

/**
 * User Entity
 *
 * @category DEX_API
 * @package  Api\WebServiceBundle\Tests\Controller
 * @author   Anit Shrestha Manandhar <ashrestha@firstglobalmoney.com>
 * @license  http://firstglobalmoney.com/license Usage License
 * @version  v1.0.0
 * @link     http://firsglobaldata.com
 */
class UserModel extends Main
{
    private $_hashObj;
    /**
     * for connection 
     */
    function __construct() 
    {
        parent::__construct();
        $this->_hashObj = new Password();      
    }


    /**
     * [authenticateUser description]
     * 
     * @param varchar $username username 
     * @param varchar $password clear password 
     * 
     * @return void 
     *
     * @todo 
     *  This logic was iimplememted in after user checking user is active or not before,
     *  We need to findout its significance and implement, if necessary.
     *  Logic ---
     * // $userAgent = $user->getAgent();
     * // $userProcessor = $userAgent->getParentAgent() ? : $userAgent; 
     * // if( !$userAgent->isActive() or !$userProcessor->isActive() ) 
     * //     return false;
     * Logic End ----
     **/
    public function authenticateUser($username, $password)
    {
       
        // $this=new Transaction();
       
        $user = $this->connection->fetchAssoc('SELECT * FROM f1_users  WHERE username = ?', array($username)); 
        
        if ( $user ) {

            if ($user['deleted'] !=0 || $user['active']==0) {
                return false;
            }
           

            if ($hash = $user['password_hash']) {
                if ( ! $this->_hashObj->password_verify($password, $hash)) {
                    return false;
                } 
            } else if ($user['password'] == md5($password)) {
                    $hashValue=$this->_hashObj->password_hash($password, PASSWORD_BCRYPT);
                    $this->connection->update(
                        'f1_users', array('password_hash' => $hashValue ,'password'=>' '), array('username' => $username)
                    ); 
            } else return false;   
        
            return true;
        }
         return false;
    }

    /**
     * [isAuthorized description]
     * 
     * @param varchar $user Username 
     * @param varchar $pass Password
     * 
     * @return boolean  
     */
    public function isAuthorized($user, $pass)
    {
        if (!empty($user) && !empty($pass)) {
            $auth=$this->conn->fetchAssoc('SELECT * from f1_api_users where username=? and password =?', array($user,$pass));
            $sendStatus=count($auth);

            return ($sendStatus<2?false:true);
        }

    }

    /**
     * [changePassword description]
     * 
     * @param [type] $username    [description]
     * @param [type] $newPassword [description]
     * 
     * @return bool
     */
    public function changePassword($username,$newPassword)
    { 
            $hashValue=$this->_hashObj->password_hash($newPassword, PASSWORD_BCRYPT);
            $this->connection->update('f1_users', array('password_hash' => $hashValue ,'password'=>' '), array('username' => $username));
            $result=$this->authenticateUser($username, $newPassword);

        if ( $result == false) {
            return false;
        } else {
            return true;
        }


    }
}