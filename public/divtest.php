<?php

function doSQL($sName, $sRelation, $sWhere, $aBindVars) {
	global $sTable;
	global $oRelations;
	global $db;
	$sSQL = 'SELECT * FROM ' . $sRelation . ' WHERE ' . $sWhere;
	#echo $sSQL;
	$rs = $db -> Execute($sSQL, $aBindVars);
	foreach ($rs as $row) {
		echo '<div class="' . $sName . '">';
		for ($i = 0; $i < $rs->FieldCount(); $i++) {
            $fld = $rs->FetchField($i);
            echo '<span class="' . $fld->name . '">' . $row[$i] . '</span>';
        } 
		if (key_exists($sRelation, $oRelations)) {
			foreach ($oRelations->$sRelation as $key => $value) {
				foreach($value as $subkey=>$subvalue){
					if ($subkey != $sTable) {
						doSQL($key, $subkey, $subvalue['foreign'] . ' = ?', array($row[$subvalue['key']]));
					}
				}
			}
		}
		if ($sRelation == $sTable) {
			foreach ($oRelations as $key => $value) {
				foreach ($value as $name => $foreign) {					
					foreach ($foreign as $subkey => $subvalue) {
						if ($subkey == $sTable && $key != 'comments') {
							doSQL($name, $key, $subvalue['key'] . ' = ?', array($row[$subvalue['foreign']]));
						}
					}
				}
			}
			if (key_exists('comments', $oRelations)){
				foreach ($oRelations -> comments as $key => $value) {
					foreach($value as $subkey =>$subvalue){
						if($subkey == $sTable){
							doSQL($key, 'comments', $subvalue['key'] . ' = ? AND approverid IS NOT NULL ORDER BY 1 DESC', array($row[$subvalue['foreign']]));
						}
						
					}
					
				}
			}
		}
		echo "</div>\n";
	}
}

if ($_SERVER['REMOTE_ADDR'] != '::1') {
	//only allow access from web server
	echo "Access Denied from " . $_SERVER['REMOTE_ADDR'];
}
require_once ('../adodb5/adodb.inc.php');
require_once ('../adodb5/adodb-active-record.inc.php');
if (file_exists('../model/db.json')) {
	$oConnections = json_decode(file_get_contents('../model/db.json'));
	$db = NewADOConnection('mysql');
	if ($_SERVER['SERVER_PORT'] == 8080) {
		$dbInfo = $oConnections -> devel;
	} else {
		$dbInfo = $oConnections -> prod;
	}
	$sUname = 'AdminReadOnly';
	$sPasswd = $dbInfo -> $sUname;
	$sDbname = $dbInfo -> dbname;
	$db -> Connect($dbInfo -> host, $sUname, $sPasswd, $sDbname);

	ADOdb_Active_Record::SetDatabaseAdapter($db);
	$rs = $db -> execute("SELECT i.CONSTRAINT_NAME, i.TABLE_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
FROM information_schema.TABLE_CONSTRAINTS i
LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
AND i.TABLE_SCHEMA = '" . $sDbname . "'");
	$oRelations = new stdClass();
	foreach ($rs as $aRow) {
		$sName = $aRow['CONSTRAINT_NAME'];
		$sTable1 = $aRow['TABLE_NAME'];
		$sColumn1 = $aRow['COLUMN_NAME'];
		$sTable2 = $aRow['REFERENCED_TABLE_NAME'];
		$sColumn2 = $aRow['REFERENCED_COLUMN_NAME'];
		$oRelations -> $sTable1 -> $sName -> $sTable2 = array(key => $sColumn1, foreign => $sColumn2);
	}
	if (array_key_exists('object', $_GET)) {
		$sObject = $_GET['object'];
	} else {
		$sObject = 'test';
	}
	$sTable = ADODB_Active_Record::_pluralize($sObject);
	$nIters = 0;
	$sWhere = '1';
	if (array_key_exists('where', $_GET)) {
		$sWhere = $_GET['where'];
	}
	$aBindVars = array();
	if (array_key_exists('bindvars', $_GET)) {
		$aBindVars = preg_split('/,/', $_GET['bindvars']);
	}
	doSQL(ADODB_Active_Record::_singularize($sTable), $sTable, $sWhere, $aBindVars);

}
?>