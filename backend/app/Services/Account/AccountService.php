<?php

namespace App\Services\Account;

use App\Services\Generator\AccountNumberGeneratorService;
use App\Services\Service;

class AccountService extends Service
{
    use AccountNumberGeneratorService;
}
