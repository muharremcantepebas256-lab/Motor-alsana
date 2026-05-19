<?php

function send_mail(string $to, string $subject, string $message): bool
{
    $headers = "From: noreply@motoralsana.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return @mail($to, $subject, $message, $headers);
}
