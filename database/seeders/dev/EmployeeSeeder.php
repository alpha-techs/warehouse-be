<?php

namespace Database\Seeders\dev;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Seed a small catalog of employees that mirrors the auth contract.
     */
    public function run(): void
    {
        $password = env('SEED_EMPLOYEE_PASSWORD', 'Secret123!');

        $employees = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'roles' => ['admin', 'warehouse_manager'],
            ],
            [
                'name' => 'Warehouse Manager',
                'email' => 'manager@example.com',
                'roles' => ['warehouse_manager'],
            ],
            [
                'name' => 'Operator',
                'email' => 'operator@example.com',
                'roles' => ['warehouse_operator'],
            ],
        ];

        foreach ($employees as $index => $data) {
            /** @var User $user */
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($password),
                    'roles' => Arr::wrap($data['roles']),
                    'email_verified_at' => now(),
                    'avatar_url' => $data['avatar_url'] ?? null,
                    'last_login_at' => now(),
                ]
            );

            if (filled($user->employee_code)) {
                continue;
            }

            $user->forceFill([
                'employee_code' => sprintf('emp_%05d', $index + 1),
            ])->save();
        }
    }
}
