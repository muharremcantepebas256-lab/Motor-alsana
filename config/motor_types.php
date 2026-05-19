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
