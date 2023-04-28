<?php

namespace App\Controllers;

use App\Adapters\PagerAdapter;
use App\Models\CustomerModel;
use CodeIgniter\API\ResponseTrait;
use Config\Services;

class CustomerController extends BaseController
{
    use ResponseTrait;

    public function list()
    {
        $model = model(CustomerModel::class);
        return $this->respond([
            'customers' => $model->paginate(3),
            'pager' => new PagerAdapter($model->pager)
        ]);
    }

    public function findById(int $customerId)
    {
        $model = model(CustomerModel::class);
        $customer = $model->find($customerId);

        if (!$customer) {
            return $this->failNotFound("Customer not found");
        }

        return $this->respond([
            'customer' => $customer
        ]);
    }

    public function create()
    {
        if (!$this->validate('customer')) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $model = model(CustomerModel::class);
        $customerId = $model->insert($this->request->getJSON());

        if (!$customerId) {
            return $this->failServerError("Customer creation failed");
        }

        return $this->respondCreated([
            'customer' => $model->find($customerId)
        ]);
    }

    public function update(int $customerId)
    {
        $rules = Services::validation()->getRuleGroup('createCustomer');
        $rules['email'] = ['required', 'valid_email', "is_unique[customers.email,id,{$customerId}]"];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $model = model(CustomerModel::class);

        if (!$model->update($customerId, $this->request->getJSON())) {
            return $this->failServerError("Customer update failed");
        }

        return $this->respondCreated([
            'customer' => $model->find($customerId)
        ]);
    }

    public function delete(int $customerId)
    {
        $model = model(CustomerModel::class);

        if (!$model->find($customerId)) {
            return $this->failNotFound("Customer not found");
        }

        $model->delete($customerId);

        if (!$model->find($customerId)) {
            return $this->failServerError("Customer deletion failed");
        }

        return $this->respondNoContent();
    }
}