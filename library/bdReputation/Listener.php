<?php
class bdReputation_Listener {
	public static function load_class($class, array &$extend) {
		static $classes = array(
			'XenForo_ControllerPublic_Thread',
			'XenForo_ControllerPublic_Post',
			'XenForo_ControllerPublic_Member',
		
			'XenForo_DataWriter_DiscussionMessage_Post',
		);
		
		if (in_array($class, $classes)) {
			$extend[] = str_replace('XenForo_', 'bdReputation_Extend_', $class);
		}
	}
	
	public static function load_class_importer($class, array &$extend) {
		if (strpos($class, 'vBulletin') != false AND !defined('bdReputation_Extend_Importer_vBulletin_LOADED')) {
			$extend[] = 'bdReputation_Extend_Importer_vBulletin';
		}		
	}
	
	public static function template_create($templateName, array &$params, XenForo_Template_Abstract $template) {
		switch ($templateName) {
			case 'thread_view':
				$template->preloadTemplate('bdreputation_injector');
				$template->preloadTemplate('bdreputation_message_user_info_extra');
				$template->preloadTemplate('bdreputation_message_latest_given');
				break;
			case 'member_view':
				$template->preloadTemplate('bdreputation_member_view_tabs_heading');
				$template->preloadTemplate('bdreputation_member_view_tabs_content');
				break;
			case 'account_alert_preferences':
				$template->preloadTemplate('bdreputation_account_alerts_achievements');
				break;
		}		
	}
	
	public static function template_hook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template) {
		switch ($hookName) {
			case 'message_user_info_extra':
				$ourTemplate = $template->create('bdreputation_message_user_info_extra', $hookParams);
				$ourTemplate->setParam('bdReputation_canViewUser', XenForo_Model::create('bdReputation_Model_Given')->canViewUser($hookParams['user']));
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'member_view_tabs_heading':
				$ourTemplate = $template->create('bdreputation_member_view_tabs_heading', $template->getParams());
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'member_view_tabs_content':
				$ourTemplate = $template->create('bdreputation_member_view_tabs_content', $template->getParams());
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'account_alerts_achievements':
				$ourTemplate = $template->create('bdreputation_account_alerts_achievements', $template->getParams());
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'message_content':
				// since 1.3
				if (bdReputation_Option::get('latestGiven') AND !empty($hookParams['message']['xf_bdreputation_latest_given'])) {
					if (!is_array($hookParams['message']['xf_bdreputation_latest_given'])) {
						$hookParams['message']['xf_bdreputation_latest_given'] = unserialize($hookParams['message']['xf_bdreputation_latest_given']);
					}
					
					if (!empty($hookParams['message']['xf_bdreputation_latest_given'])) {
						$ourTemplate = $template->create('bdreputation_message_latest_given', $hookParams);
						$rendered = $ourTemplate->render();
						$contents .= $rendered;
					}
				}
				break;
			case 'footer':
				if (isset($GLOBALS['ReputationInjectorData']) AND bdReputation_Option::get('needsInjector')) {
					$ourParams = array(
						'ReputationInjectorData' => $GLOBALS['ReputationInjectorData'],
						'placeholder' => array(
							'postId' => '-35537',
						),
					);
					
					$ourTemplate = $template->create('bdreputation_injector');
					$ourTemplate->setParams($ourParams);
					$rendered = $ourTemplate->render();
					$contents .= $rendered;
				}
				break;
		}
	}
}