<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 15:19:20
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class ModuleAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        parent::__construct(new ModuleModel());
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        if($this->data->action=='getAllListNoLimt'){
            $this->getAllListNoLimt();
        }elseif($this->data->action=='checkIsUserModule'){
            Validate::checkUserSession();
            $this->checkIsUserModule();
        }else{
            Validate::checkAdminSession();
            Validate::checkPremission('3','没有管理板块的权限');
            switch ($this->data->action) {
                case 'addModule':
                    $this->addModule();
                    break;
                case 'addUserModule':
                    $this->addUserModule();
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
                case 'getUserModule':
                    $this->getUserModule();
                    break;
                case 'deleteUserModule':
                    $this->deleteUserModule();
                    break;
                default:
                    //$this->query();
                    break;
            }
        }
            
    }
    private function checkIsUserModule(){
        $this->model->module_id=$this->data->module_id;
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->user_id=$user_result->user_id;
        if(!!$result=$this->model->checkIsUserModule()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getAllList(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        if(!!$result=$this->model->queryModules()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryModuleTotal();
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
    private function getUserModule(){
        $this->model->module_id=$this->data->id;
        if(!!$result=$this->model->getUserModule()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getAllListNoLimt(){
        if(!!$result=$this->model->queryModulesNoLimt()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }

    private function addmodule(){
            if (Validate::checkNull($this->data->name)) Tool::returnInfo('板块名不得为空');
            if (Validate::checkLength($this->data->name,16,'max')) Tool::returnInfo('板块名不得大于16位');
            if (Validate::checkLength($this->data->desc,64,'max')) Tool::returnInfo('板块描述不得大于64位');
            if (Validate::checkNull($this->data->url)) Tool::returnInfo('缩略图不得为空');
            $this->model->module_name=$this->data->name;
            if(!!$this->model->queryOneModuleByName()){
                $this->res['status']=false;
                $this->res['data']="模块已存在";
                echo json_encode($this->res);
                exit();
            };
            $manage=new ManageModel();
            $manage->manage_name=$_SESSION['manage'];
            $manage_result=$manage->queryOneManageByName();
            $this->model->manage_id=$manage_result->manage_id;
            $this->model->module_desc=$this->data->desc;
            $this->model->module_url=$this->data->url;
            if(!!$this->model->addModule()){
                $this->res['status']=true;
                $this->res['data']="添加成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="添加失败";
                echo json_encode($this->res);
            }
    }
    private function addUserModule(){
            $user=new UserModel();
            $user->user_name=$this->data->name;
            if(!!!$user->checkUser()){
                $this->res['status']=false;
                $this->res['data']="该用户名不存在";
                echo json_encode($this->res);
                exit();
            }
            $user=$user->checkUser();
            $this->model->user_id=$user->user_id;
            $this->model->module_id=$this->data->id;
            if($this->model->checkUserModule()&&$this->model->checkUserModule()->module_id==$this->data->id){
                $this->res['status']=false;
                $this->res['data']="该用户已经是版主";
                echo json_encode($this->res);
                exit();
            }
            if(!!$this->model->addUserModule()){
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
        if (Validate::checkNull($this->data->name)) Tool::returnInfo('板块名不得为空');
        if (Validate::checkLength($this->data->name,16,'max')) Tool::returnInfo('板块名不得大于16位');
        if (Validate::checkLength($this->data->desc,64,'max')) Tool::returnInfo('板块描述不得大于64位');
        if (Validate::checkNull($this->data->url)) Tool::returnInfo('缩略图不得为空');
        $this->model->module_id=$this->data->id;
        $this->model->module_name=$this->data->name;
        $this->model->module_desc=$this->data->desc;
        $this->model->module_url=$this->data->url;
        $result=$this->model->queryOneModuleById();
        if($this->data->name!=$result->module_name&&!!$this->model->queryOneModuleByName()){
            $this->res['status']=false;
            $this->res['data']="模块已存在";
            echo json_encode($this->res);
            exit();
        }
        if(!!$this->model->updateModule()){
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
            $this->model->module_id=$this->data->id;
            $post=new PostModel();
            $post->module_id=$this->data->id;
            if(!!$post->checkModuleHasPost()){
                $this->res['status']=false;
                $this->res['data']="删除失败,该板块下面有帖子";
                echo json_encode($this->res);
                exit();
            }
            if(!!$this->model->deleteModule()){
                $this->res['status']=true;
                $this->res['data']="删除成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="删除失败";
                echo json_encode($this->res);
            }
    }
    private function deleteUserModule(){
            $this->model->user_module_id=$this->data->id;
            if(!!$this->model->deleteUserModule()){
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

$module = new ModuleAction();
$module->action();