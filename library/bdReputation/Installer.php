<?php

class bdReputation_Installer
{
    /* Start auto-generated lines of code. Change made will be overwriten... */

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
            'dropQuery' => 'DROP TABLE IF EXISTS `xf_bdreputation_given`',
        ),
    );
    protected static $_patches = array(
        array(
            'table' => 'xf_user',
            'field' => 'xf_bdreputation_given',
            'showTablesQuery' => 'SHOW TABLES LIKE \'xf_user\'',
            'showColumnsQuery' => 'SHOW COLUMNS FROM `xf_user` LIKE \'xf_bdreputation_given\'',
            'alterTableAddColumnQuery' => 'ALTER TABLE `xf_user` ADD COLUMN `xf_bdreputation_given` INT(11) DEFAULT \'0\'',
            'alterTableDropColumnQuery' => 'ALTER TABLE `xf_user` DROP COLUMN `xf_bdreputation_given`',
        ),
        array(
            'table' => 'xf_post',
            'field' => 'xf_bdreputation_latest_given',
            'showTablesQuery' => 'SHOW TABLES LIKE \'xf_post\'',
            'showColumnsQuery' => 'SHOW COLUMNS FROM `xf_post` LIKE \'xf_bdreputation_latest_given\'',
            'alterTableAddColumnQuery' => 'ALTER TABLE `xf_post` ADD COLUMN `xf_bdreputation_latest_given` MEDIUMBLOB',
            'alterTableDropColumnQuery' => 'ALTER TABLE `xf_post` DROP COLUMN `xf_bdreputation_latest_given`',
        ),
    );

    public static function install($existingAddOn, $addOnData)
    {
        $db = XenForo_Application::get('db');

        foreach (self::$_tables as $table) {
            $db->query($table['createQuery']);
        }

        foreach (self::$_patches as $patch) {
            $tableExisted = $db->fetchOne($patch['showTablesQuery']);
            if (empty($tableExisted)) {
                continue;
            }

            $existed = $db->fetchOne($patch['showColumnsQuery']);
            if (empty($existed)) {
                $db->query($patch['alterTableAddColumnQuery']);
            }
        }

        self::installCustomized($existingAddOn, $addOnData);
    }

    public static function uninstall()
    {
        $db = XenForo_Application::get('db');

        foreach (self::$_patches as $patch) {
            $tableExisted = $db->fetchOne($patch['showTablesQuery']);
            if (empty($tableExisted)) {
                continue;
            }

            $existed = $db->fetchOne($patch['showColumnsQuery']);
            if (!empty($existed)) {
                $db->query($patch['alterTableDropColumnQuery']);
            }
        }

        foreach (self::$_tables as $table) {
            $db->query($table['dropQuery']);
        }

        self::uninstallCustomized();
    }

    /* End auto-generated lines of code. Feel free to make changes below */

    public static function installCustomized($existingAddOn, $addOnData)
    {
        if (XenForo_Application::$versionId < 1020000) {
            throw new XenForo_Exception('[bd] Reputation System requires XenForo 1.2.0+', true);
        }

        $db = XenForo_Application::getDb();

        $existingVersionId = 0;
        if (!empty($existingAddOn['version_id'])) {
            $existingVersionId = $existingAddOn['version_id'];
        }

        if ($existingVersionId < 1) {
            $db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, 'forum', 'bdReputation_give', permission_value, 0
				FROM xf_permission_entry
				WHERE permission_group_id = 'forum' AND permission_id = 'postThread'
			");

            $db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, 'forum', 'bdReputation_view', permission_value, 0
				FROM xf_permission_entry
				WHERE permission_group_id = 'forum' AND permission_id = 'postThread'
			");

            $db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, 'general', 'bdReputation_exempt', permission_value, 0
				FROM xf_permission_entry
				WHERE permission_group_id = 'general' AND permission_id = 'bypassFloodCheck'
			");
            $db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, 'general', 'bdReputation_giveNegative', permission_value, 0
				FROM xf_permission_entry
				WHERE permission_group_id = 'general' AND permission_id = 'bypassFloodCheck'
			");
            $db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, 'general', 'bdReputation_specify', permission_value, 0
				FROM xf_permission_entry
				WHERE permission_group_id = 'general' AND permission_id = 'bypassFloodCheck'
			");
            $db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, 'general', 'bdReputation_viewGlobal', permission_value, 0
				FROM xf_permission_entry
				WHERE permission_group_id = 'general' AND permission_id = 'bypassFloodCheck'
			");
        }

        // since 1.2
        $db->query("REPLACE INTO `xf_content_type` VALUES ('reputation', 'bdReputation', '')");
        $db->query("REPLACE INTO `xf_content_type_field` VALUES ('reputation', 'alert_handler_class', 'bdReputation_AlertHandler_Reputation')");

        /** @var XenForo_Model_ContentType $contentTypeModel */
        $contentTypeModel = XenForo_Model::create('XenForo_Model_ContentType');
        $contentTypeModel->rebuildContentTypeCache();
    }

    public static function uninstallCustomized()
    {
        // customized uninstall script goes here
    }

}