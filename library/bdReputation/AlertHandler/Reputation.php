<?php

class bdReputation_AlertHandler_Reputation extends XenForo_AlertHandler_DiscussionMessage_Post
{
    public function renderHtml(array $item, XenForo_View $view)
    {
        $item['extra_data'] = unserialize($item['extra_data']);

        return parent::renderHtml($item, $view);
    }
}