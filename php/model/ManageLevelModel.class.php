<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 00:39:38
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 14:51:13
 */
//等级实体类
class ManageLevelModel extends Model{

    private $m_level_id;
    private $m_level_name;
    private $m_level_desc;
    private $premission;
    private $m_level_time;
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
                    m_level_id,
                    m_level_name,
                    m_level_desc,
                    premission,
                    m_level_time
              FROM
                    bbs_manage_level";
        return parent::all($sql);
    }
    //查找单一等级
    public function queryOneLevelByIdOnly(){
        $sql="SELECT
                    m_level_name
              FROM
                    bbs_manage_level
              WHERE m_level_id='$this->m_level_id'
              LIMIT 1";
        return parent::one($sql);
    }
    //等级名重复
    public function queryOneLevelByName(){
        $sql="SELECT
                    m_level_id
              FROM
                    bbs_manage_level
              WHERE m_level_name='$this->m_level_name'
              LIMIT 1";
        return parent::one($sql);
    }
    //新增等级
    public function addLevel(){
        $sql="INSERT INTO
                    bbs_manage_level (
                                m_level_name,
                                m_level_desc,
                                premission,
                                m_level_time
                        )
                    VALUES (
                            '$this->m_level_name',
                            '$this->m_level_desc',
                            '$this->premission',
                            NOW()
                        )";
        return parent::aud($sql);
    }
    //修改等级
    public function updateLevel(){
        $sql="UPDATE
                    bbs_manage_level
                SET
                    m_level_desc='$this->m_level_desc',
                    premission='$this->premission',
                    m_level_name='$this->m_level_name'
                WHERE
                    m_level_id='$this->m_level_id'
                LIMIT 1";
        return parent::aud($sql);
    }
    //修改等级
    public function deleteLevel(){
        $sql="DELETE FROM 
                        bbs_manage_level 
                    WHERE 
                        m_level_id='$this->m_level_id' 
                    LIMIT 1";
        return parent::aud($sql);
    }
}