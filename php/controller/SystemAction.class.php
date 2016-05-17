<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 14:27:47
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class SystemAction extends Action{
    private $data;
    private $changeStr;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        parent::__construct(new SystemModel());
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        if($this->data->action=='getAllsystemListByUserId'){
            Validate::checkUserSession();
            $this->getAllsystemListByUserId();
        }else{
            Validate::checkAdminSession();
            Validate::checkPremission('6','没有管理系统配置的权限');
            switch ($this->data->action) {
                case 'updateConf':
                    $this->updateConf();
                    break;
                case 'getSystemLog':
                    $this->getSystemLog();
                    break;
                case 'getSystemConf':
                    $this->getSystemConf();
                    break;
                default:
                    //$this->query();
                    break;
            }
        }   
    }
    private function getSystemLog(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        if(!!$result=$this->model->getSystemLog()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->querySystemLogTotal();
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
    private function getSystemConf(){
        if(!!$result=$this->model->getSystemConf()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }

    private function updateConf(){
        $this->model->bbs_login=$this->data->bbs_login;
        $this->model->bbs_register=$this->data->bbs_register;
        $this->model->bbs_login_fail_count=$this->data->bbs_login_fail_count;
        $system=$this->model->getSystemConf();
        if($this->model->updateConf()){
            $br="\r\n";
            $tab="\t";
            $profile='<?php'.$br;
            $profile.=$tab."//系统配置文件".$br;
            $profile.=$tab."define('FAIL_COUNT', '{$this->model->bbs_login_fail_count}');".$br;
            $profile.=$tab."define('CANREG', {$this->model->bbs_register});".$br;
            $profile.=$tab."define('CANLOGIN', {$this->model->bbs_login});".$br;

            $profile.=$br;
            $profile.=$tab."//不可修改的配置".$br;
            $profile.=$br;
            $profile.=$tab."define('USERFACE', '/bbs2.0/php/source/user/all_user.jpg');".$br;
            $profile.=$tab."define('UPDIR', '/source/');".$br;
            $profile.=$tab."//数据库配置文件".$br;
            $profile.=$tab."define('DB_HOST', 'localhost'); ".$br;
            $profile.=$tab."define('DB_USER', 'root');  ".$br;
            $profile.=$tab."define('DB_PASS', ''); ".$br;
            $profile.=$tab."define('DB_NAME', 'bbs2'); ".$br;
            $profile.=$tab."define('DB_PORT', 3306); ".$br;
            $profile.=$br;
            $profile.=$tab."define('MARK', ROOT_PATH.'/images/yc.png'); ".$br;

            $profile.='?>'.$br;
            if(!file_put_contents('../config/profile.inc.php', $profile)){
                Tool::returnInfo('生成配置文件失败');
            }else{
                $this->res['status']=true;
                $this->res['data']="设置成功";
                if($system->bbs_login!=$this->data->bbs_login){
                    $this->changeStr.="登录状态:".$system->bbs_login."->".$this->data->bbs_login." ";
                }
                if($system->bbs_register!=$this->data->bbs_register){
                    $this->changeStr.="注册状态:".$system->bbs_register."->".$this->data->bbs_register." ";
                }
                if($system->bbs_login_fail_count!=$this->data->bbs_login_fail_count){
                    $this->changeStr.="失败登录次数:".$system->bbs_login_fail_count."->".$this->data->bbs_login_fail_count;
                }
                $manage=new ManageModel();
                $manage->manage_name=$_SESSION['manage'];
                $manage_result=$manage->queryOneManageByName();
                $this->model->manage_id=$manage_result->manage_id;
                $this->model->config_id=$system->config_id;
                $this->model->modify_content=$this->changeStr;
                $this->model->addChangeToLog();
                echo json_encode($this->res);
            }
        }else{
            $this->res['status']=false;
            $this->res['data']="设置失败";
            echo json_encode($this->res);
        }
    }
}
$system = new SystemAction();
$system->action();