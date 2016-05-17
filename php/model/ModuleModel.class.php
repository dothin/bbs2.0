<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-12 00:24:48
 */
//模块实体类
class ModuleModel extends Model{

    private $module_id;
    private $user_module_id;
    private $user_id;
    private $manage_id;
    private $module_name;
    private $module_desc;
    private $module_url;
    private $limit;

    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }
    //获取模块总记录
    public function queryModuleTotal(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_module";
        return parent::total($sql);
    }
    //查询所有模块
    public function queryModules(){
        $sql="SELECT 
                    m.module_id,
                    m.module_name,
                    m.module_url,
                    m.module_desc,
                    m.module_time,
                    ma.manage_name
              FROM 
                    bbs_module m,
                    bbs_manage ma
              WHERE m.manage_id=ma.manage_id
              ORDER BY
                    m.module_id DESC
                    $this->limit";
        return parent::all($sql);
    }
    //查询是否为版主
    public function checkIsUserModule(){
        $sql="SELECT 
                    user_module_id
              FROM 
                    bbs_user_module
              WHERE module_id='$this->module_id'
                AND user_id='$this->user_id'";
        return parent::one($sql);
    }
    //查询所有模块
    public function queryModulesNoLimt(){
        $sql="SELECT 
                    module_id,
                    module_name,
                    module_url,
                    module_desc
              FROM 
                    bbs_module
              ORDER BY
                    module_id DESC";
        return parent::all($sql);
    }
    //根据板块ID查询版主
    public function getUserModule(){
        $sql="SELECT 
                    m.user_module_id,
                    u.user_name
              FROM 
                    bbs_user_module m
            LEFT JOIN 
                    bbs_user u
                ON  m.user_id=u.user_id
            WHERE module_id='$this->module_id'
              ORDER BY
                    user_module_id DESC";
        return parent::all($sql);
    }
    //查询重复版主
    public function checkUserModule(){
        $sql="SELECT 
                    module_id
              FROM 
                    bbs_user_module
            WHERE user_id='$this->user_id'";
        return parent::one($sql);
    }
    //查询单个模块
    public function queryOneModuleById(){
        $sql="SELECT 
                    module_id,
                    module_name,
                    module_desc
              FROM 
                    bbs_module
              WHERE module_id='$this->module_id'
              LIMIT 1";
        return parent::one($sql);
    }
    //查询单个模块
    public function queryOneModuleByName(){
        $sql="SELECT 
                    module_id,
                    module_name,
                    module_desc
              FROM 
                    bbs_module
              WHERE module_name='$this->module_name'
              LIMIT 1";
        return parent::one($sql);
    }
    //新增模块
    public function addModule(){
        $sql="INSERT INTO 
                bbs_module (
                            module_name,
                            module_desc,
                            manage_id,
                            module_url,
                            module_time
                    ) 
                    VALUES (
                            '$this->module_name',
                            '$this->module_desc',
                            '$this->manage_id',
                            '$this->module_url',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //新增版主
    public function addUserModule(){
        $sql="INSERT INTO 
                bbs_user_module (
                            module_id,
                            user_id
                    ) 
                    VALUES (
                            '$this->module_id',
                            '$this->user_id'
                        )";
        return parent::aud($sql);
    }
    //修改模块
    public function updateModule(){
        $sql="UPDATE 
                    bbs_module 
                SET 
                    module_name='$this->module_name',
                    module_desc='$this->module_desc',
                    module_url='$this->module_url'
                WHERE 
                    module_id='$this->module_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //删除模块
    public function deleteModule(){
        $sql="DELETE FROM 
                        bbs_module 
                    WHERE 
                        module_id='$this->module_id' 
                    LIMIT 1";
        return parent::aud($sql);
    }
    //删除版主
    public function deleteUserModule(){
        $sql="DELETE FROM 
                        bbs_user_module 
                    WHERE 
                        user_module_id='$this->user_module_id' 
                    LIMIT 1";
        return parent::aud($sql);
    }
}