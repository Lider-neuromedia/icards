<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\CardsService;
use App\Enums\GroupField;
use App\Card;
use App\CardField;
use App\Seller;
use App\Subscription;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (User::where('role', User::ROLE_ADMIN)->count() == 0) {
            $this->initMasters();
        }
        if (User::where('role', User::ROLE_CLIENT)->count() == 0) {
            $this->initClients();
        }
        if (Card::count() == 0) {
            $this->initCards();
        }
        if (Schema::hasColumn('users', 'slug')) {
            $this->initClientSlugs();
        }
        if (Schema::hasTable('card_statistics')) {
            $this->refreshQRCodesForTrackVisits();
        }

        $this->updateCardNumbers();

        if (Seller::count() == 0) {
            $this->initSellers();
        }
    }

    public function initSellers()
    {
        $seller1 = Seller::create(['name' => 'Juan Perez']);
        $seller2 = Seller::create(['name' => 'Ana Rodriguez']);

        $clients1 = User::whereRole(User::ROLE_CLIENT)->inRandomOrder()->limit(2)->pluck('id');
        $clients2 = User::whereRole(User::ROLE_CLIENT)->inRandomOrder()->limit(3)->pluck('id');

        $seller1->clients()->sync($clients1);
        $seller2->clients()->sync($clients2);
    }

    public function initClientSlugs()
    {
        $clients = User::whereNull('slug')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($clients as $client) {
            $slug = \App\Services\SlugService::generate($client->name, 'users', $client->id);
            $client->update(['slug' => $slug]);

            foreach ($client->cards as $card) {
                $cardsService = new CardsService();
                $cardsService->refreshCard($card);
            }
        }
    }

    public function refreshQRCodesForTrackVisits()
    {
        $dateCardVisitsImplemented = '2022-02-09';
        $cards = Card::whereDate('updated_at', '<', $dateCardVisitsImplemented)->get();

        foreach ($cards as $card) {
            CardsService::generateQRCode($card);
        }
    }

    public function initMasters()
    {
        User::create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Jose Nieto',
            'email' => 'inge1neuro@gmail.com',
            'password' => Hash::make(env('PASSWORD_TESTS', Str::random(14))),
        ]);
    }

    public function initClients()
    {
        $c1 = User::create([
            'role' => User::ROLE_CLIENT,
            'name' => 'Centelsa',
            'email' => 'centelsa@mail.com',
            'password' => Hash::make(env('PASSWORD_TESTS', Str::random(14))),
        ]);
        $start_at = now();
        $finish_at = now()->add('years', 1);

        $subscription = new Subscription([
            'cards' => 5,
            'start_at' => $start_at,
            'finish_at' => $finish_at,
        ]);
        $subscription->client()->associate($c1);
        $subscription->save();

        $c2 = User::create([
            'role' => User::ROLE_CLIENT,
            'name' => 'Montana Group',
            'email' => 'montanagroup@mail.com',
            'password' => Hash::make(env('PASSWORD_TESTS', Str::random(14))),
        ]);

        $start_at = now();
        $finish_at = now()->add('years', 1);

        $subscription = new Subscription([
            'cards' => 3,
            'start_at' => $start_at,
            'finish_at' => $finish_at,
        ]);
        $subscription->client()->associate($c2);
        $subscription->save();
    }

    public function initCards()
    {
        $c1 = new Card(['slug' => 'evelio-molano-martinez', 'qr_code' => 'qr-evelio-molano-martinez.png']);
        $c1->client()->associate(User::onlyClients()->first());
        $c1->save();
        $c1->fields()->saveMany([
            new CardField(['group' => GroupField::OTHERS, 'key' => 'logo', 'value' => 'logo-evelio-molano-martinez.png']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'name', 'value' => 'Evelio Molano Martínez']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'cargo', 'value' => 'Jefe de Mercadeo y Asistencia Técnica']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'company', 'value' => 'CENTELSA']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'description', 'value' => 'Experiencia y respaldo que dan vidas a tus proyectos']),
            //
            new CardField(['group' => GroupField::ACTION_CONTACTS, 'key' => 'phone', 'value' => '+573154332611']),
            new CardField(['group' => GroupField::ACTION_CONTACTS, 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => GroupField::ACTION_CONTACTS, 'key' => 'whatsapp', 'value' => '+573154332611']),
            //
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'cellphone', 'value' => '+573164784035']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'phone1', 'value' => '0326083400']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'phone2', 'value' => '0323920200']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'web', 'value' => 'https://www.centelsa.com']),
            //
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'facebook', 'value' => 'https://www.facebook.com/Centelsacolombia/?fref=ts']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'instagram', 'value' => 'https://www.instagram.com/centelsacolombia/']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'linkedin', 'value' => 'https://www.linkedin.com/company/centelsa/?trk=biz-companies-cym']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'twitter', 'value' => 'https://twitter.com/CentelsaCables']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'youtube', 'value' => 'https://www.youtube.com/channel/UCcJ-STX0fEONmilYW-5UMNw']),
            //
            new CardField(['group' => GroupField::THEME, 'key' => 'main_color', 'value' => '#e00109']),
        ]);

        $c2 = new Card(['slug' => 'juan-salazar', 'qr_code' => 'qr-juan-salazar.png']);
        $c2->client()->associate(User::onlyClients()->first());
        $c2->save();
        $c2->fields()->saveMany([
            new CardField(['group' => GroupField::OTHERS, 'key' => 'logo', 'value' => 'logo-juan-salazar.png']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'name', 'value' => 'Juan Salazar']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'cargo', 'value' => 'Jefe de Mercadeo y Asistencia Técnica']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'company', 'value' => 'CENTELSA']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'description', 'value' => 'Experiencia y respaldo que dan vidas a tus proyectos']),
            //
            new CardField(['group' => GroupField::ACTION_CONTACTS, 'key' => 'phone', 'value' => '+573154332611']),
            new CardField(['group' => GroupField::ACTION_CONTACTS, 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => GroupField::ACTION_CONTACTS, 'key' => 'whatsapp', 'value' => '+573154332611']),
            //
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'cellphone', 'value' => '+573164784035']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'phone1', 'value' => '0326083400']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'phone2', 'value' => '0323920200']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'web', 'value' => 'https://www.centelsa.com']),
            //
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'facebook', 'value' => 'https://www.facebook.com/Centelsacolombia/?fref=ts']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'instagram', 'value' => 'https://www.instagram.com/centelsacolombia/']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'linkedin', 'value' => 'https://www.linkedin.com/company/centelsa/?trk=biz-companies-cym']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'twitter', 'value' => 'https://twitter.com/CentelsaCables']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'youtube', 'value' => 'https://www.youtube.com/channel/UCcJ-STX0fEONmilYW-5UMNw']),
            //
            new CardField(['group' => GroupField::THEME, 'key' => 'main_color', 'value' => '#e00109']),
        ]);

        $c3 = new Card(['slug' => 'lina-maria-montes-quintero', 'qr_code' => 'qr-lina-maria-montes-quintero.png']);
        $c3->client()->associate(User::onlyClients()->get()[1]);
        $c3->save();
        $c3->fields()->saveMany([
            new CardField(['group' => GroupField::OTHERS, 'key' => 'logo', 'value' => 'logo-lina-maria-montes-quintero.png']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'name', 'value' => 'Lina Maria Montes Quintero']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'cargo', 'value' => 'Jefe de Mercadeo y Asistencia Técnica']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'company', 'value' => 'MONTANA GROUP']),
            new CardField(['group' => GroupField::OTHERS, 'key' => 'description', 'value' => 'Experiencia y respaldo que dan vidas a tus proyectos']),
            //
            new CardField(['group' => GroupField::ACTION_CONTACTS, 'key' => 'phone', 'value' => '+573154332611']),
            new CardField(['group' => GroupField::ACTION_CONTACTS, 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => GroupField::ACTION_CONTACTS, 'key' => 'whatsapp', 'value' => '+573154332611']),
            //
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'cellphone', 'value' => '+573164784035']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'phone1', 'value' => '0326083400']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'phone2', 'value' => '0323920200']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => GroupField::CONTACT_LIST, 'key' => 'web', 'value' => 'https://www.centelsa.com']),
            //
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'facebook', 'value' => 'https://www.facebook.com/Centelsacolombia/?fref=ts']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'instagram', 'value' => 'https://www.instagram.com/centelsacolombia/']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'linkedin', 'value' => 'https://www.linkedin.com/company/centelsa/?trk=biz-companies-cym']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'twitter', 'value' => 'https://twitter.com/CentelsaCables']),
            new CardField(['group' => GroupField::SOCIAL_LIST, 'key' => 'youtube', 'value' => 'https://www.youtube.com/channel/UCcJ-STX0fEONmilYW-5UMNw']),
            //
            new CardField(['group' => GroupField::THEME, 'key' => 'main_color', 'value' => '#e00109']),
        ]);
    }

    private function updateCardNumbers()
    {
        $clients = User::onlyClients()->get();

        foreach ($clients as $client) {
            CardsService::refreshClientCardNumbers($client);
        }
    }
}
