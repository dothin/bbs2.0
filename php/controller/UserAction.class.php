<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-16 17:18:04
 */

require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class UserAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        
        parent::__construct(new UserModel());
        
    }

    public function action(){
        $this->data=json_decode(file_get_contents("php://input"));
        switch ($this->data->action) {
            case 'login':
                $this->login();
                break;
            case 'logout':
                $this->logout();
                break;
            case 'reg':
                $this->reg();
                break;
            case 'getUserInfo':
                Validate::checkUserSession();
                $this->getUserInfo();
                break;
            case 'userUpdateUser':
                Validate::checkUserSession();
                $this->userUpdateUser();
                break;
            case 'addUser':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->addUser();
                break;
            case 'updateUser':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->updateUser();
                break;
            case 'disableUser':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->disableUser();
                break;
            case 'getAllUserList':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->getAllUserList();
                break;
            case 'getAllInfo':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->getAllInfo();
                break;
            case 'addRole':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->addRole();
                break;
            case 'getAllRoleList':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->getAllRoleList();
                break;
            case 'updateRole':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->updateRole();
                break;
            case 'deleteRole':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->deleteRole();
                break;
            case 'addLevel':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->addLevel();
                break;
            case 'getAllLevelList':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->getAllLevelList();
                break;
            case 'updateLevel':
                Validate::checkAdminSession();
                $this->updateLevel();
                break;
            case 'deleteLevel':
                Validate::checkAdminSession();
                Validate::checkPremission('2','没有管理用户的权限');
                $this->deleteLevel();
                break;
            default:
                break;
        }
        
    }
    private function login(){
        if(CANLOGIN==1){
            if(isset($this->data->code)){
                if(Validate::checkLength($this->data->code,4,'equal')) Tool::returnInfo('验证码位数不对');
                if(Validate::checkEqual(strtolower($this->data->code),$_SESSION['code'])) Tool::returnInfo('验证码错误');
            }
            
            $this->model->user_name=$this->data->username;
            $this->model->user_pass=sha1($this->data->password);
            
            if (!!$user=$this->model->checkLogin()){
                $result=$this->model->queryUserActiveByName();
                if($result->user_active==1){
                    $this->res['status']=false;
                    $this->res['data']="该用户被禁用了，暂时不能登录";
                    echo json_encode($this->res);
                    exit();
                }
                $user_last_login_day=substr($user->user_last_login_time,0,10);
                $this->model->user_id=$user->user_id;
                $this->model->last_ip=$_SERVER["REMOTE_ADDR"];
                $integral=new IntegralModel();
                $integral->rule_action="登录";
                $integral_result=$integral->queryRuleByRuleAction();
                if($integral_result->rule_status==1&&date("Y-m-d",time())!=$user_last_login_day){
                    $integral->get_integral=$integral_result->get_integral;
                    $integral->rule_action="登录";
                    $integral->user_id=$user->user_id;
                    $integral->addIntegral();
                }
                $this->model->setLaterUser();
                $_SESSION["user"]=$this->data->username;
                $this->res['status']=true;
                $this->res['data']=$user;
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="用户名或者密码错误";
                if($fail_result=$this->model->checkUserInFailList()){
                    $this->model->updateFailList();
                    $this->res['fail_count']=$fail_result->fail_count+1;
                }else{
                    $this->model->reason="用户名或者密码错误";
                    $this->model->addFailToFailList();
                    $this->res['fail_count']=1;
                }
                $this->res['conf_fail_count']=FAIL_COUNT;
                echo json_encode($this->res);
            }
        }else{
            $this->res['status']=false;
            $this->res['data']="服务器维护中，暂时不能登录";
            echo json_encode($this->res);
        }
    }
    private function logout(){
        Tool::clearUserSession();
        $this->res['status']=true;
        $this->res['data']="退出成功";
        echo json_encode($this->res);
    }
    private function getUserInfo(){
        $user=new UserModel();
        $this->model->user_name=$_SESSION["user"];
        $user->user_name=$_SESSION["user"];
        $user_result=$user->queryOneUserByName();
        $integral=new IntegralModel();
        $integral->user_id=$user_result->user_id;
        if(!!$result=$this->model->getUserInfo()){
            $this->res['status']=true;
            $this->res['data']=$result;
            $this->res['data']->user_integral=$integral->getIntegralTotal();
            $level=new LevelModel();
            $level_result=$level->getAllLevelList();
            foreach($level_result as $key=>$value){
                $level_arr=explode(',',$value->user_level_piece);
                if($this->res['data']->user_integral>=$level_arr[0]&&$this->res['data']->user_integral<$level_arr[1]){
                    $this->res['data']->user_level_name=$value->user_level_name;
                }
            }
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
    }

    private function reg(){
            if(CANREG==1){
                if (Validate::checkNull($this->data->username)) Tool::returnInfo('用户名不得为空');
                if (Validate::checkLength($this->data->username,2,'min')) Tool::returnInfo('用户名不得小于2位');
                if (Validate::checkLength($this->data->username,20,'max')) Tool::returnInfo('用户名不得大于20位');
                if (Validate::checkLength($this->data->password,6,'min')) Tool::returnInfo('密码不得小于6位');
                if (Validate::checkLength($this->data->password,20,'max')) Tool::returnInfo('密码不得大于20位');
                if (Validate::checkPass($this->data->password)) Tool::returnInfo('密码必须为数字，大写字母，小写字母，特殊字符中的至少两种');
                if(Validate::checkEqual(strtolower($this->data->password),$this->data->repassword)) Tool::returnInfo('密码和确认密码不一致');
                if(Validate::checkLength($this->data->code,4,'equal')) Tool::returnInfo('验证码位数不对');
                if(Validate::checkEqual(strtolower($this->data->code),$_SESSION['code'])) Tool::returnInfo('验证码错误');
                $this->model->user_name=$this->data->username;
                $this->model->user_pass=sha1($this->data->password);
                if($this->model->checkUser()){
                    $this->res['status']=false;
                    $this->res['data']="该用户名已被注册";
                    echo json_encode($this->res);
                    exit();
                }
                $this->model->user_face=USERFACE;
                if ($this->model->addUser()) {
                    $user=$this->model->checkLogin();
                    $_SESSION["user"]=$this->data->username;
                    $integral=new IntegralModel();
                    $integral->rule_action="登录";
                    $integral_result=$integral->queryRuleByRuleAction();
                    if($integral_result->rule_status==1){
                        $integral->get_integral=$integral_result->get_integral;
                        $integral->rule_action="登录";
                        $integral->user_id=$user->user_id;
                        $integral->addIntegral();
                    }
                    $this->model->user_id=$user->user_id;
                    $this->model->last_ip=$_SERVER["REMOTE_ADDR"];
                    $this->model->setLaterUser();
                    $this->res['status']=true;
                    $this->res['data']=$user;
                    echo json_encode($this->res);
                }else{
                    $this->res['status']=false;
                    $this->res['data']="注册失败";
                    echo json_encode($this->res);
                }
            }else{
                $this->res['status']=false;
                $this->res['data']="管理员关闭了注册功能";
                echo json_encode($this->res);
            }
    }
    private function getAllUserList(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        if(!!$result=$this->model->getAllUserList()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryUserTotal();
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
    private function getAllRoleList(){
        $role=new RoleModel();
        if(!!$result=$role->getAllRoleList()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getAllLevelList(){
        $level=new LevelModel();
        if(!!$result=$level->getAllLevelList()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getAllInfo(){
        $this->model->user_id=$this->data->id;
        $integral=new IntegralModel();
        $integral->user_id=$this->data->id;
        if(!!$result=$this->model->queryOneUserById()){
            $this->res['status']=true;
            $this->res['data']=$result;
            $this->res['data']->user_integral=$integral->getIntegralTotal();
            $level=new LevelModel();
            $level_result=$level->getAllLevelList();
            foreach($level_result as $key=>$value){
                $level_arr=explode(',',$value->user_level_piece);
                if($this->res['data']->user_integral>=$level_arr[0]&&$this->res['data']->user_integral<$level_arr[1]){
                    $this->res['data']->user_level_name=$value->user_level_name;
                }
            }
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询信息失败";
            echo json_encode($this->res);
        }
        
    }

    private function addUser(){
        if (Validate::checkNull($this->data->username)) Tool::returnInfo('用户名不得为空');
        if (Validate::checkLength($this->data->username,2,'min')) Tool::returnInfo('用户名不得小于2位');
        if (Validate::checkLength($this->data->username,20,'max')) Tool::returnInfo('用户名不得大于20位');
        if (Validate::checkLength($this->data->password,6,'min')) Tool::returnInfo('密码不得小于6位');
        if (Validate::checkLength($this->data->password,20,'max')) Tool::returnInfo('密码不得大于20位');
        if (Validate::checkPass($this->data->password)) Tool::returnInfo('密码必须为数字，大写字母，小写字母，特殊字符中的至少两种');
        if(Validate::checkEqual(strtolower($this->data->password),$this->data->repassword)) Tool::returnInfo('密码和确认密码不一致');
        $this->model->user_name=$this->data->username;
        $this->model->user_pass=sha1($this->data->password);
        if($this->model->checkUser()){
            $this->res['status']=false;
            $this->res['data']="该用户名已被注册";
            echo json_encode($this->res);
            exit();
        }
        if(!!$this->model->addUser()){
            $this->res['status']=true;
            $this->res['data']="添加成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="添加失败";
            echo json_encode($this->res);
        }
    }
    private function addRole(){
        $role=new RoleModel();
        $role->role_name=$this->data->name;
        $role->role_desc=$this->data->desc;
        if($role->queryOneRoleByName()){
            $this->res['status']=false;
            $this->res['data']="该角色已被添加";
            echo json_encode($this->res);
            exit();
        }
        if(!!$role->addRole()){
            $this->res['status']=true;
            $this->res['data']="添加成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="添加失败";
            echo json_encode($this->res);
        }
    }
    private function addLevel(){
        if (Validate::checkNull($this->data->name)) Tool::returnInfo('等级名不得为空');
        if (Validate::checkLength($this->data->name,2,'min')) Tool::returnInfo('等级名不得小于2位');
        if (Validate::checkLength($this->data->name,16,'max')) Tool::returnInfo('等级名不得大于16位');
        if (Validate::checkLength($this->data->desc,64,'max')) Tool::returnInfo('等级描述不得大于64位');
        if (Validate::checkLength($this->data->piece,32,'max')) Tool::returnInfo('积分段不得大于32位');
        $level=new LevelModel();
        $level->user_level_name=$this->data->name;
        $level->user_level_desc=$this->data->desc;
        $level->user_level_piece=$this->data->piece;
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

    private function updateUser(){
        if (Validate::checkNull($this->data->name)) Tool::returnInfo('用户名不得为空');
        if (Validate::checkLength($this->data->name,2,'min')) Tool::returnInfo('用户名不得小于2位');
        if (Validate::checkLength($this->data->name,20,'max')) Tool::returnInfo('用户名不得大于20位');
        if (Validate::checkLength($this->data->password,6,'min')) Tool::returnInfo('密码不得小于6位');
        if (Validate::checkLength($this->data->password,20,'max')) Tool::returnInfo('密码不得大于20位');
        if (Validate::checkPass($this->data->password)) Tool::returnInfo('密码必须为数字，大写字母，小写字母，特殊字符中的至少两种');
        if(Validate::checkEqual(strtolower($this->data->password),$this->data->repassword)) Tool::returnInfo('密码和确认密码不一致');
        $this->model->user_id=$this->data->id;
        $this->model->user_name=$this->data->name;
        $this->model->user_active=$this->data->active;
        $this->model->user_pass=sha1($this->data->password);
        $result=$this->model->queryOneUserByIdOnly();
        if($this->data->name!=$result->user_name&&!!$this->model->queryOneUserByName()){
            $this->res['status']=false;
            $this->res['data']="用户名已存在";
            echo json_encode($this->res);
            exit();
        }
        if(!!$this->model->updateUser()){
            $this->res['status']=true;
            $this->res['data']="修改成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="修改失败";
            echo json_encode($this->res);
        }
    }

    private function userUpdateUser(){
        if (Validate::checkNull($this->data->name)) Tool::returnInfo('用户名不得为空');
        if (Validate::checkLength($this->data->name,2,'min')) Tool::returnInfo('用户名不得小于2位');
        if (Validate::checkLength($this->data->name,20,'max')) Tool::returnInfo('用户名不得大于20位');
        if (Validate::checkLength($this->data->password,6,'min')) Tool::returnInfo('密码不得小于6位');
        if (Validate::checkLength($this->data->password,20,'max')) Tool::returnInfo('密码不得大于20位');

        if (Validate::checkPass($this->data->password)) Tool::returnInfo('密码必须为数字，大写字母，小写字母，特殊字符中的至少两种');
        if(Validate::checkEqual(strtolower($this->data->password),$this->data->repassword)) Tool::returnInfo('密码和确认密码不一致');
        $this->model->user_id=$this->data->id;
        $this->model->user_name=$this->data->name;
        $this->model->user_sign_active=$this->data->user_sign_active;
        $this->model->user_signatrue=$this->data->user_signatrue;
        $this->model->user_sex=$this->data->sex;
        $this->model->user_face=$this->data->face;
        $this->model->user_pass=sha1($this->data->password);
        $result=$this->model->queryOneUserByIdOnly();
        if($this->data->name!=$result->user_name&&!!$this->model->queryOneUserByName()){
            $this->res['status']=false;
            $this->res['data']="用户名已存在";
            echo json_encode($this->res);
            exit();
        }
        if(!!$this->model->userUpdateUser()){
            $this->res['status']=true;
            $this->res['data']="修改成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="修改失败";
            echo json_encode($this->res);
        }
    }
    private function updateRole(){
        $role=new RoleModel();
        $role->role_id=$this->data->id;
        $role->role_name=$this->data->name;
        $role->role_desc=$this->data->desc;
        $result=$role->queryOneRoleByIdOnly();
        if($this->data->name!=$result->role_name&&!!$role->queryOneRoleByName()){
            $this->res['status']=false;
            $this->res['data']="角色名已存在";
            echo json_encode($this->res);
            exit();
        }
        if(!!$role->updateRole()){
            $this->res['status']=true;
            $this->res['data']="修改成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="修改失败";
            echo json_encode($this->res);
        }
    }
    private function updateLevel(){
        if (Validate::checkNull($this->data->name)) Tool::returnInfo('等级名不得为空');
        if (Validate::checkLength($this->data->name,2,'min')) Tool::returnInfo('等级名不得小于2位');
        if (Validate::checkLength($this->data->name,16,'max')) Tool::returnInfo('等级名不得大于16位');
        if (Validate::checkLength($this->data->desc,64,'max')) Tool::returnInfo('等级描述不得大于64位');
        if (Validate::checkLength($this->data->piece,32,'max')) Tool::returnInfo('积分段不得大于32位');
        $level=new LevelModel();
        $level->user_level_id=$this->data->id;
        $level->user_level_name=$this->data->name;
        $level->user_level_desc=$this->data->desc;
        $level->user_level_piece=$this->data->piece;
        $result=$level->queryOneLevelByIdOnly();
        if($this->data->name!=$result->user_level_name&&!!$level->queryOneLevelByName()){
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
    private function disableUser(){
            $this->model->user_id=$this->data->id;
            if(!!$this->model->disableUser()){
                $this->res['status']=true;
                $this->res['data']="禁用成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="禁用失败";
                echo json_encode($this->res);
            }
    }
    private function deleteRole(){
            $role=new RoleModel();
            $role->role_id=$this->data->id;
            if(!!$role->deleteRole()){
                $this->res['status']=true;
                $this->res['data']="删除成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="删除失败";
                echo json_encode($this->res);
            }
    }
    private function deleteLevel(){
            $level=new LevelModel();
            $level->user_level_id=$this->data->id;
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

}

$user = new UserAction();
$user->action();