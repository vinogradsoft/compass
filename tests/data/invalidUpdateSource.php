<?php
/**
 * string $scheme, string $user, string $password, string $host, string $port
 */
return [
    ['http', '', '', '', ''],
    ['http', '', 'password', '', '80'],
    ['http', 'user', '', '', '80'],
    ['http', 'user', 'password', '', ''],
    ['', 'user', 'password', 'host.ru', '80'],
    ['http', 'user', 'password', '', '80'],
];