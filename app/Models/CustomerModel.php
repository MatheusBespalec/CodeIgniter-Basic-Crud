<?php

namespace App\Models;

use App\Entities\Customer;
use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $returnType = Customer::class;
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'email', 'phone'];
}