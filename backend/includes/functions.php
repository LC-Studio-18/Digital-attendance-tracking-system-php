<?php

declare(strict_types=1);

function redirectTo(string $path): void
{
    header('Location: ' . $path);
    exit;
}