<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Catalog
            'view brands', 'create brands', 'edit brands', 'delete brands',
            'view collections', 'create collections', 'edit collections', 'delete collections',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view products', 'create products', 'edit products', 'delete products',
            'view tags', 'create tags', 'edit tags', 'delete tags',
            'view sizes', 'create sizes', 'edit sizes', 'delete sizes',
            'view size groups', 'create size groups', 'edit size groups', 'delete size groups',
            'view colors', 'create colors', 'edit colors', 'delete colors',
            'view product attributes', 'create product attributes', 'edit product attributes', 'delete product attributes',

            // Sales
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'view orders', 'create orders', 'edit orders', 'delete orders',
            'view reviews', 'create reviews', 'edit reviews', 'delete reviews',

            // Inventory
            'view inventory', 'create inventory', 'edit inventory', 'delete inventory',
            'view stock movements', 'create stock movements', 'edit stock movements', 'delete stock movements',

            // Content
            'view banners', 'create banners', 'edit banners', 'delete banners',
            'view pages', 'create pages', 'edit pages', 'delete pages',
            'view menus', 'create menus', 'edit menus', 'delete menus',
            'view menu items', 'create menu items', 'edit menu items', 'delete menu items',

            // Administration
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create admin role with all permissions
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions(Permission::all());

        // Create manager role with limited permissions
        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);
        $managerPermissions = Permission::whereIn('name', [
            'view brands', 'create brands', 'edit brands',
            'view collections', 'create collections', 'edit collections',
            'view categories', 'create categories', 'edit categories',
            'view products', 'create products', 'edit products',
            'view tags', 'create tags', 'edit tags',
            'view sizes', 'create sizes', 'edit sizes',
            'view size groups', 'create size groups', 'edit size groups',
            'view colors', 'create colors', 'edit colors',
            'view product attributes', 'create product attributes', 'edit product attributes',
            'view customers',
            'view orders', 'edit orders',
            'view reviews', 'edit reviews',
            'view inventory', 'edit inventory',
            'view stock movements',
            'view banners', 'create banners', 'edit banners',
            'view pages', 'create pages', 'edit pages',
            'view menus', 'create menus', 'edit menus',
            'view menu items', 'create menu items', 'edit menu items',
        ])->get();
        $managerRole->syncPermissions($managerPermissions);

        // Create editor role with view/edit permissions
        $editorRole = Role::firstOrCreate([
            'name' => 'editor',
            'guard_name' => 'web',
        ]);
        $editorPermissions = Permission::whereIn('name', [
            'view brands', 'edit brands',
            'view collections', 'edit collections',
            'view categories', 'edit categories',
            'view products', 'edit products',
            'view tags', 'edit tags',
            'view sizes', 'edit sizes',
            'view size groups', 'edit size groups',
            'view colors', 'edit colors',
            'view product attributes', 'edit product attributes',
            'view customers',
            'view orders',
            'view reviews', 'edit reviews',
            'view inventory',
            'view stock movements',
            'view banners', 'edit banners',
            'view pages', 'edit pages',
            'view menus', 'edit menus',
            'view menu items', 'edit menu items',
        ])->get();
        $editorRole->syncPermissions($editorPermissions);

        $this->command->info('Permissions and roles seeded successfully!');
    }
}