<?php

return [
    'id' => 'ID',
    'title' => 'názov',
    'description' => 'popis',
    'description_source' => 'popis - zdroj',
    'work_type' => 'výtvarný druh',
    'work_level' => 'stupeň spracovania',
    'topic' => 'žáner',
    'subject' => 'objekt',
    'measurement' => 'miery',
    'dating' => 'datovanie',
    'medium' => 'materiál',
    'technique' => 'technika',
    'inscription' => 'značenie',
    'place' => 'geografická oblasť',
    'state_edition' => 'stupeň spracovania',
    'gallery' => 'galéria',
    'credit' => 'nadobudnutie',
    'relationship_type' => 'typ integrity',
    'related_work' => 'názov integrity',
    'description_user_id' => 'popis - autor',
    'description_source_link' => 'popis - link na zdroj',
    'identifier' => 'inventárne číslo',
    'author' => 'autor',
    'tags' => 'tagy',
    'tag' => 'tag',
    'date_earliest' => 'datovanie najskôr',
    'date_latest' => 'datovanie najneskôr',
    'lat' => 'latitúda',
    'lng' => 'longitúda',
    'related_work_order' => 'poradie',
    'related_work_total' => 'z počtu',
    'primary_image' => 'hlavný obrázok',
    'images' => 'obrázky',
    'iipimg_url' => 'IIP URL',
    'filter' => [
        'year_from' => 'od roku',
        'year_to' => 'do roku',
        'has_image' => 'len s obrázkom',
        'has_iip' => 'len so zoom',
        'is_free' => 'len voľné',
        'color' => 'farba',
        'sort_by' => 'podľa',
        'sorting' => [
            'created_at' => 'dátumu pridania',
            'title' => 'názvu',
            'relevance' => 'relevancie',
            'updated_at' => 'poslednej zmeny',
            'author' => 'autora',
            'newest' => 'datovania – od najnovšieho',
            'oldest' => 'datovania – od najstaršieho',
            'view_count' => 'počtu videní',
            'random' => 'náhodného poradia'
        ],
        'title_generator' => [
            'search' => 'výsledky vyhľadávania pre: ":value"',
            'author' => 'autor: :value',
            'work_type' => 'výtvarný druh: :value',
            'tag' => 'tag: :value',
            'gallery' => 'galéria: :value',
            'credit' => 'nadobudnutie: :value',
            'topic' => 'žáner: :value',
            'medium' => 'materiál: :value',
            'technique' => 'technika: :value',
            'related_work' => 'zo súboru: :value',
            'years' => 'v rokoch :from — :to',
        ],
    ],
    'importer' => [
        'measurement' => [
            'primary_height' => 'výška hlavnej časti',
            'secondary_height' => 'výška vedľajšej časti',
            'time' => 'čas',
            'length' => 'délka',
            'depth' => 'hĺbka/hrúbka',
            'weight' => 'hmotnosť',
            'depth_with_frame' => 'hĺbka s rámom',
            'other' => 'iný nešpecifikovaný',
            'diameter' => 'priemer',
            'caliber' => 'kaliber',
            'purity' => 'rýdzosť',
            'width' => 'šírka',
            'width_graphics_board' => 'šírka grafickej dosky',
            'width_with_mat' => 'šírka s paspartou',
            'width_with_frame' => 'šírka s rámom',
            'overall_height_length' => 'celková výška/dĺžka',
            'height_graphics_board' => 'výška grafickej dosky',
            'height_with_mat' => 'výška s paspartou',
            'height_with_frame' => 'výška s rámom',
        ],
        'work_type' => [
            'graphics' => 'grafika',
            'drawing' => 'kresba',
            'image' => 'obraz',
            'sculpture' => 'sochárstvo',
            'applied_arts' => 'úžitkové umenie',
            'photography' => 'fotografia',
        ],
    ],
];
