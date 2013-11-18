<?php
class bdReputation_Installer {
	protected static $_tables = array(
		'given' => array(
			'createQuery' => 'CREATE TABLE IF NOT EXISTS `xf_bdreputation_given` (
				`given_id` INT(10) UNSIGNED AUTO_INCREMENT
				,`post_id` INT(10) UNSIGNED NOT NULL
				,`received_user_id` INT(10) UNSIGNED NOT NULL
				,`received_username` VARCHAR(50) NOT NULL
				,`given_user_id` INT(10) UNSIGNED NOT NULL
				,`given_username` VARCHAR(50) NOT NULL
				,`give_date` INT(10) UNSIGNED NOT NULL
				,`points` INT(11) NOT NULL
				,`comment` VARCHAR(255)
				, PRIMARY KEY (`given_id`)
				,UNIQUE INDEX `post_id_given_user_id` (`post_id`,`given_user_id`)
				, INDEX `received_user_id` (`received_user_id`)
				, INDEX `given_user_id` (`given_user_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
			'dropQuery' => false
		)
	);
	protected static $_patches = array(
		array(
			'table' => 'xf_user',
			'field' => 'xf_bdreputation_given',
			'showColumnsQuery' => 'SHOW COLUMNS FROM `xf_user` LIKE \'xf_bdreputation_given\'',
			'alterTableAddColumnQuery' => 'ALTER TABLE `xf_user` ADD COLUMN `xf_bdreputation_given` INT(11) DEFAULT \'0\'',
			'alterTableDropColumnQuery' => false
		)
	);

	public static function install() {
		$db = XenForo_Application::get('db');

		foreach (self::$_tables as $table) {
			$db->query($table['createQuery']);
		}
		
		foreach (self::$_patches as $patch) {
			$existed = $db->fetchOne($patch['showColumnsQuery']);
			if (empty($existed)) {
				$db->query($patch['alterTableAddColumnQuery']);
			}
		}
		
		// since 1.2
		$db->query("REPLACE INTO `xf_content_type` VALUES ('reputation', 'bdReputation', '')");
		$db->query("REPLACE INTO `xf_content_type_field` VALUES ('reputation', 'alert_handler_class', 'bdReputation_AlertHandler_Reputation')");
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
		
		// since 1.3
		$existed = $db->fetchOne("SHOW COLUMNS FROM `xf_post` LIKE 'xf_bdreputation_latest_given'");
		if (empty($existed)) {
			$db->query("ALTER TABLE `xf_post` ADD COLUMN `xf_bdreputation_latest_given` BLOB");
		}
	}
	
	public static function uninstall() {
		// TODO
	}
}