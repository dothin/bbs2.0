<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-05-09 19:12:36
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 12:38:40
 */
//留言实体类
class MessageModel extends Model{

    private $message_id;
    private $user_id;
    private $from_user_id;
    private $to_user_id;
    private $message_content;
    private $limit;

    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }
    //查询未处理的留言
    public function getNoReadMessage(){
        $sql="SELECT 
                    COUNT(*)
              FROM 
                    bbs_message m
            LEFT JOIN bbs_user u
                ON m.from_user_id=u.user_id
              WHERE m.to_user_id='$this->user_id' 
                AND m.message_status=0";
        return parent::total($sql);
    }
    //添加留言
    public function addMessage(){
        $sql="INSERT INTO 
                bbs_message (
                            from_user_id,
                            to_user_id,
                            message_content,
                            message_time
                    ) 
                    VALUES (
                            '$this->from_user_id',
                            '$this->to_user_id',
                            '$this->message_content',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //获取留言总记录
    public function queryMessageTotalByUserId(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_message m
            LEFT JOIN bbs_user u
                ON m.from_user_id=u.user_id
              WHERE m.to_user_id='$this->to_user_id'
                ";
        return parent::total($sql);
    }
    //根据userid查询留言
    public function getAllMessageByUserId(){
        $sql="SELECT 
                    m.message_id,
                    m.message_time,
                    m.message_content,
                    u.user_id,
                    u.user_name,
                    u.user_face
              FROM 
                    bbs_message m
            LEFT JOIN bbs_user u
                ON m.from_user_id=u.user_id
              WHERE m.to_user_id='$this->to_user_id'
                ORDER BY
                    m.message_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //删除留言
    public function deleteMessage(){
        $sql="DELETE FROM 
                        bbs_message 
                    WHERE 
                        message_id='$this->message_id'";
        return parent::aud($sql);
    }
    //设置已读
    public function updateMessageStatus(){
        $sql="UPDATE bbs_message 
                    SET message_status=1 
                    WHERE 
                    message_id='$this->message_id'";
        return parent::aud($sql);
    }
}