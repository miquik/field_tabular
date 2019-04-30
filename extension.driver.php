<?php

	Class extension_field_tabular extends Extension{
		
		public function about() {
			return array( 
				'name' => 'tabular',
				'version' => '0.1',
				'release-date' => '2019-04-25',
				'author' => array( 
					'name' => 'Michele Rosa',
				),
				'description' => 'Editable table field for backend'
			);
		}


		/*-------------------------------------------------------------------------
			Installation:
		-------------------------------------------------------------------------*/
		public function install(){
			try {
				Symphony::Database()->query("
					CREATE TABLE IF NOT EXISTS `tbl_fields_tabular` (
						`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
						`field_id` INT(11) UNSIGNED NOT NULL,
						`columns` VARCHAR(255) NOT NULL,
						`header` TINYINT(1) UNSIGNED NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `field_id` (`field_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				");
			}
			catch (Exception $ex) {
				$extension = $this->about();
				Administration::instance()->Page->pageAlert(__('An error occurred while installing %s. %s', array($extension['name'], $ex->getMessage())), Alert::ERROR);
				return false;
			}
			// No common preferences
			return true;
		}

		public function uninstall(){
			if(parent::uninstall() == true){
				try {
					Symphony::Database()->query("DROP TABLE `tbl_fields_tabular`");

					return true;
				}
				catch (Exception $ex) {
					$extension = $this->about();
					Administration::instance()->Page->pageAlert(__('An error occurred while uninstalling %s. %s', array($extension['name'], $ex->getMessage())), Alert::ERROR);
					return false;
				}
			}

			return false;
		}

    	/*-------------------------------------------------------------------------
    		Delegate
    	-------------------------------------------------------------------------*/

		public function getSubscribedDelegates(){
			return array(
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'InitaliseAdminPageHead',
					'callback'	=> 'initaliseAdminPageHead'
				)
			);
		}


		/*-------------------------------------------------------------------------
			Utilities:
		-------------------------------------------------------------------------*/
		public static function initaliseAdminPageHead() {
			$page = Administration::instance()->Page;
			
			// only on publish pages
			if(!$page instanceOf contentPublish) return;

			// which are showing new/edit form
			$callback = Administration::instance()->getPageCallback();
			if(!in_array($callback['context']['page'], array('new', 'edit'))) return;
			
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/field_tabular/assets/jsgrid.css', 'screen', 200);
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/field_tabular/assets/jsgrid-theme.css', 'screen', 210);
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/field_tabular/assets/tabular.publish.css', 'screen', 210);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/field_tabular/assets/jsgrid.js', 200);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/field_tabular/assets/tabular.publish.js', 200);			
		}
	}
