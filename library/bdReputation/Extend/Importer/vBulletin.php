<?php

define('bdReputation_Extend_Importer_vBulletin_LOADED', true);

class bdReputation_Extend_Importer_vBulletin extends XFCP_bdReputation_Extend_Importer_vBulletin
{
    public function getSteps()
    {
        $steps = parent::getSteps();

        // this one will replace the step in vBulletin 4 importer
        if (!empty($steps['reputation'])) {
            // do this to make sure our steps are close together
            unset($steps['reputation']);
        }

        $steps['reputation'] = array(
            'title' => 'Reputation ([bd] Reputation System)',
            'depends' => array('threads'),
        );

        $steps['reputationValue'] = array(
            'title' => 'Reputation Value ([bd] Reputation System, not recommend)',
            'depends' => array('reputation'),
        );

        return $steps;
    }

    public function stepReputation($start, array $options)
    {
        $options = array_merge(array(
            'max' => false,
            'limit' => 500,
            'processed' => 0,
        ), $options);

        $sDb = $this->_sourceDb;
        $prefix = $this->_prefix;
        $model = $this->_importModel;

        if ($options['max'] === false) {
            $data = $sDb->fetchRow('
				SELECT MAX(reputationid) AS max, COUNT(reputationid) AS rows
				FROM ' . $prefix . 'reputation
			');

            $options = array_merge($options, $data);
        }

        $reputations = $sDb->fetchAll($sDb->limit('
				SELECT *
				FROM ' . $prefix . 'reputation
				WHERE reputationid >= ' . $sDb->quote($start) . '
				ORDER BY reputationid
			', $options['limit']
        ));

        if (!$reputations) {
            return true;
        }

        $next = 0;
        $total = 0;

        $userids = array();
        $postids = array();

        foreach ($reputations AS $rep) {
            $userids[] = $rep['userid'];
            $userids[] = $rep['whoadded'];
            $postids[] = $rep['postid'];
        }

        $userIdMap = $model->getImportContentMap('user', $userids);
        $postIdMap = $model->getImportContentMap('post', $postids);

        /** @var XenForo_Model_User $userModel */
        $userModel = $model->getModelFromCache('XenForo_Model_User');
        $users = $userModel->getUsersByIds($userIdMap);

        XenForo_Db::beginTransaction();

        foreach ($reputations AS $rep) {
            $receivedUserId = $this->_mapLookUp($userIdMap, $rep['userid']);
            $givenUserId = $this->_mapLookUp($userIdMap, $rep['whoadded']);
            $postId = $this->_mapLookUp($postIdMap, $rep['postid']);

            if (strlen($rep['reason']) == 250) {
                // check the byte length only
                $rep['reason'] = ''; // reset it to avoid incorrect string value MySQL error
            }

            if ($receivedUserId > 0 && $givenUserId > 0 && $postId > 0) {
                $dw = XenForo_DataWriter::create('bdReputation_DataWriter_Given', XenForo_DataWriter::ERROR_ARRAY);
                $dw->set('post_id', $postId);
                $dw->set('received_user_id', $receivedUserId);
                $dw->set('received_username', $users[$receivedUserId]['username']);
                $dw->set('given_user_id', $givenUserId);
                $dw->set('given_username', $users[$givenUserId]['username']);
                $dw->set('give_date', $rep['dateline']);
                $dw->set('points', $rep['reputation']);
                $dw->set('comment', $rep['reason']);
                $dw->save();
            }

            $total++;
            $next = $rep['reputationid'] + 1;
        }

        XenForo_Db::commit();

        $options['processed'] += $total;
        $this->_session->incrementStepImportTotal($total);

        return array($next, $options, $this->_getProgressOutput($options['processed'], $options['rows']));
    }

    public function stepReputationValue($start, array $options)
    {
        $options = array_merge(array(
            'max' => false,
            'limit' => 500,
            'processed' => 0,
        ), $options);

        $sDb = $this->_sourceDb;
        $prefix = $this->_prefix;
        $model = $this->_importModel;

        if ($options['max'] === false) {
            $data = $sDb->fetchRow('
				SELECT MAX(userid) AS max, COUNT(userid) AS rows
				FROM ' . $prefix . 'USER
			');

            $options = array_merge($options, $data);
        }

        $users = $sDb->fetchAll($sDb->limit('
				SELECT userid, reputation
				FROM ' . $prefix . 'USER
				WHERE userid >= ' . $sDb->quote($start) . '
				ORDER BY userid
			', $options['limit']
        ));

        if (!$users) {
            return true;
        }

        $next = 0;
        $total = 0;

        $userids = array();

        foreach ($users AS $user) {
            $userids[] = $user['userid'];
        }

        $userIdMap = $model->getImportContentMap('user', $userids);

        XenForo_Db::beginTransaction();

        foreach ($users AS $user) {
            $importedUserId = $this->_mapLookUp($userIdMap, $user['userid']);

            if ($importedUserId) {
                $this->_db->query('
					UPDATE xf_user
					SET xf_bdreputation_given = ?
					WHERE user_id = ?
				', array($user['reputation'], $importedUserId));

                $total++;
            }

            $next = $user['userid'] + 1;
        }

        XenForo_Db::commit();

        $options['processed'] += $total;
        $this->_session->incrementStepImportTotal($total);

        return array($next, $options, $this->_getProgressOutput($options['processed'], $options['rows']));
    }
}