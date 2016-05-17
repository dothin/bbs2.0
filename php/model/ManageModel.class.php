<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-16 16:53:12
 */
//管理员实体类
class ManageModel extends Model{

    private $manage_id;
    private $manage_name;
    private $manage_pass;
    private $manage_sex;
    private $manage_level;
    private $last_ip;
    private $limit;

    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }
    //获取管理员总记录
    public function queryManageTotal(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_manage";
        return parent::total($sql);
    }

    //检查登录
    public function checkLogin(){
        $sql="SELECT
                    m.manage_id,
                    m.manage_name,
                    m.manage_sex,
                    l.premission
              FROM
                    bbs_manage m
            LEFT JOIN bbs_manage_level l
                ON m.manage_level=l.m_level_id
              WHERE m.manage_name='$this->manage_name'
                AND
                    m.manage_pass='$this->manage_pass'
              LIMIT 1";
        return parent::one($sql);
    }
    //设置管理员登录统计次数，IP，时间
    public function setLoginTime(){
        $sql="UPDATE 
                    bbs_manage 
                SET 
                    last_login_ip='$this->last_ip',
                    last_login_time=NOW()
                WHERE 
                    manage_id='$this->manage_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //查询登录管理员
    public function queryLoginManage(){
        $sql="SELECT 
                    m.id,
                    m.admin_user,
                    l.level_name,
                    l.premission
              FROM 
                    cms_manage m,
                    cms_level l
              WHERE m.admin_user='$this->admin_user'
                AND m.admin_pass='$this->admin_pass'
                AND m.level=l.id
              LIMIT 1";
        return parent::one($sql);
    }
    //查询所有管理员
    public function queryManages(){
        $sql="SELECT 
                    m.manage_id,
                    m.manage_name,
                    m.manage_sex,
                    m.reg_time,
                    m.last_login_time,
                    m.last_login_ip,
                    l.m_level_name
              FROM 
                    bbs_manage m,
                    bbs_manage_level l
              WHERE m.manage_level=l.m_level_id
              ORDER BY
                    m.manage_id DESC
                    $this->limit";
        return parent::all($sql);
    }
    //查询单个管理员
    public function queryOneManageById(){
        $sql="SELECT 
                    manage_id,
                    manage_name,
                    manage_pass
              FROM 
                    bbs_manage
              WHERE manage_id='$this->manage_id'
              LIMIT 1";
        return parent::one($sql);
    }
    //查询单个管理员
    public function queryOneManageByName(){
        $sql="SELECT 
                    manage_id,
                    manage_name,
                    manage_pass
              FROM 
                    bbs_manage
              WHERE manage_name='$this->manage_name'
              LIMIT 1";
        return parent::one($sql);
    }

    //根据等级id查询管理员
    public function queryManageByManageLevel(){
        $sql="SELECT 
                    manage_id
              FROM 
                    bbs_manage
              WHERE manage_level='$this->manage_level'
              LIMIT 1";
        return parent::one($sql);
    }
    //新增管理员
    public function addManage(){
        $sql="INSERT INTO 
                bbs_manage (
                            manage_name,
                            manage_pass,
                            manage_sex,
                            manage_level,
                            reg_time
                    ) 
                    VALUES (
                            '$this->manage_name',
                            '$this->manage_pass',
                            '$this->manage_sex',
                            '$this->manage_level',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //修改管理员
    public function updateManage(){
        $sql="UPDATE 
                    bbs_manage 
                SET 
                    manage_name='$this->manage_name',
                    manage_pass='$this->manage_pass',
                    manage_sex='$this->manage_sex',
                    manage_level='$this->manage_level'
                WHERE 
                    manage_id='$this->manage_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //删除管理员
    public function deleteManage(){
        $sql="DELETE FROM 
                        bbs_manage 
                    WHERE 
                        manage_id='$this->manage_id' 
                    LIMIT 1";
        return parent::aud($sql);
    }
}