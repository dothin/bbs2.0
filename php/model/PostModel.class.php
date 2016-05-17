<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 22:14:15
 */
//帖子实体类
class PostModel extends Model{

    private $post_id;
    private $post_title;
    private $module_id;
    private $post_content;
    private $post_status;
    private $post_readcount;
    private $post_commentcount;
    private $post_type;
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
    //获取帖子总记录
    public function queryPostTotal(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_post
            WHERE post_status=1";
        return parent::total($sql);
    }
    //管理员获取帖子总记录
    public function queryPostTotalByManage(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_post";
        return parent::total($sql);
    }
    //判断帖子是否属于登录用户
    public function checkIsMe(){
        $sql="SELECT 
              post_id
              FROM 
                    bbs_post
            WHERE post_id='$this->post_id'
                AND user_id='$this->user_id'";
        return parent::one($sql);
    }
    //根据module_id获取帖子总记录
    public function queryPostTotalByModuleId(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_post
            WHERE module_id='$this->module_id'
              AND post_status=1";
        return parent::total($sql);
    }
    //根据user_id获取帖子总记录
    public function queryPostTotalByUserId(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_post
            WHERE user_id='$this->user_id'";
        return parent::total($sql);
    }
    //根据user_id获取帖子总记录(已通过)
    public function queryPostTotalByUserIdIfGo(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_post
            WHERE user_id='$this->user_id'
                AND post_status=1";
        return parent::total($sql);
    }
    //模糊搜索总记录
    public function queryPostTotalByPostTitle(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_post p
            LEFT JOIN bbs_module m ON p.module_id=m.module_id
            LEFT JOIN bbs_user u ON p.user_id=u.user_id
              WHERE p.post_title LIKE '%$this->post_title%' AND p.post_status=1";
        return parent::total($sql);
    }
    //判断模块下面是否有帖子
    public function checkModuleHasPost(){
        $sql="SELECT 
                    post_id
              FROM 
                    bbs_post
            WHERE module_id='$this->module_id'";
        return parent::one($sql);
    }
    //查询所有帖子
    public function queryPosts(){
        $sql="SELECT 
                    m.module_name,
                    p.post_id,
                    p.post_title,
                    p.post_time,
                    p.post_type,
                    p.post_readcount,
                    p.post_support,
                    p.post_against,
                    p.post_status,
                    p.post_commentcount,
                    p.post_Jin,
                    p.post_Re,
                    u.user_name,
                    u.user_face
              FROM 
                    bbs_post p,
                    bbs_module m,
                    bbs_user u
              WHERE p.user_id=u.user_id
              AND p.module_id=m.module_id
              AND p.post_status=1
              ORDER BY
                    p.post_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //查询所有帖子
    public function getPostsByManage(){
        $sql="SELECT 
                    m.module_name,
                    p.post_id,
                    p.post_title,
                    p.post_time,
                    p.post_status,
                    p.post_commentcount,
                    u.user_name
              FROM 
                    bbs_post p,
                    bbs_module m,
                    bbs_user u
              WHERE p.user_id=u.user_id
              AND p.module_id=m.module_id
              ORDER BY
                    p.post_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //查询所有帖子
    public function getPostsByModuleId(){
        $sql="SELECT 
                    m.module_name,
                    p.post_id,
                    p.post_title,
                    p.post_time,
                    p.post_type,
                    p.post_readcount,
                    p.post_support,
                    p.post_against,
                    p.post_commentcount,
                    p.post_Jin,
                    p.post_Re,
                    u.user_name,
                    u.user_face
              FROM 
                    bbs_post p,
                    bbs_module m,
                    bbs_user u
              WHERE p.user_id=u.user_id
              AND p.module_id=m.module_id
              AND p.module_id='$this->module_id'
              AND p.post_status=1
              ORDER BY
                    p.post_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //查询用户所有帖子
    public function getUserPostList(){
        $sql="SELECT 
                    m.module_url,
                    m.module_name,
                    m.module_id,
                    p.post_id,
                    p.post_title,
                    p.post_time,
                    p.post_type,
                    p.post_status,
                    p.post_readcount,
                    p.post_support,
                    p.post_against,
                    p.post_commentcount,
                    p.post_Jin,
                    p.post_Re
              FROM 
                    bbs_post p,
                    bbs_module m
              WHERE p.module_id=m.module_id
              AND p.user_id='$this->user_id'
              ORDER BY
                    p.post_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //搜索部分查询用户所有帖子
    public function getUserPostListByUserId(){
        $sql="SELECT 
                    m.module_id,
                    m.module_name,
                    p.post_id,
                    p.post_title,
                    p.post_time,
                    p.post_type,
                    p.post_content,
                    p.post_readcount,
                    p.post_support,
                    p.post_against,
                    p.post_commentcount,
                    p.post_Jin,
                    p.post_Re,
                    u.user_id,
                    u.user_name,
                    u.user_face
              FROM 
                    bbs_post p
            LEFT JOIN bbs_module m ON p.module_id=m.module_id
            LEFT JOIN bbs_user u ON p.user_id=u.user_id
              WHERE p.user_id='$this->user_id' AND p.post_status=1
              ORDER BY
                    p.post_readcount DESC
                    $this->limit";
        return parent::all($sql);
    }
    //查询单个帖子
    public function queryOnePostById(){
        $sql="SELECT 
                    m.module_id,
                    m.module_name,
                    p.post_id,
                    p.post_title,
                    p.post_time,
                    p.post_type,
                    p.post_content,
                    p.post_readcount,
                    p.post_support,
                    p.post_against,
                    p.post_commentcount,
                    p.post_Jin,
                    p.post_Re,
                    u.user_id,
                    u.user_name,
                    u.user_sign_active,
                    u.user_signatrue,
                    u.user_face
              FROM 
                    bbs_post p,
                    bbs_module m,
                    bbs_user u
              WHERE p.post_id=$this->post_id
              AND p.user_id=u.user_id
              AND p.module_id=m.module_id
              LIMIT 1";
        return parent::one($sql);
    }

    //模糊搜索
    public function queryAllPostListByPostTitle(){
        $sql="SELECT 
                    m.module_id,
                    m.module_name,
                    p.post_id,
                    p.post_title,
                    p.post_time,
                    p.post_type,
                    p.post_content,
                    p.post_readcount,
                    p.post_support,
                    p.post_against,
                    p.post_commentcount,
                    p.post_Jin,
                    p.post_Re,
                    u.user_id,
                    u.user_name,
                    u.user_face
              FROM 
                    bbs_post p
            LEFT JOIN bbs_module m ON p.module_id=m.module_id
            LEFT JOIN bbs_user u ON p.user_id=u.user_id
              WHERE p.post_title LIKE '%$this->post_title%' AND p.post_status=1
              ORDER BY
                    p.post_readcount DESC
                    $this->limit";
        return parent::all($sql);
    }
    //查询单个帖子
    public function queryOnePostBytitle(){
        $sql="SELECT 
                    post_id,
                    post_title,
                    module_id
              FROM 
                    bbs_post
              WHERE post_title='$this->post_title'
              LIMIT 1";
        return parent::one($sql);
    }
    //新增帖子
    public function addPost(){ 
        $sql="INSERT INTO 
                bbs_post (
                            post_title,
                            module_id,
                            user_id,
                            post_content,
                            post_type,
                            post_time
                    ) 
                    VALUES (
                            '$this->post_title',
                            '$this->module_id',
                            '$this->user_id',
                            '$this->post_content',
                            '$this->post_type',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //修改帖子阅读量
    public function addReadcount(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_readcount=post_readcount+1
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //修改帖子评论
    public function addCommentCount(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_commentcount=post_commentcount+1
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //设置精华
    public function setJin(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_Jin=1
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //设置热帖
    public function setRe(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_Re=1
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //取消精华
    public function removeJin(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_Jin=0
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //取消热帖
    public function removeRe(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_Re=0
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //支持
    public function addSupport(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_support=post_support+1 
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //反对
    public function addAgainst(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_against=post_against+1 
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //通过审核
    public function letGo(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_status=1
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //不通过审核
    public function letStop(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_status=0
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //修改帖子
    public function updatePost(){
        $sql="UPDATE 
                    bbs_post 
                SET 
                    post_title='$this->post_title',
                    post_type='$this->post_type',
                    post_content='$this->post_content',
                    module_id='$this->module_id',
                    post_modify_time=NOW()
                WHERE 
                    post_id='$this->post_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //删除帖子
    public function deletePost(){
        $sql="DELETE FROM 
                        bbs_post 
                    WHERE 
                        post_id='$this->post_id' 
                    LIMIT 1";
        return parent::aud($sql);
    }
}