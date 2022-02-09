<?php

namespace App\Http\Controllers;

use App\Card;
use App\CardStatistic;
use Illuminate\Http\Request;

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
}
