<div
    class="modal fade"
    {{ isset($id) ? 'id=' . $id : '' }}
    tabindex="-1"
    role="dialog"
>
    <div class="modal-dialog {{ isset($class) ? $class : '' }}" role="document">
        <div class="modal-content">
            @include('app.modal.inner')
        </div>
    </div>
</div>
