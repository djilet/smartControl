<?php

use App\Models\BuildingHandbook;
use App\Models\BuildingHandbookSection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HandbookSeeder extends Seeder
{
    private $parseServerImageUrl = 'https://files.stroyinf.ru/Data1/54/54465/';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('building_handbook_works')->truncate();
        DB::table('building_handbook_sections')->truncate();
        DB::table('building_handbook_snips')->truncate();
        DB::table('handbook_works')->truncate();
        DB::table('building_handbooks')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->createConstructionHandbook();
        $this->createRepairHandbook();
        $this->createInstallationHandbook();

        $this->importHandbook();
    }

    /**
     * Строительные работы
     */
    private function createConstructionHandbook(): void
    {
        $handbookId = DB::table('building_handbooks')->insertGetId([
            'title' => "Строительные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        $earthWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Земляные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $earthWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $earthWorkId,
            'title' => "Разработка выемок (траншей) под конструкции",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.02.01-87 пп. 1.11, 3.1 - 3.6, 3.29'],
            'Указания по производству работ' => ['СНиП 3.02.01-87 пп. 3.6 - 3.8, 3.11'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $earthWorkId,
            'title' => "Разработка котлованов экскаваторами",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.02.01-87 пп. 1.11, 3.1, 3.2, 3.6'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $earthWorkId,
            'title' => "Разработка траншей под трубопроводы в нескальных грунтах",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.02.01-87 пп. 1.11, 3.29'],
            'Указания по производству работ' => ['СНиП 3.02.01-87 пп. 3.1, 3.3, 3.6 - 3.9, 3.11, 3.15, 3.17'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $earthWorkId,
            'title' => "Обратная засыпка",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.02.01-87 пп. 4.15, 4.26'],
            'Указания по производству работ' => ['СНиП 3.02.01-87 пп. 4.9 - 4.11, 4.15'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $earthWorkId,
            'title' => "Вертикальная планировка",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.02.01-87 п. 3.29'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $earthWorkId,
            'title' => "Устройство насыпей",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.02.01-87 пп. 4.2, 4.4, 4.20, 4.26'],
        ]);



        $fundamentWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Устройство фундаментов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $fundamentWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $fundamentWorkId,
            'title' => "Монтаж блоков ленточных фундаментов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.5, 3.6, 3.10'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.5, 3.9, 3.10'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $fundamentWorkId,
            'title' => "Монтаж блоков стен подземной части зданий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.5, 3.6'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.9, 3.11'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $fundamentWorkId,
            'title' => "Установка блоков фундаментов стаканного типа",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 3.10'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.8, 3.10'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $fundamentWorkId,
            'title' => "Устройство свайных фундаментов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.02.01-87 п. 11.6'],
            'Указания по производству работ' => ['СНиП 3.02.01-87 пп. 11.5, 11.10'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $fundamentWorkId,
            'title' => "Устройство сборных ростверков",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.02.01-87 пп. 11.6, 11.53'],
            'Указания по производству работ' => ['СНиП 3.02.01-87 пп. 11.49, 11.50, 11.51, 11.52'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $fundamentWorkId,
            'title' => "Устройство монолитных ростверков",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.02.01-87, СНиП 3.03.01-87'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.8 - 2.13, 2.100, 2.109, 2.110'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $fundamentWorkId,
            'title' => "Устройство горизонтальной гидроизоляции фундаментов из цементных растворов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 2.31'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 1.2, 2.28 - 2.30'],
        ]);





        $betonWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Бетонные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $betonWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $betonWorkId,
            'title' => "Опалубочные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 2.110'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.105, 2.109'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $betonWorkId,
            'title' => "Монтаж инвентарной опалубки стен монолитного дома",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 2.16, 3.7'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.105, 2.109'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $betonWorkId,
            'title' => "Монтаж инвентарной опалубки перекрытий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 2.110'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.105, 2.109'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $betonWorkId,
            'title' => "Арматурные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.97, 2.98, 2.100 - 2.102'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $betonWorkId,
            'title' => "Укладка бетонных смесей",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 2.8, 2.10 - 2.14'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.8, 2.10 - 2.13'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $betonWorkId,
            'title' => "Устройство монолитных бетонных и железобетонных стен",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 2.113'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.8, 2.10 - 2.16, 2.109, 2.110'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $betonWorkId,
            'title' => "Устройство монолитных бетонных и железобетонных колонн",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 2.112, 2.113'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.8, 2.10 - 2.16, 2.109, 2.110'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $betonWorkId,
            'title' => "Устройство монолитных бетонных и железобетонных фундаментов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 2.112, 2.113'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.8 - 2.16, 2.109, 2.110'],
        ]);






        $kamenWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Каменные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $kamenWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $kamenWorkId,
            'title' => "Кладка перегородок",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 7.4, 7.6, 7.29, 7.90'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 7.2, 7.13, 7.15'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $kamenWorkId,
            'title' => "Кладка стен",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 7.3, 7.4, 7.6, 7.21, 7.29, 7.90'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 7.7 - 7.10, 7.17 - 7.19, 7.28, 7.86'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $kamenWorkId,
            'title' => "Кладка столбов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 7.4, 7.6, 7.21, 7.29, 7.90'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 7.17, 7.18, 7.21, 7.86, 7.87'],
        ]);







        $montageWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтажные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж железобетонных колонн одноэтажных зданий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.7, 3.16'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.12, 3.13, 3.16, 3.17'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж сборных железобетонных колонн многоэтажных зданий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.7, 3.16'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.12 - 3.14, 3.17'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж железобетонных ригелей, балок, ферм",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.7, 3.22'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.18 - 3.20, 3.24'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж плит перекрытий и покрытий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.5 - 3.7'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.18 - 3.21'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж лестничных маршей и площадок",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.6, 3.7'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.3, 3.5'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж балконных плит и перемычек",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.112, 3.4'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж наружных стеновых панелей каркасных зданий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.6, 3.7, 3.25'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.25, 3.27, 3.28'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж панелей, блоков несущих стен зданий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.6, 3.7, 3.25'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.25, 3.26'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж объемных блоков шахт лифтов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 3.7'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 п. 3.30'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж сборных железобетонных вентиляционных блоков",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.5 - 3.7'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 п. 3.29'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж объемных блоков",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.5 - 3.7, 3.29'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 п. 3.29'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж санитарно-технических кабин",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.7, 3.31'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 п. 3.30'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж гипсобетонных перегородок",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.7, 6.7'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.25, 6.4, 6.7'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж асбестоцементных экструзионных панелей и плит",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 6.3 - 6.6'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж каркасно-обшивных перегородок",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 6.14 - 6.17'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Монтаж стен из панелей типа «Сэндвич» и полистовой сборки",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 6.19 - 6.21'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Сварка монтажных соединений железобетонных конструкций",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 8.39, 8.42, 8.43, 8.49, ГОСТ 10922-90, ГОСТ 14098-91'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 8.33, 8.41, 8.44 - 8.46'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Антикоррозионная защита стальных закладных изделий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.39, 3.41, 3.42, СНиП 3.04.03-85'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.38, 3.39, СНиП 3.04.03-85 пп. 3.1, 8.3, 8.5, 8.6'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Герметизация стыков",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.54, 3.58 - 3.72'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.55 - 3.72, 3.74'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Замоноличивание стыков и швов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.44, 3.46, 3.50'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.43, 3.47 - 3.48'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $montageWorkId,
            'title' => "Устройство мусоропровода",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87'],
            'Указания по производству работ' => ['СНиП 2.08.02-89 пп. 1.53 - 1.54'],
        ]);







        $krovelWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Кровельные и изоляционные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
            'title' => "Подготовка оснований и нижележащих элементов изоляции и кровли",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 2.6, 2.7, табл. 2, 3'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 2.4 - 2.6'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
            'title' => "Устройство теплоизоляции из сыпучих материалов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 2.38'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 1.2, 2.37'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
            'title' => "Устройство теплоизоляции из плит",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 2.38'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 п. 2.36'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
            'title' => "Устройство изоляции из рулонных материалов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 2.13, 2.16, 2.17, 2.23, 2.46'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 2.13, 2.14, 2.15, 2.18'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
            'title' => "Устройство изоляции из полимерных и эмульсионно-битумных составов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 2.27'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 2.24 - 2.26'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
            'title' => "Устройство кровли из рулонных материалов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 2.16, 2.17'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 2.14 - 2.17, 2.22'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
            'title' => "Устройство кровли из штучных материалов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 2.39'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 2.40 - 2.42'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
            'title' => "Устройство кровли из полимерных и эмульсионно-битумных составов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 2.24 - 2.26'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $krovelWorkId,
            'title' => "Устройство кровли металлической",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 2.45'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 2.39, 2.45'],
        ]);







        $stolarWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Столярные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $stolarWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $stolarWorkId,
            'title' => "Установка оконных блоков",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 5.6'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.73, 3.74'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $stolarWorkId,
            'title' => "Установка подоконных досок",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 5.5'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 п. 5.5'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $stolarWorkId,
            'title' => "Установка дверных блоков",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 5.6'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.73, 3.74'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $stolarWorkId,
            'title' => "Устройство антресолей, шкафов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 3.64 - 3.66'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 п. 3.64'],
        ]);







        $polWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Устройство полов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Подготовка грунтовых оснований под полы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 4.2'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 п. 4.3, ВСП 12-101.5-96 п. 5.1.5'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство бетонного подстилающего слоя, стяжек",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87, табл. 17, 20'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп 4.3, 4.8, 4.9, 4.14'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство звукоизоляции пола",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 4.18, 4.19'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 п. 4.19'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство оклеечной гидроизоляции пола",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 2.17'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 2.2, 2.3, 2.6, 2.14, 2.15'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство битумной гидроизоляции пола",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 п. 2.24'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство монолитных покрытий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 4.25, 4.26'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство полов из керамической плитки",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 4.3, 4.27, 4.28'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство мозаичных полов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 4.3, 4.27, 4.28'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство полов из полимерных материалов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 4.3, 4.39 - 4.42'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство лаг в полах по плитам перекрытий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 4.29, 4.38'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 4.3, 4.29, 4.30, 4.32'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство лаг на столбиках по грунтовому основанию",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 4.29, 4.31, 4.38'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 4.3, 4.29, 4.32, СНиП 2.03.13-88'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство дощатых полов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 4.34, 4.38'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 4.3, 4.33 - 4.36'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство полов из штучного паркета",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 4.34'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 4.3, 4.37'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $polWorkId,
            'title' => "Устройство полов из щитового паркета",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 4.33'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 4.3, 4.33 - 4.37'],
        ]);







        $otdelWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Отделочные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Штукатурные работы (простая штукатурка)",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.1, 3.3, 3.7 - 3.11, 3.15, 3.17'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Штукатурные работы (улучшенная штукатурка)",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.1, 3.3, 3.7 - 3.11, 3.15, 3.17, 3.18'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Штукатурные работы (высококачественная штукатурка)",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.1, 3.3, 3.7 - 3.11, 3.15, 3.17'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Штукатурные работы (покрытия из листов сухой гипсовой штукатурки)",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.1, 3.3, 3.19'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Малярные работы (окраска водными составами)",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['ССНиП 3.04.01-87 п. 3.7'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 1.2, 3.8, 3.12, 3.25, 3.26'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Малярные работы (окраска безводными составами)",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 3.7'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.1, 3.12, 3.23 - 3.26'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Облицовочные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.13, 3.51 - 3.55, 3.58, 3.60'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Обойные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 3.42'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.1, 3.12, 3.35 - 3.41, 3.43'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Стекольные работы (остекление переплетов)",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 3.46, 3.47'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.44 - 3.46, 3.48'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Стекольные работы (установка стеклоблоков и стеклопанелей)",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.44, 3.49, 3.50, ВСП 12-101.4-96 раздел 2.9'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Устройство ограждений из стеклопрофилита",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 п. 3.44, ВСП 12-101.5-96, раздел 4.6'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Отделка (облицовка) стен панелями, листами с заводской отделкой",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 3.64, 3.65, 3.66'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $otdelWorkId,
            'title' => "Монтаж подвесных потолков в интерьерах зданий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 3.65'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.63 - 3.64'],
        ]);







        $blagoWorkId = DB::table('handbook_works')->insertGetId([
            'title' => "Благоустройство",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $blagoWorkId,
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $blagoWorkId,
            'title' => "Устройство дренажа",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.04-85*, раздел 3, СНиП 3.05.03-85 пп. 3.10, 3.6'],
            'Указания по производству работ' => ['СНиП 3.02.01-87 пп. 2.2, 2.6, СНиП 3.05.04-85* пп. 3.4, 3.5, СНиП 3.07.03-85 пп. 5.2, 5.8, 5.9, СНиП 3.01.03-85 пп. 3.8 - 3.10'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $blagoWorkId,
            'title' => "Устройство отмостки из бетона и асфальтобетона",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87, СНиП III-10-75 п. 3.26'],
            'Указания по производству работ' => ['СНиП 111-10-75 п. 3.26'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $blagoWorkId,
            'title' => "Устройство тротуаров и дорожек из плит",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП III-10-75 пп. 3.23, 3.25'],
            'Указания по производству работ' => ['СНиП III-10-75 пп. 3.22, 3.23, 3.25'],
        ]);

        $sectionId = DB::table('building_handbook_sections')->insertGetId([
            'pid' => null,
            'handbook_id' => $handbookId,
            'work_id' => $blagoWorkId,
            'title' => "Устройство щебеночного основания и асфальтобетонного покрытия",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        $this->createSectionsWithSnips($sectionId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.06.03-85 пп. 7.1, 10.16'],
            'Указания по производству работ' => ['СНиП 3.06.03-85 пп. 1.7, 1.8, 1.12, 7.1, 7.3 - 7.4, 7.8, 10.18'],
        ]);
    }

    /**
     * Ремонтно-строительные работы
     */
    private function createRepairHandbook()
    {
        $handbookId = DB::table('building_handbooks')->insertGetId([
            'title' => "Ремонтно-строительные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Ремонт и усиление старых фундаментов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 3.6'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Укладка сборных ж-б плит перекрытий при реконструкции кирпичных зданий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 пп. 3.6, 3.7, 3.21, 3.22'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.18 - 3.21'],
        ]);




        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Устройство монолитных участков в перекрытиях",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 2.14'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 2.8 - 2.11, 2.16, 2.100, 2.109'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж плит перекрытий по металлическим балкам",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 3.7'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 п. 3.21'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Усиление кирпичных столбов и простенков",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 7.90'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Установка металлических перемычек",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж лестниц на металлических косоурах",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['Альбом 24-НТ-4 Ленжилпроекта'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 п. 3.3 ЛЖП альбом 24-НТ-4 Ленжилпроекта'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Устройство стропильной системы из деревянных элементов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87 п. 5.7'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 5.2, 5.3, 5.4, 5.5, 5.6'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Ремонт штукатурки",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 3.12, 3.21'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Ремонт штукатурки фасадов зданий",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.4, 3.7-3.10'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Окраска фасадов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 3.12'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 п. 3.12'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Установка лепных деталей фасадов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 3.20, 3.67, табл. 15'],
        ]);





        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Установка водосточных труб",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 п. 2.46'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 п. 2.46, табл. 7'],
        ]);
    }

    /**
     * Монтажные работы
     */
    private function createInstallationHandbook()
    {
        $handbookId = DB::table('building_handbooks')->insertGetId([
            'title' => "Монтажные работы",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Устройство отверстий и борозд для прокладки трубопроводов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.01-85 п. 1.5, прилож. 5'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж чугунных напорных трубопроводов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.04-85 пп. 3.6, 3.45, 3.46, табл. 1'],
            'Указания по производству работ' => ['СНиП 3.05.04-85 пп. 3.7, 3.9, 3.12, 3.44'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж напорных трубопроводов из асбестоцементных труб",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.04-85 пп. 3.6, 3.45, 3.46, табл. 1'],
            'Указания по производству работ' => ['СНиП 3.05.04-85 пп. 3.11, 3.48, 3.49, 3.50'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж трубопроводов внутреннего холодного и горячего водоснабжения",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.01-85 пп. 3.2-3.5, 3.7, табл. 2'],
            'Указания по производству работ' => ['СНиП 3.05.01-85 пп. 3.1, 3.9, 3.10'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж железобетонных и бетонных безнапорных трубопроводов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.04-85 пп. 3.6, 3.51, 3.45, 3.46, табл. 1'],
            'Указания по производству работ' => ['СНиП 3.05.04-85 пп. 3.3, 3.4, 3.5, 3.9, 3.12, 3.52, 3.54'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж канализационных трубопроводов из керамических труб",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.04-85 пп. 3.6, 3.55, 3.56, табл. 3'],
            'Указания по производству работ' => ['СНиП 3.05.04-85* пп. 3.3, 3.4, 3.5, 3.9, 3.12, 3.56, 3.57'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж системы внутренней канализации и водостока",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.01-85 пп. 3.4, 3.6'],
            'Указания по производству работ' => ['СНиП 3.05.01-85 пп. 3.1, 3.12, 3.17'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Установка водоразборной арматуры",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.01-85 пп. 3.4, 3.6'],
            'Указания по производству работ' => ['СНиП 3.05.01-85 пп. 3.1, 3.12, 3.17'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Установка ванны и умывальника",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.01-85 пп. 3.11, 3.15, табл. 3'],
            'Указания по производству работ' => ['СНиП 3.05.01-85 пп. 1.4, 3.17'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Установка санитарных приборов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.01-85 п. 3.15, табл. 3'],
            'Указания по производству работ' => ['СНиП 3.05.01-85 пп. 1.4, 3.13, 3.14, 3.16'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж системы внутреннего отопления",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.01-85 пп. 3,18, 3.20, 3.23- 3.25, 3.27'],
            'Указания по производству работ' => ['СНиП 3.05.01-85 пп. 2.2, 2.3, 3.20, 3.27'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж металлических воздуховодов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.01-85 пп. 3.34, 3.35, 3.38-3.40'],
            'Указания по производству работ' => ['СНиП 3.05.01-85 пп. 3.35-3.39'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж внутренних газопроводов и газооборудования",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['ГОСТ 4201-2002.'],
            'Указания по производству работ' => ['ГОСТ 4201-2002.'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Устройство электроосвещения",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.06-85 пп. 2.4, 2.24, 2.25, 3.32-3.35, 3.39, 3.40'],
            'Указания по производству работ' => ['СНиП 3.05.06-85 пп. 2.2, 2.4, 2.13'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Устройство круглых железобетонных колодцев",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.04-85 п. 3.17, СНиП 3.03.01-87 пп. 3.5, 3.6, табл. 12'],
            'Указания по производству работ' => ['СНиП 3.05.04-85 пп. 3.16, 3.17'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж камер теплотрасс",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.05.03-85, СНиП 3.03.01-87'],
            'Указания по производству работ' => ['СНиП 3.03.01-87 пп. 3.8, 3.10, СНиП 3.05.03-85 пп. 3.2, 3.4'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Монтаж непроходных каналов",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.03.01-87, СНиП 3.05.03-85'],
            'Указания по производству работ' => ['СНиП 3.03.01-87, СНиП 3.05.03-85'],
        ]);



        $workId = DB::table('handbook_works')->insertGetId([
            'title' => "Изоляция трубопроводов теплотрасс",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('building_handbook_works')->insert([
            'handbook_id' => $handbookId,
            'work_id' => $workId,
        ]);

        $this->createSectionWithSnips($handbookId, $workId, [
            'Состав операций и средства контроля',
            'Технические требования' => ['СНиП 3.04.01-87 пп. 2.32, 2.34, 2.35, табл. 7'],
            'Указания по производству работ' => ['СНиП 3.04.01-87 пп. 1.3, 2.1, 2.8-2.9, 2.32, 2.33, пп. 6.1, 6.2'],
        ]);
    }

    private function createSectionsWithSnips(int $sectionId, array $sections)
    {
        foreach ($sections as $key => $item) {
            if (is_array($item)) {
                $id = DB::table('building_handbook_sections')->insertGetId([
                    'pid' => $sectionId,
                    'handbook_id' => null,
                    'work_id' => null,
                    'title' => $key,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
                foreach ($item as $snip) {
                    DB::table('building_handbook_snips')->insert([
                        'section_id' => $id,
                        'title' => $snip,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ]);
                }
            }
            else {
                DB::table('building_handbook_sections')->insert([
                    'pid' => $sectionId,
                    'handbook_id' => null,
                    'work_id' => null,
                    'title' => $item,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
            }
        }
    }
    private function createSectionWithSnips(int $handbookId, int $workId, array $sections)
    {
        foreach ($sections as $key => $item) {
            if (is_array($item)) {
                $id = DB::table('building_handbook_sections')->insertGetId([
                    'pid' => null,
                    'handbook_id' => $handbookId,
                    'work_id' => $workId,
                    'title' => $key,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
                foreach ($item as $snip) {
                    DB::table('building_handbook_snips')->insert([
                        'section_id' => $id,
                        'title' => $snip,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ]);
                }
            }
            else {
                DB::table('building_handbook_sections')->insert([
                    'pid' => null,
                    'handbook_id' => $handbookId,
                    'work_id' => $workId,
                    'title' => $item,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
            }
        }
    }

    private function importHandbook()
    {
        $list = BuildingHandbook::all();
        foreach ($list as $handbook) {
            foreach ($handbook->works as $work) {
                $path = '/'.$handbook->title.'/'.$work->work->title;
                $this->importContentSection($work->sections, $path);
            }
        }
    }

    private function importContentSection(Collection $list, string $parent)
    {
        $path = dirname(__FILE__).'/handbook'.$parent;
        foreach ($list as $build) {
            $file = $path.'/'.$build->title.'.txt';
            if (file_exists($file)) {
                $content = file_get_contents($file);

                preg_match_all('/src="([^"]*)"/', $content, $matches);
                foreach ($matches[1] as $index => $src) {
                    $image = file_get_contents($this->parseServerImageUrl.ltrim($src, '/'));
                    $filename = basename($src);
                    file_put_contents(public_path('images/handbook/').$filename, $image);
                    $content = str_replace($matches[0][$index], 'src="/images/handbook/'.$filename.'"', $content);
                }

                $build->body = $content;
                $build->save();
            }

            if ($build->sections->isEmpty() == false) {
                $this->importContentSection($build->sections, $parent.'/'.$build->title);
            }
        }
    }

}
