<?php

class bdReputation_Extend_DataWriter_DiscussionMessage_Post extends XFCP_bdReputation_Extend_DataWriter_DiscussionMessage_Post
{
    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['xf_post']['xf_bdreputation_latest_given'] = array('type' => XenForo_DataWriter::TYPE_SERIALIZED, 'default' => 'a:0:{}');

        return $fields;
    }
}