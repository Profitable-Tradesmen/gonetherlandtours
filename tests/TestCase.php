<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Safety net: any HTTP call a test did not explicitly fake throws, so tests can never reach
        // the live GoHighLevel API, Google reCAPTCHA or anything else.
        Http::preventStrayRequests();
    }
}
