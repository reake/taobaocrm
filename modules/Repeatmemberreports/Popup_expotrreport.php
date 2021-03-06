<?php
require_once("config.php");
require_once("include/utils/utils.php");
require_once('include/database/PearDatabase.php');
global $mod_strings;
global $app_strings;
global $adb;
global $current_user;

if($_REQUEST['startdate'] && !empty($_REQUEST['startdate'])){
	$startdate = $_REQUEST['startdate'];
}
if($startdate && !empty($startdate)){
	$where .= "and ec_account.lastorderdate > '{$startdate} 00:00:00' ";
}
if($_REQUEST['enddate'] && !empty($_REQUEST['enddate'])){
	$enddate = $_REQUEST['enddate'];		
}
if($enddate && !empty($enddate)){
	$where .= "and ec_account.lastorderdate < '{$enddate} 23:59:59' ";
}
$startarr = split("-",$startdate);
$groupdatearr = array(
	"day"=>"".db_convert("ec_account.lastorderdate",'date_format',array("'%Y-%m-%d'"),array("'{$startarr[0]}'-'{$startarr[1]}'-DD"))."",
	"week"=>"DATE_FORMAT(ec_account.lastorderdate, '%x %v')",
	"month"=>"".db_convert('ec_account.lastorderdate','date_format',array("'%Y-%m'"),array("'{$startarr[0]}-MM'"))."",
	"year"=>"".db_convert("ec_account.lastorderdate",'date_format',array("'%Y'"),array("'{$startarr[0]}'")).""
);
//统计类型
$grouptype = 'day';
if($_REQUEST['grouptype'] && !empty($_REQUEST['grouptype'])){
	$grouptype = $_REQUEST['grouptype'];
}
$groupsql = $groupdatearr[$grouptype];
$order = "ec_account.lastorderdate";$desc = 'asc';
$query = "select {$groupsql} as groupdate,count(*) as groupnum from ec_account 
			where ec_account.deleted = 0 and ec_account.ordernum > 1 ";
if($where && !empty($where)){
	$query .= $where;	
}
$query .= "group by {$groupsql} ";
$query .= "order by {$order} {$desc} ";
//echo $query."<br>";die;
$result = $adb->query($query);
$num_rows = $adb->num_rows($result);
$reportData .= "\"序号\"";
$reportData .= ",\"统计时间\"";
$reportData .= ",\"新增客户数量\"";
$reportData .= "\r\n";
if($num_rows && $num_rows > 0){
	$for_i = 1;
	while($row = $adb->fetch_array($result)){
		$reportData .= "\"".$for_i."\"";
		$reportData .= ",\"".$row['groupdate']."\"";
		$reportData .= ",\"".$row['groupnum']."\"";
		$reportData .= "\r\n";
		$sumtotalcols += $row['groupnum'];
		$for_i ++;
	}
}

$reportData .= "\"\",\"小计\"";
$reportData .= ",\"".$sumtotalcols."\"";
$reportData .= "\r\n";

ob_clean();
header("Pragma: cache");
header("Content-type: application/octet-stream; charset=GBK");
header("Content-Disposition: attachment; filename={$_REQUEST['module']}.csv");
header("Content-transfer-encoding: binary");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
header("Cache-Control: post-check=0, pre-check=0", false );
header("Content-Length: ".strlen($reportData));
$reportData = iconv_ec("UTF-8","GBK",$reportData);
print $reportData;

exit;
	
?>
