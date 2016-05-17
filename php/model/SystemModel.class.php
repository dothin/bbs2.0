<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 19:16:14
 */
//系统配置实体类
class SystemModel extends Model{

    private $manage_id;
    private $config_id;
    private $modify_content;

    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }

    //获取数据
    public function getSystemConf(){
        $sql="SELECT 
                    config_id,
                    bbs_login,
                    bbs_register,
                    bbs_login_fail_count 
                FROM 
                    bbs_system_config 
                WHERE 
                    config_id=1";
        return parent::one($sql);
    }
    //修改
    public function updateConf(){
        $sql="UPDATE 
                    bbs_system_config 
                SET 
                    bbs_login='$this->bbs_login',
                    bbs_register='$this->bbs_register',
                    bbs_login_fail_count='$this->bbs_login_fail_count'
                WHERE 
                    config_id=1";
        return parent::aud($sql);
    }
    //获取日志的总个数
    public function querySystemLogTotal(){
        $sql="SELECT
              COUNT(*)
              FROM
                    bbs_system_modify_log";
        return parent::total($sql);
    }

    //获取系统修改日志
    public function getSystemLog(){
        $sql="SELECT 
                    l.log_id,
                    l.modify_content,
                    l.modify_time,
                    m.manage_name 
                FROM 
                    bbs_system_modify_log l
            LEFT JOIN bbs_manage m
                ON l.manage_id=m.manage_id
            ORDER BY l.log_id DESC 
                            $this->limit";
        return parent::all($sql);
    }
    //修改日志
    public function addChangeToLog(){
        $sql="INSERT INTO
                    bbs_system_modify_log (
                                manage_id,
                                config_id,
                                modify_content,
                                modify_time
                        )
                    VALUES (
                            '$this->manage_id',
                            '$this->config_id',
                            '$this->modify_content',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    
}