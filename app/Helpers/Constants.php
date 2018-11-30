<?php

namespace App\Helpers;

/**
 * Class Constants
 * @package App\Helpers
 */
class Constants
{
    const LOGO = <<<EOF
   ____  __    ___    _____   ______  _______  __      ________  _______ 
  / __ \/ /   /   |  /  _/ | / / __ \/ ____/ |/ /     / ____/  |/  / __ \
 / / / / /   / /| |  / //  |/ / / / / __/  |   /_____/ /   / /|_/ / / / /
/ /_/ / /___/ ___ |_/ // /|  / /_/ / /___ /   /_____/ /___/ /  / / /_/ / 
\____/_____/_/  |_/___/_/ |_/_____/_____//_/|_|     \____/_/  /_/_____/                                                                     
EOF;

    const LATEST_VERSION = 'v1.0';
    const API_VERSION = 'v1.0';

    const DEFAULT_REDIRECT_URI = 'https://olaindex.ningkai.wang';

    const REST_ENDPOINT = 'https://graph.microsoft.com/';
    const AUTHORITY_URL = 'https://login.microsoftonline.com/common';
    const AUTHORIZE_ENDPOINT = '/oauth2/v2.0/authorize';
    const TOKEN_ENDPOINT = '/oauth2/v2.0/token';

    // support 21vianet
    const REST_ENDPOINT_21V = 'https://microsoftgraph.chinacloudapi.cn/';
    const AUTHORITY_URL_21V = 'https://login.partner.microsoftonline.cn/common';
    const AUTHORIZE_ENDPOINT_21V = '/oauth2/authorize';
    const TOKEN_ENDPOINT_21V = '/oauth2/token';

    const SCOPES = 'offline_access user.read files.readwrite.all';
}
