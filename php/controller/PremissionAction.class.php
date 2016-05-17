<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-24 13:02:13
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-02-01 15:33:39
 */
class PremissionAction extends Action{

    //构造方法，初始化
    public function __construct($tpl){
        
        parent::__construct($tpl,new PremissionModel());
        
    }


    public function action(){
        switch (@$_GET['action']) {
            case 'show':
                $this->query();
                break;
            case 'add':
                $this->add();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                $this->query();
                break;
        }
    }
    private function query(){
        parent::page($this->model->queryPremissionTotal());
        $this->tpl->assign('show',true);
        $this->tpl->assign('title','权限列表');
        $this->tpl->assign('AllPremission',$this->model->queryLimitPremissions());
    }
    private function add(){
        if (isset($_POST['send'])) {
            if (Validate::checkNull($_POST['name'])) Tool::alertBack('权限名不得为空');
            if (Validate::checkLength($_POST['name'],2,'min')) Tool::alertBack('权限名不得小于2位');
            if (Validate::checkLength($_POST['name'],100,'max')) Tool::alertBack('权限名不得大于100位');
            if (Validate::checkLength($_POST['info'],200,'max')) Tool::alertBack('权限描述不得大于200位');
            $this->model->name=$_POST['name'];
            if($this->model->queryOnePremission()) Tool::alertBack('该权限已存在');
            $this->model->info=$_POST['info'];
            $this->model->addPremission()?Tool::alertLocation('恭喜您新增成功！','?action=show'):Tool::alertBack('很遗憾，新增失败');
        }
        $this->tpl->assign('add',true);
        $this->tpl->assign('prev_url',PREV_URL);
        $this->tpl->assign('title','新增权限');
    }     

    private function update(){
        if (isset($_POST['send'])) {
            if (Validate::checkNull($_POST['name'])) Tool::alertBack('权限名不得为空');
            if (Validate::checkLength($_POST['name'],2,'min')) Tool::alertBack('权限名不得小于2位');
            if (Validate::checkLength($_POST['name'],100,'max')) Tool::alertBack('权限名不得大于100位');
            if (Validate::checkLength($_POST['info'],200,'max')) Tool::alertBack('权限描述不得大于200位');
            $this->model->id=$_POST['id'];
            $this->model->name=$_POST['name'];
            $this->model->info=$_POST['info'];
            $this->model->updatePremission()?Tool::alertLocation('恭喜您修改成功！',$_POST['prev_url']):Tool::alertBack('很遗憾，修改失败');
        }
        if (isset($_GET['id'])) {
            $this->model->id=$_GET['id'];
            $premission=$this->model->queryOnePremission();
            is_object($premission)?true:Tool::alertBack('传值的权限ID有误');
            $this->tpl->assign('name',$premission->name);
            $this->tpl->assign('info',$premission->info);
            $this->tpl->assign('id',$premission->id);
            $this->tpl->assign('prev_url',PREV_URL);
            $this->tpl->assign('update',true);
            $this->tpl->assign('title','修改权限');
        }else{
            Tool::alertBack('非法操作');
        }
    }
    
    private function delete(){
        if (isset($_GET['id'])) {
            $this->model->id=$_GET['id'];
            $manage=new ManageModel();
            $manage->Premission=$this->model->id;
            if($manage->queryOneManage()) Tool::alertBack('该权限有管理员使用，无法删除，请先删除该权限下所有管理员，方可删除该权限');
            $this->model->deletePremission()?Tool::alertLocation('恭喜您新删除成功！',PREV_URL):Tool::alertBack('很遗憾，删除失败');
        }else{
            Tool::alertBack('非法操作');
        }
    }

}