<?php
$action = array_key_exists('code', $_GET) ? 'complete' : (array_key_exists('action', $_POST) ? $_POST['action'] : '');
$action = (array_key_exists('action', $_GET) ? $_GET['action'] : $action);
session_start();

require_once('../../adodb5/adodb.inc.php');
require_once('../../adodb5/adodb-active-record.inc.php');
if(file_exists('../../model/db.json')){
	$oConnections = json_decode(file_get_contents('../../model/db.json'));
}else{
	$oConnections = new stdClass();
	$oConnections->devel->host = 'localhost';
	$oConnections->devel->dbname = 'test';
	$oConnections->devel->ReadOnly = 'changeit';
	$oConnections->devel->LoggedInReadOnly = 'changeit';
	$oConnections->devel->AdminReadOnly = 'changeit';
	$oConnections->devel->LoggedInUpdate = 'changeit';
	$oConnections->devel->AdminUpdate = 'changeit';
	$oConnections->devel->LoggedInDelete = 'changeit';
	$oConnections->devel->AdminDelete = 'changeit';
	$oConnections->prod->host = 'localhost';
	$oConnections->prod->dbname = 'changeit';
	$oConnections->prod->ReadOnly = 'changeit';
	$oConnections->prod->LoggedInReadOnly = 'changeit';
	$oConnections->prod->AdminReadOnly = 'changeit';
	$oConnections->prod->LoggedInUpdate = 'changeit';
	$oConnections->prod->AdminUpdate = 'changeit';
	$oConnections->prod->LoggedInDelete = 'changeit';
	$oConnections->prod->AdminDelete = 'changeit';
	file_put_contents('../../model/db.json', json_encode($oConnections));
}
$db = NewADOConnection('mysql');
if($_SERVER['SERVER_PORT'] == 8080){
	$db->Connect($oConnections->devel->host, $oConnections->devel->uname, $oConnections->devel->passwd, $oConnections->devel->dbname);
}else{
	$db->Connect($oConnections->prod->host, $oConnections->prod->uname, $oConnections->prod->passwd, $oConnections->prod->dbname);
}

ADOdb_Active_Record::SetDatabaseAdapter($db);

switch ($action) {
	case 'load':
	case 'find':
		$sObject = $_GET['object'];
		eval('class ' . $sObject . ' extends ADOdb_Active_Record{}');
		$oTemplate = new $sObject;
		if(array_key_exists('bindvars', $_GET)){
			$aResults = $oTemplate->find($_GET['where'], preg_split('/,/', $_GET['bindvars']));
		}else{
			$aResults = $oTemplate->find('1');
		}
		if(!$aResults){
			$sError = $oTemplate->ErrorMsg();
			if(empty($sError)){
				$sError = "No Objects Found";
			}
			echo json_encode(array(error => $sError)) . "\n";
			break;				
		}
		if($action == 'find'){
			$aRc = new stdClass();
			$aRc->items = array();
			foreach ($aResults as $oResult) {
				$aRc->items[] = $oResult;
			}
		}else{
			if(count($aResults) == 0){
				$aRc = null;
			}else{
				$aRc = $aResults[0];				
			}
		}
		echo json_encode($aRc) . "\n";
		break;
	case 'save':
	case 'delete':
		$sGuid = FALSE;
		if(array_key_exists('sguid', $_SESSION)){
			$sGuid = $_SESSION['sguid'];
			if(file_exists('../../model/users.json')){
				$oUsers = json_decode(file_get_contents('../../model/users.json'));
				if(!isset($oUsers->$sGuid)){
					$sGuid = FALSE;
				}
			}
		}
		if(!$sGuid){
			echo json_encode(array(error => "not authorized")) . "\n";	
			break;		
		}
		$sObject = $_GET['object'];
		eval('class ' . $sObject . ' extends ADOdb_Active_Record{}');
		$oTemplate = new $sObject;
		foreach ($_GET as $key => $value) {
			$oTemplate->$key = $value;
		}
		if(array_key_exists('id', $_GET)){
			if(!$oTemplate->delete()){
				echo json_encode(array(error => $oTemplate->ErrorMsg(), "object" => $oTemplate)) . "\n";
				break;				
			}
		}
		if($action == 'save'){
			if(!$oTemplate->save()){
				echo json_encode(array(error => $oTemplate->ErrorMsg(), "object" => $oTemplate)) . "\n";
				break;
			}
		}
		echo json_encode($oTemplate)  . "\n";
		break;
	default:
		if(array_key_exists('object', $_GET)){
			$sObject = $_GET['object'];
		}else{
			$sObject = 'test';
		}
		eval('class ' . $sObject . ' extends ADOdb_Active_Record{}');
		$oTemplate = new $sObject;
		if(array_key_exists('bindvars', $_GET)){
			$aResults = $oTemplate->find($_GET['where'], preg_split('/,/', $_GET['bindvars']));
		}else{
			$aResults = $oTemplate->find('1');
		}
		if($aResults){
			foreach ($aResults as $oResult) {
				echo '<div class="' . $sObject . '">';
				foreach ($oResult as $key => $value) {
					if(substr($key, 0, 1) != '_' && $key != 'lockMode' && $key != 'foreignName'){
						echo '<span class="' . $key . '">' . $value . '</span>';
					}
				}
				echo "</div>\n";
			}
		}
}

?>