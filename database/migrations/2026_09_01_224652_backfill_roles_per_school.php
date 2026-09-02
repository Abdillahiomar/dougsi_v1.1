<?php
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $registrar = app(PermissionRegistrar::class);

        // 1. Récupérer les rôles globaux actuels (school_id = null) avec leurs permissions
        $globalRoles = \Spatie\Permission\Models\Role::whereNull('school_id')
            ->with('permissions')
            ->get();

        // 2. Pour chaque école, créer sa copie de chaque rôle
        $schools = \App\Models\School::all();

        foreach ($schools as $school) {
            // Définir le contexte team pour cette école
            $registrar->setPermissionsTeamId($school->id);

            foreach ($globalRoles as $globalRole) {
                // Créer (ou retrouver) le rôle pour cette école
                $schoolRole = \Spatie\Permission\Models\Role::firstOrCreate([
                    'name'       => $globalRole->name,
                    'guard_name' => $globalRole->guard_name,
                    'school_id'  => $school->id,
                ]);

                // Copier les permissions du rôle global
                $permNames = $globalRole->permissions->pluck('name')->toArray();
                $schoolRole->syncPermissions($permNames);
            }
        }

        // 3. Réassigner chaque utilisateur au rôle de SON école
        \App\Models\User::whereNotNull('school_id')->with('roles')->chunk(100, function ($users) use ($registrar) {
            foreach ($users as $user) {
                $registrar->setPermissionsTeamId($user->school_id);

                // Noms des rôles globaux actuellement assignés
                $roleNames = $user->roles->pluck('name')->unique()->toArray();

                if (empty($roleNames)) continue;

                // Retrouver les rôles de son école correspondants
                $schoolRoles = \Spatie\Permission\Models\Role::where('school_id', $user->school_id)
                    ->whereIn('name', $roleNames)
                    ->get();

                // Assigner les rôles de l'école (dans le contexte team)
                $user->syncRoles($schoolRoles);
            }
        });

        // 4. (optionnel, à décommenter APRÈS validation) supprimer les rôles globaux
        // \Spatie\Permission\Models\Role::whereNull('school_id')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Rollback : on ne supprime rien automatiquement pour éviter de casser les accès.
        // À gérer manuellement si besoin.
    }
};