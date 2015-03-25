<?php

class bdReputation_Option
{
    public static function get($key)
    {
        switch ($key) {
            case 'latestGivenMax':
                return 3;
            case 'needsInjector':
                return true; // return XenForo_Application::get('options')->currentVersionId < 1000270;
        }

        return XenForo_Application::get('options')->get('bdReputation_' . $key);
    }
}