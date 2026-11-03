<?php

namespace App\Contracts;

interface ConfigLoader
{
    public function load(): ConfigRepository;
}
