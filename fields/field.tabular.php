<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');

	/**
	 * @package field_
	 */
	// require_once FACE . '/interface.exportablefield.php';
	// require_once FACE . '/interface.importablefield.php';

	Class fieldTabular extends Field  {

		public function __construct() {
			parent::__construct();
			$this->_name = __('Tabular');
			$this->_required = true;

			$this->set('required', 'no');
			$this->set('show_column', 'no');
			$this->set('location', 'sidebar');
		}

		/*-------------------------------------------------------------------------
			Setup:
		-------------------------------------------------------------------------*/
		public function createTable() {
			try {
				Symphony::Database()->query(sprintf("
						CREATE TABLE IF NOT EXISTS `tbl_entries_data_%d` (
							`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
							`entry_id` INT(11) UNSIGNED NOT NULL,
							`key` TEXT NULL,
							`value` TEXT NULL,
							PRIMARY KEY (`id`),
							KEY `entry_id` (`entry_id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
					", $this->get('id')
				));
				// key : currently not supported
				// value : json representation of our table
				return true;
			}
			catch (Exception $ex) {
				return false;
			}
		}

		public function canFilter(){
			return false;
		}

		public function prePopulate(){
			return false;
		}

		public function allowDatasourceParamOutput(){
			return false;
		}


		/*-------------------------------------------------------------------------
			Settings:
		-------------------------------------------------------------------------*/
		public function displaySettingsPanel(XMLElement &$wrapper, $errors = null) {
			// Initialize field settings based on class defaults (name, placement)
			parent::displaySettingsPanel($wrapper, $errors);

			$order = $this->get('sortorder');

			$group = new XMLElement('div');
			$group->setAttribute('class', 'two columns');

			// Number of columns
			$label = Widget::Label(__('Column names'));
			$label->setAttribute('class', 'column');
			$label->appendChild(
				new XMLElement('i', __('Optional'))
			);
			$label->appendChild(Widget::Input(
				"fields[{$order}][columns]", $this->get('columns')
			));
			$group->appendChild($label);			

			$label = Widget::Label(null, null, 'column');
			$input = Widget::Input('fields['.$order.'][header]', '1', 'checkbox');
			if ($this->get('header') == '1') $input->setAttribute('checked', 'checked');
			$label->setValue(__('%s Use Header', array($input->generate())));
			$group->appendChild($label);

			$wrapper->appendChild($group);
			
			// Default options
			$div = new XMLElement('div', null, array('class' => 'two columns'));
			$this->appendRequiredCheckbox($div);
			$this->appendShowColumnCheckbox($div);

			$wrapper->appendChild($div);			
		}

		/**
		 * Save field settings in section editor.
		 */
		public function commit() {
			if(!parent::commit()) return false;

			$id = $this->get('id');
			$handle = $this->handle();

			if($id === false) return false;

			$fields = array(
				'field_id' => $id,
				'header' => $this->get('header'),
				'columns' => $this->get('columns'),
			);
			return Symphony::Database()->insert($fields, "tbl_fields_{$handle}", true);
		}

		/*-------------------------------------------------------------------------
			Input:
		-------------------------------------------------------------------------*/
		public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null, $entry_id = null) {
			extension_field_keyvalues::appendAssets();

			// Label
			$label = Widget::Label($this->get('label'));
			if ($this->get('required') == 'no') {
				$label->appendChild(new XMLElement('i', __('Optional')));
			}
			$wrapper->appendChild($label);
			
			$element_name = $this->get('element_name');
			$table = new XMLElement('div', null, 
				array(
					'id' => $element_name, 
					'class' => 'ftab-table',
					//'data-ftab-rows' => $data['value'],
					'data-ftab-columns' => $this->get('columns'),
					'data-ftab-header' => $this->get('header')
				)
			);
			$div = Widget::Input("fields[$element_name][value]", htmlspecialchars($data['value']), 'text', 
				array("class" => "ftab-data", "style" => "display: none"));
						
			// 
			$wrapper->appendChild($table);
			$wrapper->appendChild($div);
			if (!is_null($flagWithError)) {
				$wrapper = Widget::Error($wrapper, $flagWithError);
			}
		}

		public function checkPostFieldData($data, &$message, $entry_id = null) {
			// Check required
			if ($this->get('required') == 'yes') {
				$message = __(
					"'%s' is a required field.", array( $this->get('label') )
				);

				return self::__MISSING_FIELDS__;
			}

			// Return if it's allowed to be empty (and is empty)
			if (isset($data['value']) && strlen($data['value']) == 0) return self::__OK__;

			return self::__OK__;
		}

		public function processRawFieldData($data, &$status, &$message=null, $simulate=false, $entry_id=null) {
			$status = self::__OK__;

			// do nothing
			if(empty($data)) return null;

			return $data;
		}


		/*-------------------------------------------------------------------------
			Output:
		-------------------------------------------------------------------------*/

		public function fetchIncludableElements() {
			return array(
				$this->get('element_name')
			);
		}

		public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null) {
			if ($data['value'] == "" || $data['value'] == "[]") return;

			$field = new XMLElement($this->get('element_name'));
			$field->setAttribute('mode', 'normal');

			try {
				$json_decoded = json_decode($data['value']);
				$table = new XMLElement('table');
				// header
				if ($this->get('header') == '1') {
					$object = $json_decoded[0];
					$array = get_object_vars($object);
					$properties = array_keys($array);
					if (count($properties) > 0) {
						$tr = new XMLElement('head');
						foreach ($properties as $prop) {
							$td = new XMLElement('cell', $prop);
							$tr->appendChild($td);
						}	
						$table->appendChild($tr);
					}
				}
				// data
				foreach($json_decoded as $result) {
					$tr = new XMLElement('row');
					$array = get_object_vars($result);
					foreach ($array as $arr) {
						$td = new XMLElement('cell', $arr);
						$tr->appendChild($td);
					}	
					$table->appendChild($tr);
				}
				$field->appendChild($table);
			}
			catch (Exception $ex) {
				return;
			}
			$wrapper->appendChild($field);			
		}

		/**
		 * At this stage we will just return the Key's
		 */
		public function getParameterPoolValue(array $data, $entry_id=NULL) {
			return $data['value'];
		}

		public function prepareTableValue($data, XMLElement $link = null, $entry_id = null) {
			if(is_null($data)) return __('None');

			return parent::prepareTableValue(array('value' => $data['value']), $link);
		}
	}
