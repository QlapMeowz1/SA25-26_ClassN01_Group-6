<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    public function test_locale_switch_route_updates_session(): void
    {
        $this->from('/dashboard')
            ->get('/locale/vi')
            ->assertRedirect('/dashboard');

        $this->assertSame('vi', session('locale'));
    }

    public function test_web_middleware_applies_locale_from_session(): void
    {
        Route::get('/__locale_probe', function () {
            return response()->json(['locale' => app()->getLocale()]);
        })->middleware('web');

        $this->withSession(['locale' => 'vi'])
            ->get('/__locale_probe')
            ->assertOk()
            ->assertJson(['locale' => 'vi']);
    }
}