<?php

/*
|--------------------------------------------------------------------------
| Shop Language Lines
|--------------------------------------------------------------------------
|
| The following language lines are used during authentication for various
| messages that we need to display to the user. You are free to modify
| these language lines according to your application's requirements.
|
*/

return [
    'welcome' => [
        'products' => 'Produkty',
        'categories' => 'Kategorie',
        'price' => 'Cena',
        'filter' => 'Filtruj'
    ],
    'columns' => [
        'actions' => 'Akcje'
    ],
    'messages' => [
        'delete_confirm' => 'Na pewno chcesz usunąć rekord?'
    ],
    'button' => [
        'save' => 'Zapisz',
        'add' => 'Dodaj',
    ],
    'user' => [
        'index_title' => 'Lista użytkowników',
        'status' => [
            'delete' => [
                'success' => 'Użytkownik usunięty!'
            ],
        ],
    ],
    'product' => [
        'add_title' => 'Dodawanie produktu',
        'edit_title' => 'Edycja produktu: :name',
        'show_title' => 'Podgląd produktu',
        'index_title' => 'Lista produktów',
        'status' => [
            'store' => [
                'success' => 'Produkt zapisany!'
            ],
            'update' => [
                'success' => 'Produkt zaktualizowany!'
            ],
            'delete' => [
                'success' => 'Produkt usunięty!'
            ],
            'sold' => [
                'success' => 'Płatność zatwierdzona. Dziękujemy za zakupy.'
            ],
        ],
        'fields' => [
            'name' => 'Nazwa',
            'description' => 'Opis',
            'amount' => 'Ilość',
            'price' => 'Cena',
            'image' => 'Grafika',
            'category' => 'Kategoria',
        ]
    ]
];
