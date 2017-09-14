<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8"/>
 <title>(>_<)</title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no"/>
 <link rel="shortcut icon"    href="/static/images/favicon-error.png"/>
 <style type="text/css">*{font-family:sans-serif}body{width:90%;height:94%;margin:0;padding:3% 5%;background-color:#3D5DFF;color:#fff}a{color:#eee;}.slash{padding: 0 2px;}ol{direction:ltr;font-size: 14px;}li{padding-bottom:5px}.addr{font-size: 11px; font-weight: normal;}#no{z-index:-1;position:absolute;bottom:5%;right:10%;opacity:0.3;line-height:50vh;font-size:100px;font-size:20vw}#smile{font-size:7em}</style>
</head>
<body>
 <h1><?php echo $HTTP_ERROR?></h1>
 <b class='slash'><?php echo $STRING; ?></b>
<?php if(defined("DEBUG") && defined("Tld") && DEBUG && Tld === 'dev') {?>
 <ol>
<?php
$obj = array_reverse($obj);
foreach ($obj as $key => $value):?>
<?php
  $fileaddr = isset($obj[$key]['file'])? $obj[$key]['file']: null;
  if($fileaddr)
  {
  	$fileaddr = substr($fileaddr ,mb_strlen(core)-mb_strlen(core_name)-1);
    $FILE = '<span class="addr">'.$fileaddr;
    $FILE = str_replace("/", "<span class='slash'>/</span>", $FILE);
    $FILE = preg_replace("/([^\/<>]+)$/", "</span>$1", $FILE);
?>
   <li><?php echo $FILE.": Line ".$obj[$key]['line'];?></li>
<?php } ?>
<?php endforeach; ?>
  </ol>
<?php } else {?>
 <div id="smile">:(</div>
<?php } ?>
 <br/><br/><br/>
 <?php if(defined("Service")) {?>
 <a href="http://<?php echo defined("Service") ? Service: ''; ?>">Return to Homepage</a>
 <?php } else { ?>
 <a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>">Return to Homepage</a>
 <?php } ?>
 <div id="no"><?php echo $STATUS?></div>
</body>
</html>