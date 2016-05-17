<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 09:52:36
 */
//评论实体类
class CommentModel extends Model{

   private $post_id;
   private $user_id;
   private $repost_id;
   private $rerepost_id;
   private $repost_content;
   private $rerepost_content;
   private $repost_support;
   private $repost_against;
   private $cid;
   private $limit;
   private $states;
    //拦截器（__set）
    public function __set($key,$value){
        if (is_array($value)) {
            $this->$key=$value;
        }else{
            $this->$key=Tool::mysqlString($value);
        }
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }

    //批量审核
    public function setStates(){
        foreach ($this->states as $key => $value) {
            if (!is_numeric($value)) continue;
            if($value>0) $value=1;
            if($value<0) $value=0;
            @$sql.="UPDATE bbs_repost SET state='$value' WHERE id='$key';";
        }
        return parent::multi($sql);
    }
    //获取评论的总个数(前台)
    public function queryRepostTotal(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_repost
              WHERE post_id='$this->post_id'";
        return parent::total($sql);
    }
    //获取评论的总个数(后台)
    public function queryCommentListTotal(){
        $sql="SELECT 
              COUNT(*)
              FROM 
                    bbs_repost";
        return parent::total($sql);
    }
    //添加帖子评论
    public function addPostComment(){
        $sql="INSERT INTO 
                bbs_repost (
                            user_id,
                            post_id,
                            repost_content,
                            repost_time
                    ) 
                    VALUES (
                            '$this->user_id',
                            '$this->post_id',
                            '$this->repost_content',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //添加楼层评论
    public function addRepostComment(){
        $sql="INSERT INTO 
                bbs_rerepost (
                            user_id,
                            repost_id,
                            rerepost_content,
                            rerepost_time
                    ) 
                    VALUES (
                            '$this->user_id',
                            '$this->repost_id',
                            '$this->rerepost_content',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //所有评论（前台）
    public function getRepostList(){
        $sql="SELECT 
                    r.repost_id,
                    r.repost_content,
                    r.repost_time,
                    r.repost_support,
                    r.repost_against,
                    u.user_face,
                    u.user_name,
                    u.user_sign_active,
                    u.user_signatrue,
                    u.user_id
              FROM 
                    bbs_repost r
            LEFT JOIN 
                    bbs_user u
                ON  r.user_id=u.user_id
              WHERE r.post_id='$this->post_id'
            ORDER BY r.repost_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //根据postID获取它所有的回帖
    public function getRepostListByPostId(){
        $sql="SELECT 
                    repost_id
              FROM 
                    bbs_repost
              WHERE post_id='$this->post_id'";
        return parent::all($sql);
    }
    //所有楼层评论（前台）
    public function getRerepostList(){
        $sql="SELECT 
                    r.rerepost_id,
                    r.rerepost_content,
                    r.rerepost_time,
                    u.user_face,
                    u.user_name,
                    u.user_id
              FROM 
                    bbs_rerepost r
            LEFT JOIN 
                    bbs_user u
                ON  r.user_id=u.user_id
              WHERE r.repost_id='$this->repost_id'
            ORDER BY r.rerepost_time DESC
                    $this->limit";
        return parent::all($sql);
    }
    //所有评论（后台）
    public function queryCommentList(){
        $sql="SELECT 
                    c.id,
                    c.user,
                    c.cid,
                    c.content,
                    c.content full,
                    c.state,
                    c.state num,
                    ct.title
              FROM 
                    bbs_repost c,
                    cms_content ct
              WHERE c.cid=ct.id
              ORDER BY c.date DESC
                    $this->limit";
        return parent::all($sql);
    }

    //通过审核
    public function setStateOk(){
        $sql="UPDATE 
                    bbs_repost 
                SET 
                    state=1
                WHERE 
                    id='$this->id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //取消通过审核
    public function setStateCancel(){
        $sql="UPDATE 
                    bbs_repost 
                SET 
                    state=0
                WHERE 
                    id='$this->id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //获取三条最火评论，如果其中有支持+反对=0的话，那么就不显示出来（前台）
    public function queryThreeHotComment(){
        $sql="SELECT 
                    c.id,
                    c.cid,
                    c.user,
                    c.manner,
                    c.content,
                    c.sustain,
                    c.oppose,
                    c.date,
                    u.face
              FROM 
                    bbs_repost c
            LEFT JOIN 
                    cms_user u
                ON  c.user=u.user
              WHERE c.cid='$this->cid'
                AND c.state=1
                AND c.sustain+c.oppose>0
            ORDER BY c.sustain+c.oppose DESC
                LIMIT 0,3";
        return parent::all($sql);
    }
    //获取三条最新评论（前台）
    public function queryThreeNewComment(){
        $sql="SELECT 
                    c.id,
                    c.cid,
                    c.user,
                    c.manner,
                    c.content,
                    c.sustain,
                    c.oppose,
                    c.date,
                    u.face
              FROM 
                    bbs_repost c
            LEFT JOIN 
                    cms_user u
                ON  c.user=u.user
              WHERE c.cid='$this->cid'
                AND c.state=1
            ORDER BY c.date DESC
                LIMIT 0,3";
        return parent::all($sql);
    }
    //查找单一评论
    public function queryOneComment(){
        $sql="SELECT 
                    id
              FROM 
                    bbs_repost
            
              WHERE id='$this->id'
              LIMIT 1";
        return parent::one($sql);
    }
    //支持
    public function addRepostSupport(){
        $sql="UPDATE 
                    bbs_repost 
                SET 
                    repost_support=repost_support+1 
                WHERE 
                    repost_id='$this->repost_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //反对
    public function addRepostAgainst(){
        $sql="UPDATE 
                    bbs_repost 
                SET 
                    repost_against=repost_against+1 
                WHERE 
                    repost_id='$this->repost_id' 
                LIMIT 1";
        return parent::aud($sql);
    }
    //删除评论
    public function deleteComment(){
        $sql="DELETE FROM 
                        bbs_repost 
                    WHERE 
                        id='$this->id' 
                    LIMIT 1";
        return parent::aud($sql);
    }
    //根据postID删除回帖
    public function deleteRepostByPostId(){
        $sql="DELETE FROM 
                        bbs_repost 
                    WHERE 
                        post_id='$this->post_id'";
        return parent::aud($sql);
    }
    //根据repostID删除楼中回帖
    public function deleteRerepostByRepostId(){
        $sql="DELETE FROM 
                        bbs_rerepost 
                    WHERE 
                        repost_id='$this->repost_id'";
        return parent::aud($sql);
    }
}