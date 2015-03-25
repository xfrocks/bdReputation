<?php

class bdReputation_DataWriter_Given extends XenForo_DataWriter
{
    protected function _postSave()
    {
        if ($this->isInsert()) {
            $this->_db->query("
				UPDATE `xf_user`
				SET xf_bdreputation_given = xf_bdreputation_given + ?
				WHERE user_id = ?
			", array(
                $this->get('points'),
                $this->get('received_user_id'),
            ));
        } else {
            $this->_db->query("
				UPDATE `xf_user`
				SET xf_bdreputation_given = xf_bdreputation_given + ?
				WHERE user_id = ?
			", array(
                $this->get('points') - $this->getExisting('points'),
                $this->get('received_user_id'),
            ));
        }

        if (XenForo_Model_Alert::userReceivesAlert(array('user_id' => $this->get('received_user_id')), 'reputation', 'post')) {
            XenForo_Model_Alert::alert($this->get('received_user_id'),
                $this->get('given_user_id'), $this->get('given_username'),
                'reputation', $this->get('post_id'),
                'post', array('points' => $this->get('points'), 'comment' => $this->get('comment'))
            );
        }
    }

    protected function _postSaveAfterTransaction()
    {
        if (bdReputation_Option::get('latestGiven')) {
            // caches the latest given
            // since 1.3
            $this->_getGivenModel()->updatePostLatestGiven($this->get('post_id'));
        }
    }

    protected function _getFields()
    {
        return array(
            'xf_bdreputation_given' => array(
                'given_id' => array('type' => 'uint', 'autoIncrement' => true),
                'post_id' => array('type' => 'uint', 'required' => true),
                'received_user_id' => array('type' => 'uint', 'required' => true),
                'received_username' => array('type' => 'string', 'required' => true, 'maxLength' => 50),
                'given_user_id' => array('type' => 'uint', 'required' => true),
                'given_username' => array('type' => 'string', 'required' => true, 'maxLength' => 50),
                'give_date' => array('type' => 'uint', 'default' => XenForo_Application::$time),
                'points' => array('type' => 'int', 'required' => true),
                'comment' => array('type' => 'string', 'maxLength' => 255)
            )
        );
    }

    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'given_id')) {
            return false;
        }

        return array('xf_bdreputation_given' => $this->_getGivenModel()->getGivenById($id));
    }

    protected function _getUpdateCondition($tableName)
    {
        $conditions = array();

        foreach (array('given_id') as $field) {
            $conditions[] = $field . ' = ' . $this->_db->quote($this->getExisting($field));
        }

        return implode(' AND ', $conditions);
    }

    /**
     * @return bdReputation_Model_Given
     */
    protected function _getGivenModel()
    {
        return $this->getModelFromCache('bdReputation_Model_Given');
    }
}