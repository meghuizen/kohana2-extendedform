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
	
	public static function add_orm(ORM & $object, $alias = NULL) {
		$object_name = (!empty($alias)) ? $alias : $object->object_name;
		
		if (!empty(self::$_ormobjects[$object_name])) // don't go in a recursive loop
			return FALSE;
		
		$columndata = self::parse_column_data($object, $alias);
		
		if (!empty($columndata) && count($columndata) > 0)
		foreach ($columndata as $column => $columndata) {
			$inputdata = array(
				"name" => preg_replace('/[^a-zA-Z0-9_]/', '_', $columndata["name"]),
				"id" => preg_replace('/[^a-zA-Z0-9_]/', '_', "txt" . $columndata["name"]), //for CSS
				"class" => $columndata["valuetype"],
				"title" => $columndata["description"]
			);
			$labeldata = array(
				"for" => preg_replace('/[^a-zA-Z0-9_]/', '_', "txt" . $columndata["name"]), //for CSS
				"id" => preg_replace('/[^a-zA-Z0-9_]/', '_', "lbl" . $columndata["name"]), //for CSS
				"class" => $columndata["valuetype"],
				"title" => $columndata["description"]
			);
			
			if (!empty($columndata["required"]) && $columndata["required"] === TRUE) {
				if (strpos($inputdata["class"], "required") === FALSE)
					$inputdata["class"] = trim($inputdata["class"] . " required");
				if (strpos($labeldata["class"], "required") === FALSE)
					$labeldata["class"] = trim($labeldata["class"] . " required");
			}
			if (!empty($columndata["type"]))
				$inputdata["type"] = $columndata["type"];
			
			if (!empty($columndata["type"]) && strpos($labeldata["class"], $columndata["type"]) === FALSE)
				$labeldata["class"] = trim($labeldata["class"] . " " . $columndata["type"]);
			
			self::$_inputelements[$columndata["name"]] = $inputdata;
			self::$_labelelements[$columndata["name"]] = $labeldata;
			self::$_ormobjects[$object_name] = & $object;
		}

		if (count($object->has_one) > 0)
		foreach ($object->has_one as $relatedobject) {
			self::add_orm($object->$relatedobject);
		}
		
		return TRUE;
	}
	
	public static function parse_column_data(ORM & $object, $alias = NULL) {
		$column_data = array();
		$object_name = (!empty($alias)) ? $alias : $object->object_name;
		
		if (!empty(self::$_orm_column_data[$object_name]))
			return self::$_orm_column_data[$object_name];
		
		if (method_exists($object, 'get_column_data'))
			$column_data = $object->get_column_data();
		
		if (count($object->table_columns) > 0)
		foreach ($object->table_columns as $col => $type) {
			if (empty($column_data[$col]))
				$column_data[$col] = array();
			
			if (is_array($type)) {
				$column = $type;
				$type = $type["valuetype"];
				
				if (!empty($column['null']))
					$column_data[$col]["required"] = FALSE;
				else {
					$column_data[$col]["required"] = TRUE;
				}
				if (!empty($column['length']))
					$column_data[$col]["maxlength"] = $column["length"];
			}
			
			if ($object->primary_key == $col) {
				if (empty($column_data[$col]["type"]))
					$column_data[$col]["type"] = "hidden";
			}
			
			$type = strtolower($type);
			switch (preg_replace('/\([0-9a-z]+\)/i', '', $type)) {
				case "varchar":
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = "text";
					
					$length = intval(trim(str_replace(array('varchar', '(', ')'), '', $type)));
					$column_data[$col]["maxlength"] = $length;
					
					if (empty($column_data[$col]["type"]))
						$column_data[$col]["type"] = "text";
					break;
				case "string":
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = "text";
					
					if (empty($column_data[$col]["type"]))
						$column_data[$col]["type"] = "text";
					break;
				case "bool":
				case "boolean":
				case "tinyint":
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = "boolean";
					
					if (empty($column_data[$col]["type"]))
						$column_data[$col]["type"] = "checkbox";
					break;
				case "integer":
				case "int":
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = "integer";
					
					if (empty($column_data[$col]["type"]))
						$column_data[$col]["type"] = "text";
					break;
				case "float":
				case "double":
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = "float";
					
					if (empty($column_data[$col]["type"]))
						$column_data[$col]["type"] = "text";
					break;
				case "timestamp":
				case "datetime":
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = "datetime";
					
					if (empty($column_data[$col]["type"]))
						$column_data[$col]["type"] = "datetime";
					break;
				case "date":
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = "date";
					
					if (empty($column_data[$col]["type"]))
						$column_data[$col]["type"] = "date";
					break;
				case "tinytext":
				case "text":
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = "text";
					
					if (empty($column_data[$col]["type"]))
						$column_data[$col]["type"] = "textarea";
					break;
				case "uuid":
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = "uuid";
					$column_data[$col]["maxlength"] = 36;
					$column_data[$col]["minlength"] = 36;
					
					if (empty($column_data[$col]["type"]))
						$column_data[$col]["type"] = "uuid";
					break;
				default:
					if (empty($column_data[$col]["valuetype"]))
						$column_data[$col]["valuetype"] = $type;
					
					if (empty($column_data[$col]["type"]))
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
				$column_data[$col]["name"] = $object_name . '_' . $col;
		}
		
		self::$_orm_column_data[$object_name] = $column_data;
		
		if (method_exists($object, 'set_column_data'))
			$object->set_column_data($column_data);
		
		return $column_data;
	}
	
	public static function get_input_attributes(ORM & $object, $fieldname) {
		$column_data = self::parse_column_data($object);
		
		return array();
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
		foreach (self::$_ormobjects as $object_name => $object) {
			$inparr = self::parse_column_data($object, $object_name);
			
			if (count($inparr) > 0)
			foreach ($inparr as $key => $val) {
				$jsonarr[preg_replace('/[^a-zA-Z0-9_]/', '_', "txt" . $object_name . '-' . $key)] = $val;
			}
		}
		
		return json_encode($jsonarr);
	}
	
	public static function read_view($view, $other_inputfields = FALSE) {
		$viewdata = '';
		
		try {
			if ($view instanceof View)
				$viewdata = $view->kohana_filename;
			else
				$viewdata = (string) Kohana::find_file('views', $view, TRUE);
			
			$viewdata = file_get_contents($viewdata);
		} catch (Exception $e) {}
		
		return self::parse_html($viewdata, $other_inputfields);
	}
	
	public static function parse_view($view, $other_inputfields = FALSE) {
		$viewdata = '';
		
		try {
			if ($view instanceof View)
				$viewdata = $view->render();
			else
				$viewdata = (string) $view;
		} catch (Exception $e) {}
		
		return self::parse_html($viewdata, $other_inputfields);
	}
	
	public static function parse_element($attributes) {
		if (empty($attributes["type"]))
			$attributes["type"] = "text";
		
		$html = '';
		$cvalue = NULL;
		
		if (!empty($attributes["value"])) {
			$cvalue = $attributes["value"];
			unset($attributes["value"]);
		}
		
		switch ($attributes["type"]) {
			case 'label':
				$html = self::label($attributes, $cvalue);
				break;
			case 'textarea':
				if (!empty($attributes["length"])) {
					$attributes["maxlength"] = $attributes["length"];
					unset($attributes["length"]);
				}
				
				$html = self::textarea($attributes, $cvalue);
				break;
			case 'hidden':
				if (!empty($attributes["length"])) {
					$attributes["maxlength"] = $attributes["length"];
					unset($attributes["length"]);
				}
				
				$html = self::input($attributes, $cvalue);
				break;
			case 'password':
				if (!empty($attributes["length"])) {
					$attributes["maxlength"] = $attributes["length"];
					unset($attributes["length"]);
				}
				
				$html = self::password($attributes, $cvalue);
				break;
			case 'checkbox':
				$html = self::checkbox($attributes, $cvalue);
				break;
			case 'radio':
				$html = self::radio($attributes, $cvalue);
				break;
			case 'integer':
			case 'int':
			case 'text':
			case 'textbox':
			case 'date':
			case 'datetime':
			case 'uuid':
			default:
				if (!empty($attributes["length"])) {
					$attributes["maxlength"] = $attributes["length"];
					unset($attributes["length"]);
				}
				
				$html = self::input($attributes, $cvalue);
				break;
		}
		return $html;
	}
	
	public static function parse_html($view, $other_inputfields = FALSE) {
		$viewdata = $view;
		
		if (empty($viewdata))
			return $viewdata;
		
		// TODO: this needs to work as well: attr="dgfds >" or attr='bla> dfs', etc. (also multiple times) (so does NOT work yet!!)
		preg_match_all('/<input[^>]*?>/i', $viewdata, $originalinputs);
		preg_match_all('/<textarea[^>]*?>.*?<\/textarea>/i', $viewdata, $originaltextareas);
		preg_match_all('/<select[^>]*?>.*?<\/select>/i', $viewdata, $originalselects);
		
		preg_match_all('/<label[^>]*?>.*?<\/label>/i', $viewdata, $originallabels);
		
		// plain input fields
		if (!empty($originalinputs[0]) && count($originalinputs[0]) > 0)
		foreach ($originalinputs[0] as $input) {
			preg_match('/.*?name="(.*?)"/i', $input, $originalname);
			
			if (empty($originalname[1]))
				continue;
			
			$originalname = $originalname[1];
			$originalhtml = $input;
			
			if ((empty(self::$_inputelements[$originalname]) && !$other_inputfields))
				continue;
			if ((empty(self::$_inputelements[$originalname]) || !is_array(self::$_inputelements[$originalname])) && $other_inputfields) {
					self::$_inputelements[$originalname] = array();
			}
			
			//attributes need to have quotes. Without they will not be catched
			preg_match_all('/([ ]+([a-z0-9]+)="([^"]*?)"|[ ]+([a-z0-9]+)=\'([^\']*?)\'|[ ]+([a-z0-9]+))/i', $input, $originalattributes);
			
			$oldattrs = self::$_inputelements[$originalname];
			$newattributes = array_merge($oldattrs, self::htmlattributes_tokeyvalarray($originalattributes[0]));
			
			if (!empty($newattributes["class"]) && !empty($oldattrs["class"])) {
				$oldclasses = (strpos($oldattrs["class"], ' ') !== FALSE) ? explode(' ', $oldattrs["class"]) : array($oldattrs["class"]);
				$newclasses = (strpos($newattributes["class"], ' ') !== FALSE) ? explode(' ', $newattributes["class"]) : array($newattributes["class"]);
				
				$newclasses = array_merge($oldclasses, $newclasses);
				$newclasses = array_unique($newclasses);
				
				$newattributes["class"] = trim(implode(' ', $newclasses));
			}
			if (!empty($newattributes["style"]) && !empty($oldattrs["style"])) {
				$oldstyle = self::cssattributes_tokeyvalarray($oldattrs["style"]);
				$newstyle = self::cssattributes_tokeyvalarray($newattributes["style"]);
				$newstyle = array_merge($oldstyle, $newstyle);
				
				$newattributes["style"] = '';
				if (!empty($newstyle) && count($newstyle) > 0)
				foreach ($newstyle as $attr => $val) {
					$newattributes["style"] .= $attr . ': ' . $val . ';';
				}
			}
			if (empty($newattributes["type"]))
				$newattributes["type"] = "text";
			
			self::$_inputelements[$originalname] = $newattributes;
			
			$viewdata = str_replace($input, self::parse_element(self::$_inputelements[$originalname]), $viewdata);
		}
		
		// labels
		if (!empty($originallabels[0]) && count($originallabels[0]) > 0)
		foreach ($originallabels[0] as $label) {
			preg_match('/.*?for="(.*?)"/i', $label, $originalid);
			
			if (empty($originalid[1]))
				continue;
			
			$originalid = $originalid[1];
			$originalhtml = $label;
			
			if ((empty(self::$_labelelements[$originalid]) && !$other_inputfields))
				continue;
			if ((empty(self::$_labelelements[$originalid]) || !is_array(self::$_labelelements[$originalid])) && $other_inputfields) {
					self::$_labelelements[$originalid] = array();
			}
			
			// this needs to work as well: attr="dgfds >" or attr='bla> dfs', etc. (also multiple times)
		
			preg_match('/(<label.*?"[^>]*?>[^>]*?"[^>]*?>|<label.*?\'[^>]*?>[^>]*?\'[^>]*?>|<label[^>]*?>)(.*?<\/label>)/i', $label, $attributepart);
			
			$otherpart = $attributepart[2];
			$attributepart = $attributepart[0];
			
			//attributes need to have quotes. Without they will not be catched
			preg_match_all('/([ ]+([a-z0-9]+)="([^"]*?)"|[ ]+([a-z0-9]+)=\'([^\']*?)\'|[ ]+([a-z0-9]+))/i', $attributepart, $originalattributes);
			
			$oldattrs = self::$_labelelements[$originalid];
			$newattributes = array_merge($oldattrs, self::htmlattributes_tokeyvalarray($originalattributes[0]));
			
			if (!empty($newattributes["class"]) && !empty($oldattrs["class"])) {
				$oldclasses = (strpos($oldattrs["class"], ' ') !== FALSE) ? explode(' ', $oldattrs["class"]) : array($oldattrs["class"]);
				$newclasses = (strpos($newattributes["class"], ' ') !== FALSE) ? explode(' ', $newattributes["class"]) : array($newattributes["class"]);
				
				$newclasses = array_merge($oldclasses, $newclasses);
				$newclasses = array_unique($newclasses);
				
				$newattributes["class"] = trim(implode(' ', $newclasses));
			}
			if (!empty($newattributes["style"]) && !empty($oldattrs["style"])) {
				$oldstyle = self::cssattributes_tokeyvalarray($oldattrs["style"]);
				$newstyle = self::cssattributes_tokeyvalarray($newattributes["style"]);
				$newstyle = array_merge($oldstyle, $newstyle);
				
				$newattributes["style"] = '';
				if (!empty($newstyle) && count($newstyle) > 0)
				foreach ($newstyle as $attr => $val) {
					$newattributes["style"] .= $attr . ': ' . $val . ';';
				}
			}
			if (empty($newattributes["type"]))
				$newattributes["type"] = "label";
				
			$otherpart = trim(str_ireplace('</label>', '', $otherpart));
			if (!empty($otherpart))
				$newattributes["value"] = $otherpart;
			
			self::$_labelelements[$originalid] = $newattributes;
			
			$viewdata = str_replace($label, self::parse_element(self::$_labelelements[$originalid]), $viewdata);
		}
		
		// select boxes
		if (!empty($originalselects[0]) && count($originalselects[0]) > 0)
		foreach ($originalselects[0] as $input) {
			preg_match('/.*?name="(.*?)"/i', $input, $originalname);
			
			if (empty($originalname[1]))
				continue;
			
			$originalname = $originalname[1];
			$originalhtml = $input;
			
			if ((empty(self::$_inputelements[$originalname]) && !$other_inputfields))
				continue;
			if ((empty(self::$_inputelements[$originalname]) || !is_array(self::$_inputelements[$originalname])) && $other_inputfields) {
					self::$_inputelements[$originalname] = array();
			}
			
			// this needs to work as well: attr="dgfds >" or attr='bla> dfs', etc. (also multiple times)
			preg_match('/(<select.*?"[^>]*?>[^>]*?"[^>]*?>|<select.*?\'[^>]*?>[^>]*?\'[^>]*?>|<select[^>]*?>)(.*?<\/select>)/i', $input, $attributepart);
			
			$otherpart = $attributepart[2];
			$attributepart = $attributepart[0];
			
			//attributes need to have quotes. Without they will not be catched
			preg_match_all('/([ ]+([a-z0-9]+)="([^"]*?)"|[ ]+([a-z0-9]+)=\'([^\']*?)\'|[ ]+([a-z0-9]+))/i', $attributepart, $originalattributes);
			
			$oldattrs = self::$_inputelements[$originalname];
			$newattributes = array_merge($oldattrs, self::htmlattributes_tokeyvalarray($originalattributes[0]));
			
			if (!empty($newattributes["class"]) && !empty($oldattrs["class"])) {
				$oldclasses = (strpos($oldattrs["class"], ' ') !== FALSE) ? explode(' ', $oldattrs["class"]) : array($oldattrs["class"]);
				$newclasses = (strpos($newattributes["class"], ' ') !== FALSE) ? explode(' ', $newattributes["class"]) : array($newattributes["class"]);
				
				$newclasses = array_merge($oldclasses, $newclasses);
				$newclasses = array_unique($newclasses);
				
				$newattributes["class"] = trim(implode(' ', $newclasses));
			}
			if (!empty($newattributes["style"]) && !empty($oldattrs["style"])) {
				$oldstyle = self::cssattributes_tokeyvalarray($oldattrs["style"]);
				$newstyle = self::cssattributes_tokeyvalarray($newattributes["style"]);
				$newstyle = array_merge($oldstyle, $newstyle);
				
				$newattributes["style"] = '';
				if (!empty($newstyle) && count($newstyle) > 0)
				foreach ($newstyle as $attr => $val) {
					$newattributes["style"] .= $attr . ': ' . $val . ';';
				}
			}
			$newattributes["type"] = "select";
			
			//TODO: something with the options (<option>)
			$otherpart = trim(str_ireplace('</select>', '', $otherpart));
			if (!empty($otherpart))
				$newattributes["value"] = $otherpart;
			
			self::$_inputelements[$originalname] = $newattributes;
			
			$viewdata = str_replace($input, self::parse_element(self::$_inputelements[$originalname]), $viewdata);
		}
		
		// textareas
		if (!empty($originaltextareas[0]) && count($originaltextareas[0]) > 0)
		foreach ($originaltextareas[0] as $input) {
			preg_match('/.*?name="(.*?)"/i', $input, $originalname);
			
			if (empty($originalname[1]))
				continue;
			
			$originalname = $originalname[1];
			$originalhtml = $input;
			
			if ((empty(self::$_inputelements[$originalname]) && !$other_inputfields))
				continue;
			if ((empty(self::$_inputelements[$originalname]) || !is_array(self::$_inputelements[$originalname])) && $other_inputfields) {
					self::$_inputelements[$originalname] = array();
			}
			
			// this needs to work as well: attr="dgfds >" or attr='bla> dfs', etc. (also multiple times)
			preg_match('/(<textarea.*?"[^>]*?>[^>]*?"[^>]*?>|<textarea.*?\'[^>]*?>[^>]*?\'[^>]*?>|<textarea[^>]*?>)(.*?<\/textarea>)/i', $input, $attributepart);
			
			$otherpart = $attributepart[2];
			$attributepart = $attributepart[0];
			
			//attributes need to have quotes. Without they will not be catched
			preg_match_all('/([ ]+([a-z0-9]+)="([^"]*?)"|[ ]+([a-z0-9]+)=\'([^\']*?)\'|[ ]+([a-z0-9]+))/i', $attributepart, $originalattributes);
			
			$oldattrs = self::$_inputelements[$originalname];
			$newattributes = array_merge($oldattrs, self::htmlattributes_tokeyvalarray($originalattributes[0]));
			
			if (!empty($newattributes["class"]) && !empty($oldattrs["class"])) {
				$oldclasses = (strpos($oldattrs["class"], ' ') !== FALSE) ? explode(' ', $oldattrs["class"]) : array($oldattrs["class"]);
				$newclasses = (strpos($newattributes["class"], ' ') !== FALSE) ? explode(' ', $newattributes["class"]) : array($newattributes["class"]);
				
				$newclasses = array_merge($oldclasses, $newclasses);
				$newclasses = array_unique($newclasses);
				
				$newattributes["class"] = trim(implode(' ', $newclasses));
			}
			if (!empty($newattributes["style"]) && !empty($oldattrs["style"])) {
				$oldstyle = self::cssattributes_tokeyvalarray($oldattrs["style"]);
				$newstyle = self::cssattributes_tokeyvalarray($newattributes["style"]);
				$newstyle = array_merge($oldstyle, $newstyle);
				
				$newattributes["style"] = '';
				if (!empty($newstyle) && count($newstyle) > 0)
				foreach ($newstyle as $attr => $val) {
					$newattributes["style"] .= $attr . ': ' . $val . ';';
				}
			}
			$newattributes["type"] = "textarea";
				
			$otherpart = trim(str_ireplace('</textarea>', '', $otherpart));
			if (!empty($otherpart))
				$newattributes["value"] = $otherpart;
			
			self::$_inputelements[$originalname] = $newattributes;
			
			$viewdata = str_replace($input, self::parse_element(self::$_inputelements[$originalname]), $viewdata);
		}
		
		return $viewdata;
	}
	
	public static function htmlattributes_tokeyvalarray($htmlattributesarray) {
		$retarr = array();
		
		if (!empty($htmlattributesarray) && count($htmlattributesarray) > 0)
		foreach ($htmlattributesarray as $htmlattribute) {
			$htmlattribute = trim($htmlattribute);
			
			if (strpos($htmlattribute, "=") === FALSE) { // single html element
				$retarr[strtolower(trim($htmlattribute))] = $htmlattribute;
				continue;
			}
			
			$htmlattribute = explode("=", $htmlattribute);
			
			//string quotes away around the value, but cannot be done with trim. This is because attribute="'blal'" should be poosible.
			if ( (substr($htmlattribute[1], 0, 1) == '"' || substr($htmlattribute[1], 0, 1) == "'") &&
				substr($htmlattribute[1], 0, 1) === substr($htmlattribute[1], strlen($htmlattribute[1]) - 1, 1))
					$htmlattribute[1] = trim($htmlattribute[1], substr($htmlattribute[1], 0, 1));
			
			$retarr[strtolower(trim($htmlattribute[0]))] = $htmlattribute[1];
		}
		
		return $retarr;
	}
	
	public static function cssattributes_tokeyvalarray($cssattributes) {
		$retarr = array();
		$cssattributesarray = array();
		
		if (empty($cssattributes))
			return array();
		if (is_array($cssattributes))
			$cssattributesarray = $cssattributes;
		else
			$cssattributesarray = (strpos($cssattributes, ';') !== FALSE) ? explode(';', $cssattributes) : array($cssattributes);
		
		if (!empty($cssattributesarray) && count($cssattributesarray) > 0)
		foreach ($cssattributesarray as $cstyle) {
			$cstyle = trim($cstyle);
			
			if (empty($cstyle))
				continue;
			if (strpos($cstyle, ':') === FALSE) //not valid
				continue;
			
			$cstyle = explode(':', $cstyle);
			
			$retarr[trim(strtolower($cstyle[0]))] = trim($cstyle[1]);
		}
		
		return $retarr;
	}
}