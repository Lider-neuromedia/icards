<?php

namespace App\Services;

use App\Card;
use App\CardField;
use App\CardStatistic;
use App\Http\Requests\ThemeRequest;
use App\Mail\CardCreated;
use App\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use JeroenDesloovere\VCard\VCard;

class CardsService
{
    public function cards(Request $request, User $client)
    {
        $events = CardStatistic::analyticsEvents();

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
            ->with('statistics')
            ->orderBy('slug', 'asc')
            ->paginate(20);

        return view('clients.cards.index', compact('cards', 'search', 'client', 'events'));
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
                                $value = $request->get($field_key . "_current") ?: null;

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

            $isNewCard = $card == null;

            $card_id = $card != null ? $card->id : null;
            $slug = \App\Services\SlugService::generate($request->get('others_name'), 'cards', $card_id);
            $data = ['slug' => $slug];

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

                        $value = null;

                        if ($field['type'] == 'image') {
                            $value = $request->get($field_key . "_current") ?: null;

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

            $this->refreshCard($client, $card);

            // Notificar usuario dueño de la tarjeta que su tarjeta fué creada.
            if ($isNewCard) {
                $clientUser = new User(['name' => $card->name, 'email' => $card->email]);
                Mail::to($clientUser)->send(new CardCreated($card));
            }

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

    public function refreshCard(User $client, Card $card)
    {
        $this->updateCardFields($client);
        self::generateQRCode($card);
        $this->generateVCard($card);
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

    public static function generateQRCode(Card $card)
    {
        $qrCardUrl = "{$card->url}?action=scan";
        $qrFile = "qr-{$card->slug}.png";

        Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($qrCardUrl)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(30)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build()
            ->saveToFile(storage_path("app/public/cards/{$qrFile}"));

        $card->update(['qr_code' => $qrFile]);
    }

    private function generateVCard(Card $card)
    {
        $vcard = new VCard();
        $cardName = $this->generateCardName($card);

        $vcard->addName(
            $cardName->lastname,
            $cardName->firstname,
            $cardName->additional,
            $cardName->prefix,
            $cardName->suffix);

        $company = strtoupper($card->field('others', 'company'));
        $cargo = $card->field('others', 'cargo');
        $email = $card->field('action_contacts', 'email');
        $web = $card->field('contact_list', 'web');
        // $vcard->addRole('Data Protection Officer');
        // $vcard->addAddress(null, null, 'street', 'worktown', null, 'workpostcode', 'Belgium');
        // $vcard->addLabel('street, worktown, workpostcode Belgium');

        if ($company != '') {
            $vcard->addCompany($company);
        }
        if ($cargo != '') {
            $vcard->addJobtitle($cargo);
        }
        if ($email != '') {
            $vcard->addEmail($email);
        }
        if ($web != '') {
            $vcard->addURL($web, 'PREF');
        }

        $phone = $card->field('action_contacts', 'phone');
        $phone1 = $card->field('contact_list', 'phone1');
        $phone2 = $card->field('contact_list', 'phone2');
        $cellphone = $card->field('contact_list', 'cellphone');

        if ($phone != '') {
            $vcard->addPhoneNumber($phone, 'PREF;WORK;VOICE');
        }
        if ($phone1 != '') {
            $vcard->addPhoneNumber($phone1, 'WORK;VOICE');
        }
        if ($phone2 != '') {
            $vcard->addPhoneNumber($phone2, 'WORK;VOICE');
        }
        if ($cellphone != '') {
            $vcard->addPhoneNumber($cellphone, 'WORK;VOICE;CELL');
        }

        $facebook = $card->field('social_list', 'facebook');
        $instagram = $card->field('social_list', 'instagram');
        $linkedin = $card->field('social_list', 'linkedin');
        $twitter = $card->field('social_list', 'twitter');
        $youtube = $card->field('social_list', 'youtube');

        if ($facebook != '') {
            $vcard->addURL($facebook, 'X-ABLabel=FACEBOOK');
        }
        if ($instagram != '') {
            $vcard->addURL($instagram, 'X-ABLabel=INSTAGRAM');
        }
        if ($linkedin != '') {
            $vcard->addURL($linkedin, 'X-ABLabel=LINKEDIN');
        }
        if ($twitter != '') {
            $vcard->addURL($twitter, 'X-ABLabel=TWITTER');
        }
        if ($youtube != '') {
            $vcard->addURL($youtube, 'X-ABLabel=YOUTUBE');
        }

        $logo = $card->field('others', 'logo');
        $photo = $card->field('others', 'profile');

        if ($logo != '') {
            $logoContent = \Storage::get("public/cards/$logo");
            $vcard->addLogoContent($logoContent);
        }
        if ($photo != '') {
            $photoContent = \Storage::get("public/cards/$photo");
            $vcard->addPhotoContent($photoContent);
        }

        $path = storage_path("app/public/cards/");
        $filename = "card-{$card->slug}";

        $vcard->setFilename($filename);
        $vcard->setSavePath($path);
        $vcard->save();
    }

    public function generateCardName(Card $card)
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

        return (Object) [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'additional' => $additional,
            'prefix' => $prefix,
            'suffix' => $suffix,
        ];
    }
}
