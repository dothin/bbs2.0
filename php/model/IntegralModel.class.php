<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-05-09 19:12:36
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 15:22:59
 */
//积分规则实体类
class integralModel extends Model{

    private $integral_id;
    private $user_id;
    private $rule_action;
    private $get_integral;
    private $rule_status;
    private $modify_manage_id;
    private $limit;

    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }

    //添加积分规则
    public function updateIntegral(){
        $sql="UPDATE
                    bbs_integral_rule
                SET rule_status='$this->rule_status',
                    get_integral='$this->get_integral',
                    modify_manage_id='$this->modify_manage_id',
                    modify_time=NOW()
                WHERE rule_id='$this->rule_id'
                ";
        return parent::aud($sql);
    }
    //获取总记录
    public function queryIntegralTotalByUserId(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_integral_log
            WHERE user_id='$this->user_id'";
        return parent::total($sql);
    }
    //获取日志总记录
    public function queryIntegralLogsTotal(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_integral_log";
        return parent::total($sql);
    }
    //获取积分规则
    public function getIntegralRuleList(){ 
        $sql="SELECT 
                    r.rule_id,
                    r.rule_action,
                    r.get_integral,
                    r.rule_status,
                    r.modify_time,
                    m.manage_name
              FROM 
                    bbs_integral_rule r
            LEFT JOIN bbs_manage m
                ON r.modify_manage_id=m.manage_id";
        return parent::all($sql);
    }
    //获取总积分
    public function getIntegralTotal(){
        $sql="SELECT 
                    sum(get_integral)
              FROM 
                    bbs_integral_log
            WHERE user_id='$this->user_id'";
        return parent::total($sql);
    }
    //获取用户积分日志
    public function getAllIntegralListByUserId(){
        $sql="SELECT 
                    get_integral,
                    user_id,
                    rule_action,
                    time
              FROM 
                    bbs_integral_log
            WHERE user_id='$this->user_id'
            ORDER BY time DESC
                $this->limit";
        return parent::all($sql);
    }
    //获取用户积分日志
    public function getAllIntegralListByUserIdNeedName(){
        $sql="SELECT 
                    l.get_integral,
                    l.rule_action,
                    l.time,
                    u.user_name
              FROM 
                    bbs_integral_log l
            LEFT JOIN bbs_user u
                ON l.user_id=u.user_id
            WHERE l.user_id='$this->user_id'
            ORDER BY time DESC
                $this->limit";
        return parent::all($sql);
    }
    //获取积分日志
    public function getIntegralLogs(){
        $sql="SELECT 
                    l.get_integral,
                    l.rule_action,
                    l.time,
                    u.user_name
              FROM 
                    bbs_integral_log l
            LEFT JOIN bbs_user u
                ON l.user_id=u.user_id
            ORDER BY l.time DESC
                $this->limit";
        return parent::all($sql);
    }
    //根据积分动作获取积分信息
    public function queryRuleByRuleAction(){
        $sql="SELECT 
                    rule_status,
                    get_integral
              FROM 
                    bbs_integral_rule
            WHERE rule_action='$this->rule_action'";
        return parent::one($sql);
    }
    //新增积分日志
    public function addIntegral(){
        $sql="INSERT INTO
                    bbs_integral_log (
                                get_integral,
                                user_id,
                                rule_action,
                                time
                        )
                    VALUES (
                            '$this->get_integral',
                            '$this->user_id',
                            '$this->rule_action',
                            NOW()
                        )";
        return parent::aud($sql);
    }
}