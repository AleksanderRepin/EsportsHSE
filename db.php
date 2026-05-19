<?php

$pdo = new PDO(
    "pgsql:host=localhost;dbname=esports",
    "admin_user",
    "xGghpXEC2lEP9wF",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
