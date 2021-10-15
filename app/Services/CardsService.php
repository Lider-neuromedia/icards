<?php

namespace App\Services;

use App\Card;
use App\CardField;
use App\Http\Requests\ThemeRequest;
use App\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class CardsService
{
    public function cards(Request $request, User $client)
    {
        $search = $request->get('search');
        $cards = $client->cards()
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

        return view('clients.cards.index', compact('cards', 'search', 'client'));
    }

    public function create(User $client)
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $card = new Card([]);
        return view('clients.cards.create', compact('card', 'groups', 'client'));
    }

    public function edit(User $client, Card $card)
    {
        if (\Auth::user()->isClient() && $card->client->id != $client->id) {
            if (\Auth::user()->isAdmin()) {
                return redirect()->action('Admin\CardsController@index', $client);
            }
            return redirect()->action('Clients\CardsController@index');
        }

        $groups = CardField::TEMPLATE_FIELDS;
        return view('clients.cards.edit', compact('card', 'groups', 'client'));
    }

    public function destroy(User $client, Card $card)
    {
        if (\Auth::user()->isClient() && $card->client->id != $client->id) {
            if (\Auth::user()->isAdmin()) {
                return redirect()->action('Admin\CardsController@index', $client);
            }
            return redirect()->action('Clients\CardsController@index');
        }

        $card->delete();
        session()->flash('message', "Tarjeta borrada.");

        if (\Auth::user()->isAdmin()) {
            return redirect()->action('Admin\CardsController@index', $client);
        }
        return redirect()->action('Clients\CardsController@index');
    }

    public function theme(User $client)
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $card = $client->cards()->first();
        return view('clients.cards.theme', compact('card', 'groups', 'client'));
    }

    public function storeTheme(ThemeRequest $request, User $client)
    {
        try {

            \DB::beginTransaction();

            $groups = CardField::TEMPLATE_FIELDS;

            foreach ($groups as $group_key => $group) {
                foreach ($group['values'] as $field) {
                    $field_key = $group_key . '_' . $field['key'];

                    if ($field['general'] == true) {
                        foreach ($client->cards as $card) {
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

                            $this->generateVCard($card);
                        }
                    }
                }
            }

            \DB::commit();

            session()->flash('message', "Tema guardado correctamente.");

            if (\Auth::user()->isAdmin()) {
                return redirect()->action('Admin\CardsController@theme', $client);
            }
            return redirect()->action('Clients\CardsController@theme');

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            session()->flash('message-error', "Error interno al guardar tema.");
            return redirect()->back()->withInput($request->input());
        }
    }

    public function saveOrUpdate(Request $request, User $client, Card $card = null)
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
                \Storage::delete("public/cards/card-{$card->slug}.vcf");
                \Storage::delete("public/cards/qr-{$card->slug}.png");
                $card->update($data);
            } else {
                $card = new Card($data);
                $card->client()->associate($client);
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

            $this->updateCardFields($client);
            $this->generateQRCode($card);
            $this->generateVCard($card);

            \DB::commit();

            session()->flash('message', "Tarjeta guardada correctamente.");

            if (\Auth::user()->isAdmin()) {
                return redirect()->action('Admin\CardsController@edit', [$client, $card]);
            }
            return redirect()->action('Clients\CardsController@edit', $card);

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

    private function generateVCard(Card $card)
    {
        $full_name = strtoupper($card->field('others', 'name'));
        $name_split = explode(' ', $full_name);
        $firstname = '';
        $lastname = '';
        $additional = '';
        $prefix = '';
        $suffix = '';

        if (count($name_split) == 1) {
            $firstname = $name_split[0];
        } else if (count($name_split) == 2) {
            $lastname = $name_split[1];
            $firstname = $name_split[0];
        } else if (count($name_split) == 3) {
            $lastname = $name_split[2];
            $firstname = $name_split[0];
        } else if (count($name_split) == 4) {
            $lastname = $name_split[2];
            $firstname = $name_split[0];
        }

        $company = strtoupper($card->field('others', 'company'));
        $cargo = $card->field('others', 'cargo');
        $email = $card->field('action_contacts', 'email');
        $web = $card->field('contact_list', 'web');

        $logo = $card->field('others', 'logo');
        if ($logo != '') {
            $logo_path = storage_path('app/public/cards/' . $logo);
            $logo_ext = strtoupper(array_reverse(explode('.', $logo_path))[0]);
            $logo_data = base64_encode(\Storage::get("public/cards/$logo"));
        }

        $phone = $card->field('others', 'phone');
        $phone1 = $card->field('contact_list', 'phone1');
        $phone2 = $card->field('contact_list', 'phone2');

        $cellphone = $card->field('contact_list', 'cellphone');
        $facebook = $card->field('social_list', 'facebook');
        $instagram = $card->field('social_list', 'instagram');
        $linkedin = $card->field('social_list', 'linkedin');
        $twitter = $card->field('social_list', 'twitter');
        $youtube = $card->field('social_list', 'youtube');

        if (count($name_split) == 1) {
            $firstname = $name_split[0];
        } else if (count($name_split) == 2) {
            $lastname = $name_split[1];
            $firstname = $name_split[0];
        } else if (count($name_split) == 3) {
            $lastname = $name_split[1];
            $firstname = $name_split[0];
        } else if (count($name_split) == 4) {
            $lastname = $name_split[2];
            $firstname = $name_split[0];
        }

        $social_networks = '';
        $count = 1;

        if ($web != '') {
            $social_networks .= "item{$count}.URL;type=pref:$web\nitem{$count}.X-ABLabel:_$!<HomePage>!\$_\n";
            $count++;
        }
        if ($facebook != '') {
            $social_networks .= "item{$count}.URL:$facebook\nitem{$count}.X-ABLabel:Facebook\n";
            $count++;
        }
        if ($instagram != '') {
            $social_networks .= "item{$count}.URL:$instagram\nitem{$count}.X-ABLabel:Instagram\n";
            $count++;
        }
        if ($linkedin != '') {
            $social_networks .= "item{$count}.URL:$linkedin\nitem{$count}.X-ABLabel:LinkedIn\n";
            $count++;
        }
        if ($twitter != '') {
            $social_networks .= "item{$count}.URL:$twitter\nitem{$count}.X-ABLabel:Twitter\n";
            $count++;
        }
        if ($youtube != '') {
            $social_networks .= "item{$count}.URL:$youtube\nitem{$count}.X-ABLabel:Youtube\n";
            $count++;
        }

        $content = "";
        $content .= "BEGIN:VCARD\n";
        $content .= "VERSION:3.0\n";
        $content .= "PRODID:-//Apple Inc.//iPhone OS 14.4.2//EN\n";
        $content .= "N:$lastname;$firstname;;;\n";
        $content .= "FN:$full_name\n";
        if ($company != '') {$content .= "ORG:$company;\n";}
        if ($cargo != '') {$content .= "TITLE:$cargo\n";}
        if ($email != '') {$content .= "EMAIL;type=INTERNET;type=pref:$email\n";}
        if ($phone != '') {$content .= "TEL;type=WORK;type=VOICE;type=pref:$phone\n";}
        if ($phone1 != '') {$content .= "TEL;type=WORK;type=VOICE:$phone1\n";}
        if ($phone2 != '') {$content .= "TEL;type=WORK;type=VOICE:$phone2\n";}
        if ($cellphone != '') {$content .= "TEL;type=CELL;type=VOICE:$cellphone\n";}
        // $content .= "ADR;type=pref:;;;;;;\n";
        $content .= $social_networks;
        if ($logo != '') {$content .= "PHOTO;ENCODING=b;TYPE=$logo_ext:$logo_data\n";}
        $content .= "END:VCARD\n";

        \Storage::put("public/cards/card-{$card->slug}.vcf", $content);
    }
}
