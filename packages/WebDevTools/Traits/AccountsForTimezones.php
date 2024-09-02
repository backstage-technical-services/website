<?php

namespace Package\WebDevTools\Traits;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;

trait AccountsForTimezones
{
    /**
     * Correct a date for storage (from user's timezone to UTC).
     *
     * @param Carbon $date
     * @param null   $offset
     *
     * @return Carbon
     * @throws Exception
     */
    public static function correctForDatabase(Carbon $date, $offset = null)
    {
        static::storeTzCorrection();
        return $date->addMinutes($offset !== null ? $offset : static::getTzCorrection());
    }

    /**
     * Correct a date for displaying (from UTC to user's timezone).
     *
     * @param Carbon $date
     * @param null   $offset
     *
     * @return Carbon
     * @throws Exception
     */
    public static function correctForDisplay(Carbon $date, $offset = null)
    {
        return $date->subMinutes($offset !== null ? $offset : static::getTzCorrection());
    }

    /**
     * Automatically store the offset in a session for later use.
     * Laravel 5 only.
     *
     * @return void
     */
    protected static function storeTzCorrection()
    {
        if (function_exists('request') && function_exists('session')) {
            $request = request();
            session([
                'tz_correction' => $request->has('TZ-OFFSET') ? (float)$request->get('TZ-OFFSET') : (float)$request->header('TZ-OFFSET'),
            ]);
        }
    }

    /**
     * Get the offset to use for timezone correction.
     *
     * @return float
     * @throws Exception
     */
    protected static function getTzCorrection()
    {
        if (function_exists('request') && function_exists('session')) {
            $request = request();
            $session = session();

            if ($request->has('TZ-OFFSET')) {
                return (float)$request->get('TZ-OFFSET');
            } else if ($request->header('TZ-OFFSET')) {
                return (float)$request->header('TZ-OFFSET');
            } else if ($session->has('tz_correction')) {
                return (float)$session->get('tz_correction');
            }
        }

        // Default to assuming the user is in Europe/London
        // and the server is storing values as UTC.
        $date   = new DateTime('now', new DateTimeZone('Europe/London'));
        $offset = round($date->getOffset() / 60, 2);

        return $offset * -1;
    }

    /**
     * Hook into the saving event to prepare any attributes for storage.
     * Laravel 5 only.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // When saving the model, correct any dates and remove the uncorrected values
        // so that MySQL doesn't try to update non-existent fields.
        static::saving(function ($model) {
            $attributes = $model->attributesToCorrect();
            foreach ($attributes as $attribute) {
                if (isset($model->attributes[$attribute])) {
                    // Force updating all attributes by resetting the "original". Not pretty, but makes sure they update.
                    $model->original[$attribute]   = Carbon::create(1970, 1, 1)->format($model->getDateFormat());
                    $model->attributes[$attribute] = static::correctForDatabase($model->asDateTime($model->attributes[$attribute]))
                                                           ->format($model->getDateFormat());
                    if (isset($model->attributes[$attribute . '__uncorrected'])) {
                        unset($model->attributes[$attribute . '__uncorrected']);
                    }
                    if (isset($model->original[$attribute . '__uncorrected'])) {
                        unset($model->original[$attribute . '__uncorrected']);
                    }
                }
            }
        });

        // After the model has been saved, re-process any dates
        // and correct the timezone for displaying.
        static::saved(function ($model) {
            $attributes = $model->attributesToCorrect();
            foreach ($attributes as $attribute) {
                $model->convertAttributeFromDatabase($attribute);
            }
        });
    }

    /**
     * Override the default database retrieval to automatically correct any specified attributes.
     * Laravel 5 only.
     *
     * @param array $attributes
     * @param null  $connection
     *
     * @return mixed
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection);

        if ($model->exists) {
            foreach ($model->attributesToCorrect() as $attribute) {
                $model->convertAttributeFromDatabase($attribute);
            }
        }


        return $model;
    }

    /**
     * Get the original, uncorrected version of a tz corrected date.
     * Laravel 5 only.
     *
     * @param $attributeName
     *
     * @return mixed
     */
    public function uncorrected($attributeName)
    {
        if ($this->shouldCorrectTimezone($attributeName) && isset($this->attributes[$attributeName . '__uncorrected'])) {
            return $this->asDateTime($this->attributes[$attributeName . '__uncorrected']);
        }
    }

    /**
     * Get the list of attributes to correct the timezone for.
     * Aimed for use with Laravel 5.
     *
     * @return array
     */
    protected function attributesToCorrect()
    {
        return isset($this->correct_tz) && is_array($this->correct_tz) ? $this->correct_tz : [];
    }

    /**
     * Check whether an attribute should be corrected.
     *
     * @param $attributeName
     *
     * @return bool
     */
    protected function shouldCorrectTimezone($attributeName)
    {
        return in_array($attributeName, $this->attributesToCorrect());
    }

    /**
     * Convert an attribute's date value from the database.
     * Aimed for use with Laravel 5.
     *
     * @param $attributeName
     *
     * @return void
     * @throws Exception
     */
    protected function convertAttributeFromDatabase($attributeName)
    {
        if (isset($this->attributes[$attributeName])) {
            $this->attributes[$attributeName . '__uncorrected'] = $this->attributes[$attributeName];
            $this->attributes[$attributeName]                   = static::correctForDisplay($this->asDateTime($this->attributes[$attributeName]))
                                                                        ->format($this->getDateFormat());

            if (isset($this->original[$attributeName])) {
                $this->original[$attributeName . '__uncorrected'] = $this->attributes[$attributeName];
                $this->original[$attributeName]                   = $this->attributes[$attributeName];
            }
        }
    }
}
