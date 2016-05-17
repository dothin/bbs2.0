<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:12:34
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 16:13:35
 */
require substr(dirname(__FILE__),0,-11).'/init.inc.php';
class CollectionAction extends Action{
    private $data;
    private $res=array();
    //构造方法，初始化
    public function __construct(){
        parent::__construct(new CollectionModel());
        
    }

    public function action(){
        
        $this->data=json_decode(file_get_contents("php://input"));
        Validate::checkUserSession();
        switch ($this->data->action) {
            case 'deleteCollection':
                $this->deleteCollection();
                break;
            case 'getPostCollectionList':
                $this->getPostCollectionList();
                break;
            default:
                //$this->query();
                break;
        }
            
    }
    private function deleteCollection(){
        $this->model->collection_id=$this->data->id;
        if(!!$result=$this->model->deleteCollection()){
            $this->res['status']=true;
            $this->res['data']=$result;
            echo json_encode($this->res);
        }else{
            $this->res['status']=false;
            $this->res['data']="删除失败";
            echo json_encode($this->res);
        }
        
    }
    private function getPostCollectionList(){
        $this->model->limit="LIMIT ".($this->data->pageIndex-1)*$this->data->pageSize.",".$this->data->pageSize;
        $user=new UserModel();
        $user->user_name=$_SESSION['user'];
        $user_result=$user->queryOneUserByName();
        $this->model->user_id=$user_result->user_id;
        if(!!$result=$this->model->getPostCollectionList()){
            $this->res['status']=true;
            $this->res['totalNum']=$this->model->queryCollectionTotalByUserId();
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

    
}

$collection = new CollectionAction();
$collection->action();