<?php
/**
 * usage:
 * php artisan matches:createUser {name} {e-mail} {role} {password}
 *
 * help:
 * php artisan matches:createUser --help
 *
 * example:
 * php artisan matches:createUser test123 test123@test123.ng company 123
 *
 * This script as a modified version of
 * src/spresnac/createcliuser/CreateCliUserCommand.php
 *
 */

namespace Modules\Matches\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\Matches\Enums\EMatchRoles;
use Symfony\Component\Console\Input\InputArgument;


class CreateMatchesUser extends Command
{

    public const E_OK = 0;
    public const E_USER_EXISTS = 1;
    public const E_UPDATING_FAILED = 2;
    public const E_CHAOS = 5;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'matches:createUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new user by : '
    . ' php artisan matches:createUser {name} {e-mail} {role} {password}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        /** @var User $user */
        $user = new User();
        $user->name = $this->argument('name');
        $user->email = $this->argument('email');
        $user->password = $this->argument('password');

        $exists = (new User())->where([
            'name' => $user->name,
            'email' => $user->email,
        ])->first();
        if ($exists === null) {
            $user->save();
            $user->assignRole($this->argument('role'));
            $this->info('Created a user with id: '.$user->id);
            return self::E_OK;

        }
        $this->error('User already exist with id: '.$exists->id);
        return self::E_USER_EXISTS;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        $roles = implode(', ', array_column(EMatchRoles::cases(), 'value'));
        return [
            ['name', InputArgument::REQUIRED, 'name'],
            ['email', InputArgument::REQUIRED, 'email'],
            ['role', InputArgument::REQUIRED, 'role (' . $roles . ')'],
            ['password', InputArgument::REQUIRED, 'password'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
