<?php
class bdReputation_Extend_ControllerPublic_Thread extends XFCP_bdReputation_Extend_ControllerPublic_Thread {
	public function actionIndex() {
		$response = parent::actionIndex();
		
		if ($response instanceof XenForo_ControllerResponse_View) {
			$givenModel = $this->getModelFromCache('bdReputation_Model_Given');
			$existed = $givenModel->getAllFromGivenUserForPostIds(XenForo_Visitor::getUserId(), array_keys($response->params['posts']));
			
			$existedSimple = array();
			foreach ($existed as $given) {
				$existedSimple[$given['post_id']] = $given['points'];
			}
			
			$postsSimple = array();
			$postIdToQueryLatestGiven = array();
			
			foreach ($response->params['posts'] as &$post) {
				$post['bdReputation_canView'] = $givenModel->canView($post, $response->params['thread'], $response->params['forum']);
				$post['bdReputation_canGive'] = $givenModel->canGive($post, $response->params['thread'], $response->params['forum']);
				$post['bdRepubation_given'] = !empty($existedSimple[$post['post_id']]) ? $existedSimple[$given['post_id']] : 0;
				
				if (bdReputation_Option::get('latestGiven')) {
					if (empty($post['xf_bdreputation_latest_given'])) {
						// this post data hasn't been built yet
						// we will have to query the database
						$postIdToQueryLatestGiven[] = $post['post_id'];
						$post['xf_bdreputation_latest_given'] = array();
					} else {
						$post['xf_bdreputation_latest_given'] = unserialize($post['xf_bdreputation_latest_given']);
					}
				}
				
				$postsSimple[] = array(
					'post_id' => $post['post_id'],
					'user_id' => $post['user_id'],
					'bdReputation_canView' => $post['bdReputation_canView'],
					'bdReputation_canGive' => $post['bdReputation_canGive'],
					'bdRepubation_given' => $post['bdRepubation_given'],
				);
			}
			
			if (!empty($postIdToQueryLatestGiven)) {
				$latestGivenForPosts = $givenModel->getAllForPostId($postIdToQueryLatestGiven, array('order' => 'give_date', 'direction' => 'asc'));
				$latestGivenMax = bdReputation_Option::get('latestGivenMax');
				foreach ($latestGivenForPosts as $latestGiven) {
					$response->params['posts'][$latestGiven['post_id']]['xf_bdreputation_latest_given'][] = $latestGiven;
					
					if (count($response->params['posts'][$latestGiven['post_id']]['xf_bdreputation_latest_given']) > $latestGivenMax) {
						array_shift($response->params['posts'][$latestGiven['post_id']]['xf_bdreputation_latest_given']);
					}
				}
			}
			
			$GLOBALS['ReputationInjectorData'] = array(
				'posts' => $postsSimple,
				'visitorUserId' => XenForo_Visitor::getUserId(),
			);
		}
		
		return $response;
	}
} 