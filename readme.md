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
