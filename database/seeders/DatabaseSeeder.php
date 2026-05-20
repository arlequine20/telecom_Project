<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\SimCard;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@telecom.local'],
            [
                'name' => 'Telecom Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'user@telecom.local'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
            ]
        );

        Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 100.00,
                'total_spend' => 0.00,
                'data_balance' => 1024.00,
                'data_unit' => 'MB',
            ]
        );

        $customer = Customer::firstOrCreate(
            ['email' => 'user@telecom.local'],
            [
                'user_id' => $user->id,
                'first_name' => 'Demo',
                'last_name' => 'User',
                'phone' => '+250788000001',
                'address' => 'Kigali, Rwanda',
                'national_id' => 'RWA1234567',
                'date_of_birth' => '1995-05-15',
                'status' => 'active',
            ]
        );

        SimCard::firstOrCreate(
            ['sim_number' => '250788000001'],
            [
                'phone_number' => '+250788000001',
                'balance' => 50.00,
                'data_balance' => 512.00,
                'status' => 'active',
                'tariff_plan' => 'prepaid',
                'customer_id' => $customer->id,
                'assigned_at' => Carbon::now(),
                'last_activity_at' => Carbon::now(),
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+1234567890',
                'address' => '123 Main St',
                'national_id' => 'ID123456',
                'date_of_birth' => '1990-01-01',
                'status' => 'active',
            ]
        );

        SimCard::firstOrCreate(
            ['sim_number' => '8901234567890123456'],
            [
                'phone_number' => '+1234567890',
                'balance' => 500.00,
                'data_balance' => 2048.00,
                'status' => 'active',
                'tariff_plan' => 'prepaid',
                'customer_id' => $customer2->id,
                'assigned_at' => Carbon::now(),
                'last_activity_at' => Carbon::now(),
            ]
        );

        $customer3 = Customer::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'phone' => '+1234567891',
                'address' => '456 Oak Ave',
                'national_id' => 'ID123457',
                'date_of_birth' => '1992-05-15',
                'status' => 'active',
            ]
        );

        SimCard::firstOrCreate(
            ['sim_number' => '8901234567890123457'],
            [
                'phone_number' => '+1234567891',
                'balance' => 100.00,
                'data_balance' => 1024.00,
                'status' => 'active',
                'tariff_plan' => 'prepaid',
                'customer_id' => $customer3->id,
                'assigned_at' => Carbon::now(),
                'last_activity_at' => Carbon::now(),
            ]
        );

        SimCard::firstOrCreate(
            ['sim_number' => '8901234567890123458'],
            [
                'phone_number' => '+1234567892',
                'balance' => 0.00,
                'data_balance' => 0.00,
                'status' => 'inactive',
                'tariff_plan' => 'prepaid',
            ]
        );
    }
}