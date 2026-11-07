<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Error de regla de negocio de citas (horario ocupado, transición inválida, etc.).
 * El mensaje es apto para mostrarse al usuario.
 */
class CitaException extends RuntimeException
{
}