<div data-type="modal-template" data-id="backups">
    <div class="modal-header">
        <h1 class="modal-title">Create Backup</h1>
    </div>
    <div class="modal-body">
        <p>
            Database backups are generated daily, and full site backups are generated weekly. If you want to manually
            create a backup of either now, you can do so below.
        </p>
        <button
            class="btn btn-success btn-full"
            data-submit-ajax="{{ route('backup.store', ['type' => 'db']) }}"
            data-redirect="true"
            data-disable="click"
        >
            <span class="fa fa-database"></span>
            <span>Database Only</span>
        </button>
        <button
            class="btn btn-success btn-full"
            data-submit-ajax="{{ route('backup.store', ['type' => 'full']) }}"
            data-redirect="true"
            data-disable="click"
        >
            <span class="fa fa-sitemap"></span>
            <span>Database and Resources</span>
        </button>
        <p class="help-block">
            A <strong>Database and Resources</strong> backup includes any files that aren't version controlled.
            At the moment this includes: member profile pictures, resources, breakage report images and election
            manifestos.
        </p>
    </div>
</div>
