<?php

class bdReputation_Extend_ControllerPublic_Post extends XFCP_bdReputation_Extend_ControllerPublic_Post
{
    public function actionReputationGive()
    {
        $postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

        /** @var XenForo_ControllerHelper_ForumThreadPost $ftpHelper */
        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);

        /** @var bdReputation_Model_Given $givenModel */
        $givenModel = $this->getModelFromCache('bdReputation_Model_Given');
        if (!$givenModel->canGive($post, $thread, $forum)) {
            return $this->responseNoPermission();
        }

        $given = $givenModel->getOneFromGivenUserForPostId(XenForo_Visitor::getUserId(), $post['post_id']);
        if (!empty($given)) {
            return $this->responseReroute(__CLASS__, 'reputation-view');
        }

        $givenModel->assertWithinLimit($post, $thread, $forum);

        $maximumGivenPoints = $givenModel->getMaximumGivenPoints();
        $canSpecify = $givenModel->canSpecify();
        $canGiveNegative = $givenModel->canGiveNegative();

        if (!$this->isConfirmedPost()) {
            // phase 1, display form
            $viewParams = array(
                'post' => $post,
                'thread' => $thread,
                'forum' => $forum,
                'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),

                'bdReputation_points' => $maximumGivenPoints,
                'bdReputation_canSpecify' => $canSpecify,
                'bdReputation_canGiveNegative' => $canGiveNegative,
            );

            return $this->responseView('bdReputation_ViewPublic_Post_ReputationGive', 'bdreputation_post_reputation_give', $viewParams);
        } else {
            $input = $this->_input->filter(array(
                'positive' => XenForo_Input::UINT,
                'specify' => XenForo_Input::INT,
                'comment' => XenForo_Input::STRING,
            ));

            if (!$canSpecify) {
                if (!$canGiveNegative) {
                    $points = $maximumGivenPoints;
                } else {
                    if ($input['positive']) {
                        $points = $maximumGivenPoints;
                    } else {
                        $points = -1 * $maximumGivenPoints;
                    }
                }
            } else {
                $low = (!$canGiveNegative) ? 0 : (-1 * $maximumGivenPoints);
                $high = $maximumGivenPoints;

                if ($input['specify'] < $low OR $input['specify'] > $high) {
                    return $this->responseError(new XenForo_Phrase('bdreputation_your_range_of_giving_points_is_x_to_y', array('low' => $low, 'high' => $high)));
                }

                $points = $input['specify'];
            }

            if (empty($points)) {
                return $this->responseNoPermission();
            }

            $givenModel->give($post, $points, $input['comment']);

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('posts', $post),
                new XenForo_Phrase('bdreputation_thanks'),
                array('linkPhrase' => ($givenModel->canView($post, $thread, $forum) ? new XenForo_Phrase('bdreputation_reputation_view') : ' '))
            );
        }
    }

    public function actionReputationView()
    {
        $postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

        /** @var XenForo_ControllerHelper_ForumThreadPost $ftpHelper */
        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);

        /** @var bdReputation_Model_Given $givenModel */
        $givenModel = $this->getModelFromCache('bdReputation_Model_Given');
        if (!$givenModel->canView($post, $thread, $forum)) {
            return $this->responseNoPermission();
        }

        $fetchOptions = array(
            'join' => bdReputation_Model_Given::FETCH_GIVEN_USER | bdReputation_Model_Given::FETCH_POST,
            'order' => 'give_date',
            'direction' => 'desc',
        );

        $records = $givenModel->getAllForPostId($post['post_id'], $fetchOptions);

        $viewParams = array(
            'post' => $post,
            'thread' => $thread,
            'forum' => $forum,
            'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),

            'records' => $records,
        );

        return $this->responseView('bdReputation_ViewPublic_Post_ReputationView', 'bdreputation_post_reputation_view', $viewParams);
    }
}