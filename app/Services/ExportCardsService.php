<?php

namespace App\Services;

use League\Csv\Reader;
use League\Csv\Writer;
use App\Enums\GroupField;
use App\User;
use App\Card;

class ExportCardsService
{
    private const HEADERS = [
        'ID Cliente',
        'Nombre Cliente',
        'Nombre',
        'Cargo',
        'Descripción',
        'Teléfono',
        'E-mail',
        'Whatsapp',
        'Mensaje de Whatsapp',
        'Celular',
        'Teléfono 1',
        'Teléfono 2	',
    ];

    public function exportCSVFile(array $clientsIds): void
    {
        $cards = [];

        foreach ($clientsIds as $clientId) {
            $cards = array_merge($cards, $this->exportCards(User::findOrFail($clientId)));
        }

        $now = now()->format('YmdHis');
        $filename = "tarjetas-$now.csv";
        $path = storage_path("app/public/$filename");

        $csv = Writer::createFromPath($path, 'w+');
        $csv->setDelimiter(',');
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->insertOne(self::HEADERS);
        $csv->insertAll($cards);
        $csv->output($filename);
        die;
    }

    /**
     * @return array
     */
    public function exportCards(User $client)
    {
        $cards = Card::query()
            ->where('client_id', $client->id)
            ->get();

        $table_cards = $cards
            ->map(function (Card $card) use ($client) {
                return [
                    'ID Cliente' => $client->id,
                    'Nombre Cliente' => $client->name,
                    'Nombre' => $card->field(GroupField::OTHERS, 'name', false) ?? "",
                    'Cargo' => $card->field(GroupField::OTHERS, 'cargo', false) ?? "",
                    'Descripción' => $card->field(GroupField::OTHERS, 'description', false) ?? "",
                    'Teléfono' => $card->field(GroupField::ACTION_CONTACTS, 'phone', false) ?? "",
                    'E-mail' => $card->field(GroupField::ACTION_CONTACTS, 'email', false) ?? "",
                    'Whatsapp' => $card->field(GroupField::ACTION_CONTACTS, 'whatsapp', false) ?? "",
                    'Mensaje de Whatsapp' => $card->field(GroupField::ACTION_CONTACTS, 'whatsapp_message', false) ?? "",
                    'Celular' => $card->field(GroupField::CONTACT_LIST, 'cellphone', false) ?? "",
                    'Teléfono 1' => $card->field(GroupField::CONTACT_LIST, 'phone1', false) ?? "",
                    'Teléfono 2	' => $card->field(GroupField::CONTACT_LIST, 'phone2', false) ?? "",
                ];
            })
            ->toArray();

        return $table_cards;
    }
}
