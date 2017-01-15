<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;


class UserGeneratorCommand extends Command
{
    protected $signature = 'user:generate {email : The account email}
        {--name= : The user name.}
        {--password= : The user password.}
        {--admin : The user should be created as admin.}';

    protected $description = 'Generate a new user';

    protected $name = 'user:generate';

    protected $user;

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function fire()
    {
        $email = trim($this->input->getArgument('email'));

        $existing = User::where('email', '=', $email)->first();

        if ($existing) {
            $this->error("Email already registered to user id {$existing->id}");
            return;
        }

        $this->user->email = $this->argument('email');

        if (!$this->option('name')) {
            $this->user->name = $this->user->email;
        } else {
            $this->user->name = $this->option('name');
        }

        $password = $this->option('password');

        if (!$password) {
            $password = $this->getRandomPassword();
        }

        $this->user->password = $password;

        //TODO: use admin option

        // Save the user to the database.
        $this->user->save();

        // Report that the user has been saved.
        $this->info("[{$this->user->id}] {$this->user->name} has been generated and saved.");

        if (!$this->option('password')) {
            $this->info("Generated password: {$password}");
        }
    }

    protected function getArguments()
    {
        return [
            ['email', InputOption::VALUE_REQUIRED, 'Email address'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['name', null, InputOption::VALUE_OPTIONAL, 'Name of the new user'],
            ['password', null, InputOption::VALUE_OPTIONAL, 'Password'],
            ['admin', null, InputOption::VALUE_OPTIONAL, 'Set user as superuser']
        ];
    }

    protected function getRandomPassword()
    {
        return Str::random(16);
    }
}
