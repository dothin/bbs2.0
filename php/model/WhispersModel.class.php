<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-05-09 19:12:36
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 12:58:36
 */
//私信实体类
class WhispersModel extends Model{

    private $whispers_id;
    private $user_id;
    private $from_user_id;
    private $to_user_id;
    private $whispers_content;
    private $limit;

    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }

    //查询未处理的私信
    public function getNoReadWhispers(){
        $sql="SELECT 
                    COUNT(*)
              FROM bbs_whispers w
            LEFT JOIN bbs_user u
                ON  w.from_user_id=u.user_id
            RIGHT JOIN bbs_friend f
                ON (f.from_user_id='$this->user_id' AND  f.to_user_id=u.user_id AND f.friend_status=1) 
                OR (f.to_user_id='$this->user_id' AND f.from_user_id=u.user_id AND f.friend_status=1)
              WHERE  w.to_user_id='$this->user_id' AND w.whispers_status=0";
        return parent::total($sql);
    }
    //查询单个用户发送的未处理的私信
    public function getNoReadWhispersByFromUserId(){
        $sql="SELECT 
                    COUNT(*)
              FROM 
                    bbs_whispers
              WHERE to_user_id='$this->to_user_id' 
                AND from_user_id='$this->from_user_id'
                AND whispers_status=0";
        return parent::total($sql);
    }
    //添加私信
    public function addWhispers(){
        $sql="INSERT INTO 
                bbs_whispers (
                            from_user_id,
                            to_user_id,
                            whispers_content,
                            whispers_time
                    ) 
                    VALUES (
                            '$this->from_user_id',
                            '$this->to_user_id',
                            '$this->whispers_content',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //获取私信总记录
    public function querywhispersTotalByUserId(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_whispers
              WHERE (from_user_id='$this->from_user_id' AND to_user_id='$this->to_user_id')
                OR (to_user_id='$this->from_user_id' AND from_user_id='$this->to_user_id')
                ";
        return parent::total($sql);
    }
    //根据userid查询私信
    public function getWhispersByUserId(){
        $sql="SELECT 
                    u.user_id,
                    u.user_name,
                    u.user_face,
                    w.whispers_id,
                    w.whispers_content,
                    w.whispers_time
              FROM 
                    bbs_whispers w,
                    bbs_user u
              WHERE (w.from_user_id='$this->from_user_id' AND  w.to_user_id='$this->to_user_id' AND u.user_id=w.from_user_id)
                OR (w.from_user_id='$this->to_user_id' AND  w.to_user_id='$this->from_user_id'AND u.user_id=w.from_user_id)
                ORDER BY
                    w.whispers_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //删除私信
    public function deleteWhispers(){
        $sql="DELETE FROM 
                        bbs_whispers 
                    WHERE 
                        whispers_id='$this->whispers_id'";
        return parent::aud($sql);
    }
    //设置已读
    public function updateStatusToReadByWhispersId(){
        $sql="UPDATE bbs_whispers 
                    SET whispers_status=1 
                    WHERE 
                    whispers_id='$this->whispers_id'";
        return parent::aud($sql);
    }
}