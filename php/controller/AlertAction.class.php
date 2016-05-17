<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 10:56:20
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class AlertAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        Validate::checkUserSession();
        switch ($this->data->action) {
            case 'getAlert':
                $this->getAlert();
                break;
            default:
                //$this->query();
                break;
        }
            
    }
    private function getAlert(){
        $user = new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $friend=new FriendModel();
        $friend->user_id=$user_result->user_id;
        $this->res['no_agree_friend_nums']=$friend->getNoAgreeFriendNums();
        $whispers=new WhispersModel();
        $whispers->user_id=$user_result->user_id;
        $this->res['no_read_whispers']=$whispers->getNoReadWhispers();
        $message=new MessageModel();
        $message->user_id=$user_result->user_id;
        $this->res['no_read_message']=$message->getNoReadMessage();
        $this->res['status']=true;
        echo json_encode($this->res);
        
    }

    
}

$alert = new AlertAction();
$alert->action();