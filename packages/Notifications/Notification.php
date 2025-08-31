<?php

namespace Package\Notifications;

class Notification
{
    /**
     * Define the notification levels.
     */
    const LEVELS = ['info', 'success', 'warning', 'error'];

    /**
     * @var NotificationHandler
     */
    private $handler;

    /**
     * @var int
     */
    private $level = 'info';

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $bag = 'default';

    /**
     * @var string
     */
    private $enclose;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = array_merge(
            [
                'data-close' => 'auto',
            ],
            $this->attributes,
        );

        array_walk($attributes, function (&$value, $key) {
            $value = $key . '=' . e($value);
        });

        return [
            'level' => $this->level,
            'title' => $this->title,
            'message' => $this->message,
            'bag' => $this->bag,
            'class' => $this->handler->getClassPrefix() . config('notifications.classes.levels.' . $this->level),
            'icon' => $this->handler->getIconPrefix() . config('notifications.icons.levels.' . $this->level),
            'attributes' => implode(' ', $attributes),
            'enclose' => $this->enclose ?: null,
        ];
    }

    /**
     * Notification constructor.
     *
     * @param string              $message
     * @param string              $level
     * @param NotificationHandler $handler
     */
    public function __construct($message, $level, NotificationHandler $handler)
    {
        $this->handler = $handler;
        $this->message($message);
        $this->level($level);
    }

    /**
     * Set the notification title.
     *
     * @param $title
     *
     * @return Notification
     */
    public function title($title)
    {
        $title = $this->clean($title);

        if (!empty($title) && $title !== null) {
            $this->title = $title;
            $this->handler->sync();
        }

        return $this;
    }

    /**
     * Set the notification message.
     *
     * @param $message
     *
     * @return Notification
     */
    public function message($message)
    {
        $message = $this->clean($message);

        if (!empty($message) && $message !== null) {
            $this->message = $message;
            $this->handler->sync();
        }

        return $this;
    }

    /**
     * Set the notification level.
     *
     * @param $level
     *
     * @return Notification
     */
    public function level($level)
    {
        if (in_array($level, self::LEVELS)) {
            $this->level = $level;
            $this->handler->sync();
        }

        return $this;
    }

    /**
     * Set an element for the notification to be enclosed in.
     *
     * @param $tag
     *
     * @return Notification
     */
    public function enclose($tag)
    {
        if (!empty($tag) && $tag !== null) {
            $this->enclose = $tag;
            $this->handler->sync();
        }

        return $this;
    }

    /**
     * Set the level to 'info'.
     *
     * @return Notification
     */
    public function info()
    {
        return $this->level('info');
    }

    /**
     * Set the level to 'success'.
     *
     * @return Notification
     */
    public function success()
    {
        return $this->level('success');
    }

    /**
     * Set the level to 'warning'.
     *
     * @return Notification
     */
    public function warning()
    {
        return $this->level('warning');
    }

    /**
     * Set the level to 'info'.
     *
     * @return Notification
     */
    public function error()
    {
        return $this->level('error');
    }

    /**
     * Set the 'bag' the notification should be placed in.
     *
     * @param $bag
     *
     * @return Notification|string
     */
    public function bag($bag = null)
    {
        if (!empty($bag) && $bag !== null) {
            $this->bag = $bag;
            $this->handler->sync();

            return $this;
        } else {
            return $this->bag;
        }
    }

    /**
     * An alias to make the notification 'permanent' (no auto close).
     *
     * @return Notification
     */
    public function permanent()
    {
        return $this->bag('permanent')->close('manual');
    }

    /**
     * Set an attribute for the notification.
     *
     * @param string $name
     * @param string $value
     *
     * @return Notification
     */
    public function attribute($name, $value)
    {
        $name = $this->clean($name);
        $value = $this->clean($value);

        if (!empty($name) && $name !== null) {
            $this->attributes[$name] = $value;
            $this->handler->sync();
        }

        return $this;
    }

    /**
     * Set the 'data-close' attribute of the notification.
     *
     * @param $close
     *
     * @return Notification
     */
    public function close($close)
    {
        return $this->attribute('data-close', $close);
    }

    /**
     * Clean a string input.
     *
     * @param string $string
     *
     * @return string
     */
    private function clean($string)
    {
        return strip_tags((string) $string);
    }
}
