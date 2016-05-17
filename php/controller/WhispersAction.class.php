<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 16:04:20
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class WhispersAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        parent::__construct(new WhispersModel());
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        Validate::checkUserSession();
        switch ($this->data->action) {
            case 'addWhispers':
                $this->addWhispers();
                break;
            case 'getWhispersByUserId':
                $this->getWhispersByUserId();
                break;
            case 'deleteWhispers':
                $this->deleteWhispers();
                break;
            default:
                //$this->query();
                break;
        }
            
    }
    private function getWhispersByUserId(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->to_user_id=$user_result->user_id;
        $this->model->from_user_id=$this->data->user_id;
        if(!!$result=$this->model->getWhispersByUserId()){
            $this->res['status']=true;
            if($this->data->noSetStatus=='no'){
                foreach($result as $key=>$value){
                   $this->model->whispers_id=$value->whispers_id;
                   $this->model->updateStatusToReadByWhispersId();
                }
            }
            $this->res['totalNum']=$this->model->querywhispersTotalByUserId();
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
    private function addWhispers(){
            $this->model->to_user_id=$this->data->to_user_id;
            $this->model->whispers_content=$this->data->content;
            $user=new UserModel();
            $user->user_name=$_SESSION['user'];
            $user_result=$user->queryOneUserByName();
            $this->model->from_user_id=$user_result->user_id;
            if(!!$this->model->addWhispers()){
                $integral=new IntegralModel();
                $integral->rule_action="私信";
                $integral_result=$integral->queryRuleByRuleAction();
                if($integral_result->rule_status==1){
                    $integral->get_integral=$integral_result->get_integral;
                    $integral->rule_action="私信";
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
    private function deleteWhispers(){
            $this->model->whispers_id=$this->data->whispers_id;
            if(!!$this->model->deleteWhispers()){
                $this->res['status']=true;
                $this->res['data']="删除成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="删除失败";
                echo json_encode($this->res);
            }
    }
    
}

$whispers = new WhispersAction();
$whispers->action();