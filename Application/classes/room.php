<?php

class roomArray extends ArrayClass {
	protected $db;

	function __construct(){
		parent::__construct("room");
		if(!isset($GLOBALS["DB_ADAPTER"])) {
			$GLOBALS["DB_ADAPTER"] = new DBCon();
			$GLOBALS["DB_ADAPTER"]->Link();
		}
		$this->db = $GLOBALS["DB_ADAPTER"];
		$this->db->setTBL(self::getClass()); // ArrayClass function
	}

	function load() {
		$strSQL = $this->db->SStatement(array(), self::getClass());
		$this->db->SetQueryStmt($strSQL);
		if($this->db->Query()) {
			foreach ($this->db->GetAll() as $row) {
				$this->_arrObjects[$row["ID"]] = new room();
				$this->_arrObjects[$row["ID"]]->setVarsFromRow($row);
			}
			return true;
		} else {
			return false;
		}
	}

	function loadByBuilding($BuildingID) {
		$strSQL = $this->db->SStatement(array(), self::getClass(), array("BuildingID" => $BuildingID));
		$this->db->SetQueryStmt($strSQL);
		if($this->db->Query()) {
			$tmp = array();
			foreach ($this->db->GetAll() as $row) {
				$tmp[$row["ID"]] = $row["RoomNumber"];
			}
			return $tmp;
		} else {
			return false;
		}
	}

	function getArrayKeyValue() {
		$tmp = array();
		foreach($this->_arrObjects as $object) {
			$tmp[$object->getID()] = $object->getRoomNumber();
		}
		return $tmp;
	}

	function toOptionList($BuildingID) {
		$strSQL = $this->db->SStatement(array(), self::getClass(), array("BuildingID" => $BuildingID));
		$this->db->SetQueryStmt($strSQL);
		if($this->db->Query()) {
			$rooms = array();
			foreach ($this->db->GetAll() as $row) {
				$rooms[$row["ID"]] = new room();
				$rooms[$row["ID"]]->setVarsFromRow($row);
			}
		}
		$tmp = array();
		foreach($rooms as $object) {
			$tmp[] = array("value" => $object->getID(), "label" => $object->getRoomNumber());
		}
		return $tmp;
	}

}

class room extends BaseDB {
	protected $_ID;
	protected $_BuildingID;
	protected $_RoomNumber;

	public function getID() { return $this->_ID; }
	public function getBuildingID() { return $this->_BuildingID; }
	public function getRoomNumber() { return $this->_RoomNumber; }

	public function setID($value) { $this->_ID = $value; }
	public function setBuildingID($value) { $this->_BuildingID = $value; }
	public function setRoomNumber($value) { $this->_RoomNumber = $value; }

	protected $columns = array("ID", "BuildingID", "RoomNumber");
	protected $db;

	public function __construct($id=null) {
		if(!isset($GLOBALS["DB_ADAPTER"])) {
			$GLOBALS["DB_ADAPTER"] = new DBCon();
			$GLOBALS["DB_ADAPTER"]->Link();
		}
		$this->db = $GLOBALS["DB_ADAPTER"];
		$this->db->setTBL(get_class($this));
		if($id) {
			$this->load($id);
		}
	}

	public function delete() {
		if($this->_id) {
			$strSQL = "DELETE FROM " . DB_NAME . "." . get_class($this) . "
				WHERE id = $this->_id";
			$this->db->setQueryStmt($strSQL);
			return $this->db->Query();
		}
	}

	private function insert() {
		$strSQL = $this->db->IStatement(get_class($this),self::prepare_data());
		$this->db->setQueryStmt($strSQL);
		if($this->db->Query()) {
			$this->_id = $this->db->GetLastInsertedId();
			return $this->_id;
		} else {
			return false;
		}
	}

	public function load($id)
	{
		if (!$id) return false;
		$strSQL = $this->db->SStatement(array(), get_class($this), array("ID" => strval($id)));
		$this->db->setQueryStmt($strSQL);
		if ($this->db->Query()) {
			$this->setVarsFromRow($this->db->getRow());
			return true;
		} else {
			return false;
		}
	}

	public function save() {
		$this->setLastModified(base::now());
		if($this->_id) {
			return self::update();
		} else {
			$this->setDateAdded(base::now());
			return self::insert();
		}
	}

	private function update() {
		$strSQL = $this->db->UStatement(self::prepare_data(),get_class($this),array("ID" => array(0 => $this->getId())));
		$this->db->setQueryStmt($strSQL);
		if($this->db->Query())
			return ($this->db->GetAffectedRows() > -1);
		return false;
	}

	public function setDB(DBCon $db) {
		$this->db = $db;
	}

}
?>