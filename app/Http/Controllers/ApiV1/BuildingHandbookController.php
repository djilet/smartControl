<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Http\Resources\JsonApiCollection;
use App\Models\BuildingHandbook;
use App\Models\BuildingHandbookSection;
use Illuminate\Http\Request;

class BuildingHandbookController extends Controller
{
    public function building(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $list = BuildingHandbook::paginate($perPage);
        return new JsonApiCollection($list);
    }

    public function byWork(int $id)
    {
        $sections = BuildingHandbookSection::where('work_id', $id)
            ->setEagerLoads([])
            ->select('id', 'title')
            ->with('sectionIds', 'snips')
            ->get()
            ->toArray();
        $sections = $this->filterSections($sections);

        return response()->json($sections);
    }

    private function filterSections($sections)
    {
        foreach ($sections as &$section) {
            if (empty($section['section_ids']) == false) {
                $section['section_ids'] = $this->filterSections($section['section_ids']);
            }
        }

        return array_values(array_filter($sections, function($section) {
            return count($section['section_ids']) > 0 || count($section['snips']) > 0;
        }));
    }

}
