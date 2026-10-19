<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The entry point redirects to the hall list.
     */
    public function test_the_application_redirects_to_the_halls_page(): void
    {
        $this->get('/')->assertRedirect(route('halls.index'));
    }
}
