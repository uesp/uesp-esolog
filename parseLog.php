<?php

/*
	SKILL_TYPE_NONE = 0
	SKILL_TYPE_CLASS = 1
	SKILL_TYPE_WEAPON = 2
	SKILL_TYPE_ARMOR = 3
	SKILL_TYPE_WORLD = 4
	SKILL_TYPE_GUILD = 5
	SKILL_TYPE_AVA = 6
	SKILL_TYPE_RACIAL = 7
	SKILL_TYPE_TRADESKILL = 8
	
	1 , 1, Aedric Spear
	1 , 2, Dawn's Wrath
	1 , 3, Restoring Light
	2 , 1, Two Handed
	2 , 2, One Hand and Shield
	2 , 3, Dual Wield
	2 , 4, Bow
	2 , 5, Destruction Staff
	2 , 6, Restoration Staff
	3 , 1, Light Armor
	3 , 2, Medium Armor
	3 , 3, Heavy Armor
	4 , 1, Soul Magic
	5 , 1, Fighters  Guild
	5 , 2, Mages Guild
	6 , 1, Assault
	6 , 2, Support
	7 , 1, Breton Skills
	8 , 1, Alchemy
	8 , 2, Blacksmithing
	8 , 3, Clothing
	8 , 4, Enchanting
	8 , 5, Provisioning
	8 , 6, Woodworking

 */

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");


class EsoLogParser
{
	const ELP_INPUT_LOG_PATH = "log/";
	const ELP_INDEX_FILENAME = "log/esolog.index";
	const ELP_OUTPUTLOG_FILENAME = "log/parser.log";
	
	const ELP_POSITION_FACTOR = 1000;	// Converts floating point position in log to integer value for db
	
	public $db = null;
	private $dbReadInitialized  = false;
	private $dbWriteInitialized = false;
	public $lastQuery = "";
	
	public $currentLogFilename = "tmp.log";
	public $currentLogIndex = 1;
	public $rawLogData = array();
	
	public $currentParseLine = 0;
	public $currentParseFile = "";
	
	public $duplicateCount = 0;
	public $skipDuplicates = false;
	
	public $lastValidTime = array();
	
	public $users = array();
	public $ipAddresses = array();
	
	public $currentLogEntryId = -1;
	public $currentUser = null;
	public $currentIpAddress = null;
	
	public $lastBookRecord = null;
	public $lastBookLogEntry = null;
	
	public $skillInfo = array();
		//index
		//type
		//name
	
	const FIELD_INT = 1;
	const FIELD_STRING = 2;
	const FIELD_FLOAT = 3;
	
	public static $FIELD_NAMES = array(
			self::FIELD_INT => "integer",
			self::FIELD_STRING => "string",
			self::FIELD_FLOAT => "string",
	);
	
