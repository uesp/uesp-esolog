<?php


	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");


class EsoLogViewer
{
	
	const PRINT_DB_ERRORS = true;
	
		// Must be same as matching value in the log parser
	const ELV_POSITION_FACTOR = 1000;
	
	public $db = null;
	public $dbReadInitialized = false;
	public $totalRowCount = 0;
	public $lastQuery = "";
	
	public $action = "";
	public $recordType = '';
	public $recordID = -1;
	public $recordSort = '';
	public $recordSortOrder = '';
	public $recordFilter = '';
	public $recordFilterId = '';
	public $recordField = '';
	public $displayLimit = 100;
	public $displayStart = 0;
	
		// TODO: Use same definitions as parseLog.php?
	const FIELD_INT = 1;
	const FIELD_STRING = 2;
	const FIELD_FLOAT = 3;
	const FIELD_POSITION = 4;
	const FIELD_INTPOSITIVE = 5;
	const FIELD_INTBOOLEAN = 6;
	const FIELD_LARGESTRING = 7;
	const FIELD_INTTRANSFORM = 8;
	const FIELD_INTID = 9;
	
	public static $FIELD_NAMES = array(
			self::FIELD_INT => "integer",
			self::FIELD_STRING => "string",
	);
	
	public static $BOOK_FIELDS = array(
			'id' => self::FIELD_INTID,
			'title' => self::FIELD_STRING,
			'body' => self::FIELD_LARGESTRING,
			'icon' => self::FIELD_STRING,
			'isLore' => self::FIELD_INTBOOLEAN,
			'skill' => self::FIELD_STRING,
			'mediumIndex' => self::FIELD_INTTRANSFORM,
			'categoryIndex' => self::FIELD_INTPOSITIVE,
			'collectionIndex' => self::FIELD_INTPOSITIVE,
			'bookIndex' => self::FIELD_INTPOSITIVE,
			'guildIndex' => self::FIELD_INTPOSITIVE,
			'logId' => self::FIELD_INTID,
	);
	
	public static $CHEST_FIELDS = array(
			'id' => self::FIELD_INTID,
			'locationId' => self::FIELD_INTID,
			'quality' => self::FIELD_INTTRANSFORM,
			'logId' => self::FIELD_INTID,
	);
	
	public static $LOCATION_FIELDS = array(
			'id' => self::FIELD_INTID,
			'type' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'count' => self::FIELD_INT,
			'zone' => self::FIELD_STRING,
			'x' => self::FIELD_POSITION,
			'y' => self::FIELD_POSITION,
			'rawX' => self::FIELD_FLOAT,
			'rawY' => self::FIELD_FLOAT,
			'bookId' => self::FIELD_INTID,
			'npcId' => self::FIELD_INTID,
			'questId' => self::FIELD_INTID,
			'itemId' => self::FIELD_INTID,
			'logId' => self::FIELD_INTID,
	);
	
	
	public static $RECORD_TYPES = array(
			
			'book' => array(
					'displayName' => 'Books',
					'displayNameSingle' => 'Book',
					'record' => 'book',
					'table' => 'book',
					'method' => 'DoRecordDisplay',
					'sort' => 'title',
					
					'transform' => array(
							'mediumIndex' => 'GetBookMediumText',
					),
					
					'filters' => array(
							array(
								'record' => 'location',
								'field' => 'bookId',
								'thisField' => 'id',
								'displayName' => 'View Locations',
								'type' => 'fiter',
							),
					),
						
					//'fields' => self::$BOOK_FIELDS,
			),
			
			'chest' => array(
					'displayName' => 'Chests',
					'displayNameSingle' => 'Chest',
					'record' => 'chest',
					'table' => 'chest',
					'method' => 'DoRecordDisplay',
					'sort' => 'quality',
					
					'transform' => array(
							'quality' => 'GetChestQualityText',
					),
					
					'filters' => array(
							array(
									'record' => 'location',
									'field' => 'id',
									'thisField' => 'locationId',
									'displayName' => 'View Location',
									'type' => 'viewRecord',
							),
					),
			),
			
			'location' => array (
					'displayName' => 'Locations',
					'displayNameSingle' => 'Location',
					'record' => 'location',
					'table' => 'location',
					'method' => 'DoRecordDisplay',
					'sort' => 'zone',
					
					'join' => array(
							/*'bookId' => array(
									'table' => 'book',
									'fields' => array('title', 'isLore'),
									'joinField' => 'id',
							), */
					),
					
					'filters' => array(
							array(
									'record' => 'book',
									'field' => 'id',
									'thisField' => 'bookId',
									'displayName' => 'View Book',
									'type' => 'viewRecord',
							),
					),
					
					//'fields' => self::$LOCATION_FIELDS,
			),
	);
	

	
	public function __construct ()
	{
			// TODO: Static initialization?
		self::$RECORD_TYPES['book']['fields'] = self::$BOOK_FIELDS;
		self::$RECORD_TYPES['chest']['fields'] = self::$CHEST_FIELDS;
		self::$RECORD_TYPES['location']['fields'] = self::$LOCATION_FIELDS;
		
		$this->InitDatabase();
		$this->setInputParams();
		$this->parseInputParams();
	}
	
	
	private function InitDatabase ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		if ($this->dbReadInitialized) return true;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($db->connect_error) return $this->ReportError("Could not connect to mysql database!");
		
