<?php
/**
*	PHP多行注释提取工具
*	多行注释标准模板
*(每行例如:@title 任务可以看成2个参数,注意2点每行以@开头,2参数间隔一个空格3.一个参数中不要出现空格，如注释语句不要使用空格间隔)

*	类前声明模板
* @title 任务
* @author xietg
* @version 1.6
* @date 2015/1/20
* @update 3.1
* @updateTime 2016/4/21

*	接口模板
* @title xx任务
* @author:xxx
* @date:2016/8/19
* @url material
* @desc 判断xxxxxx
* @version 1.6
* @update 优化
* @updateTime 2016/5/3 15:00
	 
* @param uid int 11 必填 用户ID
* @param mid int 11 必填 任务ID

* @return isBind int 11 是否绑定银行卡1:为绑定,0:为未绑定
* @return isSmall int 11 是否小号
* @return isOne list 11 是否通过任务1:为是,0:为不是
* @return isTwo list 11 是否1:为是,0:为不是
* @return isThere list 32 完善度

* @return isOne name string 32 名字
* @return isOne address string 32 地址
* @return isOne tel string 11 电话
* @return isOne avatar string 100 头像	 

* @return isTwo name string 32 名字
* @return isTwo address string 32 地址
* @return isTwo tel string 11 电话
* @return isTwo avatar string 100 头像 
	 
* @return isThere name string 32 名字
* @return isThere address string 32 地址
* @return isThere tel string 11 电话
* @return isThere avatar string 100 头像

* @return  {
					"key": "e2132326e50f4b96a63e476c9f01fa15",
					"info": "讲个笑话",
					"userid": 2
				}
*/

define('DATE',date('Y-m-d H:i:s',time()));

class TOOL{
	
	const PARAM_LEN = 5;//参数长度
	static $_md_type_str = array('title','author','version','date','update','updatetime','desc','url');
	static $_md_type_table = array('return','param');
	
	public $title = '';
	public $filename = '';
	public $author = '';
	public $tags = '';
	public $category = '';
	public $summary = '';
	
