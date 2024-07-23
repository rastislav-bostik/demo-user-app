<?php

namespace App\Entity;

/**
 * Role enumeration
 */
enum Role : string {
    case ADMIN = 'ADMIN';
    case USER = 'USER';
    case WORKER = 'WORKER';
}