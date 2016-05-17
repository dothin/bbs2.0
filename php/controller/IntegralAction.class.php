<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 15:59:43
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class IntegralAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        parent::__construct(new IntegralModel());
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        if($this->data->action=='getAllIntegralListByUserId'){
            Validate::checkUserSession();
            $this->getAllIntegralListByUserId();
        }else{
            Validate::checkAdminSession();
            Validate::checkPremission('5','没有管理积分的权限');
            switch ($this->data->action) {
                case 'updateIntegral':
                    $this->updateIntegral();
                    break;
                case 'getIntegralRuleList':
                    $this->getIntegralRuleList();
                    break;
                case 'getIntegralLogs':
                    $this->getIntegralLogs();
                    break;
                case 'getOneUserLogsByUserName':
                    $this->getOneUserLogsByUserName();
                    break;
                default:
                    //$this->query();
                    break;
            }
        }
        
            
    }
    private function getOneUserLogsByUserName(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        $user=new UserModel();
        $user->user_name=$this->data->name;
        $use_result=$user->queryOneUserByName();
        $this->model->user_id=$use_result->user_id;
        if(!!$result=$this->model->getAllIntegralListByUserIdNeedName()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryIntegralTotalByUserId();
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
    private function getIntegralLogs(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        if(!!$result=$this->model->getIntegralLogs()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryIntegralLogsTotal();
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
    private function getAllIntegralListByUserId(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $use_result=$user->queryOneUserByName();
        $this->model->user_id=$use_result->user_id;
        if(!!$result=$this->model->getAllIntegralListByUserId()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryIntegralTotalByUserId();
            $this->res['totalIntegral']=$this->model->getIntegralTotal();
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
    private function getIntegralRuleList(){
        if(!!$result=$this->model->getIntegralRuleList()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }

    private function updateIntegral(){
            foreach($this->data->ruleList as $key=>$value){
               $this->model->rule_id=$value->rule_id;
               $this->model->get_integral=$value->get_integral;
               $this->model->rule_status=$value->rule_status;
               $manage=new ManageModel();
               $manage->manage_name=$_SESSION['manage'];
               $manage_result=$manage->queryOneManageByName();
               $this->model->modify_manage_id=$manage_result->manage_id;
               $this->model->updateIntegral();
            }
            $this->res['status']=true;
            $this->res['data']="设置成功";
            echo json_encode($this->res);
            
    }
    
}

$integral = new IntegralAction();
$integral->action();