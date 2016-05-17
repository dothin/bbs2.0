<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-19 20:41:58
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-14 13:46:46
 */
/**
 * 控制器基类
 */
class Action{

    protected $tpl;
    protected $model;

    protected function __construct(&$model=null){
        $this->model=$model;
    }
    
}