	public static $BOOK_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'title' => self::FIELD_STRING,
			'body' => self::FIELD_STRING,
			'icon' => self::FIELD_STRING,
			'isLore' => self::FIELD_INT,
			'skill' => self::FIELD_STRING,
			'mediumIndex' => self::FIELD_INT,
			'categoryIndex' => self::FIELD_INT,
			'collectionIndex' => self::FIELD_INT,
			'bookIndex' => self::FIELD_INT,
			'guildIndex' => self::FIELD_INT,
	);
	
	public static $BOOKLOCATION_FIELDS = array(
			'id' => self::FIELD_INT,
			'bookId' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'x' => self::FIELD_INT,
			'y' => self::FIELD_INT,
			'rawX' => self::FIELD_FLOAT,
			'rawY' => self::FIELD_FLOAT,
			'zone' => self::FIELD_STRING,
	);
	
	
	public function createNewRecord ($fieldDef)
	{
		$newRecord = array();
		
		foreach ($fieldDef as $key => $value)
		{
			if ($value == self::FIELD_INT)
			{
				$newRecord[$key] = -1;
			}
			elseif ($value == self::FIELD_FLOAT)
			{
				$newRecord[$key] = -1;
			}
			elseif ($value == self::FIELD_STRING)
			{
				$newRecord[$key] = '';
			}
		}
		
		$newRecord['logId'] = $this->currentLogEntryId;
		$newRecord['__isNew'] = true;
		$newRecord['__dirty'] = true;
		
		return $newRecord;
	}
	
	
	public function createNewRecordID ($id, $idField, $fieldDef)
	{
		$record = $this->createNewRecord($fieldDef);
		$record[$idField] = $id;
		
		return $record;
	}
	
	
	public function createSelectQuery ($table, $idField, $id, $fieldDef)
	{
		$idType = $fieldDef[$idField];
		if ($idType == null) return $this->reportError("Unknown ID field $idField in $table table!");
		
		if ($idType == self::FIELD_INT)
			$query = "SELECT * FROM $table WHERE $idField=$id LIMIT 1;";
		elseif ($idType == self::FIELD_STRING)
			$query = "SELECT * FROM $table WHERE $idField='". $this->db->real_escape_string($id) ."' LIMIT 1;";
		else
			return $this->reportError("Unknown ID type $idType in $table table!");
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function createRecordFromRow ($row, $fieldDef)
	{
		$record = array();
		
		foreach ($fieldDef as $key => $value)
		{
			if (array_key_exists($key, $row))
			{
				$record[$key] = $row[$key];
		
				settype($record[$key], self::$FIELD_NAMES[$value]);
			}
			elseif ($value == self::FIELD_INT)
			{
				$record[$key] = -1;
			}
			elseif ($value == self::FIELD_FLOAT)
			{
				$record[$key] = -1;
			}
			elseif ($value == self::FIELD_STRING)
			{
				$record[$key] = '';
			}
		}
		
		$record['__isNew'] = false;
		$record['__dirty'] = false;
		return $record;
	}
	
	
	public function loadRecord ($table, $idField, $id, $fieldDef)
	{
		$query = $this->createSelectQuery($table, $idField, $id, $fieldDef);
		if ($query === false) return false;
		
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to load record $id from $table table!");
		
		if ($result->num_rows === 0) return $this->createNewRecordID($idField, $id, $fieldDef);
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		
		return $this->createRecordFromRow($row, $fieldDef);
	}
	
	
	public function createUpdateQuery ($table, $record, $idField, $fieldDef)
	{
		$idType = $fieldDef[$idField];
		if ($idType == null) return $this->reportError("Unknown ID field $idField in $table table!");
		
		$id = $record[$idField];
		if ($id == null) return $this->reportError("$table record missing ID field $idField value!");
		
		$query = "UPDATE $table SET ";
		$isFirst = true;
		
		foreach ($fieldDef as $key => $value)
		{
			if ($key === $idField) continue;
			if ($key === 'id') continue;
			
			if (!array_key_exists($key, $record))
			{
				$this->reportError("Missing value for $key field in $table table!");
				continue;
			}
			
			if (!$isFirst) $query .= ', ';
			
			if ($value == self::FIELD_INT)
				$query .= "{$key}={$record[$key]}";
			elseif ($value == self::FIELD_FLOAT)
				$query .= "{$key}={$record[$key]}";
			elseif ($value == self::FIELD_STRING)
				$query .= "{$key}='". $this->db->real_escape_string($record[$key]) ."'";
			else
				$this->reportError("Unknown ID type $value found for $key field in $table table!");
			
			$isFirst = false;
		}
		
		if ($idType == self::FIELD_INT)
			$query .= " WHERE $idField=$id;";
		elseif ($idType == self::FIELD_FLOAT)
			$query .= " WHERE $idField=$id;";
		elseif ($idType == self::FIELD_STRING)
			$query .= " WHERE $idField='". $this->db->real_escape_string($id) ."';";
		else
			return $this->reportError("Unknown ID type $idType in $table table!");
	
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function AddSkillInfo ($index, $name, $type)
	{
		if (!array_key_exists($type, $this->skillInfo)) $this->skillInfo[$type] = array();
		if (array_key_exists($index, $this->skillInfo[$type])) return true;
		
		$this->skillInfo[$type][$index] = $name;
		
		return true;
	}
	
	
	public function DumpSkillInfo ()
	{
		foreach ($this->skillInfo as $type => $value)
		{
			foreach ($value as $index => $name)
			{
				print("$type , $index, $name\n");
			}
		}
	}
	
	
	public function createInsertQuery ($table, $record, $fieldDef)
	{
		$columns = "";
		$values = "";
		$isFirst = true;
		
		foreach ($fieldDef as $key => $value)
		{
			if ($key === 'id') continue;
			
			if (!array_key_exists($key, $record))
			{
				$this->reportError("Missing value for $key field in $table table!");
				continue;
			}
				
			if (!$isFirst)
			{
				$columns .= ', ';
				$values  .= ', ';
			}
			
			$columns .= $key;
				
			if ($value == self::FIELD_INT)
				$values .= $record[$key];
			elseif ($value == self::FIELD_FLOAT)
				$values .= $record[$key];
			elseif ($value == self::FIELD_STRING)
				$values .= "'". $this->db->real_escape_string($record[$key]) ."'";
			else
				$this->reportError("Unknown ID type $value found for $key field in $table table!");
			
			$isFirst = false;
		}
		
		$query = "INSERT INTO $table($columns) VALUES($values);";
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function saveRecord ($table, &$record, $idField, $fieldDef)
	{
		if ($record['__isNew'])
			$query = $this->createInsertQuery($table, $record, $fieldDef);
		else
			$query = $this->createUpdateQuery($table, $record, $idField, $fieldDef);
		
		if ($query === false) return false;
		
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to save record {$record[$idField]} to {$table} table!");
		
		if ($record['__isNew']) $record['id'] = $this->db->insert_id;
		$record['__isNew'] = false;
		$record['__dirty'] = false;
		
		return true;
	}
	
	
	public function loadBook ($bookTitle)
	{
		$book = $this->loadRecord('book', 'title', $bookTitle, self::$BOOK_FIELDS);
		if ($book === false) return false;
		
		return $book;
	}
	
	
	public function saveBook (&$book)
	{
		return $this->saveRecord('book', $book, 'id', self::$BOOK_FIELDS);
	}
	
	
	public function __construct ()
	{
		$this->readIndexFile();
		$this->currentLogFilename = $this->getCurrentLogFilename();
		$this->setInputParams();
	}
	
	
	private function createTables()
	{
		$result = $this->initDatabaseWrite();
		if (!$result) return false;
		
		$query = "CREATE TABLE IF NOT EXISTS logEntry (
						id BIGINT NOT NULL AUTO_INCREMENT,
						gameTime INTEGER NOT NULL,
						timeStamp BIGINT NOT NULL,
						entryHash BIGINT NOT NULL,
						userName TINYTEXT NOT NULL,
						ipAddress TINYTEXT NOT NULL,
						PRIMARY KEY (id),
						INDEX unique_entry (gameTime, timeStamp, entryHash)
					);";
		
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create logEntry table!");
		
		$query = "CREATE TABLE IF NOT EXISTS user (
						name TINYTEXT NOT NULL,
						entryCount INTEGER NOT NULL,
						errorCount INTEGER NOT NULL,
						duplicateCount INTEGER NOT NULL,
						newCount INTEGER NOT NULL,
						enabled TINYINT NOT NULL DEFAULT 1,
						PRIMARY KEY (name(64))
					);";
		
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create user table!");
		
		$query = "CREATE TABLE IF NOT EXISTS ipAddress (
						ipaddress TINYTEXT NOT NULL,
						enabled TINYINT NOT NULL DEFAULT 1,
						PRIMARY KEY (ipaddress(64))
					);";
		
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create ipAddress table!");
		
		$query = "CREATE TABLE IF NOT EXISTS book (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						title TINYTEXT NOT NULL,
						body TEXT NOT NULL,
						skill TINYTEXT NOT NULL,
						mediumIndex INTEGER NOT NULL,
						isLore INTEGER NOT NULL,
						icon TEXT NOT NULL,
						categoryIndex INTEGER NOT NULL,
						collectionIndex INTEGER NOT NULL,
						bookIndex INTEGER NOT NULL,
						guildIndex INTEGER NOT NULL,
						PRIMARY KEY (id)
					);";
		
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create book table!");
		
		$query = "CREATE TABLE IF NOT EXISTS bookLocation (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						bookId BIGINT NOT NULL,
						x INTEGER NOT NULL,
						y INTEGER NOT NULL,
						rawX FLOAT NOT NULL,
						rawY FLOAT NOT NULL,
						zone TINYTEXT NOT NULL,
						PRIMARY KEY (id),
						INDEX find_bookloc (bookId, zone(64), x, y)
					);";
		
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create bookLocation table!");
		
		return true;
	}
	
	
	public function addNewUserRecord ($userName)
	{
		print("Adding new user $userName...\n");
		
		$safeName = $this->db->real_escape_string($userName);
		
		$query = "INSERT INTO user(name) VALUES('{$safeName}');";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		
		if ($result === FALSE)
		{
			$this->reportError("Failed to add user '{$userName}'!");
			return null;
		}
		
		$this->users[$userName] = array();
		$this->users[$userName]['name'] = $userName;
		$this->users[$userName]['entryCount'] = 0;
		$this->users[$userName]['errorCount'] = 0;
		$this->users[$userName]['duplicateCount'] = 0;
		$this->users[$userName]['newCount'] = 0;
		$this->users[$userName]['enabled'] = true;
		$this->users[$userName]['__dirty'] = false;
		
		return $this->users[$userName];
	}
	
	
	public function addNewIPAddressRecord ($ipAddress)
	{
		$safeIP = $this->db->real_escape_string($ipAddress);
	
		$query = "INSERT INTO ipAddress(ipAddress) VALUES('{$safeIP}');";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
	
		if ($result === FALSE)
		{
			$this->reportError("Failed to add IP Address '{$ipAddress}'!");
			return null;
		}
		
		$this->ipAddresses[$ipAddress] = array();
		$this->ipAddresses[$ipAddress]['ipAddress'] = $ipAddress;
		$this->ipAddresses[$ipAddress]['enabled'] = true;
		$this->ipAddresses[$ipAddress]['__dirty'] = false;
	
		return $this->ipAddresses[$ipAddress];
	}
	
	
	public function &getUserRecord ($userName)
	{
		//print ("Getting data for user $userName...\n");
		if (array_key_exists($userName, $this->users)) return $this->users[$userName];
		
		$safeName = $this->db->real_escape_string($userName);
		
		$query = "SELECT * FROM user WHERE name='{$safeName}' LIMIT 1;";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		
		if ($result === FALSE)
		{
			$this->reportError("Failed to get data for user '{$userName}'!");
			return null;
		}
		
		if ($result->num_rows === 0) return $this->addNewUserRecord($userName);
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		settype($row['enabled'], "integer");
		settype($row['entryCount'], "integer");
		settype($row['errorCount'], "integer");
		settype($row['duplicateCount'], "integer");
		settype($row['newCount'], "integer");
		
		$this->users[$userName] = array();
		$this->users[$userName]['name'] = $userName;
		$this->users[$userName]['entryCount'] = $row['entryCount'];
		$this->users[$userName]['errorCount'] = $row['errorCount'];
		$this->users[$userName]['duplicateCount'] = $row['duplicateCount'];
		$this->users[$userName]['newCount'] = $row['newCount'];
		$this->users[$userName]['enabled'] = ($row['enabled'] != 0);
		$this->users[$userName]['__dirty'] = false;
		
		return $this->users[$userName];
	}
	
	
	public function &getIPAddressRecord ($ipAddress)
	{
		if (array_key_exists($ipAddress, $this->ipAddresses)) return $this->ipAddresses[$ipAddress];
	
		$safeIP = $this->db->real_escape_string($ipAddress);
	
		$query = "SELECT * FROM ipAddress WHERE ipAddress='{$safeIP}' LIMIT 1;";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
	
		if ($result === FALSE)
		{
			$this->reportError("Failed to get data for ipAddress '{$ipAddress}'!");
			return null;
		}
	
		if ($result->num_rows === 0) return $this->addNewIPAddressRecord($ipAddress);
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		settype($row['enabled'], "integer");
		
		$this->ipAddresses[$ipAddress] = array();
		$this->ipAddresses[$ipAddress]['ipAddress'] = $ipAddress;
		$this->ipAddresses[$ipAddress]['enabled'] = ($row['enabled'] != 0);
		$this->ipAddresses[$ipAddress]['__dirty'] = false;
			
		return $this->ipAddresses[$ipAddress];
	}
	
	
	public function saveData ()
	{
		$result = true;
		
		$result &= $this->saveUsers();
		$result &= $this->saveIPAddresses();
		
		return $result;
	}
	
	
	public function saveUsers ()
	{
		$result = true;
		print("Saving users...\n");
		
		foreach ($this->users as $key => $value)
		{
			
			if ($value['__dirty'] === true)
			{
				$result &= $this->saveUser($value);
			}
		}
		
		return $result;
	}
	
	
	public function saveIPAddresses ()
	{
		$result = true;
		
		foreach ($this->ipAddresses as $key => $value)
		{
			if ($value['__dirty'] === true)
			{
				$result &= $this->saveIPAddress($value);
			}
		}
		
		return $result;
	}
	
	
	public function saveUser ($user)
	{
		print("\tSaving user {$user['name']}...\n");
		
		$safeName = $this->db->real_escape_string($user['name']);
		
		$query = "UPDATE user SET entryCount={$user['entryCount']}, newCount={$user['newCount']}, errorCount={$user['errorCount']}, duplicateCount={$user['duplicateCount']} WHERE name='{$safeName}';";
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to save user '{$safeName}'!");
		
		return true;
	}
	
	
	public function saveIPAddress ($ipAddress)
	{
		$safeName = $this->db->real_escape_string($ipAddress['ipAddress']);
		
		$enabled = $ipAddress['enabled'] ? 1 : 0;
		
		$query = "UPDATE ipAddress SET enabled={$enabled} WHERE ipAddress='{$safeName}';";
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to save IP address '{$safeName}'!");
		
		return true;
	}
	
	
	public function hasLogUniqueTime ($gameTime, $timeStamp, $entryHash)
	{
		$query = "SELECT * FROM logEntry WHERE gameTime={$gameTime} AND timeStamp={$timeStamp} AND entryHash={$entryHash};";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		
		if ($result === false) return $this->reportLogParseError("Failed to check logEntry table!");
		return ($result->num_rows > 0);
	}
	
	
	public function isDuplicateLogEntry ($logEntry)
	{
		return $this->hasLogUniqueTime($logEntry['gameTime'], $logEntry['timeStamp'], $logEntry['__crc']);
	}
	
	
	public function isValidLogEntry ($logEntry)
	{
		static $VALID_FIELDS = array(
				"event", "gameTime", "timeStamp", "userName", "ipAddress"
		);
		
		if ($logEntry === null) return $this->reportLogParseError("NULL log entry received!");
		
		foreach ($VALID_FIELDS as $key => $field)
		{
			if (!array_key_exists($field, $logEntry)) return $this->reportLogParseError("Missing $field in log entry!");
			if ($logEntry[$field] == '') return $this->reportLogParseError("Found empty $field in log entry!");
		}
		
		return true;
	}
	
	
	public function addLogEntryRecord ($gameTime, $timeStamp, $entryHash, $userName, $ipAddress)
	{
		$safeName = $this->db->real_escape_string($userName);
		$safeIp = $this->db->real_escape_string($ipAddress);
	
		$query = "INSERT INTO logEntry(gameTime, timeStamp, entryHash, userName, ipAddress) VALUES($gameTime, $timeStamp, $entryHash, '$safeName', '$safeIp');";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create logEntry record!");
	
		return $this->db->insert_id;
	}
	
	
	public function addLogEntryRecordFromLog ($logEntry)
	{
		return $this->addLogEntryRecord($logEntry['gameTime'], $logEntry['timeStamp'], $logEntry['__crc'], $logEntry['userName'], $logEntry['ipAddress']);
	}
	
	
	private function initDatabase ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		if ($this->dbReadInitialized || $this->dbWriteInitialized) return true;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($db->connect_error) return $this->reportError("Could not connect to mysql database!");
	
		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = false;
	
		if ($this->skipCheckTables) return true;
		return $this->checkTables();
	}
	
	
	private function initDatabaseWrite ()
	{
		global $uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase;
	
		if ($this->dbWriteInitialized) return true;
	
		if ($this->dbReadInitialized)
		{
			$this->db->close();
			unset($this->db);
			$this->db = null;
			$this->dbReadInitialized = false;
		}
	
		$this->db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($db->connect_error) return $this->reportError("Could not connect to mysql database!");
	
		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = true;
	
		//if ($this->skipCheckTables) return true;
		//return $this->checkTables();
		return true;
	}
	
	
	public function getCurrentLogFilename()
	{
		return $this->generateLogFilename($this->currentLogIndex);
	}
	
	
	public function generateLogFilename($index)
	{
		$logFilename = self::ELP_INPUT_LOG_PATH . sprintf( 'eso%05d.log', $index);
		return $logFilename;
	}
	
	
	public function parseFormInput()
	{
		return true;
	}
	
	
	public function OnLootGainedEntry($logEntry)
	{
		return true;
	}
	
	
	public function OnSlotUpdateEntry($logEntry)
	{
		return true;
	}
	
	
	public function FindBookLocation ($x, $y, $zone, $bookId)
	{
		$safeZone = $this->db->real_escape_string($zone);
		$query = "SELECT * FROM bookLocation WHERE bookId=$bookId AND zone='$safeZone' AND x=$x AND y=$y;";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to retrieve book locations!");
		
		return ($result->num_rows > 0);
	}
	
	
	public function CheckBookLocation ($logEntry, $bookRecord)
	{
		$id = $bookRecord['id'];
		if ($id == null || $id < 0) return $this->reportLogParseError("Invalid internal ID found for book!");
		
		$x = (int) ($logEntry['x'] * self::ELP_POSITION_FACTOR);
		$y = (int) ($logEntry['y'] * self::ELP_POSITION_FACTOR);
		$zone = $logEntry['zone'];
		
		if ($this->FindBookLocation($x, $y, $zone, $id)) return true;
		
		$bookLocRecord = $this->createNewRecord(self::$BOOKLOCATION_FIELDS);
		
		$bookLocRecord['x'] = $x;
		$bookLocRecord['y'] = $y; 
		$bookLocRecord['rawX'] = $logEntry['x'];
		$bookLocRecord['rawY'] = $logEntry['y'];
		$bookLocRecord['zone'] = $zone;
		$bookLocRecord['bookId'] = $id;
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		return $this->saveRecord('bookLocation', $bookLocRecord, 'id', self::$BOOKLOCATION_FIELDS);
	}
	
	
	public function OnShowBook ($logEntry)
	{
		//event{ShowBook}  medium{3}  body{...} bookTitle{Jornibret's Last Dance}  y{0.60519206523895}  x{0.689866065979}  zone{Daggerfall}
		//gameTime{3748234}  timeStamp{4743642811914518528}  userName{Reorx}  ipAddress{72.39.63.156}  logTime{1396192529}  end{}
		
		$bookTitle = $logEntry['bookTitle'];
		print("\tShowBook: $bookTitle\n");
		
		$body = $logEntry['body'];
		$medium = (int) $logEntry['medium'];
		
		if ($bookTitle == null) return $this->reportLogParseError("Missing book title!");
		
		$bookRecord = $this->loadBook($bookTitle);
		if ($bookRecord === false) return false;
		
		if ($bookRecord['__isNew'] === true)
		{
			$bookRecord['title'] = $bookTitle;
			$bookRecord['body'] = $body;
			$bookRecord['mediumIndex'] = $medium;
			$bookRecord['isLore'] = 0;
			$bookRecord['__dirty'] = true;
			
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		elseif ($bookRecord['mediumIndex'] < 0 || $bookRecord['body'] == '')
		{
			$bookRecord['body'] = $body;
			$bookRecord['mediumIndex'] = $medium;
			$bookRecord['__dirty'] = true;
			
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		
		if ($bookRecord['__dirty']) $result &= $this->saveBook($bookRecord);
		$result = $this->CheckBookLocation($logEntry, $bookRecord);
		
		$this->lastBookRecord = $bookRecord;
		$this->lastBookLogEntry = $logEntry;
		return $result;
	}
	
	
	public function OnLoreBook ($logEntry)
	{
		//event{LoreBook}  icon{/esoui/art/icons/icon_missing.dds}  guild{0}  collection{1}  known{true}  index{18}  category{2}
		//bookTitle{A Clothier's Primer}  y{0.52298730611801}  x{0.5053853392601}  zone{Port Hunding}
		//gameTime{11874643}  timeStamp{4743642846001627136}  userName{Reorx}  ipAddress{72.39.63.156}  logTime{1396193303}  end{}
		
		$bookTitle = $logEntry['bookTitle'];
		print("\tLoreBook: $bookTitle\n");
		
		if ($bookTitle == null) return $this->reportLogParseError("Missing book title!");
	
		$bookRecord = $this->loadBook($bookTitle);
		if ($bookRecord === false) return false;
		
		if ($bookRecord['__isNew'] === true)
		{
			$bookRecord['title'] = $bookTitle;
			$bookRecord['icon'] = $logEntry['icon'];
			$bookRecord['collectionIndex'] = $logEntry['collection'];
			$bookRecord['bookIndex'] = $logEntry['index'];
			$bookRecord['categoryIndex'] = $logEntry['category'];
			$bookRecord['guildIndex'] = $logEntry['guild'];
			$bookRecord['isLore'] = 1;
			$bookRecord['__dirty'] = true;
				
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		
		if ($bookRecord['__dirty']) $result &= $this->saveBook($bookRecord);
		$result = $this->CheckBookLocation($logEntry, $bookRecord);
		
		$this->lastBookRecord = $bookRecord;
		$this->lastBookLogEntry = $logEntry;
		return $result;
	}
	
	
	public function OnSkillRankUpdate ($logEntry)
	{
		//event{SkillRankUpdate}  rank{2}  skillType{8}  name{Alchemy}  skillIndex{1}  
		//x{0.21715186536312}  y{0.46380305290222}  zone{Vulkhel Guard}  
		//gameTime{2610340}  timeStamp{4743643324668182528}  userName{...}  ipAddress{...}  logTime{1396487021}  end{}
		
		$this->AddSkillInfo($logEntry['skillIndex'], $logEntry['name'], $logEntry['skillType']);
		
		if ($this->lastBookRecord == null || $this->lastBookLogEntry == null) return true;
		if ($logEntry['userName'] != $this->lastBookLogEntry['userName']) return true;
		
		$skillGameTime = (int) $logEntry['gameTime'];
		$bookGameTime  = $this->lastBookLogEntry['gameTime'];
		$diffTime = $skillGameTime - $bookGameTime;
		if ($diffTime < 0 || $diffTime > 1000) return true;
		
		if ($this->lastBookRecord['skill'] == '')
		{
			print("\t\tFound {$logEntry['name']} skill update for book {$this->lastBookRecord['title']}...\n");
			$this->lastBookRecord['skill'] = $logEntry['name'];
			$this->saveBook($this->lastBookRecord);
		}
		
		return true;
	}
	
	
	public function OnNullEntry($logEntry)
	{
		// Do Nothing
		return true;
	}
	
	
	public function OnUnknownEntry($logEntry)
	{
		$this->reportLogParseError("Unknown event '{$logEntry['event']}' found in log entry!");
		return true;
	}
	
	
	public function handleLogEntry($logEntry)
	{
		if (!$this->isValidLogEntry($logEntry)) return false;
		
		$logId = $this->addLogEntryRecordFromLog($logEntry);
		if ($logId === false) return false;
		$this->currentLogEntryId = $logId;
		
		$user = &$this->getUserRecord($logEntry['userName']);
		$ipAddress = &$this->getIPAddressRecord($logEntry['ipAddress']);
		$this->currentUser = &$user;
		$this->currentIpAddress = &$ipAddress;
		
		if ($user == null) return $this->reportLogParseError("Invalid user found!");
		if ($ipAddress == null) return $this->reportLogParseError("Invalid ipAddress found!");
		if ($user['enabled'] === false) return $this->reportLogParseError("User is disabled...skipping entry!");
		if ($ipAddress['enabled'] === false) return $this->reportLogParseError("IP address is disabled...skipping entry!");
		
		$isDuplicate = $this->isDuplicateLogEntry($logEntry);
		
		if ($this->skipDuplicates && $isDuplicate)
		{
			$this->log("{$this->currentParseLine}: Skipping duplicate log entry ({$logEntry['gameTime']}, {$logEntry['timeStamp']}, {$logEntry['__crc']})...");
			++$this->duplicateCount;
			++$user['duplicateCount'];
			$user['__dirty'] = true;
			return true;
		}
		else if (!$isDuplicate)
		{
		}
		
		++$user['entryCount'];
		$user['__dirty'] = true;
		
		switch($logEntry['event'])
		{
			case "LootGained":					$result = $this->OnLootGainedEntry($logEntry); break;
			case "SlotUpdate":					$result = $this->OnSlotUpdateEntry($logEntry); break;
			case "TargetChange":				$result = $this->OnNullEntry($logEntry); break;
			case "ChatterBegin":				$result = $this->OnNullEntry($logEntry); break;
			case "ChatterBegin::Option":		$result = $this->OnNullEntry($logEntry); break;
			case "QuestAdded":					$result = $this->OnNullEntry($logEntry); break;
			case "QuestChanged":				$result = $this->OnNullEntry($logEntry); break;
			case "QuestAdvanced":				$result = $this->OnNullEntry($logEntry); break;
			case "CraftComplete":				$result = $this->OnNullEntry($logEntry); break;
			case "CraftComplete::Result":		$result = $this->OnNullEntry($logEntry); break;
			case "QuestRemoved":				$result = $this->OnNullEntry($logEntry); break;
			case "QuestObjComplete":			$result = $this->OnNullEntry($logEntry); break;
			case "QuestCompleteExperience":		$result = $this->OnNullEntry($logEntry); break;
			case "SkillRankUpdate":				$result = $this->OnSkillRankUpdate($logEntry); break;
			case "LoreBook":					$result = $this->OnLoreBook($logEntry); break;
			case "ShowBook":					$result = $this->OnShowBook($logEntry); break;
			case "Sell":						$result = $this->OnNullEntry($logEntry); break;
			case "Buy":							$result = $this->OnNullEntry($logEntry); break;
			case "Fish":						$result = $this->OnNullEntry($logEntry); break;
			case "Skyshard":					$result = $this->OnNullEntry($logEntry); break;
			case "Recipe":						$result = $this->OnNullEntry($logEntry); break;
			case "Recipe::Result":				$result = $this->OnNullEntry($logEntry); break;
			case "Recipe::Ingredient":			$result = $this->OnNullEntry($logEntry); break;
			case "FoundTreasure":				$result = $this->OnNullEntry($logEntry); break;
			case "Location":					$result = $this->OnNullEntry($logEntry); break;
			case "ConversationUpdated":			$result = $this->OnNullEntry($logEntry); break;
			case "ConversationUpdated::Option":	$result = $this->OnNullEntry($logEntry); break;
			case "MoneyGained":					$result = $this->OnNullEntry($logEntry); break;
			case "QuestMoney":					$result = $this->OnNullEntry($logEntry); break;
			case "SkillPointsChanged":			$result = $this->OnNullEntry($logEntry); break;
			case "InvDump":						$result = $this->OnNullEntry($logEntry); break;
			case "InvDump::Start":
			case "InvDumpStart":				$result = $this->OnNullEntry($logEntry); break;
			case "InvDump::End":
			case "InvDumpEnd":					$result = $this->OnNullEntry($logEntry); break;
			case "LockPick":					$result = $this->OnNullEntry($logEntry); break;
			default:							$result = $this->OnUnknownEntry($logEntry); break;
		}
		
		if ($result === false)
		{
			++$user['errorCount'];
			$user['__dirty'] = true;
		}
		
		return true;
	}
	
	
	public function parseLogEntry($logString)
	{
		$matchData = array();
		$resultData = array();
		
		$result = preg_match_all("|([a-zA-Z]+){(.*?)}  |s", $logString, $matchData);
		
		if ($result === 0) 
		{
			$this->reportLogParseError("Failed to find any matches for log entry: " . $logString);
			return null;
		}
		
		foreach ($matchData[1] as $key => $value)
		{
			$resultData[$value] = $matchData[2][$key];
		}
		
		if (!array_key_exists('ipAddress', $resultData))
		{
			$this->reportLogParseError("Missing IP address! Ignoring possibly corrupt data!");
			return null;
		}
		
		$this->prepareLogEntry($resultData, $logString);
		return $resultData; 
	}
	
	
	public function prepareLogEntry(&$logEntry, $logString)
	{
		$logEntry['__crc'] = crc32($logString);
		
		if (!array_key_exists('ipAddress', $logEntry)) $logEntry['ipAddress'] = '0.0.0.0';
		if (!array_key_exists('userName',  $logEntry)) $logEntry['userName']  = 'Unknown';
		
		$ipAddress = $logEntry['ipAddress'];
		
		if (!array_key_exists('gameTime', $logEntry))
		{
			$logEntry['gameTime'] = $this->lastValidTime[$ipAddress]['gameTime'];
		}
		else
		{
			$this->lastValidTime[$ipAddress]['gameTime'] = $logEntry['gameTime'];
		}
		
		if (!array_key_exists('timeStamp', $logEntry))
		{
			$logEntry['timeStamp'] = $this->lastValidTime[$ipAddress]['timeStamp'];
		}
		else
		{
			$this->lastValidTime[$ipAddress]['timeStamp'] = $logEntry['timeStamp'];
		}
		
	}
	
	
	public function parseEntireLog ($logFilename)
	{
		if (!$this->initDatabaseWrite()) return false;
		
		$this->log("Parsing entire log file $logFilename...");
		
		$this->currentParseFile = $logFilename;
		$this->currentParseLine = 0;
		
		$logEntries = array();
		$entryCount = 0;
		$errorCount = 0;
		
		$fileData = file_get_contents($logFilename);
		if ($fileData == null) return $this->reportError("Failed to load the log file '{$logFilename}'!");
		
		if (strlen($fileData) === 0) return TRUE;
		
		$result = preg_match_all('|(event{.*?end{}  )|s', $fileData, $logEntries);
		if ($result === 0) return $this->reportError("Failed to find any log entries in file '{$logFilename}'!");
		
		$totalLineCount = 0;
		
		foreach ($logEntries[1] as $key => $value)
		{
			$lineCount = substr_count($value, "\n") + 1;
			$totalLineCount += $lineCount;
			$this->currentParseLine = $totalLineCount;
			
			$entryLog = $this->parseLogEntry($value);
			
			if (!$this->handleLogEntry($entryLog))
			{
				++$errorCount;
			}
			
			++$entryCount;
		}
		
		$this->log("Parsed {$entryCount} log entries from file '{$logFilename}'.");
		$this->log("Found {$errorCount} entries with errors.");
		$this->log("Skipped {$this->duplicateCount} duplicate log entries.");
		return TRUE;
	}
	
	
	public function testParseEntry($logEntry)
	{
		$data = array();
		
		$result = preg_match_all("|([a-zA-Z]+){(.*?)}  |s", $logEntry, $data);
		print("Preg Result = " . $result . "\n");
		print("Result Count = " . count($data) . "\n");
		print("Result[0] Count = " . count($data[0]) . "\n");
		print("Result[1] Count = " . count($data[1]) . "\n");
		print("Result[2] Count = " . count($data[2]) . "\n");
		
		$varData = array();
		
		foreach ($data[1] as $key => $value)
		{
			$varData[$value] = $data[2][$key];
			//print($value . " = " . $data[2][$key] . "\n");
		}
		
		foreach ($varData as $key => $value)
		{
			print($key . " = " . $value . "\n");
		}
		
		return TRUE;
	}
	
	
	public function ParseAllLogs($path)
	{
		$files = glob($path . "eso*.log");
		$this->createTables();
		
		foreach ($files as $key => $value)
		{
			$this->parseEntireLog($value);
		}
		
		return true;
	}
	
	
	public function testParse()
	{
		$this->createTables();
		$this->parseEntireLog("/home/uesp/www/esolog/log/eso00004.log");
		return TRUE;
		
		$fileData = file_get_contents("/home/uesp/www/esolog/log/eso00001.log");
		if ($fileData == null) return $this->reportError();
		
		$logEntries = array();
		
		$result = preg_match_all("|(event{.*end{}  \n)|s", $fileData, $logEntries);
		print("Preg Result = " . $result . "\n");
		print("Result Count = " . count($logEntries) . "\n");
		print("Result[0] Count = " . count($logEntries[0]) . "\n");
		print("Result[1] Count = " . count($logEntries[1]) . "\n");
		
		foreach ($logEntries[1] as $key => $value)
		{
			//print($value);
			//$this->testParseEntry($value);
			$result = $this->parseLogEntry($value);
		}
		
		return TRUE;
	}
	
	
	public function readIndexFile()
	{
		if (!file_exists(self::ELP_INDEX_FILENAME))
		{
			$this->currentLogIndex = 1;
			return FALSE;
		}
		
		$index = file_get_contents(self::ELP_INDEX_FILENAME);
	
		if ($index === FALSE)
		{
			$this->currentLogIndex = 1;
			return FALSE;
		}
	
		$this->currentLogIndex = (int) $index;
		if ($this->currentLogIndex < 0) $this->currentLogIndex = 1;
	
		return TRUE;
	}
	
	
	public function reportError ($errorMsg)
	{
		$this->log($errorMsg);
		
		if ($this->db != null && $this->db->error)
		{
			$this->log("DB Error:" . $this->db->error);
			$this->log("Last Query:" . $this->lastQuery);
		}
		return FALSE;
	}
	
	
	public function reportLogParseError ($errorMsg)
	{
		$this->reportError("{$this->currentParseLine}: {$errorMsg}");
		return false;
	}
	
	
	public function log ($msg)
	{
		print($msg . "\n");
		$result = file_put_contents(self::ELP_OUTPUTLOG_FILENAME, $msg . "\n", FILE_APPEND | LOCK_EX);
		return TRUE;
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
	
	
$g_EsoLogParser = new EsoLogParser();
$g_EsoLogParser->ParseAllLogs("/home/uesp/www/esolog/log/");
$g_EsoLogParser->saveData();
$g_EsoLogParser->DumpSkillInfo();
	
	
?>