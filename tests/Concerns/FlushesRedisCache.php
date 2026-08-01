<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Cache;

trait FlushesRedisCache
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::store('redis')->flush();
    }

    protected function tearDown(): void
    {
        Cache::store('redis')->flush();

        parent::tearDown();
    }
}