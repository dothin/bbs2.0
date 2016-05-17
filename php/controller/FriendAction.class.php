<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 16:02:12
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class FriendAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        parent::__construct(new FriendModel());
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        Validate::checkUserSession();
        switch ($this->data->action) {
            case 'addFriend':
                $this->addFriend();
                break;
            case 'checkIsMyFriend':
                $this->checkIsMyFriend();
                break;
            case 'getIsUserFriendList':
                $this->getIsUserFriendList();
                break;
            case 'getAddUserFriendList':
                $this->getAddUserFriendList();
                break;
            case 'agreeFriend':
                $this->agreeFriend();
                break;
            case 'deleteFriend':
                $this->deleteFriend();
                break;
            default:
                //$this->query();
                break;
        }
            
    }
    private function agreeFriend(){
        $this->model->friend_id=$this->data->id;
        if(!!$result=$this->model->agreeFriend()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="添加失败";
            echo json_encode($this->res);
        }
        
    }
    private function deleteFriend(){
        $this->model->friend_id=$this->data->id;
        if(!!$result=$this->model->deleteFriend()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="删除失败";
            echo json_encode($this->res);
        }
        
    }
    private function getAddUserFriendList(){
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->user_id=$user_result->user_id;
        if(!!$result=$this->model->getAddUserFriendList()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getIsUserFriendList(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->user_id=$user_result->user_id;
        if(!!$result=$this->model->getIsUserFriendList()){
            $this->res['status']=true;
            $whispers=new WhispersModel();
            foreach($result as $key=>$value){
               $whispers->from_user_id=$value->user_id;
               $whispers->to_user_id=$user_result->user_id;
               $value->no_read_whispers=$whispers->getNoReadWhispersByFromUserId();
            }
            $this->res['totalNum']=$this->model->queryIsUserFriendTotal();
            $this->res['pageIndex']=$this->data->pageIndex;
            $this->res['pageSize']=$this->data->pageSize;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getUserfriend(){
        $this->model->friend_id=$this->data->id;
        if(!!$result=$this->model->getUserfriend()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }

    private function addFriend(){
            $this->model->to_user_id=$this->data->to_user_id;
            $this->model->friend_desc=$this->data->desc;
            $user=new UserModel();
            $user->user_name=$_SESSION['user'];
            $user_result=$user->queryOneUserByName();
            $this->model->from_user_id=$user_result->user_id;
            if(!!$this->model->checkIsFriend()){
                $this->res['status']=false;
                $this->res['data']="您(他)已经发送过请求，等待同意";
                echo json_encode($this->res);
                exit();
            }
            if(!!$this->model->addFriend()){
                $this->res['status']=true;
                $this->res['data']="添加成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="添加失败";
                echo json_encode($this->res);
            }
    }

    private function checkIsMyFriend(){
            $this->model->to_user_id=$this->data->to_user_id;
            $user=new UserModel();
            $user->user_name=$_SESSION['user'];
            $user_result=$user->queryOneUserByName();
            $this->model->from_user_id=$user_result->user_id;
            if(!!$result=$this->model->checkIsMyFriend()){
                $this->res['status']=true;
                $this->res['data']=$result;
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="不是好友关系";
                echo json_encode($this->res);
            }
    }
    
}

$friend = new FriendAction();
$friend->action();