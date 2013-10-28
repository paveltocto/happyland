<?php

class Status {

    const STATUS_ACTIVO = 'act';
    const STATUS_APROBADO = 'apr';
    const STATUS_CONFIRMADO = 'con';
    const STATUS_ELIMINADO = 'eli';
    const STATUS_VIGENTE = 'vig';
    const STATUS_EXPIRADO = 'exp';
    const STATUS_FINALIZADO = 'fin';
    const STATUS_INACTIVO = 'ina';
    const STATUS_INCOMPLETO = 'inc';
    const STATUS_PENDIENTE = 'pen';
    const STATUS_ATENDIDO = 'ate';
    const STATUS_RECHAZADO = 'rec';

    public static $statuses = array(
        self::STATUS_ACTIVO => 'Activo',
        self::STATUS_APROBADO => 'Aprobado',
        self::STATUS_CONFIRMADO => 'Confirmado',
        self::STATUS_ELIMINADO => 'Eliminado',
        self::STATUS_VIGENTE => 'Vigente',
        self::STATUS_EXPIRADO => 'Expirado',
        self::STATUS_FINALIZADO => 'Finalizado',
        self::STATUS_INACTIVO => 'Inactivo',
        self::STATUS_INCOMPLETO => 'Incompleto',
        self::STATUS_PENDIENTE => 'Pendiente',
        self::STATUS_ATENDIDO => 'Atendido',
        self::STATUS_RECHAZADO => 'Rechazado',
    );

    public static function getStatusLabel($key) {
        if (!array_key_exists($key, self::$statuses)) {
            return '';
        }
        return self::$statuses[$key];
    }

}

?>
