<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleTemplateService
{
    /**
     * Source de vérité : les 6 rôles standards et leurs permissions.
     * Toute nouvelle école reçoit exactement cette configuration.
     *
     * NOTE : la config du "comptable" est reprise de l'existant.
     * À ajuster ici si tu veux que les FUTURES écoles aient une config différente.
     */
    public static function template(): array
    {
        return [
            'admin' => [
                'absences.manage', 'absences.view',
                'academic_years.manage', 'academic_years.view',
                'announcements.manage', 'announcements.view',
                'bulletins.generate', 'bulletins.view',
                'calander.view',
                'classes.manage', 'classes.view',
                'fees.manage',
                'finance.close', 'finance.collect', 'finance.manage', 'finance.view',
                'grades.enter', 'grades.manage', 'grades.view',
                'homeworks.view',
                'school.settings',
                'staff.manage', 'staff.view',
                'students.create', 'students.delete', 'students.edit',
                'students.enroll', 'students.show', 'students.view',
                'subjects.manage', 'subjects.view',
                'timetable.view',
                'users.manage', 'users.view',
            ],

            'directeur' => [
                'absences.manage', 'absences.view',
                'academic_years.manage', 'academic_years.view',
                'announcements.manage', 'announcements.view',
                'bulletins.generate', 'bulletins.view',
                'classes.manage', 'classes.view',
                'fees.manage',
                'finance.manage', 'finance.view',
                'grades.manage', 'grades.view',
                'school.settings',
                'staff.manage', 'staff.view',
                'students.create', 'students.edit', 'students.enroll', 'students.view',
                'subjects.manage', 'subjects.view',
                'users.view',
            ],

            'comptable' => [
                'absences.manage', 'absences.view',
                'announcements.view',
                'bulletins.view',
                'calander.view',
                'classes.view',
                'homeworks.view',
                'students.view',
                'timetable.view',
            ],

            'enseignant' => [
                'absences.manage', 'absences.view',
                'announcements.view',
                'bulletins.generate', 'bulletins.view',
                'calander.view',
                'classes.view',
                'grades.enter', 'grades.view',
                'homeworks.view',
                'students.show', 'students.view',
                'timetable.view',
            ],

            'surveillant' => [
                'absences.manage', 'absences.view',
                'announcements.view',
                'classes.view',
                'students.view',
            ],

            'parent' => [
                'absences.view',
                'announcements.view',
                'bulletins.view',
                'homeworks.view',
                'students.show', 'students.view',
                'timetable.view',
            ],
        ];
    }

    /**
     * Crée (ou complète) les 6 rôles standards pour une école donnée,
     * avec leurs permissions. Idempotent : peut être relancé sans dégât.
     *
     * Usage :
     *   - À la création d'une école.
     *   - Comme réparation si une école a des rôles manquants.
     */
    public static function createForSchool(int $schoolId): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($schoolId);

        foreach (self::template() as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
                'school_id'  => $schoolId,
            ]);

            // syncPermissions ne pose que les permissions qui existent réellement.
            // (Si une permission du template n'existe pas en base, elle est ignorée.)
            $role->syncPermissions($permissions);
        }

        $registrar->forgetCachedPermissions();
    }
}