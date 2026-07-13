<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $characters = preg_split('//u', 'áéíóúüñÁÉÍÓÚÜÑ¿¡°', -1, PREG_SPLIT_NO_EMPTY);
        $replacements = [];

        foreach ($characters as $character) {
            $corrupted = mb_convert_encoding($character, 'UTF-8', 'CP850');
            if ($corrupted !== $character) {
                $replacements[$corrupted] = $character;
            }
        }

        $database = DB::getDatabaseName();
        $columns = DB::table('information_schema.COLUMNS')
            ->select(['TABLE_NAME', 'COLUMN_NAME'])
            ->where('TABLE_SCHEMA', $database)
            ->whereIn('DATA_TYPE', ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext'])
            ->orderBy('TABLE_NAME')
            ->orderBy('ORDINAL_POSITION')
            ->get();

        foreach ($columns->groupBy('TABLE_NAME') as $table => $tableColumns) {
            if (! Schema::hasColumn($table, 'id')) {
                continue;
            }

            foreach ($tableColumns as $columnInfo) {
                $column = $columnInfo->COLUMN_NAME;
                DB::table($table)
                    ->select(['id', $column])
                    ->whereNotNull($column)
                    ->where(function ($query) use ($column, $replacements) {
                        foreach (array_keys($replacements) as $corrupted) {
                            $query->orWhere($column, 'like', '%'.$corrupted.'%');
                        }
                    })
                    ->orderBy('id')
                    ->chunkById(200, function ($rows) use ($table, $column, $replacements) {
                        foreach ($rows as $row) {
                            $current = $row->{$column};
                            $repaired = strtr($current, $replacements);

                            if ($repaired !== $current) {
                                DB::table($table)->where('id', $row->id)->update([$column => $repaired]);
                            }
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        // La reparación recupera texto legible y no debe volver a introducir caracteres corruptos.
    }
};
