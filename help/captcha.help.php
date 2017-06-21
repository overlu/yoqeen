<?php

namespace Yoqeen\Help;

//验证码类
class Captcha
{
	public static $seKey = 'captcha';
	public static $expire = 3600;
	public static $codeSet = '2346789ABCDEFGHJKLMNPQRTUVWXY';
	public static $fontSize = 14;
	public static $useCurve = true;   // 是否画混淆曲线
	public static $useNoise = true;   // 是否添加杂点	
	public static $imageH = 0;
	public static $imageL = 0;
	public static $length = 4;
	public static $bg = array(254, 254, 254);  // 背景
	
	protected static $_image = null;     // 验证码图片实例
	protected static $_color = null;     // 验证码字体颜色
	
	public static function create()
	{
		self::$imageL || self::$imageL = self::$length * self::$fontSize * 1.5 + self::$fontSize*1.5; 
		self::$imageH || self::$imageH = self::$fontSize * 2;
		self::$_image = imagecreate(self::$imageL, self::$imageH); 
		imagecolorallocate(self::$_image, self::$bg[0], self::$bg[1], self::$bg[2]); 
		self::$_color = imagecolorallocate(self::$_image, mt_rand(1,120), mt_rand(1,120), mt_rand(1,120));
	//	$ttf = MEDIA.DS.'ttfs'.DS.mt_rand(2,3).'.ttf';
		$ttf = MEDIA.DS.'ttfs'.DS.'2.ttf';
		
		if (self::$useNoise)
		{
			self::_writeNoise();
		} 
		if (self::$useCurve)
		{
			self::_writeCurve();
		}

		$code = array();
		$codeNX = 0;
		for ($i = 0; $i<self::$length; $i++)
		{
			$code[$i] = self::$codeSet[mt_rand(0, 27)];
			$codeNX += mt_rand(self::$fontSize*1.2, self::$fontSize*1.6);
			imagettftext(self::$_image, self::$fontSize, mt_rand(-40, 70), $codeNX, self::$fontSize*1.5, self::$_color, $ttf, $code[$i]);
		}

		$_SESSION['common'][self::$seKey]['code'] = join('', $code); // 把校验码保存到session
		$_SESSION['common'][self::$seKey]['time'] = time();  // 验证码创建时间
				
		header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);		
		header('Pragma: no-cache');		
		header("content-type: image/png");

		imagepng(self::$_image); 
		imagedestroy(self::$_image);
	}

    protected static function _writeCurve() {
		$A = mt_rand(1, self::$imageH/2);
		$b = mt_rand(-self::$imageH/4, self::$imageH/4);
		$f = mt_rand(-self::$imageH/4, self::$imageH/4);
		$T = mt_rand(self::$imageH*1.5, self::$imageL*2);
		$w = (2* M_PI)/$T;

		$px1 = 0;
		$px2 = mt_rand(self::$imageL/2, self::$imageL * 0.667);
		for ($px=$px1; $px<=$px2; $px=$px+ 0.9)
		{
			if ($w!=0)
			{
				$py = $A * sin($w*$px + $f)+ $b + self::$imageH/2;
				$i = (int) ((self::$fontSize - 6)/4);
				while ($i > 0)
				{
				    imagesetpixel(self::$_image, $px + $i, $py + $i, self::$_color);
				    $i--;
				}
			}
		}
		
		$A = mt_rand(1, self::$imageH/2);
		$f = mt_rand(-self::$imageH/4, self::$imageH/4);
		$T = mt_rand(self::$imageH*1.5, self::$imageL*2);
		$w = (2* M_PI)/$T;
		$b = $py - $A * sin($w*$px + $f) - self::$imageH/2;
		$px1 = $px2;
		$px2 = self::$imageL;
		for ($px=$px1; $px<=$px2; $px=$px+ 0.9)
		{
			if ($w!=0)
			{
				$py = $A * sin($w*$px + $f)+ $b + self::$imageH/2;
				$i = (int) ((self::$fontSize - 8)/4);
				while ($i > 0)
				{			
				    imagesetpixel(self::$_image, $px + $i, $py + $i, self::$_color);
				    $i--;
				}
			}
		}
	}

	protected static function _writeNoise()
	{
		for($i = 0; $i < 10; $i++){
			//杂点颜色
		    $noiseColor = imagecolorallocate(
		                      self::$_image, 
		                      mt_rand(150,225), 
		                      mt_rand(150,225), 
		                      mt_rand(150,225)
		                  );
			for($j = 0; $j < 5; $j++)
			{
			    imagestring(
			        self::$_image,
			        5, 
			        mt_rand(-10, self::$imageL), 
			        mt_rand(-10, self::$imageH), 
			        self::$codeSet[mt_rand(0, 27)],
			        $noiseColor
			    );
			}
		}
	}

	/**
	 * 验证验证码
	 */
	public static function check($code='')
	{
		if(!$code || empty($_SESSION['common'][self::$seKey]))
			
		{
			return false;
		}
		if(time() - $_SESSION['common'][self::$seKey]['time'] > self::$expire)
		{
			unset($_SESSION[self::$seKey]);
			return false;
		}
		if(strtoupper($code) == $_SESSION['common'][self::$seKey]['code'])
		{
			return true;		
		}
		return false;	
	}

}