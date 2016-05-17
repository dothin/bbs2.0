<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 16:01:46
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class SearchAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        switch ($this->data->action) {
            case 'doSearch':
                $this->doSearch();
                break;
            default:
                //$this->query();
                break;
        }
            
    }
    private function doSearch(){
        $post=new PostModel();
        $post->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        if($this->data->searchType=="发帖人"){
            $user=new UserModel();
            $user->user_name=$this->data->searchText;
            $user_result=$user->queryOneUserByName();
            if($user_result){
                $post->user_id=$user_result->user_id;
                $post_result=$post->getUserPostListByUserId();
                if($post_result){
                    $this->res['status']=true;
                    $this->res['totalNum']=$post->queryPostTotalByUserIdIfGo();
                    $this->res['pageIndex']=$this->data->pageIndex;
                    $this->res['pageSize']=$this->data->pageSize;
                    $this->res['data']=$post_result;
                    echo json_encode($this->res);
                }else{
                    $this->res['status']=false;
                    $this->res['data']="查询失败";
                    echo json_encode($this->res);
                }
            }else{
                $this->res['status']=false;
                $this->res['data']="查询失败";
                echo json_encode($this->res);
            }
            
        }else{
            $post->post_title=$this->data->searchText;
            $post_result=$post->queryAllPostListByPostTitle();
            if($post_result){
                $this->res['status']=true;
                $this->res['totalNum']=$post->queryPostTotalByPostTitle();
                $this->res['pageIndex']=$this->data->pageIndex;
                $this->res['pageSize']=$this->data->pageSize;
                $this->res['data']=$post_result;
                echo json_encode($this->res);
            }else{
                $this->res['status']=false;
                $this->res['data']="查询失败";
                echo json_encode($this->res);
            }
        }
        
        
    }
}

$search = new SearchAction();
$search->action();