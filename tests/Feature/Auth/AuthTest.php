<?php

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('registers a new user and sends welcome mail', function (): void {
    Mail::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Test Learner',
        'email' => 'learner@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('home'));

    $user = User::where('email', 'learner@example.com')->first();

    expect($user)->not->toBeNull();
    $this->assertAuthenticatedAs($user);

    Mail::assertSent(WelcomeUserMail::class, function (WelcomeUserMail $mail) use ($user): bool {
        return $mail->user->is($user);
    });
});

it('logs in an existing user with valid credentials', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
});
