<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveHtmlEncoding extends Migration
{
    /**
     * @var array $blah
     */
    private $tables = [
        'quotes' => ['culprit']
    ];
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        collect($this->tables)
            ->each(function (array $columns, string $table): void {
                foreach ($columns as $column) {
                    DB::statement("update {$table} set {$column} = replace({$column}, '&amp;', '&')");
                    DB::statement("update {$table} set {$column} = replace({$column}, '&quot;', '\"')");
                    DB::statement("update "
                                  . $table
                                  . " set "
                                  . $column
                                  . " = replace("
                                  . $column
                                  . ", '&apos;', '\'')");
                }
            });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        collect($this->tables)
            ->each(function (array $columns, string $table): void {
                foreach ($columns as $column) {
                    DB::statement("update {$table} set {$column} = replace({$column}, '&', '&amp;')");
                    DB::statement("update {$table} set {$column} = replace({$column}, '\"', '&quot;')");
                    DB::statement("update "
                                  . $table
                                  . " set "
                                  . $column
                                  . " = replace("
                                  . $column
                                  . ", '\'', '&apos;')");
                }
            });
    }
}
