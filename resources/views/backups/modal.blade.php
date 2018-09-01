<div data-type="modal-template" data-id="backups">
    <div class="modal-header">
        <h1 class="modal-title">Create Backup</h1>
    </div>
    <div class="modal-body">
        <p>Database backups are generated daily, and full site backups are generated weekly. If you want to manually create a backup of either now, you can
            do so below.</p>
        <p><strong>Note:</strong> Full site backups can be very large and may take time to generate.</p>
        <button class="btn btn-success btn-full"
                data-submit-ajax="{{ route('backup.store', ['type' => 'db']) }}"
                data-redirect="true"
                data-disable="click">
            <span class="fa fa-database"></span>
            <span>Database</span>
        </button>
        <button class="btn btn-success btn-full"
                data-submit-ajax="{{ route('backup.store', ['type' => 'full']) }}"
                data-redirect="true"
                data-disable="click">
            <span class="fa fa-file-archive-o"></span>
            <span>Full site</span>
        </button>
    </div>
</div>