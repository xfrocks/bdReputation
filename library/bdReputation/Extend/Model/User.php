<?php

class bdReputation_Extend_Model_User extends XFCP_bdReputation_Extend_Model_User
{
    public function prepareUser(array $user)
    {
        if (XenForo_Visitor::hasInstance()) {
            $user['bdReputation_canView'] = $this->bdReputation_canViewUser($user);
        }

        return parent::prepareUser($user);
    }


    public function bdReputation_canViewAnyone(array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'bdReputation_viewGlobal');
    }

    public function bdReputation_canViewUser(array $user, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if ($user['user_id'] == 0) {
            // special case: always impossible to view guest
            return false;
        }

        $canView = $this->bdReputation_canViewAnyone($viewingUser);

        if (!$canView) {
            // special case: always possible to view self
            if ($user['user_id'] == $viewingUser['user_id']) {
                $canView = true;
            }
        }

        return $canView;
    }

}