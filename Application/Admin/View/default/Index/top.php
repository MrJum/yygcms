<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>top</title>
<!-- 设置资源的的初始化路径 -->
<link href="__PUBLIC__/admin/css/base.css" rel="stylesheet" type="text/css">
<script language='javascript'>
var preFrameW = '206,*';
var FrameHide = 0;
var curStyle = 1;
var totalItem = 9;

function $(oid){return document.getElementById(oid);}

function ChangeMenu(way){
	var addwidth = 10;
    var top = window.top;

    
    var btFrame = top.document.getElementById('btFrame');
    //alert(top.frames.length);
	var fcol = btFrame.cols;
    
	if(way==1) addwidth = 10;
	else if(way==-1) addwidth = -10;
	else if(way==0){
		if(FrameHide == 0){
			preFrameW = btFrame.cols;
			btFrame.cols = '0,*';
			FrameHide = 1;
			return;
		}else{
			btFrame.cols = preFrameW;
			FrameHide = 0;
			return;
		}
	}
	fcols = fcol.split(',');
	fcols[0] = parseInt(fcols[0]) + addwidth;
	btFrame.cols = fcols[0]+',*';
}


function mv(selobj,moveout,itemnum)
{
   if(itemnum==curStyle) return false;
   if(moveout=='m') selobj.className = 'itemsel';
   if(moveout=='o') selobj.className = 'item';
   return true;
}

function changeSel(itemnum)
{
  curStyle = itemnum;
  for(i=1;i<=totalItem;i++)
  {
     if(document.getElementById('item'+i)) document.getElementById('item'+i).className='item';
  }
  document.getElementById('item'+itemnum).className='itemsel';
}

</script>
<style>
body { padding:0px; margin:0px; }
#tpa {
	color: #009933;
	margin:0px;
	padding:0px;
	float:right;
	padding-right:10px;
}

#tpa dd {
	margin:0px;
	padding:0px;
	float:left;
	margin-right:2px;
}

#tpa dd.ditem {
	margin-right:8px;
}

#tpa dd.img {
  padding-top:6px;
}

div.item
{
  text-align:center;
	background:url(__PUBLIC__/admin/images/frame/topitembg.gif</view>) 0px 3px no-repeat;
	width:82px;
	height:26px;
	line-height:28px;
}


*html .itemsel {
	height:26px;
	line-height:26px;
}

a:link,a:visited {
    text-decoration: none;
	color:#eeeeee;
}

a:hover{
	color:#ffffff;
}



.rmain {
  padding-left:10px;
  /* background:url(skin/images/frame/toprightbg.gif) no-repeat; */
}
.logo{
	margin-left:20px;
    font-size:18px;
    font-weight:bold;
}
.btn{
    padding: 2px 12px;
    border:1px solid #aaa;
    cursor:pointer;
    background: #aaaaaa;
    color:#000;
}

</style>
</head>
<body bgColor='#ffffff'>
    
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#23262e;color:#eeeeee; ">
  <tr>
    <td width='40%' height="65" ><span class="logo">又一个CMS网站后台系统&nbsp;</span></td>
    <td width='60%' style="color:white"></td>
    <td width='20%' align="right" valign="bottom">
    	<table width="450" border="0" cellspacing="0" cellpadding="0">
      <tr>
      <td align="right" height="26" style="padding-right:10px;line-height:26px;">
        	您好！<span class="username"><?php echo session(C("__YYG_AUTH_NAME__")) ?></span>，欢迎使用又一个CMS网站后台系统！
        	[<a href="<?php echo admin_url("Login/logout")?>" target="_top">注销退出</a>]&nbsp;
      </td>
      </tr>
      <tr>
        <td align="right" height="34" class="rmain">
            <a class="btn" id="index_link" style="display:none" href="/" target="_blank">网站首页</a>
            <button class="btn" onclick="gotoIndex()" style="margin-right:4px;margin-bottom:4px; ">网站首页</button>
            <button class="btn" onclick="changePassword('<?php echo session(C("__YYG_AUTH_NAME__")) ?>')" style="margin-right:4px;margin-bottom:4px; ">修改密码</button>
            <button class="btn" onclick="clearCache()" style="margin-right:4px;margin-bottom:4px; ">清除缓存</button>
		</td>
      </tr>
    </table></td>
  </tr>
</table>
    <script type="text/javascript">
    function clearCache(){
        if(window.top.confirm("执行该操作将删除系统缓存、运行日志及无用附件，请确认执行？")){
        	window.location = "<?php echo admin_url("ClearCache/") ?>";
        }
        
    }
    function changePassword(name){
        window.top.frames['main'].location = '<?php echo admin_url("ChangePassword/index/name/") ?>' + name;
    }

    function gotoIndex(){
       document.getElementById('index_link').click();
    }
    </script>
</body>
</html>
