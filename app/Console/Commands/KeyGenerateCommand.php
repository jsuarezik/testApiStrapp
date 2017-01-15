<?php
namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class KeyGenerateCommand extends Command
{
    protected $signature = 'key:generate
        {--s|show : Display the key instead of modifying files.}
        {--f|force : Skip confirmation when overwriting an existing key.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Set the application key";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $key = $this->getRandomKey();

        if ($this->option('show')) {
            return $this->comment($key);
        }

        $path = base_path('.env');

        if (file_exists($path)) {

            // check if there is already a secret set first
            if (! Str::contains(file_get_contents($path), 'APP_KEY')) {
                file_put_contents($path, PHP_EOL."APP_KEY=$key", FILE_APPEND);
            } else {

                // let's be sure you want to do this, unless you already told us to force it
                $confirmed = $this->option('force') || $this->confirm('This will invalidate all existing tokens. Are you sure you want to override the secret key?');

                if ($confirmed) {
                    file_put_contents($path, str_replace(
                        'APP_KEY='.$this->laravel['config']['app.key'], 'APP_KEY='.$key, file_get_contents($path)
                    ));
                } else {
                    return $this->comment('Phew... No changes were made to your secret key.');
                }
            }
        }

        $this->info("Application key [$key] set successfully.");
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function getRandomKey()
    {
        return Str::random(32);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('show', null, InputOption::VALUE_NONE, 'Simply display the key instead of modifying files.'),
        );
    }
}
