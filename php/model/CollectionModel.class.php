<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-05-09 19:12:36
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 12:09:22
 */
//收藏实体类
class CollectionModel extends Model{

    private $collection_id;
    private $post_id;
    private $collection_title;
    private $module_id;
    private $collection_time;
    private $collection_type;
    private $user_id;
    private $limit;

    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }

    //添加收藏
    public function addCollection(){
        $sql="INSERT INTO 
                bbs_post_collection (
                            post_id,
                            user_id,
                            collection_time
                    ) 
                    VALUES (
                            '$this->post_id',
                            '$this->user_id',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //获取收藏总记录
    public function queryCollectionTotalByPostId(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_post_collection
              WHERE post_id=$this->post_id";
        return parent::total($sql);
    }
    //根据userid查询收藏
    public function queryCollectionPostIdByUserId(){
        $sql="SELECT 
              post_id
              FROM 
                    bbs_post_collection
              WHERE user_id=$this->user_id";
        return parent::one($sql);
    }
    //根据userid查询收藏
    public function queryCollectionTotalByUserId(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_post_collection c
            LEFT JOIN bbs_user u ON c.user_id=u.user_id
            LEFT JOIN bbs_post p ON p.post_id=c.post_id
            LEFT JOIN bbs_module m ON m.module_id=p.module_id
              WHERE c.user_id='$this->user_id'";
        return parent::total($sql);
    }
    //根据postid查询收藏
    public function queryCollectionByPostIdAndUserId(){
        $sql="SELECT 
              user_id
              FROM 
                    bbs_post_collection
              WHERE post_id=$this->post_id
              AND user_id=$this->user_id";
        return parent::one($sql);
    }
    //根据userid查询收藏
    public function getPostCollectionList(){
        $sql="SELECT 
                    m.module_name,
                    u.user_id,
                    u.user_name,
                    u.user_face,
                    c.collection_id,
                    c.collection_time,
                    p.post_title,
                    p.post_id
              FROM 
                    bbs_post_collection c
            LEFT JOIN bbs_post p ON p.post_id=c.post_id
            LEFT JOIN bbs_user u ON p.user_id=u.user_id
            LEFT JOIN bbs_module m ON m.module_id=p.module_id
              WHERE c.user_id='$this->user_id'
                ORDER BY
                    c.collection_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //删除收藏
    public function deleteCollection(){
        $sql="DELETE FROM 
                        bbs_post_collection 
                    WHERE 
                        collection_id='$this->collection_id'";
        return parent::aud($sql);
    }
    //删除收藏
    public function deleteCollectionByPostId(){
        $sql="DELETE FROM 
                        bbs_post_collection 
                    WHERE 
                        post_id='$this->post_id'";
        return parent::aud($sql);
    }
}