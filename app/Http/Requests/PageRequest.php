<?php

namespace App\Http\Requests;

use App\Models\Page;

class PageRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->route == 'page.store') {
            return $this->user()->can('create', Page::class);
        } else if ($this->route == 'page.update') {
            $page = Page::findBySlug($this->route('slug'));

            return $this->user()->can('update', $page);
        } else {
            return false;
        }
    }

    /**
     * Override the default validationData method to automatically create the slug if needed.
     *
     * @return array
     */
    protected function validationData()
    {
        $slug = $this->has('slug') ? $this->get('slug') : str_slug($this->get('title'));
        $this->merge([
            'slug' => $slug,
        ]);

        return $this->all();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $route     = $this->route()->getName();
        $slug_rule = $route == 'page.update' ? ('unique:pages,slug,' . $this->get('slug') . ',slug') : 'unique:pages';

        return [
            'title'     => 'required',
            'slug'      => 'required|' . $slug_rule . '|alpha_dash',
            'content'   => 'required',
            'published' => 'required|boolean',
            'user_id'   => 'required|exists:users,id',
        ];
    }

    /**
     * Define the custom error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required'     => 'Please enter the page\'s title',
            'slug.required'      => 'Please enter the page\'s slug',
            'slug.unique'        => 'That slug is already used by another page',
            'slug.alpha_dash'    => 'Please use letters, numbers and dashes only for the slug',
            'content.required'   => 'Please enter the page\'s content',
            'published.required' => 'Please select the page\'s published state',
            'published.boolean'  => 'Please select \'Yes\' or \'No\'',
            'user_id.required'   => 'Please select the page\'s author',
            'user_id.exists'     => 'Please select the page\'s author',
        ];
    }
}
