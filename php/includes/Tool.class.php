<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 13:17:52
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 23:49:41
 */
class Tool {

    //返回信息
    static public function returnInfo($info) {
        $res=array();
        $res['status']=false;
        $res['data']=$info;
        echo json_encode($res);
        exit();
    }
    //弹窗关闭
    static public function alertClose($info){
        echo "<script type='text/javascript'>alert('$info');close();</script>";
        exit();
    }
    static public function alertBack($info) {
        echo "<script type='text/javascript'>alert('$info');history.back();</script>";
        exit();
    }
    //清理user session
    static public function clearUserSession(){
        if (isset($_SESSION['user'])) {
            // session_destroy();
            unset($_SESSION['user']);
        }
    }
    //清理admin session
    static public function clearAdminSession(){
        if (isset($_SESSION['admin'])) {
            // session_destroy();
            unset($_SESSION['admin']);
        }
    }

    

    //显示html过滤处理
    static public function htmlString($data){
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                //递归
                $string[$key]=Tool::htmlString($value);
            }
        }elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                @$string->$key=Tool::htmlString($value);
            }
        }else{
            $string=htmlspecialchars($data);
        }
        return @$string;
    }
    //数据库输出过滤
    static public function mysqlString($data){
        return mysqli_real_escape_string(DB::getDB(),$data);
        //return addslashes($data);
    }

    //弹窗赋值（上传头像专用）
    static public function alertOpenerClose($info,$path){
        echo "<script type='text/javascript'>alert('$info');</script>";
        echo "<script type='text/javascript'>opener.document.getElementById('user_face').value='$path';</script>";
        echo "<script type='text/javascript'>opener.document.getElementById('face').src='$path';</script>";
        echo "<script type='text/javascript'>window.close();</script>";
        exit();
    }

    //弹窗赋值（上传板块缩略图专用）
    static public function alertOpenerModuleClose($info,$path){
        echo "<script type='text/javascript'>alert('$info');</script>";
        echo "<script type='text/javascript'>opener.document.getElementById('module_url').value='$path';</script>";
        echo "<script type='text/javascript'>opener.document.getElementById('url').src='$path';</script>";
        echo "<script type='text/javascript'>window.close();</script>";
        exit();
    }

}