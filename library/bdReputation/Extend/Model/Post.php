<?php

class bdReputation_Extend_Model_Post extends XFCP_bdReputation_Extend_Model_Post
{
    public function preparePost(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        $post['bdReputation_canView'] = $this->bdReputation_canViewPost($post, $thread, $forum, $null, $nodePermissions, $viewingUser);
        $post['bdReputation_canGive'] = $this->bdReputation_canGivePost($post, $thread, $forum, $null, $nodePermissions, $viewingUser);

        /** @var bdReputation_Extend_Model_User $userModel */
        $userModel = $this->_getUserModel();
        $post['bdReputation_canViewUser'] = $userModel->bdReputation_canViewUser($post, $viewingUser);

        return parent::preparePost($post, $thread, $forum, $nodePermissions, $viewingUser);
    }

    public function bdReputation_canViewPost(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if ($post['user_id'] == $viewingUser['user_id']) {
            // special case: always possible to view self
            return true;
        }

        if ($post['user_id'] == 0) {
            // special case: always impossible to view guest
            return false;
        }

        if (!$this->canViewPost($post, $thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) {
            return false;
        }


        return XenForo_Permission::hasContentPermission($nodePermissions, 'bdReputation_view');
    }

    public function bdReputation_canGivePost(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if ($post['user_id'] == $viewingUser['user_id']) {
            // special case: always impossible to give self
            return false;
        }

        if ($post['user_id'] == 0) {
            // special case: always impossible to give guest
            return false;
        }

        if (!$this->canViewPost($post, $thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser)) {
            return false;
        }

        return XenForo_Permission::hasContentPermission($nodePermissions, 'bdReputation_give');
    }

}