<?php

namespace Package\WebDevTools\Laravel\Validation;

use Illuminate\Validation\Validator as BaseValidator;

class Validator extends BaseValidator
{
    /**
     * Validate as a phone number.
     *
     * @param $attribute
     * @param $value
     *
     * @return bool
     */
    public function validatePhone($attribute, $value)
    {
        $value = preg_replace('/\s+/', '', $value);

        return $this->validateRegex($attribute, $value, ["/^(([+][\d]{2})|(0))[\d]{10}$/"]);
    }

    /**
     * Validate as a name.
     *
     * @param $attribute
     * @param $value
     *
     * @return bool
     */
    public function validateName($attribute, $value)
    {
        return $this->validateRegex($attribute, $value, ["/^[a-zA-Z-']+\s[a-zA-Z-']+$/"]);
    }

    /**
     * Custom validation for dates that use the date/time picker.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function validateDate($attribute, $value)
    {
        return $this->validateDatetime($attribute, $value, ['Y-m-d']);
    }

    /**
     * Custom validation for times that use the date/time picker.
     *
     * @param $attribute
     * @param $value
     *
     * @return bool
     */
    public function validateTime($attribute, $value)
    {
        return $this->validateDatetime($attribute, $value, ['H:i:s']);
    }

    /**
     * Custom validation for datetimes that use the date/time picker.
     *
     * @param       $attribute
     * @param       $value
     * @param array $parameters
     *
     * @return bool
     */
    public function validateDatetime($attribute, $value, array $parameters = [])
    {
        $format = isset($parameters[0]) ? $parameters[0] : 'Y-m-d H:i:s';
        $regex = $format;
        $regex = str_replace(['Y', 'm', 'd', 'H', 'i', 's'], ['[0-9]{4}', '[0-9]{2}', '[0-9]{2}', '[0-9]{2}', '[0-9]{2}', '[0-9]{2}'], $regex);
        $regex = str_replace(['[0-9]', '-', ' '], ['[\d]', '\-', '\s'], $regex);
        $regex = '/^'.$regex.'$/';

        return $this->validateDateFormat($attribute, $value, [$format])
               && $this->validateRegex($attribute, $value, [$regex]);
    }
}
