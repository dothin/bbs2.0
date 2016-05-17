<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-12 00:24:24
 */
//等级实体类
class LevelModel extends Model{

    private $user_level_id;
    private $user_level_name;
    private $user_level_desc;
    private $user_level_piece;
    private $user_level_time;
    //拦截器（__set）
    public function __set($key,$value){
        $this->$key=Tool::mysqlString($value);
    }
    //拦截器（__get）
    public function __get($key){
        return $this->$key;
    }
    //取得所有的等级
    public function getAllLevelList(){
        $sql="SELECT
                    user_level_id,
                    user_level_name,
                    user_level_desc,
                    user_level_piece,
                    user_level_time
              FROM
                    bbs_user_level
              ORDER BY
                    user_level_id DESC";
        return parent::all($sql);
    }
    //查找单一等级
    public function queryOneLevelByIdOnly(){
        $sql="SELECT
                    user_level_name
              FROM
                    bbs_user_level
              WHERE user_level_id='$this->user_level_id'
              LIMIT 1";
        return parent::one($sql);
    }
    //等级名重复
    public function queryOneLevelByName(){
        $sql="SELECT
                    user_level_id
              FROM
                    bbs_user_level
              WHERE user_level_name='$this->user_level_name'
              LIMIT 1";
        return parent::one($sql);
    }
    //新增等级
    public function addLevel(){
        $sql="INSERT INTO
                    bbs_user_level (
                                user_level_name,
                                user_level_desc,
                                user_level_piece,
                                user_level_time
                        )
                    VALUES (
                            '$this->user_level_name',
                            '$this->user_level_desc',
                            '$this->user_level_piece',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //修改等级
    public function updateLevel(){
        $sql="UPDATE
                    bbs_user_level
                SET
                    user_level_desc='$this->user_level_desc',
                    user_level_name='$this->user_level_name',
                    user_level_piece='$this->user_level_piece'
                WHERE
                    user_level_id='$this->user_level_id'
                LIMIT 1";
        return parent::aud($sql);
    }
    //修改等级
    public function deleteLevel(){
        $sql="DELETE FROM 
                        bbs_user_level 
                    WHERE 
                        user_level_id='$this->user_level_id' 
                    LIMIT 1";
        return parent::aud($sql);
    }
}