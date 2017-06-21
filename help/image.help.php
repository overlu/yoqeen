<?php

namespace Yoqeen\Help;

use \YQ;

/* 驱动相关常量定义 */
define('IMAGE_GD',      1); //常量，标识GD库类型
define('IMAGE_IMAGICK', 2); //常量，标识imagick库类型

/* 缩略图相关常量定义 */
define('IMAGE_THUMB_SCALE',     1); //常量，标识缩略图等比例缩放类型
define('IMAGE_THUMB_FILLED',    2); //常量，标识缩略图缩放后填充类型
define('IMAGE_THUMB_CENTER',    3); //常量，标识缩略图居中裁剪类型
define('IMAGE_THUMB_NORTHWEST', 4); //常量，标识缩略图左上角裁剪类型
define('IMAGE_THUMB_SOUTHEAST', 5); //常量，标识缩略图右下角裁剪类型
define('IMAGE_THUMB_FIXED',     6); //常量，标识缩略图固定尺寸缩放类型

/* 水印相关常量定义 */
define('IMAGE_WATER_NORTHWEST', 1); //常量，标识左上角水印
define('IMAGE_WATER_NORTH',     2); //常量，标识上居中水印
define('IMAGE_WATER_NORTHEAST', 3); //常量，标识右上角水印
define('IMAGE_WATER_WEST',      4); //常量，标识左居中水印
define('IMAGE_WATER_CENTER',    5); //常量，标识居中水印
define('IMAGE_WATER_EAST',      6); //常量，标识右居中水印
define('IMAGE_WATER_SOUTHWEST', 7); //常量，标识左下角水印
define('IMAGE_WATER_SOUTH',     8); //常量，标识下居中水印
define('IMAGE_WATER_SOUTHEAST', 9); //常量，标识右下角水印

/* 默认图片 */
/**
 * 图片处理驱动类，可配置图片处理库
 * 目前支持GD库和imagick
 * @author 麦当苗儿 <zuojiazi.cn@gmail.com>
 */
class Image
{
    /**
     * 图片资源
     * @var resource
     */
    private $img;
    private $imgname;
    private $class;
    public $thumbType = '1';

    /**
     * 构造方法，用于实例化一个图片处理对象
     * @param string $type 要使用的类库，默认使用GD库
     */
    public function __construct($imgname=null, $type=IMAGE_GD)
    {
        /* 判断调用库的类型 */
        switch ($type)
        {
            case IMAGE_GD:
                $class = 'ImageGd';
                break;
            case IMAGE_IMAGICK:
                $class = 'ImageImagick';
                break;
            default:
                throw new Exception('不支持的图片处理库类型');
        }
        $this->imgname = $imgname;
        $this->class = $class;
       if(is_file(MEDIA.DS.$this->imgname) || is_file($this->imgname))
        {
            $this->imgname = is_file($this->imgname) ? $this->imgname : MEDIA.DS.$this->imgname;
            /* 引入处理库，实例化图片处理对象 */
            require_once "image".DS.lcfirst($class).".help.php";
            $class = __NAMESPACE__."\\".$class;
            $this->img = new $class($this->imgname);
        }
    }

    /**
     * 返回图像宽度
     * @return integer 图像宽度
     */
    public function width()
    {
        return $this->img->width();
    }

    /**
     * 返回图像高度
     * @return integer 图像高度
     */
    public function height()
    {
        return $this->img->height();
    }

    /**
     * 返回图像类型
     * @return string 图像类型
     */
    public function type()
    {
        return $this->img->type();
    }

    /**
     * 返回图像MIME类型
     * @return string 图像MIME类型
     */
    public function mime()
    {
        return $this->img->mime();
    }

    /**
     * 返回图像尺寸数组 0 - 图像宽度，1 - 图像高度
     * @return array 图像尺寸
     */
    public function size()
    {
        return $this->img->size();
    }

    /**
     * 保存图像
     * @param  string  $imgname   图像保存名称
     * @param  string  $type      图像类型
     * @param  boolean $interlace 是否对JPEG类型图像设置隔行扫描
     */
    public function save($imgname=null, $type=null, $interlace=true)
    {
        if(!$imgname)
        {
            $size = max($this->width(),$this->height());
            $path = dirname($this->imgname);
            $file = basename($this->imgname);
            $sizepath = $this->thumbType=='1'?$size:($size.'-'.$this->thumbType);
            if(!is_dir($path.DS.$sizepath))
            {
                @mkdir($path.DS.$sizepath);
            }
            $imgname = $path.DS.$sizepath.DS.$file;
        }
        $this->img->save($imgname, $type, $interlace);
        return $imgname;
    }

    /**
     * 获取图片http地址
     */
    public function url($size = '',$type = IMAGE_THUMB_SCALE)
    {
        $baseUrl = YQ::baseUrl();
        $matter = "|".$baseUrl."|";
        if(preg_match($matter, $this->imgname))
        {
            $url = $this->imgname;

            require_once "image".DS.lcfirst($this->class).".help.php";
            $class = __NAMESPACE__."\\".$this->class;
            $this->imgname = str_replace($baseUrl,BP.DS,$url);
            $this->imgname = str_replace("/",DS,$this->imgname);
            if(!is_file($this->imgname))
            {
                return IMAGE_DEFAULT;
            }
            $this->img = new $class($this->imgname);
        }
        else
        {
            if(!is_file($this->imgname))
            {
                return IMAGE_DEFAULT;
            }
            $file = basename($this->imgname);
            $path = str_replace(BP.DS,'',$this->imgname);
            $url = str_replace(DS,'/',$baseUrl.$path);
            
        }
        $_size = max($this->width(),$this->height());
        if($size && $size < $_size)
        {
            if(!is_file($this->thumbFile($size)))
            {
                $this->thumb($size,'',$type)->save();
            }
            $path = dirname($url);
            $file = basename($url);
            return $path.'/'.$size.'/'.$file;
        }
        return $url;

    }

    /**
     * 获取缩略图路径
     */
    protected function thumbFile($size)
    {
        $path = dirname($this->imgname);
        $file = basename($this->imgname);
        return $path.DS.$size.DS.$file;
    }

    /**
     * 魔术方法，用于调用驱动方法
     * @param  string $method 方法名称
     * @param  array  $args   参数列表
     * @return object         当前图片处理对象
     */
    public function __call($method, $args)
    {
        call_user_func_array(array($this->img, $method), $args);
        return $this;
    }
}