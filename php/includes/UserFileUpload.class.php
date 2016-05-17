<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-24 20:56:50
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-12 00:29:46
 */
//上传文件类
class UserFileUpload{
    //错误代码
    private $error;
    //表单设置的上传约束最大值
    private $maxSize;
    //类型
    private $type;
    //类型合集
    private $typeArr=array('image/jpeg','image/pjpeg','image/png','image/x-png','image/gif');
    //目录文件
    private $path;
    //当天时间
    private $linkToday;
    //当天时间组成的子目录路径
    private $today;
    //文件名
    private $name;
    //临时文件
    private $tmp;
    //链接路径
    private $linkPath;
    //构造方法，初始化
    public function __construct($file,$maxSize){
        $this->error=$_FILES[$file]['error'];
        $this->maxSize=$maxSize/1024;
        $this->path=ROOT_PATH.UPDIR;
        //$this->linkToday=date('Ymd').'/';
        //改用用户ID来存照片
        $user = new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->linkToday='userId'.$user_result->user_id.'/';
        $this->today=$this->path.$this->linkToday;
        $this->name=$_FILES[$file]['name'];
        $this->type=$_FILES[$file]['type'];
        $this->tmp=$_FILES[$file]['tmp_name'];
        $this->checkPath();
        $this->checkError();
        $this->checkType();
        $this->moveUpload();
    }
    //返回路径
    public function getPath(){
        $path=$_SERVER["SCRIPT_NAME"];
        $dir=dirname(dirname($path));
        if ($dir == '\\') $dir='/';
        $this->linkPath=$dir.$this->linkPath;
        return $this->linkPath;
    }
    //移动文件
    private function moveUpload(){
        if (is_uploaded_file($this->tmp)) {
            if (!move_uploaded_file($this->tmp, $this->setNewName())) {
                Tool::alertBack('上传失败');
            }
        }else{
            Tool::alertBack('上传的临时文件不存在，可能是上传文件过大造成的');
        }
    }
    //设置新文件名
    private function setNewName(){
        $nameArr=explode('.', $this->name);
        //取得后缀名
        $postFix=$nameArr[count($nameArr)-1];
        $newName=date('YmdHis').mt_rand(100,1000).'.'.$postFix;
        $this->linkPath=UPDIR.$this->linkToday.$newName;
        return $this->today.$newName;
    }
    //验证目录
    private function checkPath(){
        if (!is_dir($this->path) || !is_writeable($this->path)) {
            if (!mkdir($this->path, 0777)) {
                Tool::alertBack('上传主目录创建失败');
                //chmod($this->path, 0777);
            }
        }
        if (!is_dir($this->today) || !is_writeable($this->today)) {
            if (!mkdir($this->today, 0777)) {
                Tool::alertBack('上传子目录创建失败');
            }
        }
    }

    //验证类型
    private function checkType(){
        //如果不加!empty($this->type)的话，ckeditor上传过大也报类型错误
        if (!in_array($this->type, $this->typeArr)&&!empty($this->type)) {
            Tool::alertBack('上传类型错误');
        }
    }
    //验证错误
    private function checkError(){
        if (!empty($this->error)) {
            switch ($this->error) {
                case '1':
                    Tool::alertBack('上传值超过了约定最大值');
                    break;
                case '2':
                    Tool::alertBack('上传值超过了'.$this->maxSize.'kb');
                    break;
                case '3':
                    Tool::alertBack('只有部分文件被上传');
                    break;
                case '4':
                    Tool::alertBack('没有任何文件被上传');
                    break;
                default:
                    Tool::alertBack('未知错误');
                    break;
            }
        }
    }
}