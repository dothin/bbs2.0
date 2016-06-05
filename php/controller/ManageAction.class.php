<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-06-05 10:12:30
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class ManageAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        parent::__construct(new ManageModel());
        
    }

    public function action(){
        $this->data=json_decode(file_get_contents("php://input"));
        if($this->data->action=='login'){
            $this->login();
        }elseif($this->data->action=='logout'){
            $this->logout();
        }else{ 
            Validate::checkAdminSession();
            Validate::checkPremission('1','没有管理管理员的权限');
            switch ($this->data->action) {
                case 'addManage':
                    $this->addManage();
                    break;
                case 'update':
                    $this->update();
                    break;
                case 'delete':
                    $this->delete();
                    break;
                case 'getAllList':
                    $this->getAllList();
                    break;
                case 'addLevel':
                    $this->addLevel();
                    break;
                case 'getAllLevelList':
                    $this->getAllLevelList();
                    break;
                case 'updateLevel':
                    $this->updateLevel();
                    break;
                case 'deleteLevel':
                    $this->deleteLevel();
                    break;
                case 'getAllpremissionList':
                    $this->getAllpremissionList();
                    break;
                default:
                    //$this->query();
                    break;
            }
        }
    }
     private function addLevel(){
        if (Validate::checkNull($this->data->name)) Tool::returnInfo('等级名不得为空');
        if (Validate::checkLength($this->data->name,2,'min')) Tool::returnInfo('等级名不得小于2位');
        if (Validate::checkLength($this->data->name,20,'max')) Tool::returnInfo('等级名不得大于16位');
        if (count($this->data->premission)<1) Tool::returnInfo('权限不得为空');
        $level=new ManageLevelModel();
        $level->m_level_name=$this->data->name;
        $level->m_level_desc=$this->data->desc;
        $level->premission=implode(",",$this->data->premission);
        if($level->queryOneLevelByName()){
            $this->res['status']=false;
            $this->res['data']="该等级已被添加";
            echo json_encode($this->res);
            exit();
        }
        if(!!$level->addLevel()){
            $this->res['status']=true;
            $this->res['data']="添加成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="添加失败";
            echo json_encode($this->res);
        }
    }
    private function updateLevel(){
        if (Validate::checkNull($this->data->name)) Tool::returnInfo('等级名不得为空');
        if (Validate::checkLength($this->data->name,2,'min')) Tool::returnInfo('等级名不得小于2位');
        if (Validate::checkLength($this->data->name,20,'max')) Tool::returnInfo('等级名不得大于16位');
        if (count($this->data->premission)<1) Tool::returnInfo('权限不得为空');
        $level=new ManageLevelModel();
        $level->m_level_id=$this->data->id;
        $level->m_level_name=$this->data->name;
        $level->m_level_desc=$this->data->desc;
        $level->premission=implode(",",$this->data->premission);
        $result=$level->queryOneLevelByIdOnly();
        if($this->data->name!=$result->m_level_name&&!!$level->queryOneLevelByName()){
            $this->res['status']=false;
            $this->res['data']="角色名已存在";
            echo json_encode($this->res);
            exit();
        }
        if(!!$level->updateLevel()){
            $this->res['status']=true;
            $this->res['data']="修改成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="修改失败";
            echo json_encode($this->res);
        }
    }
    private function deleteLevel(){
            $level=new ManageLevelModel();
            $level->m_level_id=$this->data->id;
            $manage=new ManageModel();
            $manage->manage_level=$this->data->id;
            if($manage->queryManageByManageLevel()){
                $this->res['status']=false;
                $this->res['data']="该等级下面有管理员，不能删除";
                echo json_encode($this->res);
                exit();
            }
            if(!!$level->deleteLevel()){
                $this->res['status']=true;
                $this->res['data']="删除成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="删除失败";
                echo json_encode($this->res);
            }
    }
    private function getAllLevelList(){
        $level=new ManageLevelModel();
        if(!!$result=$level->getAllLevelList()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="获取等级失败";
            echo json_encode($this->res);
        }
    }

    private function getAllpremissionList(){
        $premission=new PremissionModel();
        if(!!$result=$premission->getAllpremissionList()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="获取权限失败";
            echo json_encode($this->res);
        }
    }
    private function login(){ 
        if (Validate::checkNull($this->data->username)) Tool::returnInfo('用户名不得为空');
        if (Validate::checkLength($this->data->username,2,'min')) Tool::returnInfo('用户名不得小于2位');
        if (Validate::checkLength($this->data->username,32,'max')) Tool::returnInfo('用户名不得大于32位');
        if (Validate::checkLength($this->data->password,6,'min')) Tool::returnInfo('密码不得小于6位');
        if(Validate::checkLength($this->data->code,4,'equal')) Tool::returnInfo('验证码位数不对');
        if(Validate::checkEqual(strtolower($this->data->code),$_SESSION['code'])) Tool::returnInfo('验证码错误');
        $this->model->manage_name=$this->data->username;
        $this->model->manage_pass=sha1($this->data->password);
        if (!!$manage=$this->model->checkLogin()){
            $preArr=explode(',', $manage->premission);
            $_SESSION['premission']=$preArr;
            $this->model->manage_id=$manage->manage_id;
            $this->model->last_ip=$_SERVER["REMOTE_ADDR"];
            $this->model->setLoginTime();
            $_SESSION["manage"]=$this->data->username;
            $this->res['status']=true;
            $this->res['data']=$manage;
            $this->res['data']->premission=$_SESSION['premission'];
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="用户名或者密码错误";
            echo json_encode($this->res);
        }
    }
    private function logout(){
        Tool::clearAdminSession();
        $this->res['status']=true;
        $this->res['data']="退出成功";
        echo json_encode($this->res);
    }
    private function getAllList(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        if(!!$result=$this->model->queryManages()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryManageTotal();
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

    private function addManage(){
            if (Validate::checkNull($this->data->username)) Tool::returnInfo('用户名不得为空');
            if (Validate::checkLength($this->data->username,2,'min')) Tool::returnInfo('用户名不得小于2位');
            if (Validate::checkLength($this->data->username,32,'max')) Tool::returnInfo('用户名不得大于32位');
            if (Validate::checkLength($this->data->password,6,'min')) Tool::returnInfo('密码不得小于6位');
            $this->model->manage_name=$this->data->username;
            if(!!$this->model->queryOneManageByName()){
                $this->res['status']=false;
                $this->res['data']="用户已存在";
                echo json_encode($this->res);
                exit();
            };
            $this->model->manage_pass=sha1($this->data->password);
            $this->model->manage_level=$this->data->level;
            $this->model->manage_sex=$this->data->sex;
            //$this->model->level=$_POST['level'];
            if(!!$this->model->addManage()){
                $this->res['status']=true;
                $this->res['data']="添加成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="添加失败";
                echo json_encode($this->res);
            }
    }

    private function update(){
        if (Validate::checkNull($this->data->username)) Tool::returnInfo('用户名不得为空');
        if (Validate::checkLength($this->data->username,2,'min')) Tool::returnInfo('用户名不得小于2位');
        if (Validate::checkLength($this->data->username,32,'max')) Tool::returnInfo('用户名不得大于32位');
        if (Validate::checkLength($this->data->password,6,'min')) Tool::returnInfo('密码不得小于6位');
        $this->model->manage_id=$this->data->id;
        $this->model->manage_pass=sha1($this->data->password);
        $this->model->manage_level=$this->data->level;
        $this->model->manage_sex=$this->data->sex;
        $this->model->manage_name=$this->data->username;
        $result=$this->model->queryOneManageById();
        if($this->data->username!=$result->manage_name&&!!$this->model->queryOneManageByName()){
            $this->res['status']=false;
            $this->res['data']="用户已存在";
            echo json_encode($this->res);
            exit();
        }
        //$this->model->level=$_POST['level'];
        if(!!$this->model->updateManage()){
            $this->res['status']=true;
            $this->res['data']="添加成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="添加失败";
            echo json_encode($this->res);
        }
    }
    private function delete(){
            $this->model->manage_id=$this->data->id;
            if(!!$this->model->deleteManage()){
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

$manage = new ManageAction();
$manage->action();