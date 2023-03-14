<?php

namespace App\Services;

use App\Card;
use App\CardField;
use App\CardStatistic;
use App\Http\Requests\ThemeRequest;
use App\Mail\CardCreated;
use App\Services\SlugService;
use App\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use JeroenDesloovere\VCard\VCard;
use League\Csv\Reader;
use League\Csv\Writer;

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

        $clientCards = $client->cards()
            ->select('slug_number')
            ->pluck('slug_number')
            ->unique()
            ->count();

        $subscription = $client->subscription();

        $cards->setCollection(
            $cards->getCollection()
                ->map(function ($x) use ($clientCards, $client, $subscription) {
                    $x->use_card_number = $x->field(CardField::GROUP_OTHERS, 'use_card_number') == 1;
                    $card_numbers = [$x->slug_number];

                    if ($subscription) {
                        // Números de tarjetas usados.
                        $usedNumbers = $client->cards()
                            ->select('slug_number')
                            ->pluck('slug_number')
                            ->unique();

                        for ($i = 1; $i <= $subscription->cards; $i++) {
                            if (!in_array($i, $usedNumbers->toArray())) {
                                $card_numbers[] = $i;
                            }
                        }
                    }

                    $x->card_numbers = array_unique($card_numbers);
                    return $x;
                })
        );

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
            $cards = $client->cards()->get();

            foreach ($groups as $group_key => $group) {
                foreach ($group['values'] as $field) {
                    $field_key = $group_key . '_' . $field['key'];

                    if ($field['general'] == true) {
                        foreach ($cards as $card) {
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
                            } else if ($field['type'] == 'gradient') {
                                $value = json_encode($request->get($field_key));
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

            // Actualizar archivos de vcards.
            $cards = $client->cards()->get();

            foreach ($cards as $card) {
                self::generateQRCode($card); // TODO: Optimizar usando cron.
                $this->generateVCard($card);
            }

            \DB::commit();

            session()->flash('message', "Tema guardado correctamente.");

            if (\Auth::user()->isAdmin()) {
                return redirect()->action('Admin\CardsController@theme', $client);
            }
            return redirect()->action('Clients\CardsController@theme');

        } catch (Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            session()->flash('message-error', "Error interno al guardar tema.");
            return redirect()->back()->withInput($request->input());
        }
    }

    public function saveOrUpdate(Request $request, bool $notify, User $client, Card $card = null)
    {
        try {

            \DB::beginTransaction();

            $isNewCard = $card == null;

            $card_id = $card != null ? $card->id : null;
            $slug = SlugService::generate($request->get('others_name'), 'cards', $card_id);
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
                        } else if ($field['type'] == 'gradient') {
                            $value = json_encode($request->get($field_key));
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
            if ($isNewCard && $notify) {
                $clientUser = new User(['name' => $card->name, 'email' => $card->email]);
                Mail::to($clientUser)->send(new CardCreated($card));
            }

            \DB::commit();

            session()->flash('message', "Tarjeta guardada correctamente.");

            if (\Auth::user()->isAdmin()) {
                return redirect()->action('Admin\CardsController@edit', [$client, $card]);
            }
            return redirect()->action('Clients\CardsController@edit', $card);

        } catch (Exception $ex) {
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
                                $isJson = false;

                                if ($field['type'] == 'gradient') {
                                    $isJson = true;
                                }

                                if ($card_field) {
                                    $card_field->update([
                                        'value' => $isJson ? json_encode($value) : $value,
                                    ]);
                                } else {
                                    $card->fields()->save(new CardField([
                                        'group' => $group_key,
                                        'key' => $field['key'],
                                        'value' => $isJson ? json_encode($value) : $value,
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
        $use_card_number = $card->field(CardField::GROUP_OTHERS, 'use_card_number') == 1;
        $cardUrl = $use_card_number ? $card->url_number : $card->url;
        $qrCardUrl = "{$cardUrl}?action=scan";
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
        $name = $card->field('others', 'name');
        $name = ucwords(strtolower($name));

        $firstname = $name;
        $lastname = '';
        $additional = '';
        $prefix = '';
        $suffix = '';

        return (Object) [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'additional' => $additional,
            'prefix' => $prefix,
            'suffix' => $suffix,
        ];
    }

    /**
     * Actualizar los números de tarjetas de un cliente.
     *
     * @param User $client
     * @return void
     */
    public static function refreshClientCardNumbers(User $client)
    {
        $cardsCount = $client->cards()->count();
        $cardsNumbers = $client->cards()
            ->select('slug_number')
            ->pluck('slug_number')
            ->unique()
            ->count();

        if ($cardsCount == 0) {
            // Si el cliente no tiene tarjetas no hacer nada.
            return;
        } else if ($cardsCount == $cardsNumbers) {
            // Si la cantidad de tarjetas y los número asignados no se repiten, no hacer nada.
            return;
        }

        $lastCardNumber = $client->cards()
            ->select('slug_number')
            ->pluck('slug_number')
            ->unique()
            ->sort()
            ->last();

        // Obtener tarjetas agrupadas por número asignado.
        $cards = $client->cards()
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('slug_number');

        // Corregir número de tarjeta de tarjetas con números repetidos.
        foreach ($cards as $cardNumber => $nCards) {
            if ($nCards->count() == 1) {
                continue;
            }

            foreach ($nCards as $n => $card) {
                if ($n == 0) {
                    continue;
                }
                $card->update(['slug_number' => ++$lastCardNumber]);
            }
        }

        foreach ($client->cards()->get() as $card) {
            (new CardsService)->refreshCard($client, $card);
        }
    }

    /**
     * Actualizar número de tarjeta.
     */
    public function updateCardNumber(Request $request, Card $card, User $client)
    {
        $cardNumber = $request->get('slug_number');
        $usedNumbers = $client->cards()
            ->select('slug_number')
            ->where('id', '<>', $card->id)
            ->pluck('slug_number')
            ->unique();

        if (in_array($cardNumber, $usedNumbers->toArray())) {
            session()->flash('message-error', "No se puede asignar el número $cardNumber a esta tarjeta.");
            return redirect()->back();
        }

        $card->update(["slug_number" => $cardNumber]);
        $this->refreshCard($client, $card);

        session()->flash('message', "Número de tarjeta actualizado.");
        return redirect()->back();
    }

    public function createMultiple(User $client)
    {
        return view('clients.cards.multiple', compact('client'));
    }

    public function templateMultiple(User $client)
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $record = [];
        $headers = [];

        foreach ($groups as $group_key => $group) {
            if (CardField::hasGroupWithSpecificFields($group_key)) {

                foreach ($group['values'] as $field) {
                    if ($field['general'] == false && !in_array($field['type'], ['image'])) {

                        $headers[] = $field['label'];
                        $record[] = $field['example'];

                    }
                }

            }
        }

        $filename = "tarjetas-{$client->id}.csv";
        $path = storage_path("app/csv/$filename");
        $csv = Writer::createFromPath($path, 'w+');
        $csv->insertOne(array_unique($headers));
        $csv->insertAll([
            array_unique($record),
            array_unique($record),
            array_unique($record),
        ]);
        $csv->output('tarjetas.csv');
        die;
    }

    public function storeMultiple(Request $request, User $client)
    {
        $cardsLimit = 40;
        $request->validate([
            'csv_file' => ['required', 'file', 'max:150'],
        ]);

        try {

            \DB::beginTransaction();

            $path = $request->file('csv_file')->store("csv");
            $filename = array_reverse(explode("/", $path))[0];
            $fullpath = storage_path("app/csv/$filename");

            $csv = Reader::createFromPath($fullpath, 'r');
            $csv->setHeaderOffset(0);
            $header_offset = $csv->getHeaderOffset();
            $header = $csv->getHeader();

            $subscription = $client->subscription();

            if (count($csv) > $cardsLimit) {
                throw new Exception("No se pueden subir mas de $cardsLimit tarjetas a la vez.", 1);
            }
            if ($subscription != null && count($csv) > $subscription->cards) {
                throw new Exception("La cantidad de tarjetas a importar sobrepasa el límite.", 1);
            }

            foreach ($csv as $listItem) {
                $formatValue = $this->formatImportCardData($listItem);
                $emailKey = $formatValue['action_contacts_email'];
                $nameKey = $formatValue['others_name'];

                if (!$emailKey || !$nameKey) {
                    continue;
                }

                $card = Card::query()
                    ->where('client_id', $client->id)
                    ->whereHas('fields', function ($q) use ($emailKey) {
                        $q->where('group', 'action_contacts')
                            ->where('key', 'email')
                            ->where('value', $emailKey);
                    })
                    ->first();

                if ($card) {
                    $formatValue['id'] = $card->id;
                }

                $request = new Request();
                $request->merge($formatValue);
                $this->saveOrUpdate($request, false, $client, $card);
            }

            // Borrar últimas tarjetas creadas que sobrepasen el límite.
            $clientCountCards = $client->cards()->count();

            if ($subscription->cards < $clientCountCards) {
                $deleteCountCards = $clientCountCards - $subscription->cards;
                $n = $client->cards()
                    ->orderBy('created_at', 'desc')
                    ->take($deleteCountCards)
                    ->delete();
            }

            \DB::commit();

            session()->flash('message', "Tarjetas guardadas correctamente.");

            if (\Auth::user()->isAdmin()) {
                return redirect()->action('Admin\CardsController@index', $client);
            }
            return redirect()->action('Clients\CardsController@index');

        } catch (Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            session()->flash('message-error', "Error interno al guardar tarjeta.");
            return redirect()->back()->withInput($request->input());
        }
    }

    public function formatImportCardData($data)
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $formatData = [];

        foreach ($groups as $group_key => $group) {
            if (CardField::hasGroupWithSpecificFields($group_key)) {

                foreach ($group['values'] as $field) {
                    $field_key = $group_key . '_' . $field['key'];

                    if ($field['general'] == true) {
                        continue;
                    }

                    if (isset($data[$field['label']])) {
                        $value = $data[$field['label']];
                        $formatData[$field_key] = $value != null ? trim($value) : null;
                    }
                }

            }
        }

        return $formatData;
    }
}
