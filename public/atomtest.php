<?php

function xmlTime($phpTime)
{
    $millis = strtotime($phpTime); // Convert time to milliseconds since 1970, using default timezone
	if(date_default_timezone_get() == 'UTC') {
    $offsetString = 'Z'; // No need to calculate offset, as default timezone is already UTC
} else {
    $timezone = new DateTimeZone(date_default_timezone_get()); // Get default system timezone to create a new DateTimeZone object
    $offset = $timezone->getOffset(new DateTime($phpTime)); // Offset in seconds to UTC
    $offsetHours = round(abs($offset)/3600);
    $offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60);
    $offsetString = ($offset < 0 ? '-' : '+')
                . ($offsetHours < 10 ? '0' : '') . $offsetHours
                . ':'
                . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes;
}
return(date('Y-m-d\TH:i:s', $millis) . $offsetString ); // This is the correct XML format
	
}

require_once ('../adodb5/adodb.inc.php');
require_once ('../adodb5/adodb-active-record.inc.php');
if(file_exists('../model/db.json')) {
	$oConnections=json_decode(file_get_contents('../model/db.json'));
	$db = NewADOConnection('mysql');
	if($_SERVER['SERVER_PORT'] == 8080){
		$dbInfo = $oConnections->devel;
	}else{
		$dbInfo = $oConnections->prod;
	}
	$sUname = 'ReadOnly';
	$sPasswd = $dbInfo->$sUname;
	$db->Connect($dbInfo->host, $sUname, $sPasswd, $dbInfo->dbname);
	
	ADOdb_Active_Record::SetDatabaseAdapter($db);

	$xml_source=file_get_contents('../model/feedtemplate.xml');
	$x=simplexml_load_string($xml_source);
	class post extends ADOdb_Active_Record{}
	class author extends ADOdb_Active_Record{}
	class link extends ADOdb_Active_Record{}
	class contributor extends ADOdb_Active_Record{}
	
	$oTemplate = new post();
	foreach ($oTemplate->find('1') as $i => $oPost) {
		$x->entry[$i]->title=$oPost->title;
		$x->entry[$i]->summary=$oPost->summary;
		$x->entry[$i]->updated=xmlTime($oPost->updated);
		$x->entry[$i]->published=xmlTime($oPost->published);
		$x->entry[$i]->content = $oPost->content;
		$x->entry[$i]->content->addAttribute('type', $oPost->contenttype);
		$x->entry[$i]->addChild('georss:point',$oPost->geoposition);
		$oAuthor = new author();
		$oAuthor->load('id =?', array($oPost->authorid));
		$x->entry[$i]->author->name=$oAuthor->name;
		$x->entry[$i]->author->uri=$oAuthor->uri;
		$oTemplate = new link();
		foreach ($oTemplate->find('id = ?', array($oPost->id)) as $oLink) {
			$oNewLink = $x->entry[$i]->addChild('link');
			$oNewLink->addAttribute('rel', $oLink->rel);
			$oNewLink->addAttribute('href', $oLink->href);
			if(!empty($oLink->length)){
				$oNewLink->addAttribute('length', $oLink->length);
			}
		}
		$oTemplate = new contributor();
		foreach ($oTemplate->find('id = ?', array($oPost->id)) as $j => $oContributor) {
			$oAuthor2 = new author();
			$oAuthor2->load('id =?', array($oContributor->authorid));
			$x->entry[$i]->contributor[$j]->name=$oAuthor2->name;
			$x->entry[$i]->contributor[$j]->uri=$oAuthor2->uri;
		}
	}
	header('Content-Type: text/xml');
	echo $x->asXML();
}
?>