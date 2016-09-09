<?php

if (! function_exists('settings')) {
    function settings($key, $default = null, $extraColumns = [])
    {
        $settings = app('settings');

        if (is_null($key)) {
            return $settings;
        }

        if (! empty($extraColumns) && is_array($extraColumns)) {
            $settings->setExtraColumns($extraColumns);
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $settings->set($k, $v);
            }

            return;
        }

        return $settings->get($key, $default);
    }
}
