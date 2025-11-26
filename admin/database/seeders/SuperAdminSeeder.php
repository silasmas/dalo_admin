<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\MainUser;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Enums\UserStatus; // si tu as créé l'enum, sinon remplace par 1

class SuperAdminSeeder extends Seeder
{
    /**
     * Exécute le seeder.
     */
    public function run(): void
    {
        // ⚙️ Définis ici le guard de ton panel Filament.
        //   Mets 'web' si tu n'as pas séparé les guards.
        $guard = env('FILAMENT_AUTH_GUARD', 'admin');

        // 🛡️ Nom du rôle super admin tel qu’attendu par Filament Shield (par défaut: super_admin)
        $superRoleName = config('filament-shield.super_admin.name', 'super_admin');

        // 👤 Identifiants de ton super admin (à adapter / ou mets des variables d'env)
        $email     = env('SUPERADMIN_EMAIL', 'admin@daloministries.org');
        $phone     = env('SUPERADMIN_PHONE', '0990000000');
        $password  = env('SUPERADMIN_PASSWORD', 'password'); // 🔒 change en prod
        $firstname = 'Dalo';
        $lastname  = 'SuperAdmin';

        // 🔁 Assure-toi que le rôle existe (idempotent)
        $role = Role::firstOrCreate(
            ['name' => $superRoleName, 'guard_name' => $guard],
            ['name' => $superRoleName]
        );

        // 👥 Crée ou met à jour l’utilisateur super admin (idempotent)
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'phone'        => $phone,
                'firstname'    => $firstname,
                'lastname'     => $lastname,
                'username'     => 'dalo.superadmin',
                'status'       => defined(UserStatus::class . '::Activated') ? UserStatus::Activated->value : 1,
                'default_role' => 0,
                'password'     => Hash::make($password),
                'created_by'   => 0,
                'country'      => 'CD',
                'city'         => 'Kinshasa',
            ]
        );

        // 🔁 Si le user existe déjà, assure le password si env a changé (optionnel)
        if (! Hash::check($password, $user->password)) {
            $user->password = Hash::make($password);
            $user->save();
        }

        // 🚮 Vide le cache Spatie Permission (important)
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 🔐 (Optionnel) Donne toutes les permissions existantes au rôle super_admin
        // Shield considère souvent le rôle super_admin comme "all-access".
        // Ce bloc assure que le rôle a tout ce qui existe déjà.
        $role->syncPermissions(Permission::where('guard_name', $guard)->get());

        // 🎟️ Assigne le rôle à l’utilisateur (idempotent)
        if (! $user->hasRole($superRoleName)) {
            $user->assignRole($superRoleName);
        }

        // ✅ Log console
        $this->command->info("Super admin prêt :
Email: {$email}
Password: {$password}
Role: {$superRoleName} (guard: {$guard})");
    }
}
