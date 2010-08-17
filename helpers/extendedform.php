<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Extended Form helper class, based on the Kohana default form class.
 *
 */
class extendedform_Core extends form {
	protected static $_inputelements = array();
	protected static $_labelelements = array();
	protected static $_ormobjects = array();
	protected static $_orm_column_data = array();
	
	public static function add_orm(ORM & $object) {
		if (!empty(self::$_ormobjects[$object->object_name])) // don't go in a recursive loop
			return FALSE;
		
		$columndata = self::parse_column_data($object);
		
		if (!empty($columndata) && count($columndata) > 0)
		foreach ($columndata as $column => $columndata) {
			$inputdata = array(
				"name" => $columndata["name"],
				"id" => str_replace(".", "-", "txt" . $columndata["name"]), //for CSS
				"class" => $columndata["type"],
				"title" => $columndata["description"]
			);
			$labeldata = array(
				"for" => str_replace(".", "-", "txt" . $columndata["name"]), //for CSS
				"id" => str_replace(".", "-", "lbl" . $columndata["name"]), //for CSS
				"class" => $columndata["type"],
				"title" => $columndata["description"]
			);
			self::$_inputelements[$columndata["name"]] = self::input($inputdata, $object->$column);
			self::$_labelelements[$columndata["name"]] = self::label($labeldata, $columndata["label"]);
			self::$_ormobjects[$object->object_name] = & $object;
		}

		if (count($object->has_one) > 0)
		foreach ($object->has_one as $relatedobject) {
			self::add_orm($object->$relatedobject);
		}
		
		return TRUE;
	}
	
	public static function parse_column_data(ORM & $object) {
		$column_data = array();
		
		if (!empty(self::$_orm_column_data[$object->object_name]))
			return self::$_orm_column_data[$object->object_name];
		
		if (method_exists($object, 'get_column_data'))
			$column_data = $object->get_column_data();
		
		if (count($object->table_columns) > 0)
		foreach ($object->table_columns as $col => $type) {
			if (empty($column_data[$col]))
				$column_data[$col] = array();
			
			if (is_array($type)) {
				$column = $type;
				$type = $type["type"];
				
				if (!empty($column['null']))
					$column_data[$col]["required"] = FALSE;
				else
					$column_data[$col]["required"] = TRUE;
				if (!empty($column['length']))
					$column_data[$col]["maxlength"] = $column["length"];
			}
			
			$type = strtolower($type);
			
			if (empty($column_data[$col]["type"]))
			switch (preg_replace('/\([0-9a-z]+\)/i', '', $type)) {
				case "varchar":
					$column_data[$col]["type"] = "text";
					$length = intval(trim(str_replace(array('varchar', '(', ')'), '', $type)));
					$column_data[$col]["maxlength"] = $length;
					break;
				case "string":
					$column_data[$col]["type"] = "text";
					break;
				case "bool":
				case "boolean":
				case "tinyint":
					$column_data[$col]["type"] = "checkbox";
					break;
				case "integer":
				case "int":
					$column_data[$col]["type"] = "integer";
					break;
				case "float":
				case "double":
					$column_data[$col]["type"] = "float";
					break;
				case "timestamp":
				case "datetime":
					$column_data[$col]["type"] = "datetime";
					break;
				case "date":
					$column_data[$col]["type"] = "date";
					break;
				case "tinytext":
				case "text":
					$column_data[$col]["type"] = "textarea";
					break;
				case "uuid":
					$column_data[$col]["type"] = "uuid";
					break;
				default:
					$column_data[$col]["type"] = $type;
					break;
			}
			
			if (empty($column_data[$col]["description"]))
				$column_data[$col]["description"] = Kohana::lang($object->object_name . '.field.' . $col . '.description');
			if (empty($column_data[$col]["label"]))
				$column_data[$col]["label"] = Kohana::lang($object->object_name . '.field.' . $col . '.label');
			if (empty($column_data[$col]["watermark"]))
				$column_data[$col]["watermark"] = Kohana::lang($object->object_name . '.field.' . $col . '.watermark');
			if (empty($column_data[$col]["name"]))
				$column_data[$col]["name"] = $object->object_name . '.' . $col;
		}
		
		self::$_orm_column_data[$object->object_name] = $column_data;
		
		if (method_exists($object, 'set_column_data'))
			$object->set_column_data($column_data);
		
		return $column_data;
	}
	
	public static function get_inputs() {
		return self::$_inputelements;
	}
	
	public static function get_labels() {
		return self::$_labelelements;
	}
	
	public static function get_inputdata_json() {
		$jsonarr = array();
		
		if (!empty(self::$_ormobjects) && count(self::$_ormobjects) > 0)
		foreach (self::$_ormobjects as $object) {
			$inparr = self::parse_column_data($object);
			
			if (count($inparr) > 0)
			foreach ($inparr as $key => $val) {
				$jsonarr[str_replace(".", "-", "txt" . $object->object_name . '.' . $key)] = $val;
			}
		}
		
		return json_encode($jsonarr);
	}
	
	public static function parse_view($view) {
		$viewdata = '';
		
		if ($view instanceof View)
			$viewdata = $view->render();
		else
			$viewdata = (string) $view;
		
		preg_match_all('/<input[^>]*?>/i', $viewdata, $originalinputs);
		preg_match_all('/<textarea[^>]*?>.*?<\/textarea>/i', $viewdata, $toriginalinputs);
		if (!empty($originalinputs[0]) && !empty($toriginalinputs[0]))
			$originalinputs[0] = array_merge($originalinputs[0], $toriginalinputs[0]);
		preg_match_all('/<select[^>]*?>.*?<\/select>/i', $viewdata, $toriginalinputs);
		if (!empty($originalinputs[0]) && !empty($toriginalinputs[0]))
			$originalinputs[0] = array_merge($originalinputs[0], $toriginalinputs[0]);
		
		preg_match_all('/<label[^>]*?>.*?<\/label>/i', $viewdata, $originallabels);
		
		if (!empty($originalinputs[0]) && count($originalinputs[0]) > 0)
		foreach ($originalinputs[0] as $input) {
			preg_match('/.*?name="(.*?)"/i', $input, $originalname);
			
			if (empty($originalname[1]))
				continue;
			
			$originalname = $originalname[1];
			$originalhtml = $input;
			
			if (empty(self::$_inputelements[$originalname]))
				continue;
			
			$viewdata = str_replace($originalhtml, self::$_inputelements[$originalname], $viewdata);
		}
		
		if (!empty($originallabels[0]) && count($originallabels[0]) > 0)
		foreach ($originallabels[0] as $label) {
			preg_match('/.*?for="(.*?)"/i', $label, $originalid);
			
			if (empty($originalid[1]))
				continue;
			
			$originalid = $originalid[1];
			$originalhtml = $label;
			
			if (empty(self::$_labelelements[$originalid]))
				continue;
			
			$viewdata = str_replace($originalhtml, self::$_labelelements[$originalid], $viewdata);
		}
		
		return $viewdata;
	}
}