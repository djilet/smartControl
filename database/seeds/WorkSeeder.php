<?php

use Illuminate\Database\Seeder;

class WorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (($handle = fopen("database/seeds/works/works.csv", "r")) !== false) {
            $works = [];

            while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                $works[] = [
                    'id' => (int)trim($row[0]),
                    'title' => trim($row[1]),
                    'parent_id' => empty(trim($row[2])) ? null : (int)trim($row[2]),
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];
            }
            fclose($handle);
        }

        DB::table('works')->truncate();
        DB::table('works')->insert($works);
    }
}
