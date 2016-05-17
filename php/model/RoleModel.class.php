<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-12 00:25:16
 */
//角色实体类
class RoleModel extends Model{

    private $role_id;
    private $role_name;
    private $role_desc;
    private $role_time;
    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }
    //取得所有的角色
    public function getAllRoleList(){
        $sql="SELECT
                    role_id,
                    role_name,
                    role_desc,
                    role_time
              FROM
                    bbs_role
              ORDER BY
                    role_id DESC";
        return parent::all($sql);
    }
    //查找单一角色
    public function queryOneRoleByIdOnly(){
        $sql="SELECT
                    role_name
              FROM
                    bbs_role
              WHERE role_id='$this->role_id'
              LIMIT 1";
        return parent::one($sql);
    }
    //角色名重复
    public function queryOneRoleByName(){
        $sql="SELECT
                    role_id
              FROM
                    bbs_role
              WHERE role_name='$this->role_name'
              LIMIT 1";
        return parent::one($sql);
    }
    //新增角色
    public function addRole(){
        $sql="INSERT INTO
                    bbs_role (
                                role_name,
                                role_desc,
                                role_time
                        )
                    VALUES (
                            '$this->role_name',
                            '$this->role_desc',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //修改角色
    public function updateRole(){
        $sql="UPDATE
                    bbs_role
                SET
                    role_desc='$this->role_desc',
                    role_name='$this->role_name'
                WHERE
                    role_id='$this->role_id'
                LIMIT 1";
        return parent::aud($sql);
    }
    //修改角色
    public function deleteRole(){
        $sql="DELETE FROM 
                        bbs_role 
                    WHERE 
                        role_id='$this->role_id' 
                    LIMIT 1";
        return parent::aud($sql);
    }
}