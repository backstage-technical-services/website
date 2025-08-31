<?php

namespace Package\Notifications;

use Illuminate\Session\Store;

class NotificationHandler
{
    /**
     * Store the session object.
     *
     * @var Store
     */
    private $session;

    /**
     * Store the array of notifications.
     *
     * @var array
     */
    private $notifications = [];

    /**
     * Notifications constructor.
     *
     * @param Store $session
     */
    public function __construct(Store $session)
    {
        $this->session = $session;
    }

    /**
     * Output the configuration for the JS to be able to use.
     *
     * @return string
     */
    public function config()
    {
        $config = [
            'levels' => [],
            'classes' => [],
            'icons' => [],
            'close-class' => $this->getIconPrefix() . config('notifications.icons.close'),
            'timeout' => config('notifications.timeout'),
        ];

        foreach (Notification::LEVELS as $level) {
            $config['levels'][] = $level;
            $config['classes'][$level] = e($this->getClassPrefix() . config('notifications.classes.levels.' . $level));
            $config['icons'][$level] = $this->getIconPrefix() . config('notifications.icons.levels.' . $level);
        }

        return '<script>var NotificationConfig = ' . json_encode($config) . ';</script>';
    }

    /**
     * Create a new generic notification.
     *
     * @param        $message
     * @param        $level
     * @param null   $title
     * @param string $bag
     *
     * @return Notification
     */
    public function notification($message, $level, $title = null, $bag = 'default')
    {
        // Create
        $notification = new Notification($message, $level, $this);
        $notification->bag($bag);
        $notification->title($title);

        // Add to array
        $this->notifications[] = $notification;

        // Sync to the session
        $this->sync();

        return $notification;
    }

    /**
     * Create an 'info' notification.
     *
     * @param string $message
     * @param null   $title
     * @param null   $bag
     *
     * @return Notification
     */
    public function info($message, $title = null, $bag = null)
    {
        return $this->notification($message, 'info', $title, $bag);
    }

    /**
     * Create a 'success' notification.
     *
     * @param string $message
     * @param null   $title
     * @param null   $bag
     *
     * @return Notification
     */
    public function success($message, $title = null, $bag = null)
    {
        return $this->notification($message, 'success', $title, $bag);
    }

    /**
     * Create a 'warning' notification.
     *
     * @param string $message
     * @param null   $title
     * @param null   $bag
     *
     * @return Notification
     */
    public function warning($message, $title = null, $bag = null)
    {
        return $this->notification($message, 'warning', $title, $bag);
    }

    /**
     * Create an 'error' notification.
     *
     * @param string $message
     * @param null   $title
     * @param null   $bag
     *
     * @return Notification
     */
    public function error($message, $title = null, $bag = null)
    {
        return $this->notification($message, 'error', $title, $bag);
    }

    /**
     * Synchronise the session with the internal notification array.
     *
     * @return void
     */
    public function sync()
    {
        $session = $this->session->pull('notifications') ?: [];
        $synced = [];

        // Update the notifications that already exist in the session
        foreach ($session as &$notification) {
            $index = array_search($notification, $this->notifications, true);
            if ($index !== false) {
                $notification = $this->notifications[$index];
                $synced[] = $index;
            }
        }

        // Add new notifications to the session
        $new = array_diff(array_keys($this->notifications), $synced);
        foreach ($new as $index) {
            $session[] = $this->notifications[$index];
        }

        $this->session->flash('notifications', $session);
    }

    /**
     * Get notifications from the session.
     *
     * @param string $bag
     *
     * @return array
     */
    public function get($bag = 'default')
    {
        if ($this->session->has('notifications')) {
            $session = $this->session->get('notifications');
            $notifications = [];
            foreach ($session as $index => $notification) {
                if ($notification->bag() == $bag) {
                    $notifications[] = $notification;
                    unset($session[$index]);
                }
            }

            $this->session->put('notifications', $session);

            return array_map(function ($notification) {
                return $notification->toArray();
            }, $notifications);
        } else {
            return [];
        }
    }

    /**
     * Get all of the notifications, irrespective of the bag.
     *
     * @return array
     */
    public function all()
    {
        $all_notifications = [];

        if ($this->has()) {
            $bags = $this->bags();
            foreach ($bags as $bag) {
                $all_notifications = array_merge($all_notifications, $this->get($bag));
            }
        }

        return $all_notifications;
    }

    /**
     * Check whether any notifications exist.
     *
     * @param string $bag
     *
     * @return bool
     */
    public function has($bag = null)
    {
        return $bag === null ? $this->session->has('notifications') : in_array($bag, $this->bags());
    }

    /**
     * Get a list of bags that have notifications.
     *
     * @return array
     */
    public function bags()
    {
        $bags = array_unique(
            array_map(function ($notification) {
                return $notification->bag();
            }, $this->session->get('notifications')),
        );

        return $this->session->has('notifications') ? $bags : [];
    }

    /**
     * Open a notification bag for outputting.
     *
     * @param string $bag
     *
     * @return string
     */
    public function open($bag = 'default')
    {
        return '<div class="notification-bag" data-type="notification-bag" data-bag="' . $bag . '">';
    }

    /**
     * Close a notification bag.
     *
     * @return string
     */
    public function close()
    {
        return '</div>';
    }

    /**
     * A shortcut for rendering an entire bag.
     *
     * @param string $bag
     *
     * @return string
     */
    public function renderBag($bag = 'default')
    {
        $html = [$this->open($bag)];
        foreach ($this->get($bag) as $notification) {
            $html[] = view('laravel-notifications::notification')->with('notification', $notification);
        }
        $html[] = $this->close();

        return implode("\n", $html);
    }

    /**
     * Get the prefix for the notification class.
     *
     * @return string
     */
    public function getClassPrefix()
    {
        switch (config('notifications.classes.provider')) {
            case 'bootstrap':
                return 'alert alert-';
            default:
                return '';
        }
    }

    /**
     * Get the prefix for the notification icon.
     *
     * @return string
     */
    public function getIconPrefix()
    {
        switch (config('notifications.icons.provider')) {
            case 'bootstrap':
                return 'glyphicon glyphicon-';
            case 'font-awesome':
                return 'fa fa-';
            default:
                return '';
        }
    }
}
