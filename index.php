<!DOCTYPE HTML>
<HTML>
<HEAD>
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<script type='text/javascript' src='jquery3.js'></script>
<style>
body{
	margin:0px;
	padding:0px;
	font-family: Arial,Helvetica,sans-serif;
	font-size:16px;
	line-height:1.5em;
	text-align: center;
	
}

.form_1{
	border: 1px solid #000;
    color: #666;
	width:300px;
    font-size: 14px;
    line-height: 35px;
	margin:12px auto;
	padding: 12px 20px;
}
.tit{
	border-top: 2px solid #000;
    color: #333;
    font-size: 26px;
    font-weight: 400;
    line-height: 50px;
}
 
.input_t{
	border: 1px solid #cbcbcb;
    color: #B0B0B0;
    height: 14px;
    line-height: 15px;
    padding: 8px;
    width: 170px;
}
.text_t{
	border: 1px solid #cbcbcb;
    color: #B0B0B0;
    rows:3;
	cols:15;
	font-size:14px;
	line-height: 15px;
    padding: 8px;
    width: 170px;
}
textarea:focus{
	border: 1px solid #38f;
	color: #000;
}
input:focus {
    border: 1px solid #38f;
	color: #000;
}
</style>
<script type="text/javascript">
 function note_tip(target){
	tips = $(target).attr('tips');
	content = $(target).val();
	 if(content == ''){
		  target.style.color="#B0B0B0";
		  target.value=tips;
	 }else if(content!=tips){
		 target.style.color="#000000";
	 }
 }
</script>
</HEAD>
<BODY>
<h2 class="tit">注释文档生成工具</h2>
<div class="d_1">
<form action="index.php" method="post" class="form_1">
	文档作者:&nbsp;<input class="input_t" type="text" value="<?php echo isset($_POST['author'])?$_POST['author']:'请输入姓名';?>" name="author"   tips="请输入姓名" onclick="this.value=''" onblur="note_tip(this)"/><br/>
	文档名称:&nbsp;<input class="input_t" type="text" value="<?php echo isset($_POST['filename'])?$_POST['filename']:'请输入文档名称';?>" name="filename"   tips="请输入文档名称" onclick="this.value=''" onblur="note_tip(this)"/><br/>
	文档标题:&nbsp;<input class="input_t" type="text" value="<?php echo isset($_POST['title'])?$_POST['title']:'请输入文档标题';?>" name="title"  tips="请输入文档标题"  onclick="this.value=''" onblur="note_tip(this)"/><br/>
	文档标签:&nbsp;<input class="input_t" type="text" value="<?php echo isset($_POST['tags'])?$_POST['tags']:'请输入文档标签';?>" name="tags"  tips="请输入文档标签"  onclick="this.value=''" onblur="note_tip(this)"/><br/>
	文档分类:&nbsp;<input class="input_t" type="text" value="<?php echo isset($_POST['category'])?$_POST['category']:'请输入文档分类';?>" name="category"  tips="请输入文档分类"  onclick="this.value=''" onblur="note_tip(this)"/><br/>
	文档摘要:&nbsp;<textarea  class="text_t"  name="summary"  tips="请输入文档摘要"  onclick="this.value=''" onblur="note_tip(this)"><?php echo isset($_POST['summary'])?$_POST['summary']:'请输入文档标题';?>
	</textarea><br/>
	<input type="submit" value="生成注释文档" name="submit"/>
</form>
 
</div>
<?php
include_once(dirname(__FILE__).'\Tool.class.php');
define('PATH',dirname(__FILE__).'\Controller');
//获取文件内容
if(!empty($_POST)){
	if(empty($_POST['author']) || trim($_POST['author']) == '请输入姓名'){
		 exit("<font color='red'>请输入姓名</font>");
	 }
	if(empty($_POST['filename']) || trim($_POST['filename']) == '请输入文档名称'){
		 exit("<font color='red'>请输入文档名称</font>");
	 }
	if(empty($_POST['title']) || trim($_POST['title']) == '请输入文档标题'){
		 exit("<font color='red'>请输入文档标题</font>");
	 }	
	if(empty($_POST['tags']) || trim($_POST['tags']) == '请输入文档标签'){
		 exit("<font color='red'>请输入文档标签</font>");
	 }
	if(empty($_POST['category']) || trim($_POST['category']) == '请输入文档分类'){
		 exit("<font color='red'>请输入文档分类</font>");
	 }
	if(empty($_POST['summary']) || trim($_POST['summary']) == '请输入文档摘要'){
		 exit("<font color='red'>请输入文档摘要</font>");
	 }
	$data = array(
			'title' 	=> $_POST['title'],
			'filename' 	=> $_POST['filename'],
			'author' 	=> $_POST['author'],
			'tags' 		=> $_POST['tags'],
			'category' 	=> $_POST['category'],
			'summary' 	=> $_POST['summary'],
	);
	$tool = new TOOL($data);
	$res = $tool->run(PATH,'md');
}
?>
</BODY>
</HTML>
