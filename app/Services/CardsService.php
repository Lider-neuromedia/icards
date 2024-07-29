<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use JeroenDesloovere\VCard\VCard;
use League\Csv\Reader;
use League\Csv\Writer;
use App\Filters\CardsFilter;
use App\Http\Requests\ThemeRequest;
use App\Services\SlugService;
use App\Services\FieldService;
use App\Mail\CardCreated;
use App\Enums\GroupField;
use App\Enums\FieldType;
use App\User;
use App\Card;
use App\CardField;
use App\CardStatistic;
use Exception;

class CardsService
{
    /**
     * @param Request $request
     * @param User|Authenticatable $client
     * @return Factory|View
     */
    public function cards(Request $request, User $client)
    {
        $cardsFilter = new CardsFilter($request, $client);
        $filters = $cardsFilter->getFilters();
        $filtersLists = $cardsFilter->getFiltersLists();

        $clientAccount = $client;
        if ($cardsFilter->account) {
            $clientAccount = $cardsFilter->selectedAccount();
        }

        $events = CardStatistic::analyticsEvents();
        $subscription = $clientAccount->subscription();

        $cards = $this->clientCardQuery($client, $cardsFilter)
            ->when($cardsFilter->search, function ($q) use ($cardsFilter) {
                $q->search($cardsFilter->search);
            })
            ->with('statistics')
            ->orderBy('slug', 'asc')
            ->paginate(20);

        $cards->setCollection(
            $cards->getCollection()
                ->map(function ($x) use ($clientAccount, $subscription) {
                    $x->use_card_number = $x->field(GroupField::OTHERS, 'use_card_number') == 1;
                    $card_numbers = [$x->slug_number];

                    if ($subscription) {
                        // Números de tarjetas usados.
                        $usedNumbers = $clientAccount->cards()
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

        if ($cardsFilter->account) {
            $client = $clientAccount;
        }

        return view('clients.cards.index', compact('cards', 'client', 'events', 'filters', 'filtersLists'));
    }

    /**
     * @param User|Authenticatable $client
     * @return Factory|View
     */
    public function create(Request $request, User $client)
    {
        $cardsFilter = new CardsFilter($request, $client);
        $filters = $cardsFilter->getFilters();

        if ($cardsFilter->account) {
            if (!$this->canAccessAccount($cardsFilter->selectedAccount())) {
                return redirect()->action('Clients\CardsController@index');
            }
        }

        $groups = CardField::TEMPLATE_FIELDS;
        $card = new Card([]);
        return view('clients.cards.create', compact('card', 'groups', 'client', 'filters'));
    }

    /**
     * @param User|Authenticatable $client
     * @param Card $card
     * @return Factory|View|RedirectResponse
     */
    public function edit(Request $request, User $client, Card $card)
    {
        if (!$this->canAccessCard($client, $card)) {
            return redirect()->action('Clients\CardsController@index');
        }

        $groups = CardField::TEMPLATE_FIELDS;
        return view('clients.cards.edit', compact('card', 'groups', 'client'));
    }

    /**
     * @param User|Authenticatable $client
     * @param Card $card
     * @return RedirectResponse
     */
    public function destroy(User $client, Card $card)
    {
        /** @var User */
        $authUser = auth()->user();

        if (!$this->canAccessCard($client, $card)) {
            return redirect()->action('Clients\CardsController@index');
        }

        $card->delete();
        session()->flash('message', "Tarjeta borrada.");

        if ($authUser->isAdmin()) {
            return redirect()->action('Admin\CardsController@index', $client);
        }
        return redirect()->action('Clients\CardsController@index');
    }

    /**
     * @param Request $request
     * @param User|Authenticatable $client
     * @return Factory|View
     */
    public function theme(Request $request, User $client)
    {
        $cardsFilter = new CardsFilter($request, $client);

        if ($cardsFilter->account) {
            if (!$this->canAccessAccount($cardsFilter->selectedAccount())) {
                return redirect()->action('Clients\CardsController@index');
            }
        }

        $groups = CardField::TEMPLATE_FIELDS;
        $filters = $cardsFilter->getFilters();
        $card = $this->clientCardQuery($client, $cardsFilter)->first();

        return view('clients.cards.theme', compact('card', 'groups', 'client', 'filters'));
    }

    /**
     * @param ThemeRequest $request
     * @param User|Authenticatable $client
     * @return RedirectResponse
     */
    public function storeTheme(ThemeRequest $request, User $client)
    {
        try {

            DB::beginTransaction();

            $groups = CardField::TEMPLATE_FIELDS;
            $cardsFilter = new CardsFilter($request, $client);

            if ($cardsFilter->account) {
                if (!$this->canAccessAccount($cardsFilter->selectedAccount())) {
                    throw new Exception("No puede modificar este tema.", 1);
                }
            }

            $cards = $this->clientCardQuery($client, $cardsFilter)->get();

            foreach ($groups as $group_key => $group) {
                foreach ($group['values'] as $field) {
                    $field_key = $group_key . '_' . $field['key'];
                    $isFieldGeneral = FieldService::isFieldGeneral($client, $group_key, $field['key']);

                    if ($isFieldGeneral) {
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
                            } elseif ($field['type'] == FieldType::GRADIENT) {
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
            $cards = $this->clientCardQuery($client, $cardsFilter)->get();

            foreach ($cards as $card) {
                self::generateQRCode($card); // TODO: Optimizar usando cron.
                $this->generateVCard($card);
            }

            DB::commit();

            session()->flash('message', "Tema guardado correctamente.");

            /** @var User */
            $authUser = auth()->user();

            if ($authUser->isAdmin()) {
                return redirect()->action('Admin\CardsController@theme', $client);
            }
            return redirect()->action(
                'Clients\CardsController@theme',
                $cardsFilter->account ? ['account' => $cardsFilter->account] : []
            );
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            Log::info($ex->getTraceAsString());
            DB::rollBack();

            session()->flash('message-error', "Error interno al guardar tema.");
            return redirect()->back()->withInput($request->input());
        }
    }

    /**
     * @param User|Authenticatable $client
     * @param Card|null $card
     * @return RedirectResponse
     */
    public function saveOrUpdate(Request $request, bool $notify, User $client, Card $card = null)
    {
        try {

            DB::beginTransaction();

            $isNewCard = $card == null;
            $isEditCard = $card != null;
            $cardsFilter = new CardsFilter($request, $client);

            $card_id = $isEditCard ? $card->id : null;
            $slug = SlugService::generate($request->get('others_name'), 'cards', $card_id);
            $data = ['slug' => $slug];

            if ($isEditCard) {
                Storage::delete("public/cards/card-{$card->slug}.vcf");
                Storage::delete("public/cards/qr-{$card->slug}.png");
                $card->update($data);
            } else {
                $accountClient = $client;
                if (isUserClient() && $cardsFilter->account) {
                    $accountClient = $cardsFilter->selectedAccount();
                }
                $card = new Card($data);
                $card->client()->associate($accountClient);
                $card->save();
            }

            $groups = CardField::TEMPLATE_FIELDS;

            foreach ($groups as $group_key => $group) {
                foreach ($group['values'] as $field) {
                    $field_key = $group_key . '_' . $field['key'];
                    $isFieldSpecific = FieldService::isFieldSpecific($client, $group_key, $field['key']);

                    if ($isFieldSpecific) {
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
                        } elseif ($field['type'] == FieldType::GRADIENT) {
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

            $this->refreshCard($card);

            // Notificar usuario dueño de la tarjeta que su tarjeta fué creada.
            if ($isNewCard && $notify) {
                try {

                    $clientUser = new User(['name' => $card->name, 'email' => $card->email]);
                    Mail::to($clientUser)->send(new CardCreated($card));
                } catch (Exception $ex) {
                    Log::info($ex->getMessage());
                    Log::info($ex->getTraceAsString());
                }
            }

            DB::commit();

            session()->flash('message', "Tarjeta guardada correctamente.");

            /** @var User */
            $authUser = auth()->user();

            if ($authUser->isAdmin()) {
                return redirect()->action('Admin\CardsController@edit', [$client, $card]);
            }
            return redirect()->action(
                'Clients\CardsController@edit',
                $cardsFilter->account
                    ? ['card' => $card, 'account' => $cardsFilter->account]
                    : ['card' => $card]
            );
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            Log::info($ex->getTraceAsString());
            DB::rollBack();

            session()->flash('message-error', "Error interno al guardar tarjeta.");
            return redirect()->back()->withInput($request->input());
        }
    }

    /**
     * @param User|Authenticatable $client
     * @param Card $card
     * @return void
     */
    public function refreshCard(Card $card): void
    {
        $this->updateCardFields($card->client);
        self::generateQRCode($card);
        $this->generateVCard($card);
    }

    /**
     * Actualizar los datos generales de todas las tarjetas para que sean iguales.
     * @param User|Authenticatable $client
     * @return void
     */
    private function updateCardFields(User $client)
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $primary_card = $client->cards()->first();

        // Validar que haya mas de una tarjeta.
        if ($primary_card && $client->cards()->count() > 1) {
            foreach ($client->cards as $card) {
                // Validar que no sea la misma tarjeta que la principal.
                if ($primary_card->id != $card->id) {

                    foreach ($groups as $group_key => $group) {
                        foreach ($group['values'] as $field) {
                            $field_key = $group_key . '_' . $field['key'];
                            $isFieldGeneral = FieldService::isFieldGeneral($client, $group_key, $field['key']);

                            if ($isFieldGeneral) {
                                $card_field = $card->fields()
                                    ->where('group', $group_key)
                                    ->where('key', $field['key'])
                                    ->first();

                                $value = $primary_card->field($group_key, $field['key']);
                                $isJson = false;

                                if ($field['type'] == FieldType::GRADIENT) {
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

    /**
     * @param Card $card
     * @return void
     */
    public static function generateQRCode(Card $card)
    {
        $use_card_number = $card->field(GroupField::OTHERS, 'use_card_number') == 1;
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

    /**
     * @param Card $card
     * @return void
     */
    private function generateVCard(Card $card)
    {
        $vcard = new VCard();
        $cardName = $this->generateCardName($card);

        $vcard->addName(
            $cardName->lastname,
            $cardName->firstname,
            $cardName->additional,
            $cardName->prefix,
            $cardName->suffix
        );

        $company = strtoupper($card->field(GroupField::OTHERS, 'company'));
        $cargo = $card->field(GroupField::OTHERS, 'cargo');
        $email = $card->field(GroupField::ACTION_CONTACTS, 'email');
        $web = $card->field(GroupField::CONTACT_LIST, 'web');
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

        $phone = $card->field(GroupField::ACTION_CONTACTS, 'phone');
        $phone1 = $card->field(GroupField::CONTACT_LIST, 'phone1');
        $phone2 = $card->field(GroupField::CONTACT_LIST, 'phone2');
        $cellphone = $card->field(GroupField::CONTACT_LIST, 'cellphone');

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

        $facebook = $card->field(GroupField::SOCIAL_LIST, 'facebook');
        $instagram = $card->field(GroupField::SOCIAL_LIST, 'instagram');
        $linkedin = $card->field(GroupField::SOCIAL_LIST, 'linkedin');
        $twitter = $card->field(GroupField::SOCIAL_LIST, 'twitter');
        $youtube = $card->field(GroupField::SOCIAL_LIST, 'youtube');

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

        $logo = $card->field(GroupField::OTHERS, 'logo');
        $photo = $card->field(GroupField::OTHERS, 'profile');

        if ($logo != '') {
            /** @var string */
            $logoContent = Storage::get("public/cards/$logo");
            $vcard->addLogoContent($logoContent);
        }
        if ($photo != '') {
            /** @var string */
            $photoContent = Storage::get("public/cards/$photo");
            $vcard->addPhotoContent($photoContent);
        }

        $path = storage_path("app/public/cards/");
        $filename = "card-{$card->slug}";

        $vcard->setFilename($filename);
        $vcard->setSavePath($path);
        $vcard->save();
    }

    /**
     * @param Card $card
     * @return object
     */
    public function generateCardName(Card $card)
    {
        $name = $card->field(GroupField::OTHERS, 'name');
        $name = ucwords(strtolower($name));

        $firstname = $name;
        $lastname = '';
        $additional = '';
        $prefix = '';
        $suffix = '';

        return (object) [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'additional' => $additional,
            'prefix' => $prefix,
            'suffix' => $suffix,
        ];
    }

    /**
     * Actualizar los números de tarjetas de un cliente.
     * Solo tarjetas que le pertenecen, no tarjetas de cuentas habilitadas.
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
        } elseif ($cardsCount == $cardsNumbers) {
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
            (new CardsService())->refreshCard($card);
        }
    }

    /**
     * Actualizar número de tarjeta.
     * @param Request $request
     * @param Card $card
     * @param User|Authenticatable $client
     * @return RedirectResponse
     */
    public function updateCardNumber(Request $request, Card $card, User $client)
    {
        $cardsFilter = new CardsFilter($request, $client);
        $cardNumber = $request->get('slug_number');

        $usedNumbers = $this->clientCardQuery($client, $cardsFilter)
            ->select('slug_number')
            ->where('id', '<>', $card->id)
            ->pluck('slug_number')
            ->unique()
            ->toArray();

        if (in_array($cardNumber, $usedNumbers)) {
            session()->flash('message-error', "No se puede asignar el número $cardNumber a esta tarjeta.");
            return redirect()->back();
        }

        $card->update(["slug_number" => $cardNumber]);
        $this->refreshCard($card);

        session()->flash('message', "Número de tarjeta actualizado.");
        return redirect()->back();
    }

    /**
     * @param User|Authenticatable $client
     * @return Factory|View
     */
    public function createMultiple(Request $request, User $client)
    {
        $cardsFilter = new CardsFilter($request, $client);
        $filters = $cardsFilter->getFilters();

        if ($cardsFilter->account) {
            $client = $cardsFilter->selectedAccount();
        }

        return view('clients.cards.multiple', compact('client', 'filters'));
    }

    /**
     * Descargar plantilla para registrar multiples tarjetas.
     */
    public function templateMultiple(User $client)
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $record = [];
        $headers = [];

        foreach ($groups as $group_key => $group) {
            if (FieldService::hasGroupWithSpecificFields($client, $group_key)) {
                foreach ($group['values'] as $field) {
                    $isFieldSpecific = FieldService::isFieldSpecific($client, $group_key, $field['key']);
                    if ($isFieldSpecific && !in_array($field['type'], ['image'])) {
                        $headers[] = $field['label'];
                        $record[] = $field['example'];
                    }
                }
            }
        }

        $timestamp = now()->format('YmdHis');
        $filename = "plantilla-tarjetas-{$timestamp}.csv";
        $path = storage_path("app/csv/$filename");
        $csv = Writer::createFromPath($path, 'w+');
        $csv->setDelimiter(";");
        $csv->setOutputBOM(Reader::BOM_UTF8);

        $csv->insertOne(array_unique($headers));
        $csv->insertAll([
            array_unique($record),
            array_unique($record),
            array_unique($record),
        ]);
        $csv->output($filename);
        die;
    }

    /**
     * @param Request $request
     * @param User|Authenticatable $client
     * @return RedirectResponse
     */
    public function storeMultiple(Request $request, User $client)
    {
        $cardsFilter = new CardsFilter($request, $client);

        $accountClient = $client;
        if (isUserClient() && $cardsFilter->account) {
            $accountClient = $cardsFilter->selectedAccount();
        }

        $cardsLimit = 40;
        $request->validate([
            'csv_file' => ['required', 'file', 'max:150'],
        ]);

        try {

            DB::beginTransaction();

            // TODO: Al importar multiples tarjetas no está teniendo
            // encuenta cuando son por numero y no por slug.

            $path = $request->file('csv_file')->store("csv");
            $filename = array_reverse(explode("/", $path))[0];
            $fullpath = storage_path("app/csv/$filename");

            $csv = Reader::createFromPath($fullpath, 'r');
            $csv->setDelimiter(';');
            $csv->setHeaderOffset(0);
            // $header_offset = $csv->getHeaderOffset();
            // $header = $csv->getHeader();

            $subscription = $accountClient->subscription();
            $countCSV = count($csv);

            if ($countCSV > $cardsLimit) {
                throw new Exception("No se pueden subir mas de $cardsLimit tarjetas a la vez. Conteo: $countCSV", 1);
            }
            if ($subscription != null && count($csv) > $subscription->cards) {
                throw new Exception("La cantidad de tarjetas a importar sobrepasa el límite.", 1);
            }

            foreach ($csv as $listItem) {
                $formatValue = $this->formatImportCardData($accountClient, $listItem);
                $emailKey = $formatValue['action_contacts_email'];
                $nameKey = $formatValue['others_name'];

                if (!$emailKey || !$nameKey) {
                    continue;
                }

                $card = Card::query()
                    ->where('client_id', $accountClient->id)
                    ->whereHas('fields', function ($q) use ($emailKey) {
                        $q->where('group', GroupField::ACTION_CONTACTS)
                            ->where('key', 'email')
                            ->where('value', $emailKey);
                    })
                    ->first();

                if ($card) {
                    $formatValue['id'] = $card->id;
                }

                $request2 = new Request();
                $request2->merge($formatValue);

                $this->saveOrUpdate($request2, false, $accountClient, $card);
            }

            // Borrar últimas tarjetas creadas que sobrepasen el límite.
            $clientCountCards = $accountClient->cards()->count();

            if ($subscription->cards < $clientCountCards) {
                $deleteCountCards = $clientCountCards - $subscription->cards;
                $accountClient->cards()
                    ->orderBy('created_at', 'desc')
                    ->take($deleteCountCards)
                    ->delete();
            }

            DB::commit();

            session()->flash('message', "Tarjetas guardadas correctamente.");

            /** @var User */
            $authUser = auth()->user();

            if ($authUser->isAdmin()) {
                return redirect()->action('Admin\CardsController@index', $client);
            }
            return redirect()->action('Clients\CardsController@index');
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            Log::info($ex->getTraceAsString());
            DB::rollBack();

            session()->flash('message-error', "Error interno al guardar tarjeta.");
            return redirect()->back()->withInput($request->input());
        }
    }

    /**
     * @param mixed $data
     * @return array
     */
    public function formatImportCardData(User $client, $data): array
    {
        $groups = CardField::TEMPLATE_FIELDS;
        $formatData = [];

        foreach ($groups as $group_key => $group) {
            if (FieldService::hasGroupWithSpecificFields($client, $group_key)) {

                foreach ($group['values'] as $field) {
                    $field_key = $group_key . '_' . $field['key'];
                    $isFieldGeneral = FieldService::isFieldGeneral($client, $group_key, $field['key']);

                    if ($isFieldGeneral) {
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

    /**
     * Validar si el usuario ($client) logueado tiene acceso
     * a una tarjeta solo si esta le pertenece o le
     * pertenece a las cuentas asociadas.
     * @param User|Authenticatable $client
     * @param Card $card
     * @return bool
     */
    private function canAccessCard(User $client, Card $card): bool
    {
        /** @var User */
        $authUser = auth()->user();

        if ($authUser->isAdmin()) {
            return true;
        }

        $clientIsCardOwner = $card->client->id == $client->id;
        $clientHasAccessToCard = $client->allowedAccounts()
            ->where('allowed_account_id', $card->client->id)
            ->exists();

        return $clientIsCardOwner || $clientHasAccessToCard;
    }

    /**
     * Validar si el usuario logueado tiene acceso a la cuenta a editar.
     *
     * @param User $account
     * @return boolean
     */
    private function canAccessAccount(User $account): bool
    {
        /** @var User */
        $authUser = auth()->user();

        if ($authUser->isAdmin()) {
            return true;
        }

        $isSameAccount = $authUser->id == $account->id;
        $userHasAccessToAccount = $authUser
            ->allowedAccounts()
            ->where('allowed_account_id', $account->id)
            ->exists();

        return $isSameAccount || $userHasAccessToAccount;
    }

    /**
     * @param User|Authenticatable $client
     * @param CardsFilter $cardsFilter
     * @return \Illuminate\Database\Eloquent\Builder|mixed
     */
    private function clientCardQuery(User $client, CardsFilter $cardsFilter)
    {
        return Card::query()
            ->when(isUserAdmin(), function ($q) use ($client) {
                $q->where('client_id', $client->id);
            })
            ->when(isUserClient(), function ($q) use ($client, $cardsFilter) {
                $q
                    ->when($cardsFilter->account, function ($q) use ($cardsFilter) {
                        $q->whereIn('client_id', $cardsFilter->accounts_ids)
                            ->where('client_id', $cardsFilter->account);
                    })
                    ->when(!$cardsFilter->account, function ($q) use ($client) {
                        $q->where('client_id', $client->id);
                    });
            });
    }
}
