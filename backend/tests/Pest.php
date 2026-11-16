<?php

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Platform\Rbac\Services\SeedBusinessRoles;
use App\Modules\Platform\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
function rbacSeededBusiness(): Business
{
    $business = Business::factory()->create();
    app(SeedBusinessRoles::class)->run($business);

    return $business;
}

/**
 * @param  list<Outlet>  $outlets
 */
function rbacUserWithRole(Business $business, string $role, array $outlets = []): User
{
    $user = User::factory()->for($business)->create();

    foreach ($outlets as $outlet) {
        $user->outlets()->attach($outlet->id, ['business_id' => $business->id]);
    }

    app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);
    $user->assignRole($role);

    return $user->fresh();
}
