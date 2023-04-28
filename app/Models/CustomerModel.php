<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $useTimestamps = false;
    protected $allowedFields = ['name', 'email', 'phone'];
}