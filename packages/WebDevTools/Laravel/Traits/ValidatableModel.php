<?php

namespace Package\WebDevTools\Laravel\Traits;

trait ValidatableModel
{
    /**
     * Get an array of validation rules for the model.
     *
     * @return array
     */
    public static function getValidationRules()
    {
        if (!isset(static::$ValidationRules)) {
            return [];
        }

        $fields = func_get_args();
        if (count($fields) == 1 && is_array($fields)) {
            $fields = array_pop($fields);
        }
        $rules = [];

        foreach ($fields as $field) {
            if (isset(static::$ValidationRules[$field])) {
                $rules[$field] = static::$ValidationRules[$field];
            }
        }

        return $rules;
    }

    /**
     * Get an array of validation messages.
     *
     * @return array
     */
    public static function getValidationMessages()
    {
        if (!isset(static::$ValidationMessages)) {
            return [];
        }

        $fields = func_get_args();
        if (count($fields) == 1 && is_array($fields)) {
            $fields = array_pop($fields);
        }
        $messages = [];

        foreach (static::$ValidationMessages as $rule => $msg) {
            if (in_array(substr($rule, 0, stripos($rule, '.')), $fields)) {
                $messages[$rule] = $msg;
            }
        }

        return $messages;
    }
}
