<?php

namespace Yoqeen\Help;

use \YQ;
/**
 * 文件上传类
 */
class Upload
{
	private $path = MEDIA.DS."tmp";					//上传文件保存的路径
	private $allowtype = array("gif","png", "jpg","jpeg");	//设置限制上传文件的类型
	private $maxsize = 10240000;						//限制文件上传大小（字节）
	private $israndname = true;						//设置是否随机重命名文件， false不随机
	private $originName;							//源文件名
	private $tmpFileName;							//临时文件名
	private $fileType;								//文件类型(文件后缀)
	private $fileSize;								//文件大小
	private $newFileName;							//新文件名
	private $errorNum = 0;							//错误号
	private $errorMess="";							//错误报告消息

	/**
	 * 用于设置成员属性（$path, $allowtype,$maxsize, $israndname）
	 * 可以通过连贯操作一次设置多个属性值
	 * @param  string $key  成员属性名(不区分大小写)
	 * @param  mixed  $val  为成员属性设置的值
	 * @return  object     返回自己对象$this，可以用于连贯操作
	 */
	public function set($key, $val)
	{
		$key = strtolower($key);
		if(array_key_exists($key, get_class_vars(get_class($this))))
		{
			$this->setOption($key, $val);
		}
		return $this;
	}

	/**
	 * 调用该方法上传文件
	 * @param  string $fileFile  上传文件的表单名称
	 * @return bool 如果上传成功返回数true
	 */

	public function upload($fileField)
	{
		$return = true;
		/* 检查文件路径是滞合法 */
		if(!$this->checkFilePath())
		{
			$this->errorMess = $this->getError();
			return false;
		}
		$name = $_FILES[$fileField]['name'];
		$tmp_name = $_FILES[$fileField]['tmp_name'];
		$size = $_FILES[$fileField]['size'];
		$error = $_FILES[$fileField]['error'];

		if(is_Array($name))
		{
			$name_num = count($name);
			$errors=array();
			/*多个文件上传则循环处理 ， 这个循环只有检查上传文件的作用，并没有真正上传 */
			for($i = 0; $i < $name_num; $i++)
			{
				/*设置文件信息 */
				if($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i]))
				{
					if(!$this->checkFileSize() || !$this->checkFileType())
					{
						$errors[] = $this->getError();
						$return=false;
					}
				}else{
					$errors[] = $this->getError();
					$return=false;
				}
				/* 如果有问题，则重新初使化属性 */
				if(!$return)
					$this->setFiles();
			}

