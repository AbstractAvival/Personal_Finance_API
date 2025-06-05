<?php

namespace Tests;

use App\Models\User;
use DB;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $authenticatedUser;
    protected $baseUri = '/api';
    protected $user = [
        "name" => "Test McTesty",
        "email" => "test@gmail.com",
        "email_verified_at" => "",
        "remember_token" => "",
    ];

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $this->withHeader( "Accept", "application/json" );
        $this->authenticatedUser = new User( $this->user );
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }
}
