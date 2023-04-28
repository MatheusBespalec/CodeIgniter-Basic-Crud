<?php

namespace App\Controllers;

use App\Adapters\PagerAdapter;
use App\Models\CustomerModel;
use CodeIgniter\API\ResponseTrait;
use Config\Services;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="Customer Operations", version="1.0.0")
 */
class CustomerController extends BaseController
{
    use ResponseTrait;

    /**
     * @OA\Get(
     *     path="/customers",
     *     summary="Get all customers",
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     )
     * )
     */
    public function list()
    {
        $model = model(CustomerModel::class);
        return $this->respond([
            'customers' => $model->paginate(10),
            'pager' => new PagerAdapter($model->pager)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/customers/{customerId}",
     *     summary="Get a customer by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer to get",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Customer not found"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/customers",
     *      summary="Create new customer",
     *      @OA\Response(
     *          response="201",
     *          description="Customer Created"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Check the sended data"
     *      ),
     *      @OA\Response(
     *          response="500",
     *          description="Customer creation failed"
     *      )
     * )
     */
    public function create()
    {
        if (!$this->validate('createCustomer')) {
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

    /**
     * @OA\Put(
     *      path="/customers/{customerId}",
     *      summary="Update existing customer",
     *      @OA\Parameter(
     *         name="customerId",
     *         in="path",
     *         required=true,
     *         description="ID of the customer to update",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Check the sended data"
     *      ),
     *      @OA\Response(
     *          response="500",
     *          description="Customer update failed"
     *      )
     * )
     */
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

    /**
     * @OA\Delete(
     *      path="/customers/{customerId}",
     *      summary="Delete customer",
     *     @OA\Parameter(
     *         name="customerId",
     *         in="path",
     *         required=true,
     *         description="ID of the customer to delete",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *      @OA\Response(
     *          response="204",
     *          description="Success"
     *     ),
     *      @OA\Response(
     *          response="404",
     *          description="Customer not found"
     *      ),
     *      @OA\Response(
     *          response="500",
     *          description="Customer deletion failed"
     *      )
     * )
     */
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