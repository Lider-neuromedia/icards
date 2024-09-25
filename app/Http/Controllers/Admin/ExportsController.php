<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ExportCardsService;

class ExportsController extends Controller
{
    public function exportCards(Request $request, ExportCardsService $exportCardsService)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        $exportCardsService->exportCSVFile($request->get('ids'));

        return response()->json('ok', 200);
    }
}
