<?php

namespace App\Models;

use \MvcCore\Ext\Models\Db\Attrs,
	\MvcCore\Ext\Forms\Fields;

/**
 * @table cds
 */
#[Attrs\Table('cds')]
class	Album
extends	\MvcCore\Ext\Models\Db\Models\SQLite
implements \MvcCore\Ext\ModelForms\IModel {

	use \MvcCore\Ext\ModelForms\Model\Features;

	/**
	 * @column id
	 * @keyPrimary
	 * @var ?int
	 */
	#[Attrs\Column('id'), Attrs\KeyPrimary]
	protected $id;

	/**
	 * @column title
	 * @field Text({
	 *    "label":			"Title:",
	 *    "maxLength":		200,
	 *    "required":		true,
	 *    "autocomplete":	"off"
	 * })
	 * @var string
	 */
	#[Attrs\Column('title')]
	#[Fields\Text(
		label:			'Title:',
		maxLength:		200,
		required:		TRUE,
		autocomplete:	'off'
	)]
	protected $title;

	/**
	 * @column interpret
	 * @field Text({
	 *    "label":			"Interpret:",
	 *    "maxLength":		200,
	 *    "required":		true,
	 *    "autocomplete":	"off"
	 * })
	 * @var string
	 */
	#[Attrs\Column('interpret')]
	#[Fields\Text(
		label:			'Interpret:',
		maxLength:		200,
		required:		TRUE,
		autocomplete:	'off'
	)]
	protected $interpret;

	/**
	 * @column year
	 * @field Number({
	 *    "label":			"Year:",
	 *    "maxLength":		200,
	 *    "size":			4,
	 *    "validators":		["IntNumber"]
	 * })
	 * @var int
	 */
	#[Attrs\Column('year')]
	#[Fields\Number(
		label:		'Year:',
		maxLength:	200,
		size:		4,
		validators:	['IntNumber']
	)]
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
			->ToInstances(get_called_class());
	}

	/**
	 * Get single album instance by given id or null if no record by id in database.
	 * @param int $id
	 * @return \MvcCore\Model|null
	 */
	public static function GetById ($id) {
		return self::GetConnection()
			->Prepare("SELECT * FROM cds WHERE id = :id")
			->FetchOne([
				":id" => $id,
			])
			->ToInstance(
				get_called_class(), 
				self::$defaultPropsFlags | self::PROPS_INITIAL_VALUES
			);
	}
}
