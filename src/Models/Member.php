<?php
declare(strict_types=1);

namespace App\Models;

class Member extends User
{
    public function getRole(): string { return 'member'; }
}
