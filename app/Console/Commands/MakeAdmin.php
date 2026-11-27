<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Makes the first admin of a new install, without the demo content of the seeders. Asks for what is missing,
 * or takes everything as options. When the email address is taken, that user becomes an admin.
 */
class MakeAdmin extends Command
{
    protected $signature = 'app:make:admin
        {--name= : The name of the admin}
        {--email= : The email address to sign in with}
        {--password= : The password, asked when it is left out}';

    protected $description = 'Make an admin user, or make an existing user an admin';

    public function handle(): int
    {
        $this->callSilently('db:seed', ['--class' => RolesSeeder::class, '--force' => true]);

        $email = $this->option('email') ?: text(
            label: 'Email address',
            required: true,
            validate: ['email' => ['email', 'max:255']],
        );

        if (Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']])->fails()) {
            $this->components->error('That is not a valid email address.');

            return self::FAILURE;
        }

        $existingUser = User::query()->firstWhere('email', $email);

        if ($existingUser !== null) {
            $existingUser->assignRole(UserRole::Admin->value);

            $this->components->info("{$existingUser->name} ({$email}) is an admin now.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: text(label: 'Name', required: true, validate: ['name' => ['max:255']]);
        $password = $this->option('password') ?: password(
            label: 'Password',
            required: true,
            validate: ['password' => [Password::default()]],
        );

        $validator = Validator::make(
            ['name' => $name, 'password' => $password],
            ['name' => ['required', 'string', 'max:255'], 'password' => ['required', 'string', Password::default()]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->create(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->markEmailAsVerified();
        $user->assignRole(UserRole::Admin->value);

        $this->components->info("Admin {$name} ({$email}) is made. Sign in at ".route('login').'.');

        return self::SUCCESS;
    }
}
