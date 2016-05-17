<?php
/**
 * @Author: gaohuabin
 * @Date:   2016-01-21 10:28:39
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-01-21 12:08:05
 */
/**
 * 验证码类
 */
class ValidateCode{
    //随机因子
    private $charset='abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    //保存验证码
    private $code;
    //验证码长度
    private $codeLen=4;
    //验证码背景长度
    private $width=130;
    //验证码背景高度
    private $height=50;
    //图像资源句柄
    private $img;
    //指定的字体
    private $font;
    //指定大小
    private $fontSize=20;
    //字体颜色
    private $fontColor;
    //干扰线条数量
    private $lineLen=6;
    //干扰雪花数量
    private $iceLen=100;
    //初始化
    public function __construct(){
        $this->font = ROOT_PATH.'/font/elephant.ttf';
    }
    //随机生成验证码
    private function createCode(){
        $len=strlen($this->charset)-1;
        for ($i=0; $i < $this->codeLen; $i++) { 
            $this->code.=$this->charset[mt_rand(0,$len)];
        }
        return $this->code; 
    }

    //生成背景
    private function createBg(){
        $this->img=imagecreatetruecolor($this->width, $this->height);
        $color=imagecolorallocate($this->img, mt_rand(170,255), mt_rand(170,255), mt_rand(170,255));
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
    }
    //生成文字
    private function createFont() {  
        $x = $this->width / $this->codeLen;
        for ($i=0;$i<$this->codeLen;$i++) {
            $this->fontColor = imagecolorallocate($this->img,mt_rand(0,150),mt_rand(0,150),mt_rand(0,150));
            imagettftext($this->img,$this->fontSize,mt_rand(-30,30),$x*$i+mt_rand(1,10),$this->height / 1.4,$this->fontColor,$this->font,$this->code[$i]);
        }
    }
    //生成线条雪花
    private function createLine(){
        for ($i=0; $i < $this->lineLen; $i++) { 
            $color=imagecolorallocate($this->img, mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imageline($this->img, mt_rand(0,$this->width), mt_rand(0,$this->height), mt_rand(0,$this->width), mt_rand(0,$this->height), $color);
        }
        for ($i=0; $i <$this->iceLen ; $i++) { 
            $color=imagecolorallocate($this->img, mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            imagestring($this->img, mt_rand(1,5), mt_rand(0,$this->width), mt_rand(0,$this->height), '*', $color);
        }
    }
    //输出图像
    private function outPut(){
        header('Content-Type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }
    //对外生成验证码
    public function doImg(){
        $this->createBg();
        $this->createCode();
        $this->createLine();
        $this->createFont();
        $this->outPut();
    }
    //获取验证码
    public function getCode(){
        //全部转化成小写
        return strtolower($this->code);
    }
}