			if($return){
				/* 存放所有上传后文件名的变量数组 */
				$fileNames = array();
				/* 如果上传的多个文件都是合法的，则通过循环向服务器上传文件 */
				for($i = 0; $i < $name_num; $i++)
				{
					if($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i] ))
					{
						$this->setNewFileName();
						if(!$this->copyFile()){
							$errors[] = $this->getError();
							$return = false;
						}
						$fileNames[] = $this->newFileName;
					}
				}
				$this->newFileName = $fileNames;
			}
			$this->errorMess = $errors;
			return $return;
		}
		else
		{
			/* 设置文件信息 */
			if($this->setFiles($name,$tmp_name,$size,$error))
			{
				/* 上传之前先检查一下大小和类型 */
				if($this->checkFileSize() && $this->checkFileType())
				{
					/* 为上传文件设置新文件名 */
					$this->setNewFileName();
					/* 上传文件  返回0为成功， 小于0都为错误 */
					if($this->copyFile())
					{
						return true;
					}
					else
					{
						$return=false;
					}
				}
				else
				{
					$return=false;
				}
			}
			else
			{
				$return=false;
			}
			//如果$return为false, 则出错，将错误信息保存在属性errorMess中
			if(!$return)
				$this->errorMess=$this->getError();
			return $return;
		}
	}

	/**
	 * 获取上传后的文件名称
	 * @param  void   没有参数
	 * @return string 上传后，新文件的名称， 如果是多文件上传返回数组
	 */
	public function getFileName()
	{
		return $this->newFileName;
	}

	/**
	 * 上传失败后，调用该方法则返回，上传出错信息
	 * @param  void   没有参数
	 * @return string  返回上传文件出错的信息报告，如果是多文件上传返回数组
	 */
	public function getErrorMsg()
	{
		return $this->errorMess;
	}

	/**
	 * 获取上传出错信息
	 */
	private function getError()
	{
		$str = "";
		switch ($this->errorNum)
		{
			case 4:
				$str = "No files have been uploaded";
				break;
			case 3:
				$str = "Only part of the file is uploaded";
				break;
			case 2:
				$str = "The upload file size exceeds the value specified by the MAX_FILE_SIZE option in the HTML form.";
				break;
			case 1:
				$str = "The uploaded file exceeds the value of the upload_max_filesize option in the php.ini";
				break;
			case -1:
				$str = "File type not allowed";
				break;
			case -2:
				$str = "File is too large, upload files can not be more than ".($this->maxsize/1024)."k";
				break;
			case -3:
				$str = "Upload failed";
				break;
			case -4:
				$str = "Failed to create a storage upload file directory, please re specify the upload directory";
				break;
			case -5:
				$str = "Must specify the path to upload files";
				break;
			default:
				$str = "Unknown error";
		}
		return $str;
	}

	/**
	 * 设置和$_FILES有关的内容
	 * @param string  $name     [description]
	 * @param string  $tmp_name [description]
	 * @param integer $size     [description]
	 * @param integer $error    [description]
	 */
	private function setFiles($name="", $tmp_name="", $size=0, $error=0)
	{
		$this->setOption('errorNum', $error);
		if($error)
			return false;
		$this->setOption('originName', $name);
		$this->setOption('tmpFileName',$tmp_name);
		$aryStr = explode(".", $name);
		$this->setOption('fileType', strtolower($aryStr[count($aryStr)-1]));
		$this->setOption('fileSize', $size);
		return true;
	}

	/**
	 * [setOption description]
	 * @param [type] $key [description]
	 * @param [type] $val [description]
	 */
	private function setOption($key, $val)
	{
		$this->$key = $val;
	}

	/**
	 * 设置上传后的文件名称
	 */
	private function setNewFileName()
	{
		if ($this->israndname)
		{
			$this->setOption('newFileName', $this->proRandName());
		}
		else
		{
			$this->setOption('newFileName', $this->originName);
		}
	}

	/**
	 * 检查上传的文件是否是合法的类型
	 * @return boolean
	 */
	private function checkFileType()
	{
		if (in_array(strtolower($this->fileType), $this->allowtype))
		{
			return true;
		}
		else
		{
			$this->setOption('errorNum', -1);
			return false;
		}
	}

	/**
	 * 检查上传的文件是否是允许的大小
	 * @return boolean
	 */
	private function checkFileSize()
	{
		if ($this->fileSize > $this->maxsize)
		{
			$this->setOption('errorNum', -2);
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 检查是否有存放上传文件的目录
	 * @return boolean
	 */
	private function checkFilePath()
	{
		if(empty($this->path)){
			$this->setOption('errorNum', -5);
			return false;
		}
		if (!file_exists($this->path) || !is_writable($this->path))
		{
			if (!@mkdir($this->path, 0755))
			{
				$this->setOption('errorNum', -4);
				return false;
			}
		}
		return true;
	}

	/**
	 * 设置随机文件名
	 */
	private function proRandName()
	{
		$fileName = rand(100,999).round(microtime(true));
		return $fileName.'.'.$this->fileType;
	}

	/**
	 * 复制上传文件到指定的位置
	 * @return boolean
	 */
	private function copyFile()
	{
		if(!$this->errorNum)
		{
			$path = rtrim($this->path, DS).DS;
			$nfile = $path.$this->newFileName;
			$type  = YQ::help('session')->getOther('upload_image_type');
			if($this->isImage() && $type)
			{
				$tmpFileImage = YQ::help('image',$this->tmpFileName);
				$width = $tmpFileImage->width();
				if($width > 866)
				{
					$tmpFileImage->thumb(866)->save($nfile);
				}
				if($type == 'user')
				{
					$tmpFileImage->thumb(64,64,2)->save($path.'64'.DS.$this->newFileName);
					$tmpFileImage->thumb(32,32,2)->save($path.'32'.DS.$this->newFileName);
				}
				if(in_array($type, ['diary','secret']))
				{
				//	$tmpFileImage->thumb(510)->save($path.'510'.DS.$this->newFileName);
					$tmpFileImage->thumb(210)->save($path.'210'.DS.$this->newFileName);
				}
				YQ::help('session')->deleteOther('upload_image_type');
				if($width > 866)
				{
					return true;
				}
			}
			if(@move_uploaded_file($this->tmpFileName, $nfile))
			{
				return true;
			}
			else
			{
				$this->setOption('errorNum', -3);
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * 判断是否是图片文件
	 */
	public function isImage()
	{
		return in_array(strtolower($this->fileType), array("gif","png", "jpg","jpeg"));
	}

	/*
	$up = new Upload;
	//设置属性(上传的位置， 大小， 类型， 名是是否要随机生成)
	$up -> set("path", "./images/");
	$up -> set("maxsize", 2000000);
	$up -> set("allowtype", array("gif", "png", "jpg","jpeg"));
	$up -> set("israndname", false);

	//使用对象中的upload方法， 就可以上传文件， 方法需要传一个上传表单的名子 pic, 如果成功返回true, 失败返回false
	if($up -> upload("pic"))
	{
		//获取上传后文件名子
		var_dump($up->getFileName());
	}
	else
	{
		//获取上传失败以后的错误提示
		var_dump($up->getErrorMsg());
	}
	*/
}