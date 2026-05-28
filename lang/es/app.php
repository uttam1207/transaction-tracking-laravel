<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Language Lines — Spanish (Español)
    |--------------------------------------------------------------------------
    */

    'app_name'  => 'Monitor de Transacciones',
    'dashboard' => 'Panel de Control',
    'welcome'   => 'Bienvenido, :name',

    'nav' => [
        'transactions'  => 'Transacciones',
        'fraud_alerts'  => 'Alertas de Fraude',
        'users'         => 'Usuarios',
        'employees'     => 'Empleados',
        'attendance'    => 'Asistencia',
        'tasks'         => 'Tareas',
        'departments'   => 'Departamentos',
        'holidays'      => 'Festivos',
        'projects'      => 'Proyectos',
        'reports'       => 'Informes',
        'settings'      => 'Configuración',
        'logout'        => 'Cerrar Sesión',
        'profile'       => 'Perfil',
    ],

    'transactions' => [
        'title'          => 'Transacciones',
        'create'         => 'Nueva Transacción',
        'edit'           => 'Editar Transacción',
        'id'             => 'ID de Transacción',
        'type'           => 'Tipo',
        'amount'         => 'Monto',
        'status'         => 'Estado',
        'sender'         => 'Remitente',
        'receiver'       => 'Destinatario',
        'flagged'        => 'Marcada',
        'risk_score'     => 'Puntuación de Riesgo',
        'created_at'     => 'Fecha',
        'no_records'     => 'No se encontraron transacciones.',
    ],

    'fraud' => [
        'title'          => 'Alertas de Fraude',
        'open'           => 'Abierta',
        'investigating'  => 'En Investigación',
        'resolved'       => 'Resuelta',
        'dismissed'      => 'Descartada',
        'severity'       => 'Gravedad',
        'critical'       => 'Crítica',
        'high'           => 'Alta',
        'medium'         => 'Media',
        'low'            => 'Baja',
        'assign'         => 'Asignar',
        'resolve'        => 'Resolver',
    ],

    'attendance' => [
        'title'       => 'Asistencia',
        'check_in'    => 'Entrada',
        'check_out'   => 'Salida',
        'present'     => 'Presente',
        'absent'      => 'Ausente',
        'on_leave'    => 'Con Permiso',
        'late'        => 'Tarde',
        'hours'       => 'Horas Trabajadas',
    ],

    'common' => [
        'save'          => 'Guardar',
        'cancel'        => 'Cancelar',
        'delete'        => 'Eliminar',
        'edit'          => 'Editar',
        'view'          => 'Ver',
        'search'        => 'Buscar',
        'filter'        => 'Filtrar',
        'export'        => 'Exportar',
        'actions'       => 'Acciones',
        'status'        => 'Estado',
        'active'        => 'Activo',
        'inactive'      => 'Inactivo',
        'yes'           => 'Sí',
        'no'            => 'No',
        'confirm_delete'=> '¿Está seguro de que desea eliminar esto?',
        'success'       => 'Operación completada correctamente.',
        'error'         => 'Ocurrió un error. Por favor intente de nuevo.',
        'no_data'       => 'No se encontraron registros.',
        'loading'       => 'Cargando...',
        'of'            => 'de',
        'page'          => 'Página',
        'per_page'      => 'Por página',
    ],

    'auth' => [
        'login'              => 'Iniciar Sesión',
        'logout'             => 'Cerrar Sesión',
        'email'              => 'Correo Electrónico',
        'password'           => 'Contraseña',
        'remember'           => 'Recuérdame',
        'forgot_password'    => '¿Olvidó su contraseña?',
        'failed'             => 'Estas credenciales no coinciden con nuestros registros.',
    ],
];
