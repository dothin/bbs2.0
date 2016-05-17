<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:06:20
 * @Last Modified by:   anchen
 * @Last Modified time: 2016-05-06 22:32:31
 */
//数据库连接类
class DB{
    /**
     * [getDB 获取数据库连接对象]
     * @return [type] [句柄资源]
     */
    static public function getDB(){
        //连接数据库，并获得对象句柄
        $mysqli=new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME,DB_PORT);
        //判断数据库是否正确连接
        if (mysqli_connect_errno()) {
            echo "数据库连接错误".mysqli_connect_error();
        }
        //设置编码集
        $mysqli->set_charset('utf8');
        //返回句柄
        return $mysqli;
    }
    /**
     * [clearDB 清理数据库，注意：必需按引用传递，否则对象不会被清理]
     * @param  [type] $result [结果集]
     * @param  [type] $db     [资源句柄]
     * @return [type]         [description]
     */
    static public function clearDB(&$result,&$db){
        if (is_object($result)) {
            //清理结果集
            $result->free();
            //销毁结果集对象
            $result=null;
        }
        if (is_object($db)) {
            //关闭数据库
            $db->close();
            //销毁对象句柄
            $db=null;
        }
    }
}