		$this->dbReadInitialized = true;
		return true;
	}
	
	
	public function ReportError ($errorMsg)
	{
		print($errorMsg);
		
		if (self::PRINT_DB_ERRORS && $this->db != null && $this->db->error)
		{
			print("<p />DB Error:" . $this->db->error . "<p />");
			print("<p />Last Query:" . $this->lastQuery . "<p />");
		}
		
		return FALSE;
	}
	
	
	public function GetBookMediumText ($mediumIndex)
	{
		static $MEDIUM_VALUES = array(
				-1 => "",
				0 => "Yellowed Paper",
				1 => "Animal Skin",
				2 => "Rubbing Paper",
				3 => "Letter",
				4 => "Note",
				5 => "Scroll",
				6 => "Tablet",
		);
		
		$key = (int) $mediumIndex;
		
		if (array_key_exists($key, $MEDIUM_VALUES)) return $MEDIUM_VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetChestQualityText ($quality)
	{
		static $QUALITY_VALUES = array(
				-1 => "",
				0 => "None",
				1 => "Simple",
				2 => "Intermediate",
				3 => "Advanced",
				4 => "Master",
				5 => "Impossible",
		);
	
		$key = (int) $quality;
	
		if (array_key_exists($key, $QUALITY_VALUES)) return $QUALITY_VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function TransformRecordValue ($recordInfo, $field, $value)
	{
		if (!array_key_exists('transform', $recordInfo)) return $value;
		if (!array_key_exists($field, $recordInfo['transform'])) return $value;
		
		$method = $recordInfo['transform'][$field];
		return $this->$method($value);
	}
	
	
	public function WritePageHeader()
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>UESP:ESO Log Data Viewer</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="viewlog.css" />
	<script type="text/javascript" src="viewlog.js"></script>
</head>
<body>
<?php
		return true;
	}
	
	
	public function WritePageFooter()
	{
		?>
</body>
</html>
	<?php
			return true;
	}
	
	
	public function GetRecordCount ($table)
	{
		$query = "SELECT COUNT(*) FROM $table;";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->ReportError("Failed to get record count for $table!");
			return 0;
		}
		
		$row = $result->fetch_row();
		return $row[0];
	}
	
	
	public function DoHomePage ($recordInfo)
	{
?>
	<h1>ESO: Record Types</h1>
The ESO log viewer displays the raw game data for Elder Scrolls Online as collected by the <a href="http://www.uesp.net/wiki/User:Daveh/uespLog_Addon">uespLog add-on</a>. It was created to be a tool for UESP editors and patrollers to
use as part of improving and maintaining <a href="http://www.uesp.net/">UESPWiki</a>. It is not intended to be a user-friendly way to learn about the Elder Scrolls games.
If you do not understand what this information means, or how to use this webpage, then go to <a href="http://www.uesp.net/"><b>UESPWiki</b></a> for user-friendly game information.
	<ul class='elvRecordTypeList'>
<?php
	
		foreach (self::$RECORD_TYPES as $key => $value)
		{
			$query = "record=" . $value['record'];
			$displayName = $value['displayName'];
			
			$output  = "\t\t<li>";
			$output .= "<a href=\"?$query\">$displayName ";
			$output .= "(" . $this->GetRecordCount($value['table']) . " records) </a>";
			$output .= "</li>\n";
			print($output);
		}
?>
	</ul>
<?php
		
		return true;
	}
	
	
	public function OutputTopMenu($recordInfo)
	{
		$output = "<a href='viewlog.php'>Back to Home</a><br />\n";
		
		print($output);
		return true;
	}
	
	
	public function OutputRecordHeader($recordInfo)
	{
		$this->OutputTopMenu($recordInfo);
		
		$displayName = $recordInfo['displayName'];
		$output .= "<h1>ESO: Viewing $displayName</h1>\n";
		
		print($output);
		return true;
	}
	
	
	public function GetSelectQueryJoins ($recordInfo)
	{
		$query = "";
		if ($recordInfo['join'] == '') return $query;
		
		foreach ($recordInfo['join'] as $key => $value)
		{
			$table1 = $recordInfo['table'];
			$table2 = $value['table'];
			$tableId1 = $key;
			$tableId2 = $value['joinField'];
			$query .= "LEFT JOIN $table2 on $table1.$tableId1 = $table2.$tableId2 ";
		}
		
		return $query;
	}
	
	
	public function GetTablesForSelectQuery($recordInfo)
	{
		$tables = $recordInfo['table'] . ".*";
		
		if ($recordInfo['join'] == '') return $tables;
		
		foreach ($recordInfo['join'] as $key => $value)
		{
			$tables .= ', ';
			
			if ($value['fields'] == '')
			{
				$tables .= $value['table'] . ".*";
			}
			else
			{
				$tables .= " {$value['table']}." . implode(", {$value['table']}.", $value['fields']);
			}
		}
		
		return $tables;
	}
	
	
	public function GetSelectQuerySort ($recordInfo)
	{
		$sort = '';
		
		if ($this->recordSort == '' && $recordInfo['sort'] == '') return '';
		
		if ($this->recordSort == '')
			$sort = " ORDER BY {$recordInfo['sort']} ";
		else
			$sort = " ORDER BY {$this->recordSort} ";
		
		if ($this->recordSortOrder != '')
			$sort .= $this->recordSortOrder . ' ';
		elseif ($recordInfo['sortOrder'] != '')
			$sort .= $recordInfo['sortOrder'] . ' ';
		
		return $sort;
	}
	
	
	public function GetSelectQueryFilter ($recordInfo)
	{
		$field = $this->recordFilter;
		$id = $this->recordFilterId;
		$table = $recordInfo['table'];
		
		if ($field == '' || $id == '') return '';
		
		if (!array_key_exists($field, $recordInfo['fields'])) 
		{
			$this->ReportError("Invalid filter field '{$field}' found for table '{$recordInfo['table']}'!");
			return '';
		}
		
		$fieldType = $recordInfo['fields'][$field];
		
		switch ($fieldType)
		{
			case self::FIELD_STRING:
			case self::FIELD_LARGESTRING:
				$filter = " WHERE $table.$field='$id' ";
				break;
			default:
				$filter = " WHERE $table.$field=$id ";
				break;
		}
		
		return $filter;
	}
	
	
	public function CreateFilterLink ($record, $filter, $id, $link)
	{	
		if ($id == '' || $id <= 0) return "";
		
		$output = "<a href='?record={$record}&filter=$filter&filterid=$id'>$link</a>";
		
		return $output;
	}
	
	
	public function CreateFilterLinks ($recordInfo, $recordData)
	{
		$output = "";
		
		if (!array_key_exists('filters', $recordInfo)) return "";
		
		foreach ($recordInfo['filters'] as $key => $value)
		{
			if ($value['type'] == 'filter')
				$output .= $this->CreateFilterLink($value['record'], $value['field'], $recordData[$value['thisField']], $value['displayName']) . " &nbsp; ";
			elseif ($value['type'] == 'viewRecord')
				$output .= $this->GetViewRecordLink($value['record'], $recordData[$value['thisField']], $value['displayName']) . " &nbsp; ";
			else
				$output .= $this->CreateFilterLink($value['record'], $value['field'], $recordData[$value['thisField']], $value['displayName']) . " &nbsp; ";
		}
		
		return $output;
	}
	
	
	public function CreateSelectQuery ($recordInfo)
	{
		$tables = $this->GetTablesForSelectQuery($recordInfo);
		$table = $recordInfo['table'];
		
		$query = "SELECT SQL_CALC_FOUND_ROWS $tables FROM $table ";
		
		$query .= $this->GetSelectQueryJoins($recordInfo);
		$query .= $this->GetSelectQueryFilter($recordInfo);
		$query .= $this->GetSelectQuerySort($recordInfo);
		
		$query .= " LIMIT $this->displayLimit OFFSET $this->displayStart ";
		$query .= ";";
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function CreateSelectQueryID ($recordInfo, $id)
	{
		$tables = $this->GetTablesForSelectQuery($recordInfo);
		$table = $recordInfo['table'];
		
		$query = "SELECT SQL_CALC_FOUND_ROWS $tables FROM $table ";
		
		$query .= $this->GetSelectQueryJoins($recordInfo);
		$query .= " WHERE $table.id=$id";
		//$query .= " ORDER BY {$recordInfo['sort']} ";
		$query .= " LIMIT 1 ";
		$query .= ";";
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function GetRecordFieldHeader ($recordInfo)
	{
		$output  = "\t<tr>\n";
		$output .= "\t\t<th></th>\n";
		
		foreach ($recordInfo['fields'] as $key => $value)
		{
			$sortLink = $this->GetSortRecordLink($key, $key);
			$output .= "\t\t<th>$sortLink</th>\n";
		}
		
		$output .= "\t\t<th></th>\n";
		$output .= "\t</tr>\n";
		
		return $output;
	}
	
	
	public function CreateFieldLink ($recordType, $field, $id, $link)
	{
		$link = "<a href=\"?record=$recordType&field=$field&id=$id&action=view\">$link</a>";
		return $link;
	}
	
	
	public function FormatField ($value, $type, $recordType, $field, $id, $recordInfo)
	{
		$output = "";
		if ($value == null) return "";
		
		switch ($type)
		{
			case self::FIELD_INT:
				$output = $value;
				break;	
			default:
			case self::FIELD_STRING:
				$output = $value;
				break;
			case self::FIELD_LARGESTRING:
				$link = "View (". strlen($value) ." bytes)";
				$output = $this->CreateFieldLink($recordType, $field, $id, $link);
				break;
			case self::FIELD_POSITION:
				$output = $value / self::ELV_POSITION_FACTOR;
				break;
			case self::FIELD_INTPOSITIVE:
				if ((int) $value >= 0) $output = $value;
				break;
			case self::FIELD_INTID:
				if ((int) $value > 0) $output = $value;
				break;
			case self::FIELD_INTTRANSFORM:
				$output = $this->TransformRecordValue($recordInfo, $field, $value);
				break;
			case self::FIELD_INTBOOLEAN:
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "false";
				elseif ($intValue > 0)
					$output = "true";
				
				break;
		}
		
		return $output;
	}
	
	
	public function FormatFieldAll ($value, $type, $recordType, $field, $id, $recordInfo)
	{
		$output = "";
		if ($value == null) return "";
		
		switch ($type)
		{
			case self::FIELD_LARGESTRING:
				$output = "<div class='elvLargeStringView'>$value</div>";
				return $output;
		}
		
		return $this->FormatField($value, $type, $recordType, $field, $id, $recordInfo);
	}
	
	
	public function GetPageQueryString ($ignoreFields)
	{
		$query = "";
		$isFirst = true;
		
		foreach($this->inputParams as $key => $value)
		{
			if (in_array($key, $ignoreFields)) continue;
			
			if (!$isFirst) $query .= "&";
			$query .= "$key=$value";
			$isFirst = false;
		}
		
		return $query;
	}
	
	
	public function GetNextPrevLink ($recordInfo)
	{
		$output = "";
		
		$prevStart = $this->displayStart - $this->displayLimit;
		$nextStart = $this->displayStart + $this->displayLimit;
		if ($prevStart < 0) $prevStart = 0;
		if ($nextStart >= $this->totalRowCount) $nextStart =  $this->totalRowCount - 1;
		if ($nextStart < 0) $nextStart = 0;
		
		$oldQuery = $this->GetPageQueryString(array("start"));
		
		if ($this->displayStart > 0) 
			$output .= "<a href='?start=$prevStart&$oldQuery'>Prev</a> &nbsp; ";
		else
			$output .= "Prev &nbsp; ";
		
		if ($this->displayStart < $nextStart) 
			$output .= "<a href='?start=$nextStart&$oldQuery'>Next</a>";
		else
			$output .= "Next";
		
		$output .= "\n";
		
		return $output;
	}
	
	
	public function GetViewRecordLink ($record, $id, $link)
	{
		if ($id == '' || $id <= 0) return "";
		
		$link = "<a class='elvRecordLink' href='?action=view&record=$record&id=$id'>$link</a>";
		
		return $link;
	}
	
	
	public function GetSortRecordLink ($sortField, $link)
	{
		$oldQuery = $this->GetPageQueryString(array("sort", "sortorder"));
		
		if ($this->recordSortOrder == "DESC")
			$sortOrder = "a";
		elseif ($this->recordSortOrder == "ASC")
			$sortOrder = "d";
		else
			$sortOrder = "a";
		
		$link = "<a href='?sort=$sortField&sortorder=$sortOrder&$oldQuery'>$link</a>";
		return $link;
	}
	
	
	public function PrintRecords ($recordInfo)
	{
		if (!$this->InitDatabase()) return false;
		
		$query = $this->CreateSelectQuery($recordInfo);
		if ($query === false) return $this->reportError("Failed to create record query!");
		
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to retrieve record data!");
		
		$result2 = $this->db->query("SELECT FOUND_ROWS();");
		$rowData = $result2->fetch_row();
		$this->totalRowCount = $rowData[0];
		
		$displayCount = $result->num_rows;
		$startIndex = $this->displayStart + 1;
		$endIndex = $this->displayStart + $this->displayLimit;
		if ($endIndex > $this->totalRowCount) $endIndex = $this->totalRowCount;
		print("Displaying $displayCount of $this->totalRowCount records from $startIndex to $endIndex.\n");
		
		$output = "<br />" . $this->GetNextPrevLink($recordInfo);
		$output .= "<table border='1' cellspacing='0' cellpadding='2'>\n";
		$output .= $this->GetRecordFieldHeader($recordInfo);
		
		$result->data_seek(0);
		
		while ( ($row = $result->fetch_assoc()) )
		{
			$id = $row['id'];
			$output .= "\t<tr>\n";
			$output .= "\t\t<td>". $this->GetViewRecordLink($recordInfo['record'], $id, "View") ."</td>\n";
			
			foreach ($recordInfo['fields'] as $key => $value)
			{
				$output .= "\t\t<td>" . $this->FormatField($row[$key], $value, $recordInfo['record'], $key, $id, $recordInfo) . "</td>\n";
			}
			
			$output .= "\t\t<td>" . $this->CreateFilterLinks($recordInfo, $row) . "</td>\n";
			$output .= "\t</tr>\n";
		}
		
		
		
		$output .= "</table>\n";
		$output .= $this->GetNextPrevLink($recordInfo);
		
		print($output);
	}
	
	
	public function DoRecordDisplay ($recordInfo)
	{
		$this->OutputRecordHeader($recordInfo);
		$this->PrintRecords($recordInfo);
		
		return true;
	}
	
	
	public function DoViewRecord ($recordInfo)
	{
		if ($this->recordField != '') return $this->DoViewRecordField($recordInfo);
		
		$this->OutputTopMenu($recordInfo);
		$displayName = $recordInfo['displayNameSingle'];
		$id = $this->recordID;
		$output  = "<h1>ESO: Viewing $displayName: ID#$id</h1>\n";
		
		if (!$this->InitDatabase()) return false;
		if ($this->recordID < 0) return $this->ReportError("Invalid record ID received!");
		
		$table = $recordInfo['table'];
		
		$query = $this->CreateSelectQueryID($recordInfo, $id);
		
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to retrieve record from database!");
		if ($result->num_rows === 0) return $this->ReportError("Failed to retrieve record from database!");
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		
		$output .= "<table border='1' cellpadding='2' cellspacing='0'>\n";
		
		foreach ($recordInfo['fields'] as $key => $value)
		{
			$rowValue = $this->FormatFieldAll($row[$key], $value, $recordInfo['record'], $key, $row['id'], $recordInfo);
			
			$output .= "\t<tr>\n";
			$output .= "\t\t<th>$key</th>\n";
			$output .= "\t\t<td>$rowValue</td>\n";
			$output .= "\t</tr>\n";
		}
		
		$output .= "</table>\n";
		
		print($output);
		return true;
	}
	
	
	public function DoViewRecordField ($recordInfo)
	{
		$this->OutputTopMenu($recordInfo);
		if (!$this->InitDatabase()) return false;
		
		if ($this->recordID < 0) return $this->ReportError("Invalid record ID received!");
		if ($this->recordField === '') return $this->ReportError("Invalid record field received!");
		
		$fieldType = $recordInfo['fields'][$this->recordField];
		if ($fieldType == null) return $this->ReportError("Invalid record field '$this->recordField' received!");
		
		$table = $recordInfo['table'];
		$id = $this->recordID;
		
		$query = $this->CreateSelectQueryID($recordInfo, $id);
		
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to retrieve record from database!");
		if ($result->num_rows === 0) return $this->ReportError("Failed to retrieve record from database!");
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		
		$displayName = $recordInfo['displayNameSingle'];
		$output  = "<h1>ESO: Viewing $displayName: ID#$id</h1>\n";
		$output .= "<div class='elvRecordView'>";
		$output .= $row[$this->recordField];
		$output .= "</div>";
		
		print($output);
		
		return true;
	}
	
	
	public function Start()
	{
		$this->writeHeaders();
		$this->WritePageHeader();
		
		foreach (self::$RECORD_TYPES as $key => $value)
		{
			if ($this->recordType == $value['record'])
			{
				
				if ($this->action == "view")
				{
					$this->DoViewRecord($value);
					$this->WritePageFooter();
					return true;
				}
				
				$method = $value['method'];
				$this->$method($value);
				$this->WritePageFooter();
				return true;
			}
		}
		
		$this->DoHomePage(null);
		$this->WritePageFooter();
		
		return true;
	}
	
	
	private function parseInputParams ()
	{
		if (array_key_exists('record', $this->inputParams)) $this->recordType = $this->db->real_escape_string($this->inputParams['record']);
		if (array_key_exists('field', $this->inputParams)) $this->recordField = $this->db->real_escape_string($this->inputParams['field']);
		if (array_key_exists('id', $this->inputParams)) $this->recordID = $this->db->real_escape_string($this->inputParams['id']);
		if (array_key_exists('action', $this->inputParams)) $this->action = $this->db->real_escape_string($this->inputParams['action']);
		if (array_key_exists('start', $this->inputParams)) $this->displayStart = (int) $this->inputParams['start'];
		if (array_key_exists('sort', $this->inputParams)) $this->recordSort = $this->db->real_escape_string($this->inputParams['sort']);
		if (array_key_exists('filter', $this->inputParams)) $this->recordFilter = $this->db->real_escape_string($this->inputParams['filter']);
		if (array_key_exists('filterid', $this->inputParams)) $this->recordFilterId = $this->db->real_escape_string($this->inputParams['filterid']);
		
		if (array_key_exists('sortorder', $this->inputParams))
		{
			switch ($this->inputParams['sortorder'])
			{
				default:
				case 'a':
				case 'A':
					$this->recordSortOrder = 'ASC';
					break;
				case 'd':
				case 'D':
					$this->recordSortOrder = 'DESC';
					break;
			}
		}
		
		return true;
	}
	
	
	private function setInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
		
			// Add command line arguments to input parameters for testing
		if ($argv !== null)
		{
			foreach ($argv as $arg)
			{
				$e = explode("=", $arg);
				
				if(count($e) == 2)
					$this->inputParams[$e[0]] = $e[1];
				else
					$this->inputParams[$e[0]] = 0;
			}
		}
	}
	
	
	public function writeHeaders ()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
	}
	
};


$g_EsoLogViewer = new EsoLogViewer();
$g_EsoLogViewer->Start();


?>