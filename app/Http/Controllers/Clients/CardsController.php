<?php

namespace App\Http\Controllers\Clients;

use App\Card;
use App\CardField;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $cards = \Auth::user()
            ->cards()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('slug', 'like', "%$search%")
                        ->orWhereHas('fields', function ($q) use ($search) {
                            $q->where('group', 'others')
                                ->where('key', 'name')
                                ->where('value', 'like', "%$search%");
                        });
                });
            })
            ->orderBy('slug', 'asc')
            ->paginate(12);

        return view('clients.cards.index', compact('cards', 'search'));
    }

    public function create()
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $card = new Card([]);
        return view('clients.cards.create', compact('card', 'groups'));
    }

    public function store(CardRequest $request)
    {
        return $this->saveOrUpdate($request);
    }

    public function show(Card $card)
    {
        //
    }

    public function edit(Card $card)
    {
        $groups = CardField::TEMPLATE_FIELDS;
        return view('clients.cards.edit', compact('card', 'groups'));
    }

    public function update(CardRequest $request, Card $card)
    {
        return $this->saveOrUpdate($request, $card);
    }

    public function destroy(Card $card)
    {
        $card->delete();
        session()->flash('message', "Tarjeta borrada.");
        return redirect()->action('Clients\CardsController@index');
    }

    private function saveOrUpdate(Request $request, Card $card = null)
    {
        try {

            \DB::beginTransaction();

            $slug = '';
            $n = 0;

            do {
                if ($n === 0) {
                    $slug = \Str::slug($request->get('others_name'));
                } else {
                    $slug = \Str::slug($request->get('others_name') . " $n");
                }
                $n++;

                $exists = Card::query()
                    ->where('slug', $slug)
                    ->when($card != null, function ($q) use ($card) {
                        $q->where('id', '!=', $card->id);
                    })
                    ->exists();
            } while ($exists);

            $data = [
                'slug' => $slug,
            ];

            if ($card != null) {
                $card->update($data);
            } else {
                $card = new Card($data);
                $card->client()->associate(\Auth::user());
                $card->save();
            }

            $groups = CardField::TEMPLATE_FIELDS;

            foreach ($groups as $group_key => $group) {
                foreach ($group['values'] as $field) {
                    $field_key = $group_key . '_' . $field['key'];

                    $card_field = $card->fields()
                        ->where('group', $group_key)
                        ->where('key', $field['key'])
                        ->first();

                    if ($card_field) {
                        $card_field->update([
                            'value' => $request->get($field_key),
                        ]);
                    } else {
                        $card->fields()->save(new CardField([
                            'group' => $group_key,
                            'key' => $field['key'],
                            'value' => $request->get($field_key),
                        ]));
                    }
                }
            }

            \DB::commit();

            session()->flash('message', "Tarjeta guardada correctamente.");
            return redirect()->action('Clients\CardsController@edit', $card->id);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            session()->flash('message-error', "Error interno al guardar tarjeta.");
            return redirect()->back()->withInput($request->input());
        }
    }
}
