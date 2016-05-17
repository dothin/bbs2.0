<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-24 20:54:49
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 11:27:30
 */
require substr(dirname(__FILE__),0,-7).'/init.inc.php';
//防止恶意调用，所以设置上传过来有值才调用
if (isset($_POST['send'])) {
    $fileUpload=new ModuleFileUpload('pic',$_POST['MAX_FILE_SIZE']);
    $path=$fileUpload->getPath();
    $img=new Image($path);
    $img->thumb(128,128);
    $img->out();
    Tool::alertOpenerModuleClose('缩略图上传成功',$path);
}else{
    Tool::alertBack('上传文件太大');
} 

