<?php
/**
 * // php artisan db:seed --class=TestUsersSeeder
 *  php artisan module:seed Matches --class="RoleAndPermissionSeeder"
 */
namespace Modules\Matches\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Matches\Enums\EMatchRoles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        sleep(1);

        $items = ['forms', 'matches', 'profiles', 'dashboard'];
        foreach ($items as $item) {
            Permission::create(['name' => 'create-' . $item, 'guard_name' => 'web']);
            Permission::create(['name' => 'edit-' . $item, 'guard_name' => 'web']);
            Permission::create(['name' => 'delete-' . $item, 'guard_name' => 'web']);
            Permission::create(['name' => 'view-' . $item, 'guard_name' => 'web']);
        }

        /** @var Role $adminRole */
        $adminRole = Role::create(['name' => EMatchRoles::ADMIN->value]);
        /** @var Role $companyRole */
        $companyRole = Role::create(['name' => EMatchRoles::COMPANY->value]);
        /** @var Role $companyRole */
        $professionalRole = Role::create(['name' => EMatchRoles::PROFESSIONAL->value]);

        $adminRole->givePermissionTo(Permission::all()->pluck('name'));
        $companyRole->givePermissionTo(
            [
                'view-matches',
                'create-profiles',
                'edit-profiles',
                'delete-profiles',
                'view-profiles',
            ]
        );
        $professionalRole->givePermissionTo(
            [
                'view-matches',
                'create-profiles',
                'edit-profiles',
                'view-profiles',
            ]
        );


    }
}
