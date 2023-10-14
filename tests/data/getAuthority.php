<?php
/**
 * bool $idn, string $host, ?string $port, ?string $user, ?string $password, $expected
 */
return [
    [false, 'host.ru', '8080', 'user', 'password', 'user:password@host.ru:8080'],
    [false, 'host.ru', '8080', 'user', 'pas@sword', 'user:pas%40sword@host.ru:8080'],
    [true, 'привет.рф', '8080', 'user', 'pas@sword', 'user:pas%40sword@xn--b1agh1afp.xn--p1ai:8080'],
    [false, 'привет.рф', '8080', 'user', 'pas@sword', 'user:pas%40sword@привет.рф:8080'],
    [false, 'host.ru', '', 'user', 'pas@sword', 'user:pas%40sword@host.ru'],
    [false, 'host.ru', '', 'user', '', 'user@host.ru'],
    [false, 'host.ru', '', '', '', 'host.ru'],
    [false, 'host.ru', '80', '', '', 'host.ru:80'],
];