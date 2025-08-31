<div class="form-group search-input">
    <div class="input-group">
        {!! Form::text('query', isset($search) ? $search->query : null, [
            'class' => 'form-control',
            'placeholder' => 'What do you want to find?',
        ]) !!}
        <span class="input-group-addon">
            <button class="btn btn-default" name="form-action" value="do-search">
                <span class="fa fa-search"></span>
            </button>
        </span>
    </div>
</div>

<div class="tools">
    @if (isset($search))
        <div class="pull-left search-tools">
            <div class="dropdown categories">
                <a class="grey dropdown-toggle" data-toggle="dropdown">
                    Category: {{ $category ? $category->name : 'None' }} <span class="fa fa-caret-down"></span>
                </a>
                <ul class="dropdown-menu">
                    @if ($search->category)
                        <li>
                            <a href="{{ route('resource.search', Request::except('page', 'category')) }}">
                                <span class="fa fa-remove"></span>
                                Remove
                            </a>
                        </li>
                        <li class="divider" role="separator"></li>
                    @endif
                    @foreach ($CategoryList as $category)
                        <li>
                            <a href="{{ $category->link }}">
                                <span class="fa{{ $category->current ? ' fa-check' : '' }}"></span>
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <span class="hidden-xs dropdown-separator"> | </span>
            <div class="dropdown tags">
                <a class="link grey dropdown-toggle" data-toggle="dropdown">
                    Tags: {{ count($search->tags) }} <span class="fa fa-caret-down"></span>
                </a>
                <ul class="dropdown-menu">
                    @if ($search->tags)
                        <li>
                            <a href="{{ route('resource.search', Request::except('page', 'tag')) }}">
                                <span class="fa fa-remove"></span>
                                Remove all
                            </a>
                        </li>
                        <li class="divider" role="separator"></li>
                    @endif
                    @foreach ($TagList as $tag)
                        <li>
                            <a href="{{ $tag->link }}">
                                <span class="fa{{ $tag->current ? ' fa-check' : '' }}"></span>
                                {{ $tag->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @can('create', \App\Models\Resources\Resource::class)
        <div class="pull-right admin-tools">
            <div class="dropdown">
                <a class="link dropdown-toggle" data-toggle="dropdown">Settings</a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
                        <a href="{{ route('resource.category.index') }}">
                            <span class="fa fa-bookmark"></span> Manage categories
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('resource.tag.index') }}">
                            <span class="fa fa-tags"></span> Manage tags
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('resource.index') }}">
                            <span class="fa fa-list-alt"></span> View all resources
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('resource.create') }}">
                            <span class="fa fa-plus"></span> Add resource
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    @endcan
</div>