	public function __construct($config){
		if(is_array($config)){
			foreach($config as $k=>$v){
				$this->$k = trim($v);
			}
		}	
	}
	/**
	*
	*	外部调用方法
	*
	*/
	public function run($path,$type){
		set_time_limit(0);
		ini_set('memory_limit','512M');
		
		$file_arr = $this->listDir($path);
		$result = array();
		if(is_array($file_arr)){
			$md_head ='<!--'.PHP_EOL
				.'author:'.$this->author.PHP_EOL
				.'date:'.DATE.PHP_EOL
				.'title:'.$this->title.PHP_EOL
				.'tags:'.$this->tags.PHP_EOL
				.'category:'.$this->category.PHP_EOL
				.'status: publish'.PHP_EOL
				.'summary:'.$this->summary.PHP_EOL
				.'-->'.PHP_EOL
				.'# '.$this->title.PHP_EOL
				.'[TOC]';
			$this->writeMdContent($md_head);
			$flag = false;
			foreach($file_arr as $f_v){
				$f_path = $path.'/'.$f_v;
				$tmp = $this->getNote($f_path);
				$result = $this->writeMdContent($tmp);
				$result or $flag = true;
				unset($tmp);
			}
			if(!$flag){
				exit("<script type='text/javascript'>alert('".$this->filename." Generate Success')</script>");
			}else{
				exit("<script type='text/javascript'>alert('".$this->filename." Generate Fail')</script>");
			}
		}else{
			exit($path."路径下不存在php文件");
		}
		 
		unset($file_arr);
		//			 
		if($type == 'html'){
				$html_str='<!DOCTYPE html>
						<html>
						<head>
						<title>'.$this->title.'</title>
						</head>
						<body>';
				if(is_array($result)){
					foreach($result as $e=>$v){
						$html_str .= $this->resolveNoteHtml($v);
						$html_str.="<hr/>";
					}
				}
				$html_str.='</body></html>';
				echo $html_str;exit;
		} 
		
	}
	public function writeMdContent($result){
			$md_str = '';
			if(is_array($result)){
					foreach($result as $e=>$v){
						$md_str .= $this->resolveNoteMD($e,$v);
						$md_str.=PHP_EOL."--------".PHP_EOL;
					}
			}else{
					$md_str = $result;
			}
				 
			return $this->saveFile($this->filename.'.md',$md_str);
	}
	/**
	*	转化为md代码
	*/
	public function resolveNoteMD($status,$content){
		$tables = '';
		$result = trim(str_replace(array('/','*'),'',$content));
		$result_arr = array_filter(explode('@',$result));
		$code_str = '';
		$head = $content = $content_param = $content_return = array();
		if(is_array($result_arr)){
			$part = '';
			foreach($result_arr as $k=>$v){
				$v = trim($v);
				if(preg_match_all('/(\{.*?\})/is',$v,$match)){
					$code_str = $match[0][0];
					continue;
				}else{
					$row_tmp = array_filter(explode(' ',$v));
				}
				$row_type = strtolower(trim($row_tmp[0]));
				switch($status){
					case 0:
						
						$head = array('Log','类详细说明');											
						($row_type == self::$_md_type_str[0])?($part.= PHP_EOL.'## 类：'.$row_tmp[1].PHP_EOL):($content[] = $row_tmp);
						break;
					case $status>0:
						if(in_array($row_type,self::$_md_type_str)){
							//其他
							$part.= $this->convertRowMD($row_tmp);
						}else  if($row_type == self::$_md_type_table[1]){
							//param
							array_shift($row_tmp);//弹出数组头部
							$head_param = array(self::$_md_type_table[1],'类型','长度','是否必须','注释');
							$content_param[] = $row_tmp;
						}else if($row_type == self::$_md_type_table[0]){
							//return
						    
								$head_return = array(self::$_md_type_table[0],'类型','长度','注释');
								$content_return[] = $row_tmp;
							 
						}
						
						break;
					default:
						break;
				}
			}
			 
			 
			if(!empty($part)){
				$tables.=$part.PHP_EOL;
			}
			if(isset($content) && isset($head) && !empty($content)){			
				$tables.=$this->getMdTable($head,$content);
				unset($head,$content);
			}
			if(isset($content_param) && isset($head_param) && !empty($content_param)){
				$tables.=">应用参数".PHP_EOL;
				$tables.=$this->getMdTable($head_param,$content_param);
				unset($head_param,$content_param);
			}		
			if(isset($content_return) && isset($head_return) && !empty($content_return)){
				$tables.=">返回参数".PHP_EOL;
				$return_tables = $tmp_body = $tmp_param_parent = array();
				foreach($content_return as $k=>$v){
					if(count($v)>self::PARAM_LEN) {						
						continue;
					}else if(count($v) == self::PARAM_LEN){
						array_shift($v);
						$tmp_param_parent[] = $v[0];
						$tmp_body[] = $v;
						unset($content_return[$k]);
					}
				}
				 
				$return_tables[] = $tmp_body;
				unset($tmp_body);
				 
				if(!empty($content_return)){
					foreach($content_return as $v1){
						$tmp_name = $v1[1];
						if(isset($tmp_param_parent) && in_array($tmp_name,$tmp_param_parent)){
							$return_tables[$tmp_name][] = array_slice($v1,2);
						}
					}
					unset($tmp_param_parent);
				}				
				//var_dump($tmp,$return_tables);exit;
				foreach($return_tables as $k2=>$v2){
					if($k2!= '0'){ 
						$tables.= '*'.$k2.'*'.PHP_EOL;
						$head_return = array($k2,'类型','长度','注释');
					}
					$tables.=$this->getMdTable($head_return,$v2);
				}
				unset($head_return,$content_return,$return_tables);
			}
			if(!empty($code_str)){
				$tables.="```JSON".PHP_EOL.$code_str.PHP_EOL.'```'.PHP_EOL;
			}
			
			
		}
		return $tables;
	}
	/**
	*	生成MD表格代码
	*/
	public function getMdTable($head,$content){
		$table = PHP_EOL;
		$rows = count($head)-1;
		foreach($head as $k=>$v){
			if($k == 0){
				$table.=ltrim('|'.ucfirst($v));
			}else if($k <$rows){
				$table.=ltrim('|'.$v);
			}else{
				$table.=ltrim('|'.$v.'|').PHP_EOL;
			}
		}
		for($i=0;$i<=$rows;$i++){
			if($i <$rows){
				$table.=ltrim('|-');
			}elseif($i==$rows){
				$table.=ltrim('|-|');
			}
		}
		foreach($content as $key=>$val){
			$table .= PHP_EOL;
			foreach($val as $k2=>$v2){
				if($k2 == 0){
					$table.=ltrim('|'.ucfirst($v2));
				}else if($k2 <$rows){
					$table.=ltrim('|'.$v2);
				}else{
					$table.=ltrim('|'.$v2.'|');
				}
			}
		}
		return $table.PHP_EOL;
	}
	/**
	*	转化为规定好的MD代码
	*/
	public function convertRowMD($row){
		$type = trim(strtolower($row[0]));
		$result = '';
		switch($type){
			case 'title':
				  $val = isset($row[1])?$row[1]:'default';
				  $result = "### ".$val.PHP_EOL;
				  //$result = "<h2>$val<h2>".PHP_EOL;
				  break;
			case 'url':
				  $val = isset($row[1])?$row[1]:'default';
				  $result = '+ **URL**	:	'.$val.PHP_EOL;
				  break;
			case 'desc':
				  $val = isset($row[1])?$row[1]:'default';
				  $result = '+ **DESC**	:	'.$val.PHP_EOL;
				  break;
			case 'author':
				  $val = isset($row[1])?$row[1]:'default';
				  $result = '+ **AUTHOR**	:	'.$val.PHP_EOL;
				  break;
			case 'version':
				  $val = isset($row[1])?$row[1]:'default';
				  $result = '+ **VERSION** :	'.$val.PHP_EOL;
				  break;
			case 'date':
				   $val = isset($row[1])?$row[1]:'default';
				  $result = '+ **DATE**	:	'.$val.PHP_EOL;
				  break;
			case 'update':
				   $val = isset($row[1])?$row[1]:'default';
				  $result = '+ **UPDATE**	:	'.$val.''.PHP_EOL;
				  break;
			case 'updatetime':
				  $val = isset($row[1])?$row[1]:'default';
				  $result = '+ **UPDATETIME**	:	'.$val.PHP_EOL;
				  break;
			default:
				  break;
		}
		return $result;
	}
	
