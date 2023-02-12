<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

enum DeviceType: int
{
    case Windows = 1;
    case Android = 2;
}
