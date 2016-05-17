<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 16:02:42
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class MessageAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        parent::__construct(new MessageModel());
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        Validate::checkUserSession();
        switch ($this->data->action) {
            case 'addMessage':
                $this->addMessage();
                break;
            case 'deleteMessage':
                $this->deleteMessage();
                break;
            case 'getAllMessageByUserId':
                $this->getAllMessageByUserId();
                break;
            default:
                //$this->query();
                break;
        }
            
    }
    private function deleteMessage(){
        $this->model->message_id=$this->data->id;
        if(!!$result=$this->model->deleteMessage()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="删除失败";
            echo json_encode($this->res);
        }
        
    }
    private function getAllMessageByUserId(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->to_user_id=$user_result->user_id;
        if(!!$result=$this->model->getAllMessageByUserId()){
            foreach($result as $key=>$value){
                $friend=new FriendModel();
                $friend->from_user_id=$user_result->user_id;
                $friend->to_user_id=$value->user_id;
                if(!!$friend->checkIsMyFriend()){
                    $value->isMyFriend=true;
                    $value->isNotMyFriend=false;
                }else{
                    $value->isMyFriend=false;
                    $value->isNotMyFriend=true;
                }
                $this->model->message_id=$value->message_id;
                $this->model->updateMessageStatus();
            }
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryMessageTotalByUserId();
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

    private function addMessage(){
            $this->model->to_user_id=$this->data->to_user_id;
            $this->model->message_content=$this->data->content;
            $user=new UserModel();
            $user->user_name=$_SESSION['user'];
            $user_result=$user->queryOneUserByName();
            $this->model->from_user_id=$user_result->user_id;
            if(!!$this->model->addMessage()){
                $integral=new IntegralModel();
                $integral->rule_action="留言";
                $integral_result=$integral->queryRuleByRuleAction();
                if($integral_result->rule_status==1){
                    $integral->get_integral=$integral_result->get_integral;
                    $integral->rule_action="留言";
                    $integral->user_id=$user_result->user_id;
                    $integral->addIntegral();
                }
                $this->res['status']=true;
                $this->res['data']="添加成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="添加失败";
                echo json_encode($this->res);
            }
    }

    
}

$message = new MessageAction();
$message->action();