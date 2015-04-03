<?php

class bdReputation_DevHelper_Config extends DevHelper_Config_Base
{
    protected $_dataClasses = array(
        'given' => array(
            'name' => 'given',
            'camelCase' => 'Given',
            'camelCasePlural' => false,
            'camelCaseWSpace' => 'Given',
            'camelCasePluralWSpace' => false,
            'fields' => array(
                'given_id' => array('name' => 'given_id', 'type' => 'uint', 'autoIncrement' => true),
                'post_id' => array('name' => 'post_id', 'type' => 'uint', 'required' => true),
                'received_user_id' => array('name' => 'received_user_id', 'type' => 'uint', 'required' => true),
                'received_username' => array('name' => 'received_username', 'type' => 'string', 'length' => 50, 'required' => true),
                'given_user_id' => array('name' => 'given_user_id', 'type' => 'uint', 'required' => true),
                'given_username' => array('name' => 'given_username', 'type' => 'string', 'length' => 50, 'required' => true),
                'give_date' => array('name' => 'give_date', 'type' => 'uint', 'required' => true),
                'points' => array('name' => 'points', 'type' => 'int', 'required' => true),
                'comment' => array('name' => 'comment', 'type' => 'string', 'length' => 255),
            ),
            'phrases' => array(),
            'id_field' => 'given_id',
            'title_field' => 'received_username',
            'primaryKey' => array('given_id'),
            'indeces' => array(
                'post_id_given_user_id' => array(
                    'name' => 'post_id_given_user_id',
                    'fields' => array('post_id', 'given_user_id'),
                    'type' => 'UNIQUE',
                ),
                'received_user_id' => array('name' => 'received_user_id', 'fields' => array('received_user_id'), 'type' => 'NORMAL'),
                'given_user_id' => array('name' => 'given_user_id', 'fields' => array('given_user_id'), 'type' => 'NORMAL'),
            ),
            'files' => array(
                'data_writer' => array('className' => 'bdReputation_DataWriter_Given', 'hash' => 'f9a1f9ab6b6eeb0fdf2ee2d8c961084b'),
                'model' => array('className' => 'bdReputation_Model_Given', 'hash' => '2ad01d413d3c809cbfe49366a8ce2380'),
                'route_prefix_admin' => false,
                'controller_admin' => false,
            ),
        ),
    );
    protected $_dataPatches = array(
        'xf_user' => array(
            'xf_bdreputation_given' => array('name' => 'xf_bdreputation_given', 'type' => 'int', 'default' => 0),
        ),
        'xf_post' => array(
            'xf_bdreputation_latest_given' => array('name' => 'xf_bdreputation_latest_given', 'type' => 'serialized'),
        ),
    );
    protected $_exportPath = '/Users/sondh/XenForo/bdReputation';
    protected $_exportIncludes = array();

    /**
     * Return false to trigger the upgrade!
     **/
    protected function _upgrade()
    {
        return true; // remove this line to trigger update

        /*
        $this->addDataClass(
            'name_here',
            array( // fields
                'field_here' => array(
                    'type' => 'type_here',
                    // 'length' => 'length_here',
                    // 'required' => true,
                    // 'allowedValues' => array('value_1', 'value_2'),
                    // 'default' => 0,
                    // 'autoIncrement' => true,
                ),
                // other fields go here
            ),
            array('primary_key_1', 'primary_key_2'), // or 'primary_key', both are okie
            array( // indeces
                array(
                    'fields' => array('field_1', 'field_2'),
                    'type' => 'NORMAL', // UNIQUE or FULLTEXT
                ),
            ),
        );
        */
    }
}