	/**
	*	解析注释为html代码
	*/
	public function resolveNoteHtml($content){
		$tables = $result_row = '';
		$result = str_replace(array('/','*'),'',$content);
		$result_arr = explode('@',$result);
		$table_head = '<table border="1" cellspacing="0" cellpadding="0" align="center">'.PHP_EOL;
		$table_foot = '</table><br/>'.PHP_EOL;
		if(is_array($result_arr)){
			$len = count($result_arr)-1;
			 
			//var_dump($result_arr);
			foreach($result_arr as $k=>$v){
				$row_val = trim($v);
				//var_dump($row_val);
				if(empty($row_val)) continue;
				$type_1_arr = explode(' ',$row_val);
				$row_len = count($type_1_arr);
				if($k>0){					
					$type_0_arr = explode(' ',trim($result_arr[$k-1]));
					$type_0 = trim(current($type_0_arr));
					$type_1 = trim(current($type_1_arr));					
				}
				 
				if($k == 0 || empty($tables)){
					$tables.=$table_head;
				}else if(!empty($type_0) && $type_0!=$type_1){
					$tables.=$table_foot.$table_head;
				}else if(strtolower($type_1) == 'return' && $row_len==6 &&($type_0_arr[1]!=$type_1_arr[1])){
					$tables.=$table_foot.$table_head;
				}
				$result_row = $this->convertRowHtml($row_val);				
				$tables.=$result_row;
				 
				if($k == $len ){
					  
					$tables.=$table_foot;
				}
				unset($type_1_arr);
			}
		}
		return $tables;
	}
	/**
	*	行转化为html代码
	*/
	public function convertRowHtml($row){
		$row_arr = explode(' ',$row);
		//var_dump($row_arr);exit('===+++');
		$type = isset($row_arr[0])?strtolower($row_arr[0]):'default';
		$result = '';
		switch($type){
			case 'title':
				  $val = isset($row_arr[1])?$row_arr[1]:'default';
				  $result = "<tr><td>$type</td><td>$val</td></tr>".PHP_EOL;
				  //$result = "<h2>$val<h2>".PHP_EOL;
				  break;
			case 'url':
				  $val = isset($row_arr[1])?$row_arr[1]:'default';
				  $result = "<tr><td>$type</td><td>$val</td></tr>".PHP_EOL;
				  break;
			case 'desc':
				  $val = isset($row_arr[1])?$row_arr[1]:'default';
				   
				  $result = "<tr><td>$type</td><td>$val</td></tr>".PHP_EOL;
				  break;
			case 'author':
				  $val = isset($row_arr[1])?$row_arr[1]:'default';
				  $result = "<tr><td>$type</td><td>$val</td></tr>".PHP_EOL;
				  break;
			case 'version':
				  $val = isset($row_arr[1])?$row_arr[1]:'default';
				  $result = "<tr><td>$type</td><td>$val</td></tr>".PHP_EOL;
				  break;
			case 'date':
				  $val = isset($row_arr[1])?$row_arr[1].' '.@$row_arr[2]:'default';
				  $result = "<tr><td>$type</td><td>$val</td></tr>".PHP_EOL;
				  break;
			case 'update':
				  $val = isset($row_arr[1])?$row_arr[1]:'default';
				  $result = "<tr><td>$type</td><td>$val</td></tr>".PHP_EOL;
				  break;
			case 'updatetime':
				  $val = isset($row_arr[1])?$row_arr[1].' '.@$row_arr[2]:'default';
				  $result = "<tr><td>$type</td><td>$val</td></tr>".PHP_EOL;
				  break;
			case 'param':
			//param msid int  11 必填 任务ID
				  $val_1 = isset($row_arr[1])?$row_arr[1]:'default';
				  $val_2 = isset($row_arr[2])?$row_arr[2]:'default';
				  $val_3 = isset($row_arr[3])?$row_arr[3]:'default';
				  $val_4 = isset($row_arr[4])?$row_arr[4]:'default';
				  $val_5 = isset($row_arr[5])?$row_arr[5]:'default';
				  $result = "<tr><td>$type</td><td>$val_1</td><td>$val_2</td><td>$val_3</td><td>$val_4</td><td>$val_5</td></tr>".PHP_EOL;
				  break;
			case 'return':
				  $val_1 = isset($row_arr[1])?$row_arr[1]:'default';
				  $val_2 = isset($row_arr[2])?$row_arr[2]:'default';
				  $val_3 = isset($row_arr[3])?$row_arr[3]:'default';
				  $val_4 = isset($row_arr[4])?$row_arr[4]:'default';
				  $val_5 = isset($row_arr[5])?$row_arr[5]:'default';
				  if(count($row_arr) == self::PARAM_LEN){
					$result = "<tr><td>$type</td><td>$val_1</td><td>$val_2</td><td>$val_3</td><td>$val_4</td></tr>".PHP_EOL;
				  }else{
					$result = "<tr><td>$val_1</td><td>$val_2</td><td>$val_3</td><td>$val_4</td><td>$val_5</td></tr>".PHP_EOL;
				  }
				  break;
			default:
				  break;
		}
		return $result;
	}
	/**
	*
	*	获取所有注释内容
	*/
	public function getNote($path){
		$content = file_get_contents($path);
		//转化为字符串
		$str = var_export($content,true);
		$pattern = '/(\/[*]{2,}.*?[*]+\/)/is';
		//$reg = '/\/[*]{2,}[^*]+[*]{2,}\//i';
		//$reg = '/(\/(\**[^\*]*(\*[^\*]+)+\*)\/[^\r\n]*)/i';		
		preg_match_all($pattern,$str,$match);
		return $match[0];
	}
	/**
	*	保存文件
	*/
	public function saveFile($fileName,$content){
		$content = $this->convert2utf8($content);
		$result = file_put_contents($fileName,$content,FILE_APPEND);
		return $result;
	}
	/**
	*
	*	遍历所有文件
	*/
	public function listDir($dir)
	{
		if(is_dir($dir))
		{
			if ($dh = opendir($dir)) 
			{
				while (($file = readdir($dh)) !== false)
				{
					if((is_dir($dir."/".$file)) && $file!="." && $file!="..")
					{
						$file_arr[$file] = listDir($dir."/".$file."/");
					}
					else
					{
						if($file!="." && $file!=".." && strstr($file,'.') === '.php')
						{
							$file_arr[] = $file;
						}
					}
				}
				closedir($dh);
			}
		}
		return $file_arr;
	}
	/**
	*	转为utf-8
	*/
	public function convert2utf8($str){
		$en_code = mb_detect_encoding($str,array("ASCII","UTF-8","GB2312","GBK","BIG5","CP936"));
		if(!empty($en_code) && $en_code !='UTF-8'){
			$str = iconv($en_code,'UTF-8',$str);
		}
		return $str;
	}
	
}