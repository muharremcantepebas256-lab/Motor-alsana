<?php

function get_motor_types(): array
{
    return [
        "Naked",
        "Sport",
        "Touring",
        "Adventure",
        "Cruiser",
        "Enduro",
        "Motocross",
        "Supermoto",
        "Cafe Racer",
        "Chopper",
        "Scooter",
        "Trail",
        "Klasik",
        "Elektrikli",
    ];
}

function is_valid_motor_type(string $type): bool
{
    return in_array($type, get_motor_types(), true);
}

function get_brands(): array
{
    return [
        "Yamaha",
        "Honda",
        "Kawasaki",
        "Suzuki",
        "BMW",
        "Ducati",
        "KTM",
        "Harley-Davidson",
        "Triumph",
        "Aprilia",
        "Benelli",
        "MV Agusta",
        "Husqvarna",
        "Royal Enfield",
        "Indian",
        "Moto Guzzi",
        "CF Moto",
        "Bajaj",
        "TVS",
        "Kymco",
        "SYM",
        "Vespa",
        "Piaggio",
        "Beta",
        "GasGas",
        "Mondial",
        "RKS",
        "Kanuni",
        "Ramzey",
        "Yuki",
        "Diger",
    ];
}

function is_valid_brand(string $brand): bool
{
    return in_array($brand, get_brands(), true);
}

function get_colors(): array
{
    return [
        "Siyah",
        "Beyaz",
        "Kirmizi",
        "Mavi",
        "Yesil",
        "Sari",
        "Turuncu",
        "Gri",
        "Gumus",
        "Lacivert",
        "Bordo",
        "Kahverengi",
        "Mor",
        "Pembe",
        "Altin",
        "Mat Siyah",
        "Mat Gri",
        "Diger",
    ];
}

function is_valid_color(string $color): bool
{
    return in_array($color, get_colors(), true);
}

function get_origin_countries(): array
{
    return [
        "Japonya",
        "Almanya",
        "Italya",
        "ABD",
        "Ingiltere",
        "Avusturya",
        "Ispanya",
        "Cin",
        "Hindistan",
        "Tayvan",
        "Turkiye",
        "Guney Kore",
        "Diger",
    ];
}

function is_valid_origin(string $origin): bool
{
    return in_array($origin, get_origin_countries(), true);
}

function get_cylinder_counts(): array
{
    return [1, 2, 3, 4, 6];
}

function is_valid_cylinder_count(int $count): bool
{
    return in_array($count, get_cylinder_counts(), true);
}
