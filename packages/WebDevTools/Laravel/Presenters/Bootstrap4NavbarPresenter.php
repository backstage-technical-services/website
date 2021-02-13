<?php

namespace Package\WebDevTools\Laravel\Presenters;

use Nwidart\Menus\MenuItem;
use Nwidart\Menus\Presenters\Presenter;
use Nwidart\Menus\Presenters\PresenterInterface;

class Bootstrap4NavbarPresenter extends Presenter implements PresenterInterface
{
    /**
     * Get open tag wrapper.
     *
     * @return string
     */
    public function getOpenTagWrapper()
    {
        return PHP_EOL . '<div class="navbar-nav">' . PHP_EOL;
    }

    /**
     * Get close tag wrapper.
     *
     * @return string
     */
    public function getCloseTagWrapper()
    {
        return PHP_EOL . '</div>' . PHP_EOL;
    }

    /**
     * Get menu tag without dropdown wrapper.
     *
     * @param \Nwidart\Menus\MenuItem $item
     * @param string                  $itemClass
     *
     * @return string
     */
    public function getMenuWithoutDropdownWrapper($item, $itemClass = 'nav-item nav-link')
    {
        $class = $itemClass . ' ' . $this->getItemClasses($item) . $this->getActiveClass($item);
        return '<a class="' . trim($class) . '" href="' . $item->getUrl() . '" ' . $item->getAttributes() . '>' .
               trim($item->getIcon() . ' ' . $item->title) .
               '</a>';
    }

    /**
     * Get divider tag wrapper.
     *
     * @return string
     */
    public function getDividerWrapper()
    {
        return '<div class="dropdown-divider"></div>';
    }

    /**
     * Get divider tag wrapper.
     *
     * @param \Nwidart\Menus\MenuItem $item
     *
     * @return mixed
     */
    public function getHeaderWrapper($item)
    {
        return '<div class="dropdown-header">' . $item->title . '</div>';
    }

    /**
     * Get menu tag with dropdown wrapper.
     *
     * @param \Nwidart\Menus\MenuItem $item
     *
     * @return string
     */
    public function getMenuWithDropDownWrapper($item)
    {
        $class = 'nav-link dropdown-toggle ' . $this->getItemClasses($item);
        return '<div class="nav-item dropdown' . $this->getActiveClass($item) . '">' .
               '<a class="' . trim($class) . '" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
               trim($item->getIcon() . ' ' . $item->title) .
               '</a>' .
               '<div class="dropdown-menu">' .
               $this->getChildMenuItems($item) .
               '</div>' .
               '</div>';
    }

    /**
     * Get child menu items.
     *
     * @param \Nwidart\Menus\MenuItem $item
     *
     * @return string
     */
    public function getChildMenuItems(MenuItem $item)
    {
        $results = '';
        foreach ($item->getChilds() as $child) {
            if ($child->hidden()) {
                continue;
            }

            if ($child->isHeader()) {
                $results .= $this->getHeaderWrapper($child);
            } else if ($child->isDivider()) {
                $results .= $this->getDividerWrapper();
            } else {
                $results .= $this->getMenuWithoutDropdownWrapper($child, 'dropdown-item');
            }
        }

        return $results;
    }

    /**
     * Determine whether the include the active class for the nav item.
     *
     * @param \Nwidart\Menus\MenuItem $item
     *
     * @return string
     */
    protected function getActiveClass(MenuItem $item)
    {
        return $item->isActive() || $item->hasActiveOnChild() ? ' active' : '';
    }

    /**
     * Get the item classes (and remove them from the item's attributes).
     *
     * @param \Nwidart\Menus\MenuItem $item
     *
     * @return string
     */
    protected function getItemClasses(MenuItem $item)
    {
        if (isset($item->attributes['class'])) {
            $class = $item->attributes['class'];
            unset($item->attributes['class']);
        } else {
            $class = '';
        }

        return $class;
    }
}