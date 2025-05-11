<?php

namespace App\Enums\Acl;

use App\Traits\Helpers\EnumHelper;

enum RoleType: string
{
    use EnumHelper;

    case NORMAL = 'normal';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super-admin';
}
