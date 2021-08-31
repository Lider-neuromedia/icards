<?php

use App\Card;
use App\CardField;
use App\Subscription;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->initMasters();
        $this->initClients();
        $this->initCards();
    }

    public function initMasters()
    {
        User::create([
            'role' => User::ROLE_MASTER,
            'name' => 'Jose Nieto',
            'email' => 'inge1neuro@gmail.com',
            'password' => \Hash::make('secret'),
        ]);
    }

    public function initClients()
    {
        $c1 = User::create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Centelsa',
            'email' => 'centelsa@mail.com',
            'password' => \Hash::make('secret'),
        ]);
        $start_at = Carbon::createFromFormat('Y-m-d', '2021-01-01');
        $finish_at = $start_at->add('years', 1);

        $subscription = new Subscription([
            'cards' => 5,
            'start_at' => $start_at,
            'finish_at' => $finish_at,
        ]);
        $subscription->client()->associate($c1);
        $subscription->save();

        $c2 = User::create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Montana Group',
            'email' => 'montanagroup@mail.com',
            'password' => \Hash::make('secret'),
        ]);

        $start_at = Carbon::createFromFormat('Y-m-d', '2021-05-21');
        $finish_at = $start_at->add('years', 1);

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
        $c1->client()->associate(User::find(2));
        $c1->save();
        $c1->fields()->saveMany([
            new CardField(['group' => 'others', 'key' => 'logo', 'value' => 'logo-evelio-molano-martinez.png']),
            new CardField(['group' => 'others', 'key' => 'name', 'value' => 'Evelio Molano Martínez']),
            new CardField(['group' => 'others', 'key' => 'cargo', 'value' => 'Jefe de Mercadeo y Asistencia Técnica']),
            new CardField(['group' => 'others', 'key' => 'company', 'value' => 'CENTELSA']),
            new CardField(['group' => 'others', 'key' => 'description', 'value' => 'Experiencia y respaldo que dan vidas a tus proyectos']),
            //
            new CardField(['group' => 'action_contacts', 'key' => 'phone', 'value' => '+573154332611']),
            new CardField(['group' => 'action_contacts', 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => 'action_contacts', 'key' => 'whatsapp', 'value' => '+573154332611']),
            //
            new CardField(['group' => 'contact_list', 'key' => 'cellphone', 'value' => '+573164784035']),
            new CardField(['group' => 'contact_list', 'key' => 'phone', 'value' => '0326083400']),
            new CardField(['group' => 'contact_list', 'key' => 'phone', 'value' => '0323920200']),
            new CardField(['group' => 'contact_list', 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => 'contact_list', 'key' => 'web', 'value' => 'https://www.centelsa.com']),
            //
            new CardField(['group' => 'social_list', 'key' => 'facebook', 'value' => 'https://www.facebook.com/Centelsacolombia/?fref=ts']),
            new CardField(['group' => 'social_list', 'key' => 'instagram', 'value' => 'https://www.instagram.com/centelsacolombia/']),
            new CardField(['group' => 'social_list', 'key' => 'linkedin', 'value' => 'https://www.linkedin.com/company/centelsa/?trk=biz-companies-cym']),
            new CardField(['group' => 'social_list', 'key' => 'twitter', 'value' => 'https://twitter.com/CentelsaCables']),
            new CardField(['group' => 'social_list', 'key' => 'youtube', 'value' => 'https://www.youtube.com/channel/UCcJ-STX0fEONmilYW-5UMNw']),
            //
            new CardField(['group' => 'theme', 'key' => 'main_color', 'value' => '#e00109']),
        ]);

        $c2 = new Card(['slug' => 'juan-salazar', 'qr_code' => 'qr-juan-salazar.png']);
        $c2->client()->associate(User::find(2));
        $c2->save();
        $c2->fields()->saveMany([
            new CardField(['group' => 'others', 'key' => 'logo', 'value' => 'logo-juan-salazar.png']),
            new CardField(['group' => 'others', 'key' => 'name', 'value' => 'Juan Salazar']),
            new CardField(['group' => 'others', 'key' => 'cargo', 'value' => 'Jefe de Mercadeo y Asistencia Técnica']),
            new CardField(['group' => 'others', 'key' => 'company', 'value' => 'CENTELSA']),
            new CardField(['group' => 'others', 'key' => 'description', 'value' => 'Experiencia y respaldo que dan vidas a tus proyectos']),
            //
            new CardField(['group' => 'action_contacts', 'key' => 'phone', 'value' => '+573154332611']),
            new CardField(['group' => 'action_contacts', 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => 'action_contacts', 'key' => 'whatsapp', 'value' => '+573154332611']),
            //
            new CardField(['group' => 'contact_list', 'key' => 'cellphone', 'value' => '+573164784035']),
            new CardField(['group' => 'contact_list', 'key' => 'phone', 'value' => '0326083400']),
            new CardField(['group' => 'contact_list', 'key' => 'phone', 'value' => '0323920200']),
            new CardField(['group' => 'contact_list', 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => 'contact_list', 'key' => 'web', 'value' => 'https://www.centelsa.com']),
            //
            new CardField(['group' => 'social_list', 'key' => 'facebook', 'value' => 'https://www.facebook.com/Centelsacolombia/?fref=ts']),
            new CardField(['group' => 'social_list', 'key' => 'instagram', 'value' => 'https://www.instagram.com/centelsacolombia/']),
            new CardField(['group' => 'social_list', 'key' => 'linkedin', 'value' => 'https://www.linkedin.com/company/centelsa/?trk=biz-companies-cym']),
            new CardField(['group' => 'social_list', 'key' => 'twitter', 'value' => 'https://twitter.com/CentelsaCables']),
            new CardField(['group' => 'social_list', 'key' => 'youtube', 'value' => 'https://www.youtube.com/channel/UCcJ-STX0fEONmilYW-5UMNw']),
            //
            new CardField(['group' => 'theme', 'key' => 'main_color', 'value' => '#e00109']),
        ]);

        $c3 = new Card(['slug' => 'lina-maria-montes-quintero', 'qr_code' => 'qr-lina-maria-montes-quintero.png']);
        $c3->client()->associate(User::find(3));
        $c3->save();
        $c3->fields()->saveMany([
            new CardField(['group' => 'others', 'key' => 'logo', 'value' => 'logo-lina-maria-montes-quintero.png']),
            new CardField(['group' => 'others', 'key' => 'name', 'value' => 'Lina Maria Montes Quintero']),
            new CardField(['group' => 'others', 'key' => 'cargo', 'value' => 'Jefe de Mercadeo y Asistencia Técnica']),
            new CardField(['group' => 'others', 'key' => 'company', 'value' => 'MONTANA GROUP']),
            new CardField(['group' => 'others', 'key' => 'description', 'value' => 'Experiencia y respaldo que dan vidas a tus proyectos']),
            //
            new CardField(['group' => 'action_contacts', 'key' => 'phone', 'value' => '+573154332611']),
            new CardField(['group' => 'action_contacts', 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => 'action_contacts', 'key' => 'whatsapp', 'value' => '+573154332611']),
            //
            new CardField(['group' => 'contact_list', 'key' => 'cellphone', 'value' => '+573164784035']),
            new CardField(['group' => 'contact_list', 'key' => 'phone', 'value' => '0326083400']),
            new CardField(['group' => 'contact_list', 'key' => 'phone', 'value' => '0323920200']),
            new CardField(['group' => 'contact_list', 'key' => 'email', 'value' => 'evelio.molano@centelsa.com.co']),
            new CardField(['group' => 'contact_list', 'key' => 'web', 'value' => 'https://www.centelsa.com']),
            //
            new CardField(['group' => 'social_list', 'key' => 'facebook', 'value' => 'https://www.facebook.com/Centelsacolombia/?fref=ts']),
            new CardField(['group' => 'social_list', 'key' => 'instagram', 'value' => 'https://www.instagram.com/centelsacolombia/']),
            new CardField(['group' => 'social_list', 'key' => 'linkedin', 'value' => 'https://www.linkedin.com/company/centelsa/?trk=biz-companies-cym']),
            new CardField(['group' => 'social_list', 'key' => 'twitter', 'value' => 'https://twitter.com/CentelsaCables']),
            new CardField(['group' => 'social_list', 'key' => 'youtube', 'value' => 'https://www.youtube.com/channel/UCcJ-STX0fEONmilYW-5UMNw']),
            //
            new CardField(['group' => 'theme', 'key' => 'main_color', 'value' => '#e00109']),
        ]);
    }
}
