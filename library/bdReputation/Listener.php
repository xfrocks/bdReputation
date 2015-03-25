<?php

class bdReputation_Listener
{
    public static function load_class($class, array &$extend)
    {
        static $classes = array(
            'XenForo_ControllerPublic_Thread',
            'XenForo_ControllerPublic_Post',
            'XenForo_ControllerPublic_Member',

            'XenForo_DataWriter_DiscussionMessage_Post',

            'XenForo_Model_Post',
            'XenForo_Model_User',
        );

        if (in_array($class, $classes)) {
            $extend[] = str_replace('XenForo_', 'bdReputation_Extend_', $class);
        }
    }

    public static function load_class_importer($class, array &$extend)
    {
        if (strpos($class, 'vBulletin') != false AND !defined('bdReputation_Extend_Importer_vBulletin_LOADED')) {
            $extend[] = 'bdReputation_Extend_Importer_vBulletin';
        }
    }
}