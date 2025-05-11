<?php

namespace App\Enums\Acl;

use App\Traits\Helpers\EnumHelper;

enum PermissionType: string
{
    use EnumHelper;

    case MANAGE_LISTINGS = 'manage-listings';
    case MANAGE_USERS = 'manage-users';
    case ACCESS_SUPPORT_TAB = 'access-support-tab';
    case ACCESS_MARKETING_TOOLS = 'access-marketing-tools';
    case ACCESS_USER_CHAT_BUTTON = 'access-user-chat-button';
    case BAN_USERS = 'ban-users';
}
