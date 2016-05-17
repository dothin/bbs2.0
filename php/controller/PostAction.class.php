<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 16:09:44
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class PostAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        parent::__construct(new PostModel());
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        switch ($this->data->action) {
            case 'getPostDetail':
                $this->getPostDetail();
                break;
            case 'addCollection':
                Validate::checkUserSession();
                $this->addCollection();
                break;
            case 'sendPost':
                Validate::checkUserSession();
                $this->sendPost();
                break;
            case 'getPosts':
                $this->getPosts();
                break;
            case 'getPostsByModuleId':
                $this->getPostsByModuleId();
                break;
            case 'getRepostList':
                $this->getRepostList();
                break;
            case 'getRerepostList':
                $this->getRerepostList();
                break;
            case 'addSupport':
                Validate::checkUserSession();
                $this->addSupport();
                break;
            case 'addAgainst':
                Validate::checkUserSession();
                $this->addAgainst();
                break;
            case 'addRepostSupport':
                Validate::checkUserSession();
                $this->addRepostSupport();
                break;
            case 'addRepostAgainst':
                Validate::checkUserSession();
                $this->addRepostAgainst();
                break;
            case 'addPostComment':
                Validate::checkUserSession();
                $this->addPostComment();
                break;
            case 'addRepostComment':
                Validate::checkUserSession();
                $this->addRepostComment();
                break;
            case 'setJin':
                Validate::checkUserSession();
                $this->setJin();
                break;
            case 'setRe':
                Validate::checkUserSession();
                $this->setRe();
                break;
            case 'removeJin':
                Validate::checkUserSession();
                $this->removeJin();
                break;
            case 'removeRe':
                Validate::checkUserSession();
                $this->removeRe();
                break;
            case 'checkIsMe':
                Validate::checkUserSession();
                $this->checkIsMe();
                break;
            case 'deletePost':
                Validate::checkUserSession();
                $this->deletePost();
                break;
            case 'updatePost':
                Validate::checkUserSession();
                $this->updatePost();
                break;
            case 'getUserPostList':
                Validate::checkUserSession();
                $this->getUserPostList();
                break;
            case 'getPostsByManage':
                Validate::checkAdminSession();
                Validate::checkPremission('4','没有管理帖子的权限');
                $this->getPostsByManage();
                break;
            case 'letGo':
                Validate::checkAdminSession();
                Validate::checkPremission('4','没有管理帖子的权限');
                $this->letGo();
                break;
            case 'letStop':
                Validate::checkAdminSession();
                Validate::checkPremission('4','没有管理帖子的权限');
                $this->letStop();
                break;
            default:
                //$this->query();
                break;
        }
    }
    private function letGo(){
        $this->model->post_id=$this->data->post_id;
        if(!!$result=$this->model->letGo()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="审核失败";
            echo json_encode($this->res);
        }
    }
    private function letStop(){
        $this->model->post_id=$this->data->post_id;
        if(!!$result=$this->model->letStop()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="审核失败";
            echo json_encode($this->res);
        }
    }
    private function getUserPostList(){
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->user_id=$user_result->user_id;
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        if(!!$result=$this->model->getUserPostList()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryPostTotalByUserId();
            $this->res['pageIndex']=$this->data->pageIndex;
            $this->res['pageSize']=$this->data->pageSize;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
    }
    private function updatePost(){
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->user_id=$user_result->user_id;
        $this->model->post_id=$this->data->post_id;
        $this->model->post_title=$this->data->post_title;
        $this->model->post_type=$this->data->post_type;
        $this->model->post_content=$this->data->post_content;
        $this->model->module_id=$this->data->module_id;
        if(!!$result=$this->model->checkIsMe()&&!!$result=$this->model->updatePost()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
    }
    private function deletePost(){
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->user_id=$user_result->user_id;
        $this->model->post_id=$this->data->post_id;
        //操作的帖子属于我
        if(!!$result=$this->model->checkIsMe()){
            $repost=new CommentModel();
            $repost->post_id=$this->data->post_id;
            
            if(!!$repost_result=$repost->getRepostListByPostId()){
                //删除楼中帖子
                foreach($repost_result as $key=>$value){
                   $repost->repost_id=$value->repost_id;
                   if($repost->deleteRerepostByRepostId()<0){
                        $this->res['status']=false;
                        $this->res['data']="删除楼中回帖失败";
                        echo json_encode($this->res);
                        exit();
                   }
                }
            }
            //删除楼中帖子->删除回帖
            if($repost->deleteRepostByPostId()<0){
                $this->res['status']=false;
                $this->res['data']="删除回帖失败";
                echo json_encode($this->res);
                exit();
           }
           $collection=new CollectionModel();
           $collection->post_id=$this->data->post_id;
            //删除楼中帖子->删除回帖->删除收藏
            if($collection->deleteCollectionByPostId()<0){ 
                $this->res['status']=false;
                $this->res['data']="删除收藏失败";
                echo json_encode($this->res);
                exit();
           }
           //删除楼中帖子->删除回帖->删除收藏->删除帖子
            if(!!$result=$this->model->deletePost()){
                $this->res['status']=true;
                $this->res['data']=$result;
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="删除失败";
                echo json_encode($this->res);
            }
        }else{
            $this->res['status']=false;
            $this->res['data']="不是你的帖子";
            echo json_encode($this->res);
        }
    }
    private function checkIsMe(){
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->user_id=$user_result->user_id;
        $this->model->post_id=$this->data->post_id;
        if(!!$result=$this->model->checkIsMe()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
    }
    private function checkUserModule(){
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $module=new ModuleModel();
        $module->module_id=$this->data->module_id;
        $module->user_id=$user_result->user_id;
        if(!!!$module->checkIsUserModule()){
            $this->res['status']=false;
            $this->res['data']="您不是改版版主，不要非法操作";
            echo json_encode($this->res);
            exit();
        }
    }
    private function setJin(){
        $this->model->post_id=$this->data->post_id;
        $this->checkUserModule();
        if(!!$result=$this->model->setJin()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function setRe(){
        $this->model->post_id=$this->data->post_id;
        $this->checkUserModule();
        if(!!$result=$this->model->setRe()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function removeJin(){
        $this->model->post_id=$this->data->post_id;
        $this->checkUserModule();
        if(!!$result=$this->model->removeJin()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function removeRe(){
        $this->model->post_id=$this->data->post_id;
        $this->checkUserModule();
        if(!!$result=$this->model->removeRe()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getRepostList(){
        $comment=new CommentModel();
        $comment->post_id=$this->data->post_id;
        $comment->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize; 
        if(!!$result=$comment->getRepostList()){
            foreach($result as $key=>$value){
               $comment->repost_id=$value->repost_id;
               //楼层的评论必须和回帖一起返回，不然前端不好处理
               $value->rerepostList=$comment->getRerepostList();
               if(isset($_SESSION['user'])){
                   $friend=new FriendModel();
                   $user=new UserModel();
                   $user->user_name=$_SESSION['user'];
                   $user_result=$user->queryOneUserByName();
                   $friend->from_user_id=$user_result->user_id;
                   $friend->to_user_id=$value->user_id;
                   if(!!$friend->checkIsMyFriend()){
                        $value->isMyFriend=true;
                        $value->isNotMyFriend=false;
                    }else{
                        $value->isMyFriend=false;
                        $value->isNotMyFriend=true;
                    }
               }
                $integral=new IntegralModel();
                $integral->user_id=$value->user_id;
                $value->user_integral=$integral->getIntegralTotal();
                $level=new LevelModel();
                $level_result=$level->getAllLevelList();
                foreach($level_result as $keys=>$values){
                    $level_arr=explode(',',$values->user_level_piece);
                    if($value->user_integral>=$level_arr[0]&&$value->user_integral<$level_arr[1]){
                        $value->user_level_name=$values->user_level_name;
                    }
                }
            }
            $this->res['status']=true;
            $this->res['totalNum']=$comment->queryRepostTotal();
            $this->res['pageIndex']=$this->data->pageIndex;
            $this->res['pageSize']=$this->data->pageSize;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getPosts(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        if(!!$result=$this->model->queryPosts()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryPostTotal();
            $this->res['pageIndex']=$this->data->pageIndex;
            $this->res['pageSize']=$this->data->pageSize;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getPostsByManage(){
        
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        if(!!$result=$this->model->getPostsByManage()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryPostTotalByManage();
            $this->res['pageIndex']=$this->data->pageIndex;
            $this->res['pageSize']=$this->data->pageSize;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
    }
    private function getPostsByModuleId(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        $this->model->module_id=$this->data->module_id;
        if(!!$result=$this->model->getPostsByModuleId()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryPostTotalByModuleId();
            $this->res['pageIndex']=$this->data->pageIndex;
            $this->res['pageSize']=$this->data->pageSize;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }
    private function getPostDetail(){
        $this->model->post_id=$this->data->post_id;
        $this->model->addReadcount();
        if(!!$result=$this->model->queryOnePostById()){
            $collection=new CollectionModel();
            $collection->post_id=$this->data->post_id;
            $post_collection_num=$collection->queryCollectionTotalByPostId();
            $result->post_collection_num=$post_collection_num;
            $this->res['status']=true;
            $this->res['data']=$result;
            $integral=new IntegralModel();
            $integral->user_id=$result->user_id;
            $this->res['data']->user_integral=$integral->getIntegralTotal();
            $level=new LevelModel();
            $level_result=$level->getAllLevelList();
            foreach($level_result as $key=>$value){
                $level_arr=explode(',',$value->user_level_piece);
                if($this->res['data']->user_integral>=$level_arr[0]&&$this->res['data']->user_integral<$level_arr[1]){
                    $this->res['data']->user_level_name=$value->user_level_name;
                }
            }
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="查询失败";
            echo json_encode($this->res);
        }
        
    }

    private function sendPost(){
            if (Validate::checkNull($this->data->title)) Tool::returnInfo('帖子标题不得为空');
            if (Validate::checkNull($this->data->content)) Tool::returnInfo('帖子内容不得为空');
            if (Validate::checkLength($this->data->title,2,'min')) Tool::returnInfo('帖子标题不得小于2位');
            if (Validate::checkLength($this->data->title,32,'max')) Tool::returnInfo('帖子标题不得大于32位');
            $this->model->post_title=$this->data->title;
            $this->model->post_type=$this->data->type;
            $this->model->module_id=$this->data->module;
            $this->model->post_content=$this->data->content;
            $user=new UserModel();
            $user->user_name=$_SESSION['user'];
            $result=$user->queryOneUserByName();
            $this->model->user_id=$result->user_id;
            if(!!$this->model->addPost()){
                $integral=new IntegralModel();
                $integral->rule_action="发帖";
                $integral_result=$integral->queryRuleByRuleAction();
                if($integral_result->rule_status==1){
                    $integral->get_integral=$integral_result->get_integral;
                    $integral->rule_action="发帖";
                    $integral->user_id=$result->user_id;
                    $integral->addIntegral();
                }
                $this->res['status']=true;
                $this->res['data']="发布成功";
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="发布失败";
                echo json_encode($this->res);
            }
    }

    private function addCollection(){
        $collection=new CollectionModel();
        $collection->post_id=$this->data->post_id;
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $result=$user->queryOneUserByName();
        $collection->user_id=$result->user_id;
        if(!!$collection->queryCollectionByPostIdAndUserId()){
            $this->res['status']=false;
            $this->res['data']="已在收藏夹";
            echo json_encode($this->res);
            exit();
        }
        if(!!$collection_result=$collection->addCollection()){
            $integral=new IntegralModel();
            $integral->rule_action="收藏";
            $integral_result=$integral->queryRuleByRuleAction();
            if($integral_result->rule_status==1){
                $integral->get_integral=$integral_result->get_integral;
                $integral->rule_action="收藏";
                $integral->user_id=$result->user_id;
                $integral->addIntegral();
            }
            $this->res['status']=true;
            $this->res['data']=$collection_result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="添加失败";
            echo json_encode($this->res);
        }
    }

    private function addSupport(){
        $this->model->post_id=$this->data->post_id;
        if(!!$this->model->addSupport()){
            $this->res['status']=true;
            $this->res['data']="支持成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="支持失败";
            echo json_encode($this->res);
        }
    }
    private function addAgainst(){
        $this->model->post_id=$this->data->post_id;
        if(!!$this->model->addAgainst()){
            $this->res['status']=true;
            $this->res['data']="反对成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="反对失败";
            echo json_encode($this->res);
        }
    }
    private function addRepostSupport(){
        $comment=new CommentModel();
        $comment->repost_id=$this->data->repost_id;
        if(!!$comment->addRepostSupport()){
            $this->res['status']=true;
            $this->res['data']="支持成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="支持失败";
            echo json_encode($this->res);
        }
    }
    private function addRepostAgainst(){
        $comment=new CommentModel();
        $comment->repost_id=$this->data->repost_id;
        if(!!$comment->addRepostAgainst()){
            $this->res['status']=true;
            $this->res['data']="反对成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="反对失败";
            echo json_encode($this->res);
        }
    }
    private function addPostComment(){
        $comment=new CommentModel();
        $comment->post_id=$this->data->post_id;
        $this->model->post_id=$this->data->post_id;
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $result=$user->queryOneUserByName();
        $comment->user_id=$result->user_id;
        $comment->repost_content=$this->data->repost_content;
        if(!!$comment->addPostComment()){
            $this->model->addCommentCount();
            $integral=new IntegralModel();
            $integral->rule_action="回帖";
            $integral_result=$integral->queryRuleByRuleAction();
            if($integral_result->rule_status==1){
                $integral->get_integral=$integral_result->get_integral;
                $integral->rule_action="回帖";
                $integral->user_id=$result->user_id;
                $integral->addIntegral();
            }
            $this->res['status']=true;
            $this->res['data']="评论成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="评论失败";
            echo json_encode($this->res);
        }
    }
    private function addRepostComment(){
        $comment=new CommentModel();
        $comment->repost_id=$this->data->repost_id;
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $result=$user->queryOneUserByName();
        $comment->user_id=$result->user_id;
        $comment->rerepost_content=$this->data->rerepost_content;
        if(!!$comment->addRepostComment()){
            $integral=new IntegralModel();
            $integral->rule_action="楼中回帖";
            $integral_result=$integral->queryRuleByRuleAction();
            if($integral_result->rule_status==1){
                $integral->get_integral=$integral_result->get_integral;
                $integral->rule_action="楼中回帖";
                $integral->user_id=$result->user_id;
                $integral->addIntegral();
            }
            $this->res['status']=true;
            $this->res['data']="评论成功";
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="评论失败";
            echo json_encode($this->res);
        }
    }
    
}

$post = new PostAction();
$post->action();