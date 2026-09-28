<?php

declare(strict_types=1);

function redirect_to(string $path): void
{
    header('Location: ' . $path);
    exit;
}
