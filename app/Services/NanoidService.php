<?php

namespace App\Services;

use Hidehalo\Nanoid\Client;

class NanoidService
{
    protected int $length;

    protected string $alphabet;

    protected Client $client;

    public function __construct(
        int $length = 8,
        string $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ) {
        $this->length = $length;
        $this->alphabet = $alphabet;
        $this->client = new Client($this->length);
    }

    public function generate()
    {
        return $this->client->formattedId($this->alphabet);
    }
}
