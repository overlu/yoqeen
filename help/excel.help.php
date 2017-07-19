<?php

namespace Yoqeen\Help;

use \PHPExcel_IOFactory;
use \PHPExcel;
use \PHPExcel_Cell;

/**
 | 文件操作类
 */
class Excel
{
	/**
	 * excel实例
	 * @var [type]
	 */
	private $ex;

	/**
	 * reader实例
	 * @var [type]
	 */
	private $reader;

	/**
	 * writer实例
	 */
	private $writer;

	/**
	 * 文件后缀
	 * @var [type]
	 */
	private $ext;

	/**
	 * 文件
	 * @var [type]
	 */
	private $file = '';

    const FILETYPEEXCEL 	= 'Excel2007';  
	const FILETYPEEXCEL5 	= 'Excel5';  
    const FILETYPECSV 		= 'CSV';  

	function __construct($file='') {
		$this->file = $file;
		$this->ext = $file ? pathinfo($this->file, PATHINFO_EXTENSION) : '';
	}

	/**
	 * 读取excel文件
	 * @param  [type] $file 文件路径
	 * @return [type] 数组
	 */
	public function read($firstIndex = true)
	{
		if(!$this->file)
		{
			throw new \Exception("Please set file first", 1);
			
		}
		if($this->ext == 'xlsx')
		{
			$this->reader = PHPExcel_IOFactory::createReader(self::FILETYPEEXCEL)->load($this->file);  
		} elseif ($this->ext == 'xls') {
			$this->reader = PHPExcel_IOFactory::createReader(self::FILETYPEEXCEL5)->load($this->file);  
		} elseif ($this->ext == 'csv') {
			$this->reader = PHPExcel_IOFactory::createReader(self::FILETYPECSV)->load($this->file);  
		} else {
			throw new \Exception("Error file extension", 1);
		}
		$objWorksheet = $this->reader->getActiveSheet();          
		$highestRow = $objWorksheet->getHighestRow();   // 取得总行数       
		$highestColumn = $objWorksheet->getHighestColumn();          
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
		 
		$result = [];
		for ($row = 1;$row <= $highestRow;$row++)
		{
		    $strs=[];
		    //注意highestColumnIndex的列数索引从0开始  
		    for ($col = 0;$col < $highestColumnIndex;$col++)
		    {
		        $strs[$col] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();  
		    }  
		    $result[] = $strs;  
		}
		// $result = $this->reader->getActiveSheet()->toArray();
		if($firstIndex && count($result) > 0)
		{
			$this->key = $result[0];
			unset($result[0]);
			$result = array_map(array(__NAMESPACE__.'\Excel','firstIndex'), $result);
		}
		return $result;
	}

	private function firstIndex($value)
	{
		// dd($value);
		return array_combine($this->key, $value);
	}

	// private function readExcel($file)
	// {
	// 	 $this->reader = PHPExcel_IOFactory::createReader(FILETYPEEXCEL)->load($file);  
	// }

	// private function readExcel5()
	// {
	// 	$this->reader = PHPExcel_IOFactory::createReader(FILETYPEEXCEL5)->load($file);  
	// }

	// private function readCsv()
	// {
	// 	$this->reader = PHPExcel_IOFactory::createReader(FILETYPECSV)->load($file);  
	// }

	/**
	 * 保存excel文件
	 * @return [type] [description]
	 */
	private function write($keyWasHeader)
	{
		if(!$this->file)
		{
			throw new \Exception("Please set file first", 1);
			
		}
		$phpExcelObj = new PHPExcel();
		$this->ex = $phpExcelObj->getActiveSheet();
		//利用setCellValue()填充数据
		//$this->ex->setCellValue("A1","张三")->setCellValue("B1","李四");
		//利用fromArray()填充数据
		$data = $this->data;
		if($keyWasHeader)
		{
			$header = array_keys($this->data);
			$this->ex->fromArray($header);
			$data = [];
			$dataNum = count($this->data);
			for ($i=0 ; $i<$dataNum ; $i++) {
				$columnNum = count($this->data[$i]);
				for($j=0 ; $j<$columnNum ; $j++)
				{
					$data[$j][$i] = $this->data[$i][$j];
				}
			}
		}
		// dd($this->data, $data);
		$this->ex->fromArray($data, null, 'A2');
		if($this->ext == 'xlsx')
		{
			$this->writer = PHPExcel_IOFactory::createWriter($phpExcelObj, self::FILETYPEEXCEL);  
		} elseif ($this->ext == 'xls') {
			$this->writer = PHPExcel_IOFactory::createWriter($phpExcelObj, self::FILETYPEEXCEL5);  
		} elseif ($this->ext == 'csv') {
			$this->writer = PHPExcel_IOFactory::createWriter($phpExcelObj, self::FILETYPECSV);  
		} else {
			throw new \Exception("Error file extension", 1);
		}
		return $this->writer;
	}

	/**
	 * 保存excel文件
	 * @return [type] [description]
	 */
	public function save($keyWasHeader = true)
	{
		$this->write($keyWasHeader)->save($this->file);
		return true;
	}

	/**
	 * 输出excel文件到浏览器
	 * @return [type] [description]
	 */
	public function output($keyWasHeader = true)
	{
		/*header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
	    header("Content-Type:application/force-download");
	    header("Content-Type:application/vnd.ms-execl");
	    header("Content-Type:application/octet-stream");
	    header("Content-Type:application/download");;
	    header('Content-Disposition:attachment;filename=文件名称.xls');
	    header("Content-Transfer-Encoding:binary");
	    $objWriter->save('文件名称.xls');
	    $objWriter->save('php://output');*/

	    // Redirect output to a client’s web browser
	    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-Disposition: attachment;filename="'.$this->file.'"');
	    header('Cache-Control: max-age=0');
	    // If you're serving to IE 9, then the following may be needed
	    header('Cache-Control: max-age=1');

	    // If you're serving to IE over SSL, then the following may be needed
	    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	    header ('Pragma: public'); // HTTP/1.0

	    $this->write($keyWasHeader)->save('php://output');
	    exit;
	}

	/**
	 * 读取文件
	 * @return [type] [description]
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * 获取文件类型
	 * @return [type] [description]
	 */
	public function getFileExt()
	{
		return $this->ext;
	}

	/**
	 * 设置文件
	 */
	public function setFile($file)
	{
		$this->file = $file;
		// if(is_file($file))
		// {
		$this->ext = pathinfo($this->file, PATHINFO_EXTENSION);	
		// }
		return $this;
	}

	/**
	 * 设置数据
	 */
	public function setData($array)
	{
		if(!is_array($array))
		{
			throw new \Exception("Error Data Type", 1);
			
		}
		$this->data = $array;
		return $this;
	}
}