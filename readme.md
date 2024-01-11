# NeuroMedia iCards

Compilar assets.

```
npm run dev
npm run watch
```

Compilar assets para producción.

```
npm run prod
```

---

Cambiar la contraseña de todos los usuarios para pruebas.

```php
\DB::table('users')->update(['password' => \Hash::make('secret')]);
```

---

Ejemplo API para generar los campos de las plantillas.

```php
public const TEMPLATE_FIELDS = [
    GroupField::OTHERS => new Group('Datos de Tarjeta', [
        (new ImageField('profile', 'Foto de perfil'))
            ->general()
            ->default(null)
            ->max(250)
            ->help('(jpeg, jpg, png) Tamaño máximo: 250kb')
            ->example('foto-nombre.jpg'),
        (new SelectField('header_bg_type', 'Tipo de Cabecera', [
                new SelectOption('header_bg_color', 'Color plano de fondo'),
                new SelectOption('header_bg_gradient', 'Gradiente de fondo'),
                new SelectOption('header_bg_image', 'Imagen de fondo'),
            ]))
            ->general()
            ->default('header_bg_color')
            ->example('header_bg_color')
            ->watchable(),
    ]),
];
```


```php

// desde

$fields = [
    GroupField::OTHERS => [
        'label' => 'Datos de Tarjeta',
        'values' => [
            [
                'key' => 'default_lang',
                'label' => 'Idioma de tarjetas',
                'type' => FieldType::SELECT,
                'general' => Field::GENERAL,
                'default' => 'es',
                'options' => [
                    ['id' => 'es', 'name' => 'Español'],
                    ['id' => 'en', 'name' => 'Inglés'],
                ],
                'example' => 'es',
            ],
        ],
    ],
];

// hacia

$fields = [
    (new Group(GroupField::OTHERS, 'Datos de Tarjeta'))
        ->fields([
            (new Field('default_lang', 'Idioma de tarjetas'))
                ->select([
                    new SelectOption('es', 'Español'),
                    new SelectOption('en', 'Inglés'),
                ])
                ->general()
                ->default('es')
                ->example('es'),
        ]),
];
```