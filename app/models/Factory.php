<?php

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use Exception;
use Roles;

class Factory extends Model
{
    protected string $table = "etablissements";
    public string $id = 'code_etablissement';

}
