<?php

namespace App\Models;

/**
 * @table cds
 */
class Album extends \MvcCore\Ext\Models\Db\Models\SQLite {

	/**
	 * @column id
	 * @keyPrimary
	 * @var ?int
	 */
	protected $id;
	/**
	 * @column title
	 * @var string
	 */
	protected $title;
	/**
	 * @column interpret
	 * @var string
	 */
	protected $interpret;
	/**
	 * @column year
	 * @var int
	 */
	protected $year;

	public function GetId () {
		return $this->id;
	}
	public function SetId ($id) {
		$this->id = $id;
		return $this;
	}
	public function GetTitle () {
		return $this->title;
	}
	public function SetTitle ($title) {
		$this->title = $title;
		return $this;
	}
	public function GetInterpret () {
		return $this->interpret;
	}
	public function SetInterpret ($interpret) {
		$this->interpret = $interpret;
		return $this;
	}
	public function GetYear () {
		return $this->year;
	}
	public function SetYear ($year) {
		$this->year = $year;
		return $this;
	}


	/**
	 * Get all albums in database as array, keyed by $album->Id.
	 * @return \App\Models\Album[]
	 */
	public static function GetAll () {
		return self::GetConnection()
			->Prepare("SELECT * FROM cds")
			->FetchAll()
			->ToInstances(get_class());
	}

	/**
	 * Get single album instance by given id or null if no record by id in database.
	 * @param int $id
	 * @return \MvcCore\Model|null
	 */
	public static function GetById ($id) {
		return self::GetConnection()
			->Prepare("
				SELECT *
				FROM cds
				WHERE id = :id
			")
			->FetchOne([
				":id" => $id,
			])
			->ToInstance(
				get_class(), 
				self::$defaultPropsFlags | self::PROPS_INITIAL_VALUES
			);
	}
}
