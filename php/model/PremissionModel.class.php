<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 14:12:30
 */
//权限实体类
class PremissionModel extends Model{

    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }
    //查询所有权限no limit
    public function getAllpremissionList(){
        $sql="SELECT 
                    premission_id,
                    premission_name,
                    premission_desc
              FROM 
                    bbs_premission
              ORDER BY
                    premission_id ASC";
        return parent::all($sql);
    }
}