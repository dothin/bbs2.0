<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-05-09 19:12:36
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 12:53:16
 */
//好友实体类
class FriendModel extends Model{

    private $friend_id;
    private $user_id;
    private $from_user_id;
    private $to_user_id;
    private $friend_desc;
    private $limit;

    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }

    //查询未处理的好友请求
    public function getNoAgreeFriendNums(){
        $sql="SELECT 
                    COUNT(*)
              FROM 
                    bbs_friend f
            LEFT JOIN bbs_user u
                ON f.from_user_id=u.user_id
              WHERE f.to_user_id='$this->user_id' 
                AND f.friend_status=0";
        return parent::total($sql);
    }
    //添加好友
    public function addFriend(){
        $sql="INSERT INTO 
                bbs_friend (
                            from_user_id,
                            to_user_id,
                            friend_desc,
                            friend_time
                    ) 
                    VALUES (
                            '$this->from_user_id',
                            '$this->to_user_id',
                            '$this->friend_desc',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //获取好友总记录
    public function queryIsUserFriendTotal(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_friend
              WHERE (from_user_id='$this->user_id' AND friend_status=1)
                OR (to_user_id='$this->user_id' AND friend_status=1)
                ";
        return parent::total($sql);
    }
    //判断是否添加
    public function checkIsFriend(){
        $sql="SELECT 
              friend_id
              FROM 
                    bbs_friend
              WHERE (from_user_id='$this->from_user_id' AND to_user_id='$this->to_user_id')
                 OR (from_user_id='$this->to_user_id' AND to_user_id='$this->from_user_id')";
        return parent::one($sql);
    }
    //判断是否为好友
    public function checkIsMyFriend(){
        $sql="SELECT 
              friend_id
              FROM 
                    bbs_friend
              WHERE (from_user_id='$this->from_user_id' AND to_user_id='$this->to_user_id' AND friend_status=1)
                OR (from_user_id='$this->to_user_id' AND to_user_id='$this->from_user_id' AND friend_status=1)";
        return parent::one($sql);
    }
    //根据userid查询好友
    public function getIsUserFriendList(){
        $sql="SELECT 
                    f.friend_id,
                    u.user_id,
                    u.user_name,
                    u.user_face
              FROM 
                    bbs_friend f,
                    bbs_user u
              WHERE (f.from_user_id='$this->user_id' AND  f.to_user_id=u.user_id AND f.friend_status=1)
                OR (f.to_user_id='$this->user_id' AND f.from_user_id=u.user_id AND f.friend_status=1)
                ORDER BY
                    f.friend_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //根据userid查询好友
    public function getAddUserFriendList(){
        $sql="SELECT 
                    u.user_id,
                    u.user_name,
                    u.user_face,
                    f.friend_desc,
                    f.friend_time,
                    f.friend_id
              FROM 
                    bbs_friend f,
                    bbs_user u
              WHERE (f.to_user_id='$this->user_id' AND f.from_user_id=u.user_id AND f.friend_status=0)
                ORDER BY
                    f.friend_time";
        return parent::all($sql);
    }
    //同意好友申请
    public function agreeFriend(){
        $sql="UPDATE bbs_friend 
                    SET friend_status=1 ,
                        agree_time=NOW()
                    WHERE 
                    friend_id='$this->friend_id'";
        return parent::aud($sql);
    }
    //删除好友
    public function deleteFriend(){
        $sql="DELETE FROM 
                        bbs_friend 
                    WHERE 
                        friend_id='$this->friend_id'";
        return parent::aud($sql);
    }
}