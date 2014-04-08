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
	
	public static $FIELD_NAMES = array(
			self::FIELD_INT => "integer",
			self::FIELD_STRING => "string",
	);
	
	public static $BOOK_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'title' => self::FIELD_STRING,
			'body' => self::FIELD_LARGESTRING,
			'icon' => self::FIELD_STRING,
			'isLore' => self::FIELD_INTBOOLEAN,
			'skillIndex' => self::FIELD_INTPOSITIVE,
			'mediumIndex' => self::FIELD_INTPOSITIVE,
			'categoryIndex' => self::FIELD_INTPOSITIVE,
			'collectionIndex' => self::FIELD_INTPOSITIVE,
			'bookIndex' => self::FIELD_INTPOSITIVE,
			'guildIndex' => self::FIELD_INTPOSITIVE,
	);
	
	public static $BOOKLOCATION_FIELDS = array(
			'id' => self::FIELD_INT,
			'bookId' => self::FIELD_INT,
			'title' => self::FIELD_STRING, //Foreign join
			'isLore' => self::FIELD_INTBOOLEAN, //Foreign join
			'logId' => self::FIELD_INT,
			'x' => self::FIELD_POSITION,
			'y' => self::FIELD_POSITION,
			'rawX' => self::FIELD_FLOAT,
			'rawY' => self::FIELD_FLOAT,
			'zone' => self::FIELD_STRING,
	);
	
	
	public static $RECORD_TYPES = array(
			
			'book' => array(
					'displayName' => 'Books',
					'displayNameSingle' => 'Book',
					'record' => 'book',
					'table' => 'book',
					'method' => 'DoBook',
					'sort' => 'title',
					//'fields' => self::$BOOK_FIELDS,
					//'displayFields' => self::BOOK_DISPLAYFIELDS,
			),
			
			'bookLoc' => array (
					'displayName' => 'Book Locations',
					'displayNameSingle' => 'Book Location',
					'record' => 'bookLoc',
					'table' => 'bookLocation',
					'method' => 'DoBookLocation',
					'sort' => 'zone',
					'join' => array(
							'bookId' => array(
									'table' => 'book',
									'fields' => array('title', 'isLore'),
									'joinField' => 'id',
									'displayField' => 'title'
									),
							),
					'link' => array(
							array(
								'field' => 'title',
								'linkRecord' => 'book',
								'linkField' => 'bookId',
							),
					),
					
					//'fields' => self::$BOOKLOCATION_FIELDS,
					//'displayFields' => self::BOOKLOCATION_DISPLAYFIELDS,
			),
	);
	

	
	public function __construct ()
	{
			// TODO: Static initializaiton?
		self::$RECORD_TYPES['book']['fields'] = self::$BOOK_FIELDS;
		self::$RECORD_TYPES['bookLoc']['fields'] = self::$BOOKLOCATION_FIELDS;
		
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
	
	
	public function DoHomePage ($recordInfo)
	{
?>
	<h1>Record Types</h1>
	<ul>
<?php
	
		foreach (self::$RECORD_TYPES as $key => $value)
		{
			$query = "record=" . $value['record'];
			$displayName = $value['displayName'];
			
			$output  = "\t\t<li>";
			$output .= "<a href=\"?$query\">$displayName</a>";
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
		$output .= "<h1>Viewing $displayName</h1>\n";
		
		print($output);
		return true;
	}
	
	
	public function AddSelectQueryJoins($recordInfo)
	{
		$query = "";
		if ($recordInfo['join'] == '') return $query;
		
		foreach ($recordInfo['join'] as $key => $value)
		{
			$table1 = $recordInfo['table'];
			$table2 = $value['table'];
			$tableId1 = $key;
			$tableId2 = $value['joinField'];
			$query .= "INNER JOIN $table2 on $table1.$tableId1 = $table2.$tableId2 ";
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
	
	
	public function CreateSelectQuery ($recordInfo)
	{
		$tables = $this->GetTablesForSelectQuery($recordInfo);
		$table = $recordInfo['table'];
		
		$query = "SELECT SQL_CALC_FOUND_ROWS $tables FROM $table ";
		
		if ($recordInfo['join'] != '') $query .= $this->AddSelectQueryJoins($recordInfo);
		if ($recordInfo['sort'] != '') $query .= " ORDER BY {$recordInfo['sort']} ";
		
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
		
		if ($recordInfo['join'] != '') $query .= $this->AddSelectQueryJoins($recordInfo);
		$query .= " WHERE $table.id=$id";
		if ($recordInfo['sort'] != '') $query .= " ORDER BY {$recordInfo['sort']} ";
		
		$query .= " LIMIT 1 ";
		$query .= ";";
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function PrintRecordFieldHeader ($recordInfo)
	{
		print("\t<tr>\n");
		print("\t\t<th></th>\n");
		
		foreach ($recordInfo['fields'] as $key => $value)
		{
			print("\t\t<th>$key</th>\n");
		}
		
		print("\t</tr>\n");
	}
	
	
	public function CreateFieldLink($recordType, $field, $id, $link)
	{
		$link = "<a href=\"?record=$recordType&field=$field&id=$id&action=view\">$link</a>";
		return $link;
	}
	
	
	public function FormatField ($value, $type, $recordType, $field, $id)
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
	
	
	public function FormatFieldAll ($value, $type, $recordType, $field, $id)
	{
		$output = "";
		if ($value == null) return "";
		
		switch ($type)
		{
			case self::FIELD_LARGESTRING:
				$output = "<div class='elvLargeStringView'>$value</div>";
				return $output;
		}
		
		return $this->FormatField($value, $type, $recordType, $field, $id);
	}
	
	
	public function getPageQueryString($includeStart)
	{
		$query = "";
		$isFirst = true;
		
		foreach($this->inputParams as $key => $value)
		{
			if (!$includeStart && $key == 'start') continue;
			
			if (!$isFirst) $query .= "&";
			$query .= "$key=$value";
			$isFirst = false;
		}
		
		return $query;
	}
	
	
	public function PrintNextPrevLink ($recordInfo)
	{
		$output = "";
		
		$prevStart = $this->displayStart - $this->displayLimit;
		$nextStart = $this->displayStart + $this->displayLimit;
		if ($prevStart < 0) $prevStart = 0;
		if ($nextStart >= $this->totalRowCount) $nextStart =  $this->totalRowCount - 1;
		if ($nextStart < 0) $nextStart = 0;
		
		$oldQuery = $this->getPageQueryString(false);
		
		if ($this->displayStart > 0) $output .= "<a href='?start=$prevStart&$oldQuery'>Prev</a> &nbsp; ";
		if ($this->displayStart < $nextStart) $output .= "<a href='?start=$nextStart&$oldQuery'>Next</a>";
		$output .= "\n";
		
		print($output);
		return true;
	}
	
	
	public function GetViewRecordLink ($record, $id, $link)
	{
		$link = "<a class='elvRecordLink' href='?action=view&record=$record&id=$id'>$link</a>";
		
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
		$rows = $result2->fetch_row();
		$this->totalRowCount = $rows[0];
		
		$displayCount = $result->num_rows;
		$startIndex = $this->displayStart + 1;
		$endIndex = $this->displayStart + $this->displayLimit;
		if ($endIndex > $this->totalRowCount) $endIndex = $this->totalRowCount;
		print("Displaying $displayCount of $this->totalRowCount records from $startIndex to $endIndex.\n");
		
		$this->PrintNextPrevLink($recordInfo);
		
		$result->data_seek(0);
		print("<table border='1' cellspacing='0' cellpadding='2'>\n");
		$this->PrintRecordFieldHeader($recordInfo);
		
		while ( ($row = $result->fetch_assoc()) )
		{
			$id = $row['id'];
			print("\t<tr>\n");
			print("\t\t<td>". $this->GetViewRecordLink($recordInfo['record'], $id, "View") ."</td>\n");
			
			foreach ($recordInfo['fields'] as $key => $value)
			{
				$output = $this->FormatField($row[$key], $value, $recordInfo['record'], $key, $id);
				print("\t\t<td>$output</td>\n");
			}
			
			print("\t</tr>\n");
		}
		
		print("</table>\n");
		$this->PrintNextPrevLink($recordInfo);
	}
	
	
	public function DoBook ($recordInfo)
	{
		$this->OutputRecordHeader($recordInfo);
		$this->PrintRecords($recordInfo);
		
		return true;
	}
	
	
	public function DoBookLocation ($recordInfo)
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
		$output  = "<h1>Viewing $displayName: ID#$id</h1>\n";
		
		if (!$this->InitDatabase()) return false;
		if ($this->recordID < 0) return $this->ReportError("Invalid record ID received!");
		
		$table = $recordInfo['table'];
		
		$query = $this->CreateSelectQueryID($recordInfo, $id);
		//$query = "SELECT * FROM $table WHERE id=$id LIMIT 1;";
		
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to retrieve record from database!");
		if ($result->num_rows === 0) return $this->ReportError("Failed to retrieve record from database!");
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		
		$output .= "<table border='1' cellpadding='2' cellspacing='0'>\n";
		
		foreach ($recordInfo['fields'] as $key => $value)
		{
			$rowValue = $this->FormatFieldAll($row[$key], $value, $recordInfo['record'], $key, $row['id']);
			
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
		//$query = "SELECT * FROM $table WHERE id=$id LIMIT 1;";
		
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to retrieve record from database!");
		if ($result->num_rows === 0) return $this->ReportError("Failed to retrieve record from database!");
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		
		$displayName = $recordInfo['displayNameSingle'];
		$output  = "<h1>Viewing $displayName: ID#$id</h1>\n";
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
		if (array_key_exists('record', $this->inputParams)) $this->recordType = $this->inputParams['record'];
		if (array_key_exists('field', $this->inputParams)) $this->recordField = $this->inputParams['field'];
		if (array_key_exists('id', $this->inputParams)) $this->recordID = $this->inputParams['id'];
		if (array_key_exists('action', $this->inputParams)) $this->action = $this->inputParams['action'];
		if (array_key_exists('start', $this->inputParams)) $this->displayStart = $this->inputParams['start'];
		
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