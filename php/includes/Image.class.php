<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-25 19:39:55
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-15 17:31:30
 */
//图像处理类
class Image{
    //图片地址
    private $file;
    //图片高度
    private $width;
    //图片长度
    private $height;
    //图片类型
    private $type;
    //原图的资源句柄
    private $img;
    //新图的资源句柄
    private $new_img;
    public function __construct($file){
        //获取目录，这里不能用ROOT_PATH了，因为getPath返回的路径做了处理，这里只能用$_SERVER["DOCUMENT_ROOT"]
        $this->file= $_SERVER["DOCUMENT_ROOT"].$file;
        //获取图像信息
        list($this->width,$this->height,$this->type)=getimagesize($this->file);
        $this->img=$this->getFromImg($this->file,$this->type);

    }

    //加载图片，各种类型，返回图像的资源句柄
    private function getFromImg($file,$type){
        switch ($type) {
            case '1':
                $img=imagecreatefromgif($file);
                break;
            case '2':
                $img=imagecreatefromjpeg($file);
                break;
            case '3':
                $img=imagecreatefrompng($file);
                break;
            default:
                
                break;
        }
        return $img;
    }
    //缩略图像（百分比）
    /*public function thumb($per){
        $new_width=$this->width*($per/100);
        $new_height=$this->height*($per/100);
        $this->new_img=imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($this->new_img,$this->img, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
    }*/
    //缩略图像（等比例）
    public function thumbDengBi($new_width,$new_height){

        //等比例公式
        if ($this->width<$this->height) {
            //让新长度和新高度等比例
            $new_width=($new_height/$this->height)*$this->width;
        }else{
            //让新高度和新长度等比例
            $new_height=($new_width/$this->width)*$this->height;
        }

        $this->new_img=imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($this->new_img,$this->img, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
    }
    //固定宽高缩放
    public function thumbGuDing($new_width=0,$new_height=0){
        if (empty($new_width)&&empty($new_height)) {
            $new_width=$this->width;
            $new_height=$this->height;
        }
        if (!is_numeric($new_width)||!is_numeric($new_height)) {
            $new_width=$this->width;
            $new_height=$this->height;
        }
        //创建容器
        $con_w=$new_width;
        $con_h=$new_height;
        //创建裁剪点
        $cut_width=0;
        $cut_height=0;

        $this->new_img=imagecreatetruecolor($con_w, $con_h);
        imagecopyresampled($this->new_img,$this->img, 0, 0, $cut_width, $cut_height, $new_width, $new_height, $this->width, $this->height);
    }
    //缩率图像（固定长高容器，图像等比例，扩容填充，裁剪，不失真，不变形）
    public function thumb($new_width=0,$new_height=0){
        if (empty($new_width)&&empty($new_height)) {
            $new_width=$this->width;
            $new_height=$this->height;
        }
        if (!is_numeric($new_width)||!is_numeric($new_height)) {
            $new_width=$this->width;
            $new_height=$this->height;
        }
        //创建容器
        $con_w=$new_width;
        $con_h=$new_height;
        //创建裁剪点
        $cut_width=0;
        $cut_height=0;
        //等比例公式
        if ($this->width<$this->height) {
            //让新长度和新高度等比例
            $new_width=($new_height/$this->height)*$this->width;
        }else{
            //让新高度和新长度等比例
            $new_height=($new_width/$this->width)*$this->height;
        }
        //如果新宽度小于新容器宽度
        if ($new_width<$con_w) {
            //按长度求出等比例因子
            $r=$con_w/$new_width;
            //扩展填充后的长高度
            $new_width*=$r;
            $new_height*=$r;
            //求出裁剪点的高度
            $cut_height=($new_height-$con_h)/2;
        }
        //如果新高度小于新容器高度
        if ($new_height<$con_h) {
            //按高度求出等比例因子
            $r=$con_h/$new_height;
            //扩展填充后的长高度
            $new_width*=$r;
            $new_height*=$r;
            //求出裁剪点的高度
            $cut_width=($new_width-$con_w)/2;
        }

        $this->new_img=imagecreatetruecolor($con_w, $con_h);
        imagecopyresampled($this->new_img,$this->img, 0, 0, $cut_width, $cut_height, $new_width, $new_height, $this->width, $this->height);
    }
    //ckeditor专用图像处理(加水印)
    public function ckImage($new_width=0,$new_height=0){
        list($water_width,$water_height,$water_type)=getimagesize(MARK);
        $water=$this->getFromImg(MARK,$water_type);
        if (empty($new_width)&&empty($new_height)) {
            $new_width=$this->width;
            $new_height=$this->height;
        }
        if (!is_numeric($new_width)||!is_numeric($new_height)) {
            $new_width=$this->width;
            $new_height=$this->height;
        }
        //新高度根据新长度来缩放
        if ($this->width>$new_width) {
            $new_height=($new_width/$this->width)*$this->height;
        }else{
            $new_width=$this->width;
            $new_height=$this->height;
        }
        //新长度根据新高度来缩放
        if ($this->height>$new_height) {
            $new_width=($new_height/$this->height)*$this->width;
        }else{
            $new_width=$this->width;
            $new_height=$this->height;
        }
        //创建新容器
        $this->new_img=imagecreatetruecolor($new_width, $new_height);
        //设置水印坐标
        $water_x=$new_width-$water_width-5;
        $water_y=$new_height-$water_height-5;
        imagecopyresampled($this->new_img,$this->img, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
        //图片大于水印图片，才加水印
        if ($new_width>$water_width&&$new_height>$water_height) {
            imagecopy($this->new_img,$water,$water_x,$water_y,0,0,$water_width,$water_height);
        }
        imagedestroy($water);
    }
    //输出图像
    public function out(){
        imagepng($this->new_img,$this->file);
        imagedestroy($this->img);
        imagedestroy($this->new_img);
    }
}