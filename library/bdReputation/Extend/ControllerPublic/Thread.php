<?php

class bdReputation_Extend_ControllerPublic_Thread extends XFCP_bdReputation_Extend_ControllerPublic_Thread
{

    protected function _getDefaultViewParams(array $forum, array $thread, array $posts, $page = 1, array $viewParams = array())
    {
        $viewParams = parent::_getDefaultViewParams($forum, $thread, $posts, $page, $viewParams);

        if (!empty($viewParams['posts'])) {
            /** @var bdReputation_Model_Given $givenModel */
            $givenModel = $this->getModelFromCache('bdReputation_Model_Given');
            $existed = $givenModel->getAllFromGivenUserForPostIds(
                XenForo_Visitor::getUserId(),
                array_keys($viewParams['posts'])
            );

            $existedByPostId = array();
            foreach ($existed as $given) {
                $existedByPostId[$given['post_id']] = $given['points'];
            }

            foreach ($viewParams['posts'] as &$postRef) {
                $postRef['bdReputation_given'] = !empty($existedByPostId[$postRef['post_id']])
                    ? $existedByPostId[$postRef['post_id']]
                    : 0;
                $postRef['bdReputation_given'] = intval($postRef['bdReputation_given']);

                if (bdReputation_Option::get('latestGiven')) {
                    if (empty($postRef['xf_bdreputation_latest_given'])) {
                        // this post data hasn't been built yet
                        // we will have to query the database (the result will be cached though)
                        $postRef['bdReputation_latestGiven'] = $givenModel->updatePostLatestGiven($postRef['post_id']);
                    } else {
                        $postRef['bdReputation_latestGiven'] = XenForo_Permission::unserializePermissions($postRef['xf_bdreputation_latest_given']);
                    }
                }
            }
        }

        return $viewParams;
    }


} 