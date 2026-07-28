<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LogicException;

final class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim(
            (string) config('demo_user.name')
        );

        $email = mb_strtolower(
            trim(
                (string) config('demo_user.email')
            )
        );

        $password = (string) config(
            'demo_user.password'
        );

        if (
            $name === ''
            || $email === ''
            || $password === ''
        ) {
            throw new LogicException(
                'The demo user environment variables must be configured before running the seeder.'
            );
        }

        User::query()->updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => $name,
                'password' => Hash::make(
                    $password
                ),
                'email_verified_at' => now(),
            ],
        );
    }
}
