<?php
declare(strict_types=1);

namespace App\Foundation\Cloud;


use App\Foundation\Bridge\Laravel\UpTrait;
use App\Foundation\Exceptions\NotImplementedException;

class CloudStorage
{
    use UpTrait;

    public function get(string $path): void
    {
        throw new NotImplementedException();
    }

    public function delete(string $path): void
    {
        throw new NotImplementedException();
    }

    public function write(string $path, $file): void
    {
        throw new NotImplementedException();

    }

    public function put(string $path, $file): void
    {
        throw new NotImplementedException();

    }
}