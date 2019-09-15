<?php

namespace App\View\Html;

use App\Models\Users\User;
use bnjns\WebDevTools\Laravel\Html\FormBuilder as BaseFormBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class FormBuilder extends BaseFormBuilder
{
    /**
     * Create a dropdown for the active users.
     *
     * @param         $name
     * @param  null   $selected
     * @param  array  $options
     *
     * @return HtmlString
     */
    public function userList($name, $selected = null, array $options = [])
    {
        // Get the list of users
        $users = User::active()->nameOrder()->getSelect();
        
        // Check if a blank entry is allowed
        if (isset($options['include_blank']) && $options['include_blank']) {
            // Define the blank text
            if (isset($options['blank_text'])) {
                $blank_text = $options['blank_text'];
                unset($options['blank_text']);
            } else {
                $blank_text = '-- Select --';
            }
            
            $users = [null => $blank_text] + $users;
            unset($options['include_blank']);
        }
        
        // Enable the use of the select2 plugin
        if (isset($options['select2']) && $options['select2']) {
            $options['select2'] = 'Select user';
        }
        
        return $this->select($name, $users, $selected, $options);
    }
    
    /**
     * Create a dropdown for the active members.
     *
     * @param         $name
     * @param  null   $selected
     * @param  array  $options
     *
     * @return HtmlString
     */
    public function memberList($name, $selected = null, array $options = [])
    {
        // Get a list of members
        $members = User::active()->member()->nameOrder()->getSelect();
        
        // Check if a blank entry is allowed
        if (isset($options['include_blank']) && $options['include_blank']) {
            // Define the blank text
            if (isset($options['blank_text'])) {
                $blank_text = $options['blank_text'];
                unset($options['blank_text']);
            } else {
                $blank_text = '-- Select --';
            }
            
            $members = [null => $blank_text] + $members;
            unset($options['include_blank']);
        }
        
        // Enable the use of the select2 plugin
        if (isset($options['select2']) && $options['select2']) {
            $options['select2'] = 'Select member';
        }
        
        return $this->select($name, $members, $selected, $options);
    }
    
    /**
     * Determine if the value is selected.
     *
     * @param  string  $value
     * @param  string  $selected
     *
     * @return null|string
     */
    protected function getSelectedValue($value, $selected)
    {
        if (is_array($selected)) {
            return in_array($value, $selected, false) && $value !== '' ? 'selected' : null;
        } else if ($selected instanceof Collection) {
            return $selected->contains($value) ? 'selected' : null;
        }
        
        return ((string) $value == (string) $selected && $value !== '') ? 'selected' : null;
    }
}