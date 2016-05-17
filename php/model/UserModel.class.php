<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 00:23:38
 */
//用户实体类
class UserModel extends Model{

    private $user_id;
    private $user_name;
    private $user_pass;
    private $reason;
    private $user_sex;
    private $user_face;
    private $user_sign_active;
    private $user_signatrue;
    private $user_reg_time;
    private $user_last_login_time;
    private $user_last_login_ip;
    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }
    //查找单一用户
    public function queryOneUserById(){
        $sql="SELECT
                    user_id,
                    user_name,
                    user_active,
                    user_sex,
                    user_face,
                    user_sign_active,
                    user_signatrue,
                    user_reg_time,
                    user_last_login_time,
                    user_last_login_ip
              FROM
                    bbs_user
              WHERE user_id='$this->user_id'
              LIMIT 1";
        return parent::one($sql);
    }
    //查找单一用户
    public function queryOneUserByIdOnly(){
        $sql="SELECT
                    user_name
              FROM
                    bbs_user
              WHERE user_id='$this->user_id'
              LIMIT 1";
        return parent::one($sql);
    }
    //查找单一用户
    public function queryOneUserByName(){
        $sql="SELECT
                    user_id
              FROM
                    bbs_user
              WHERE user_name='$this->user_name'
              LIMIT 1";
        return parent::one($sql);
    }
    //查看用户是否被禁用
    public function queryUserActiveByName(){
        $sql="SELECT
                    user_active
              FROM
                    bbs_user
              WHERE user_name='$this->user_name'
              LIMIT 1";
        return parent::one($sql);
    }
    //查找用户信息
    public function getUserInfo(){
        $sql="SELECT
                    user_id,
                    user_name,
                    user_active,
                    user_sex,
                    user_face,
                    user_sign_active,
                    user_signatrue,
                    user_reg_time,
                    user_last_login_time,
                    user_last_login_ip
              FROM
                    bbs_user
              WHERE user_name='$this->user_name'
              LIMIT 1";
        return parent::one($sql);
    }
    //获取用户的总个数
    public function queryUserTotal(){
        $sql="SELECT
              COUNT(*)
              FROM
                    bbs_user";
        return parent::total($sql);
    }
    //取得所有的用户
    public function getAllUserList(){
        $sql="SELECT
                    user_id,
                    user_name,
                    user_active,
                    user_sex,
                    user_reg_time,
                    user_last_login_time,
                    user_last_login_ip
              FROM
                    bbs_user
              ORDER BY
                    user_id DESC
                    $this->limit";
        return parent::all($sql);
    }
    //注册和登录时更新最近的登录时间戳
    public function setLaterUser(){
        $sql="UPDATE
                    bbs_user
                SET user_last_login_time=NOW(),
                    user_last_login_ip='$this->last_ip'
              WHERE user_id='$this->user_id'
              LIMIT 1";
        return parent::aud($sql);
    }
    //检查登录
    public function checkLogin(){
        $sql="SELECT
                    user_id,
                    user_name,
                    user_sex,
                    user_last_login_time,
                    user_face
              FROM
                    bbs_user
              WHERE user_name='$this->user_name'
                AND
                    user_pass='$this->user_pass'
              LIMIT 1";
        return parent::one($sql);
    }
    //用户名重复
    public function checkUser(){
        $sql="SELECT
                    user_id
              FROM
                    bbs_user
              WHERE user_name='$this->user_name'
              LIMIT 1";
        return parent::one($sql);
    }
    //新增用户
    public function addUser(){
        $sql="INSERT INTO
                    bbs_user (
                                user_name,
                                user_pass,
                                user_face,
                                user_reg_time
                        )
                    VALUES (
                            '$this->user_name',
                            '$this->user_pass',
                            '$this->user_face',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //判断失败表里面是否已有用户
    public function checkUserInFailList(){
        $sql="SELECT
                    fail_count
              FROM
                    bbs_login_fail
              WHERE fail_name='$this->user_name'
              LIMIT 1";
        return parent::one($sql);
    }
    //新增登录失败记录
    public function addFailToFailList(){
        $sql="INSERT INTO
                    bbs_login_fail (
                                fail_name,
                                fail_count,
                                fail_reason,
                                fail_time
                        )
                    VALUES (
                            '$this->user_name',1,
                            '$this->reason',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //更新失败次数
    public function updateFailList(){
        $sql="UPDATE
                    bbs_login_fail
                SET
                    fail_count=fail_count+1,
                    fail_time=NOW()
                WHERE
                    fail_name='$this->user_name'
                LIMIT 1";
        return parent::aud($sql);
    }
    //管理员修改用户
    public function updateUser(){
        $sql="UPDATE
                    bbs_user
                SET
                    user_pass='$this->user_pass',
                    user_name='$this->user_name',
                    user_active='$this->user_active'
                WHERE
                    user_id='$this->user_id'
                LIMIT 1";
        return parent::aud($sql);
    }
    //用户修改用户
    public function userUpdateUser(){
        $sql="UPDATE
                    bbs_user
                SET
                    user_pass='$this->user_pass',
                    user_name='$this->user_name',
                    user_sign_active='$this->user_sign_active',
                    user_signatrue='$this->user_signatrue',
                    user_face='$this->user_face',
                    user_sex='$this->user_sex'
                WHERE
                    user_id='$this->user_id'
                LIMIT 1";
        return parent::aud($sql);
    }
    //修改用户
    public function disableUser(){
        $sql="UPDATE
                    bbs_user
                SET
                    user_active=1
                WHERE
                    user_id='$this->user_id'
                LIMIT 1";
        return parent::aud($sql);
    }
}