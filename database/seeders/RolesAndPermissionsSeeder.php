<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos organizados

        // VER
        Permission::create(['name' => 'ver personas']);
        Permission::create(['name' => 'ver nacionalidad']);
        Permission::create(['name' => 'ver usuario']);
        Permission::create(['name' => 'ver pacientes']);
        Permission::create(['name' => 'ver medicocentromedico']);
        Permission::create(['name' => 'ver centromedico']);
        Permission::create(['name' => 'ver enfermedades']);
        Permission::create(['name' => 'ver especialidad']);
        Permission::create(['name' => 'ver especialidadmedicos']);
        Permission::create(['name' => 'ver medicos']);
        Permission::create(['name' => 'ver enfermedades_pacientes']);
        Permission::create(['name' => 'ver recetas']);
        Permission::create(['name' => 'ver consultas']);
        Permission::create(['name' => 'ver contratomedico']);
        Permission::create(['name' => 'ver nomina']);
        Permission::create(['name' => 'ver detallenomina']);
        Permission::create(['name' => 'ver citas']); // NUEVO
        Permission::create(['name' => 'ver cai_correlativos']);
        Permission::create(['name' => 'ver cai_autorizaciones']);
        Permission::create(['name' => 'ver cuentas_por_cobrars']);
        Permission::create(['name' => 'ver servicio']);
        Permission::create(['name' => 'ver Impuesto']);
        Permission::create(['name' => 'ver factura']);
        Permission::create(['name' => 'ver descuento']);
        Permission::create(['name' => 'ver factura_detalles']);
        Permission::create(['name' => 'ver pagos_facturas']);

        // CREAR
        Permission::create(['name' => 'crear personas']);
        Permission::create(['name' => 'crear nacionalidad']);
        Permission::create(['name' => 'crear usuario']);
        Permission::create(['name' => 'crear pacientes']);
        Permission::create(['name' => 'crear medicocentromedico']);
        Permission::create(['name' => 'crear centromedico']);
        Permission::create(['name' => 'crear enfermedades']);
        Permission::create(['name' => 'crear especialidad']);
        Permission::create(['name' => 'crear especialidadmedicos']);
        Permission::create(['name' => 'crear medicos']);
        Permission::create(['name' => 'crear enfermedades_pacientes']);
        Permission::create(['name' => 'crear recetas']);
        Permission::create(['name' => 'crear consultas']);
        Permission::create(['name' => 'crear contratomedico']);
        Permission::create(['name' => 'crear nomina']);
        Permission::create(['name' => 'crear detallenomina']);
        Permission::create(['name' => 'crear cai_correlativos']);
        Permission::create(['name' => 'crear cai_autorizaciones']);
        Permission::create(['name' => 'crear cuentas_por_cobrars']);
        Permission::create(['name' => 'crear servicio']);
        Permission::create(['name' => 'crear Impuesto']);
        Permission::create(['name' => 'crear factura']);
        Permission::create(['name' => 'crear descuento']);
        Permission::create(['name' => 'crear factura_detalles']);
        Permission::create(['name' => 'crear pagos_facturas']);


        // ACTUALIZAR
        Permission::create(['name' => 'actualizar personas']);
        Permission::create(['name' => 'actualizar nacionalidad']);
        Permission::create(['name' => 'actualizar usuario']);
        Permission::create(['name' => 'actualizar pacientes']);
        Permission::create(['name' => 'actualizar medicocentromedico']);
        Permission::create(['name' => 'actualizar centromedico']);
        Permission::create(['name' => 'actualizar enfermedades']);
        Permission::create(['name' => 'actualizar especialidad']);
        Permission::create(['name' => 'actualizar especialidadmedicos']);
        Permission::create(['name' => 'actualizar medicos']);
        Permission::create(['name' => 'actualizar enfermedades_pacientes']);
        Permission::create(['name' => 'actualizar recetas']);
        Permission::create(['name' => 'actualizar consultas']);
        Permission::create(['name' => 'actualizar contratomedico']);
        Permission::create(['name' => 'actualizar nomina']);
        Permission::create(['name' => 'actualizar detallenomina']);
        Permission::create(['name' => 'actualizar citas']); // NUEVO
        Permission::create(['name' => 'actualizar cai_correlativos']);
        Permission::create(['name' => 'actualizar cai_autorizaciones']);
        Permission::create(['name' => 'actualizar cuentas_por_cobrars']);
        Permission::create(['name' => 'actualizar servicio']);
        Permission::create(['name' => 'actualizar Impuesto']);
        Permission::create(['name' => 'actualizar factura']);
        Permission::create(['name' => 'actualizar descuento']);
        Permission::create(['name' => 'actualizar factura_detalles']);
        Permission::create(['name' => 'actualizar pagos_facturas']);


        // BORRAR
        Permission::create(['name' => 'borrar personas']);
        Permission::create(['name' => 'borrar nacionalidad']);
        Permission::create(['name' => 'borrar usuario']);
        Permission::create(['name' => 'borrar pacientes']);
        Permission::create(['name' => 'borrar centromedico']);
        Permission::create(['name' => 'borrar medicocentromedico']);
        Permission::create(['name' => 'borrar enfermedades']);
        Permission::create(['name' => 'borrar especialidad']);
        Permission::create(['name' => 'borrar especialidadmedicos']);
        Permission::create(['name' => 'borrar medicos']);
        Permission::create(['name' => 'borrar enfermedades_pacientes']);
        Permission::create(['name' => 'borrar recetas']);
        Permission::create(['name' => 'borrar consultas']);
        Permission::create(['name' => 'borrar contratomedico']);
        Permission::create(['name' => 'borrar nomina']);
        Permission::create(['name' => 'borrar detallenomina']);
        Permission::create(['name' => 'borrar citas']); // NUEVO
        Permission::create(['name' => 'borrar cai_correlativos']);
        Permission::create(['name' => 'borrar cai_autorizaciones']);
        Permission::create(['name' => 'borrar cuentas_por_cobrars']);
        Permission::create(['name' => 'borrar servicio']);
        Permission::create(['name' => 'borrar Impuesto']);
        Permission::create(['name' => 'borrar factura']);
        Permission::create(['name' => 'borrar descuento']);
        Permission::create(['name' => 'borrar factura_detalles']);
        Permission::create(['name' => 'borrar pagos_facturas']);

        // Crear roles y asignar permisos
        $roleAdmin = Role::create(['name' => 'root']);
        $roleAdmin->givePermissionTo([
            // VER
            'ver personas', 'ver nacionalidad', 'ver usuario', 'ver pacientes', 'ver medicocentromedico', 'ver enfermedades', 'ver centromedico', 'ver especialidad', 'ver especialidadmedicos', 'ver medicos', 'ver enfermedades_pacientes', 'ver recetas', 'ver consultas', 'ver contratomedico', 'ver nomina', 'ver detallenomina', 'ver cai_correlativos', 'ver cai_autorizaciones', 'ver cuentas_por_cobrars', 'ver servicio', 'ver Impuesto', 'ver factura', 'ver descuento', 'ver factura_detalles', 'ver pagos_facturas',
            // CREAR
            'crear personas', 'crear nacionalidad', 'crear usuario', 'crear pacientes', 'crear medicocentromedico', 'crear centromedico', 'crear enfermedades', 'crear especialidad', 'crear especialidadmedicos', 'crear medicos', 'crear enfermedades_pacientes', 'crear recetas', 'crear consultas', 'crear contratomedico', 'crear nomina', 'crear detallenomina', 'crear cai_correlativos', 'crear cai_autorizaciones', 'crear cuentas_por_cobrars', 'crear servicio', 'crear Impuesto', 'crear factura', 'crear descuento', 'crear factura_detalles', 'crear pagos_facturas',
            // ACTUALIZAR
            'actualizar personas', 'actualizar nacionalidad', 'actualizar usuario', 'actualizar pacientes', 'actualizar medicocentromedico', 'actualizar centromedico', 'actualizar enfermedades', 'actualizar especialidad', 'actualizar especialidadmedicos', 'actualizar medicos', 'actualizar enfermedades_pacientes', 'actualizar recetas', 'actualizar consultas', 'actualizar contratomedico', 'actualizar nomina', 'actualizar detallenomina', 'actualizar cai_correlativos', 'actualizar cai_autorizaciones', 'actualizar cuentas_por_cobrars', 'actualizar servicio', 'actualizar Impuesto', 'actualizar factura', 'actualizar descuento', 'actualizar factura_detalles', 'actualizar pagos_facturas',
            // BORRAR
            'borrar personas', 'borrar nacionalidad', 'borrar usuario', 'borrar pacientes', 'borrar centromedico', 'borrar medicocentromedico', 'borrar enfermedades', 'borrar especialidad', 'borrar especialidadmedicos', 'borrar medicos', 'borrar enfermedades_pacientes', 'borrar recetas', 'borrar consultas', 'borrar medicocentromedico', 'borrar centromedico', 'borrar contratomedico', 'borrar nomina', 'borrar detallenomina', 'borrar cai_correlativos', 'borrar cai_autorizaciones', 'borrar cuentas_por_cobrars', 'borrar servicio', 'borrar Impuesto', 'borrar factura', 'borrar descuento', 'borrar factura_detalles', 'borrar pagos_facturas'
        ]);

        $roleAdminCentro = Role::create(['name' => 'administrador']);
        $roleAdminCentro->givePermissionTo([
            // VER
            'ver medicos', 'ver pacientes', 'ver usuario', 'ver enfermedades', 'ver especialidad', 'ver recetas', 'ver consultas', 'ver citas', 'ver contratomedico', 'ver nomina', 'ver detallenomina',
            // CREAR
            'crear medicos', 'crear pacientes', 'crear usuario', 'crear recetas', 'crear consultas', 'crear citas', 'crear contratomedico', 'crear nomina', 'crear detallenomina',
            // ACTUALIZAR
            'actualizar medicos', 'actualizar pacientes', 'actualizar usuario', 'actualizar recetas', 'actualizar consultas', 'actualizar citas', 'actualizar contratomedico', 'actualizar nomina', 'actualizar detallenomina',
            // BORRAR
            'borrar medicos', 'borrar pacientes', 'borrar usuario', 'borrar recetas', 'borrar consultas', 'borrar citas', 'borrar contratomedico', 'borrar nomina', 'borrar detallenomina'
        ]);

       

        $roleAdminMedicos = Role::create(['name' => 'medico']);
        $roleAdminMedicos->givePermissionTo(['crear pacientes', 'ver pacientes', 'actualizar pacientes', 'borrar pacientes',
            'crear consultas', 'ver consultas', 'actualizar consultas', 'borrar consultas',
            'crear recetas', 'ver recetas', 'actualizar recetas', 'borrar recetas',
            'ver citas', // Solo pueden VER sus propias citas, NO crear/modificar/borrar
            'ver contratomedico', // Permiso para ver sus contratos
        ]);
    }
}
