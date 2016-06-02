<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PopulateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('countries')->truncate();

        $data = $this->importCSV(__DIR__ . '/data/countries.csv');
        $cols = array('country', 'code', 'region');

        foreach($data as $row) {
            $countries[] = array_combine($cols, $row);
        }

        DB::table('countries')->insert($countries);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('countries')->truncate();
    }

    private function importCSV($file)
    {
        ini_set('auto_detect_line_endings', true);

        $array = [];

        $file = fopen($file, 'r');

        while (($line = fgetcsv($file)) !== false) {
            if (!empty($line)) {
                $array[] = $line;
            }
        }

        fclose($file);

        return $array;
    }
}
