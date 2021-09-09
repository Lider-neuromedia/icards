<?php

namespace App\Http\Controllers\Clients;

use App\Card;
use App\CardField;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardRequest;
use App\Http\Requests\ThemeRequest;
use App\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
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

    public function edit(Card $card)
    {
        if ($card->client->id != \Auth::user()->id) {
            return redirect()->action('Clients\CardsController@index');
        }

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

    public function theme()
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $card = \Auth::user()->cards()->first();
        return view('clients.cards.theme', compact('card', 'groups'));
    }

    public function storeTheme(ThemeRequest $request)
    {
        try {

            \DB::beginTransaction();

            $groups = CardField::TEMPLATE_FIELDS;

            foreach ($groups as $group_key => $group) {
                foreach ($group['values'] as $field) {
                    $field_key = $group_key . '_' . $field['key'];

                    if ($field['general'] == true) {
                        foreach (\Auth::user()->cards as $card) {
                            $card_field = $card->fields()
                                ->where('group', $group_key)
                                ->where('key', $field['key'])
                                ->first();

                            $value = $card_field ? $card_field->value : '';

                            if ($field['type'] == 'image') {
                                if ($request->hasFile($field_key)) {
                                    $image_path = $request->file($field_key)->store('public/cards');
                                    $image_path = array_reverse(explode('/', $image_path))[0];
                                    $value = $image_path;
                                }
                            } else {
                                $value = $request->get($field_key);
                            }

                            if ($card_field) {
                                $card_field->update([
                                    'value' => $value,
                                ]);
                            } else {
                                $card->fields()->save(new CardField([
                                    'group' => $group_key,
                                    'key' => $field['key'],
                                    'value' => $value,
                                ]));
                            }
                        }
                    }
                }
            }

            \DB::commit();

            session()->flash('message', "Tema guardado correctamente.");
            return redirect()->action('Clients\CardsController@theme');

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            session()->flash('message-error', "Error interno al guardar tema.");
            return redirect()->back()->withInput($request->input());
        }
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

                    if ($field['general'] == false) {
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
            }

            $this->updateCardFields(\Auth::user());
            $this->generateQRCode($card);

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

    /**
     * Actualizar los datos generales de todas las tarjetas para que sean iguales.
     */
    private function updateCardFields(User $client)
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $primary_card = $client->cards()->first();

        if ($primary_card && $client->cards()->count() > 1) { // Validar que haya mas de una tarjeta.
            foreach ($client->cards as $card) {
                if ($primary_card->id != $card->id) { // Validar que no sea la misma tarjeta que la principal.

                    foreach ($groups as $group_key => $group) {
                        foreach ($group['values'] as $field) {
                            $field_key = $group_key . '_' . $field['key'];

                            if ($field['general'] == true) {
                                $card_field = $card->fields()
                                    ->where('group', $group_key)
                                    ->where('key', $field['key'])
                                    ->first();

                                $value = $primary_card->field($group_key, $field['key']);

                                if ($card_field) {
                                    $card_field->update([
                                        'value' => $value,
                                    ]);
                                } else {
                                    $card->fields()->save(new CardField([
                                        'group' => $group_key,
                                        'key' => $field['key'],
                                        'value' => $value,
                                    ]));
                                }
                            }
                        }
                    }

                }
            }
        }
    }

    private function generateQRCode(Card $card)
    {
        Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($card->url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(30)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build()
            ->saveToFile(storage_path("app/public/cards/qr-{$card->slug}.png"));

        $card->update(['qr_code' => "qr-{$card->slug}.png"]);
    }
}
