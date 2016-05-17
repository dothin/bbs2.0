<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-25 11:50:04
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 23:26:26
 */
require substr(dirname(__FILE__),0,-7).'/init.inc.php';
if (isset($_GET['type'])) {
    //ckeditor没做表单限制，第二个参数为null即可
    $fileUpload=new PostFileUpload('upload',null);
    $ckefn=$_GET['CKEditorFuncNum'];
    $path=$fileUpload->getPath();
    /*$img=new Image($path);
    $img->ckImage(650,0);
    $img->out();*/
    echo "<script>window.parent.CKEDITOR.tools.callFunction($ckefn,\"$path\",'图片上传成功');</script>";
    exit();
}else{
    Tool::alertBack('上传失败，请稍后再试');
}