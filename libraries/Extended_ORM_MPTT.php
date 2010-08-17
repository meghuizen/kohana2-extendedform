<?php defined('SYSPATH') OR die('No direct script access.');

class Extended_ORM_MPTT extends ORM_MPTT {

	/*protected $_column_data = array(
		"fieldname" => array(
			"type" => "type", // integer, text, textarea, wysiwyg, uuid, date, datetime, checkbox, radiobutton, select
			"description" => "group.field.id.description", // $this->object_name . '.' . 'field' . '$fieldname' . 'description';
			"label" => "group.field.id.label", // $this->object_name . '.' . 'field' . '$fieldname' . 'label';
			"watermark" => "group.field.id.watermark",  // $this->object_name . '.' . 'field' . '$fieldname' . 'watermark';
			"value" => NULL,
			"editable" => FALSE,
			"required" => FALSE,
			"maxlength" => NULL,
			"minlength" => NULL,
			"callbacks" = array(),  // may NOT be a string
			"pre_filters" = array(),  // may NOT be a string
			"post_filters" = array(),  // may NOT be a string
			"regexp" => '/^[0-9]+$/d',
			"options" => array( // for select boxes
				"value" => "Text",
				"value2" => "Text 2"
			)
		),
		
	);*/
	protected $_column_data = array();
	
	public function fill_column_data () {
		extendedform::parse_column_data($this);
	}
	
	public function get_column_data() {
		return $this->_column_data;
	}
	
	public function set_column_data($column_data) {
		$this->_column_data = $column_data;
	}

	/**
	 * Validates and optionally saves a new record from an array.
	 *
	 * @param  array    values to check
	 * @param  boolean  save[Optional] the record when validation succeeds
	 * @return boolean
	 */
	public function validate(array & $array, $save = FALSE)
	{
		// Initialise the validation library and setup some rules
		$array = Validation::factory($array)
				->pre_filter('trim');
 
		return parent::validate($array, $save);
	}
}