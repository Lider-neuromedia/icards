<?php

namespace App\Http\Controllers;

use App\Card;
use App\CardStatistic;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AnalyticsController extends Controller
{
    public function trackAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'string', 'max:50'],
            'data' => ['required', 'array', 'min:1'],
            'data.cardId' => ['required', 'integer', 'exists:cards,id'],
        ]);

        $action = $request->get('action');
        $data = $request->get('data');
        $card = Card::findOrFail($data['cardId']);

        $cardStatistic = CardStatistic::query()
            ->where('action', $action)
            ->whereHas('card', function ($q) use ($card) {
                $q->where('id', $card->id);
            })
            ->first();

        if ($cardStatistic == null) {
            $cardStatistic = new CardStatistic([
                'action' => $action,
                'data' => '1',
            ]);
            $cardStatistic->card()->associate($card);
        } else {
            $count = intVal($cardStatistic->data);
            $cardStatistic->update([
                'data' => $count + 1,
            ]);
        }

        $cardStatistic->save();
        return response()->json(['mesage' => 'Acción registrada.']);
    }

    public function download(User $client)
    {
        if (auth()->user()->hasNotAllowedAccount($client)) {
            return abort(401);
        }

        $events = CardStatistic::allAnalyticsEvents();
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'W', 'X', 'Y', 'Z'];
        $headers = ["Nombre", "URL"];

        foreach ($events as $key => $value) {
            $headers[] = $value;
        }

        $cardsStatistics = CardStatistic::query()
            ->whereHas('card', function ($q) use ($client) {
                $q->where('client_id', $client->id);
            })
            ->orderBy('action', 'asc')
            ->get()
            ->groupBy('card_id');

        $data = [];
        $rowIndex = 2;

        foreach ($cardsStatistics as $cardId => $card) {
            $rowData = [];
            $columnIndex = 0;
            $letter = $letters[$columnIndex++];
            $rowData["{$letter}{$rowIndex}"] = $card->first()->name;
            $letter = $letters[$columnIndex++];
            $rowData["{$letter}{$rowIndex}"] = $card->first()->url;

            foreach ($events as $eventKey => $eventTitle) {
                $action = $card->firstWhere('action', $eventKey);
                $letter = $letters[$columnIndex++];
                $rowData["{$letter}{$rowIndex}"] = $action ? $action->data : "0";
            }

            $rowIndex++;
            $data[] = $rowData;
        }

        $timestamp = Carbon::now()->format('Ymd');
        $filename = "estadisticas-{$client->id}-$timestamp.xlsx";
        $path = storage_path("/app/statistics/$filename");

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $key => $headerTitle) {
            $letter = $letters[$key];
            $cell = "{$letter}1";
            $sheet->setCellValue($cell, $headerTitle);
        }

        foreach ($data as $rows) {
            foreach ($rows as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return \Storage::download("statistics/$filename");
    }
}
