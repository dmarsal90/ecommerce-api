<?php

namespace Tests\Feature;

use Tests\TestCase;

class RootRouteTest extends TestCase
{
    public function test_root_does_not_expose_the_framework(): void
    {
        $this->get('/')->assertNotFound();
    }
}
