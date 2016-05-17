<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:48:14
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-12 00:24:42
 */
/**
 * 模型基类
 */
class Model{

    /**
     * [one 查找总记录模型]
     * @param  [type] $sql [sql语句]
     * @return [type]      [description]
     */
    protected function total($sql){
        $db=DB::getDB();
        $result=$db->query($sql);
        $total=$result->fetch_row();
        DB::clearDB($result,$db);
        return $total[0];
    }
    /**
     * [one 查找单个数据模型]
     * @param  [type] $sql [sql语句]
     * @return [type]      [description]
     */
    protected function one($sql){
        $db=DB::getDB();
        //获取结果集
        $result=$db->query($sql);
        $objects=$result->fetch_object();
        DB::clearDB($result,$db);
        return Tool::htmlString($objects);
    }
    /**
     * [one 查找全部数据模型]
     * @param  [type] $sql [sql语句]
     * @return [type]      [description]
     */
    protected function all($sql){
        $db=DB::getDB();
        //获取结果集
        $result=$db->query($sql);
        $html=array();
        while(!!$objects=$result->fetch_object()){
            //因为数组赋值会被覆盖，所以这里采用二维数组赋值的方法将$objects赋值给$html[]
            $html[]=$objects;
        }
        DB::clearDB($result,$db);
        return Tool::htmlString($html);
    }
    /**
     * [one 增删改模型]
     * @param  [type] $sql [sql语句]
     * @return [type]      [description]
     */
    protected function aud($sql){
        $db=DB::getDB();
        $db->query($sql);
        $affected_rows=$db->affected_rows;
        DB::clearDB($result,$db);
        return $affected_rows;
    }
    /**
     * [nextid 获取下一个增值id，注意，并不是当前id+1那么简单，要用show指令实现]
     * @param  [type] $table [description]
     * @return [type]        [description]
     */
    protected function nextId($table){
        $sql="SHOW TABLE STATUS LIKE '$table'";
        $object = $this->one($sql);
        return $object->Auto_increment;
    }
    /**
     * [multi 查询多条是sql语句]
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    protected function multi($sql){
        $db=DB::getDB();
        $db->multi_query($sql);
        DB::clearDB($result,$db);
        return true;
    }
}