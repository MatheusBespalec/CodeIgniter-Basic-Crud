<?php

namespace App\Controllers;

use App\Adapters\PagerAdapter;
use App\Models\CustomerModel;
use CodeIgniter\API\ResponseTrait;
use Config\Services;

/**
 * @OA\Info(title="Customer Operations", version="1.0.0")
 * @OA\Schema(
 *     schema="Customer",
 *     type="object",
 *     required={"name", "email", "phone"},
 *     @OA\Property(
 *          property="name",
 *          type="string",
 *          example="John Doe",
 *          description="Customer name",
 *     ),
 *     @OA\Property(
 *          property="email",
 *          type="string",
 *          uniqueItems=true,
 *          example="johndoe@example.com",
 *          description="Customer email address",
 *     ),
 *     @OA\Property(
 *          property="phone",
 *          type="string",
 *          uniqueItems=true,
 *          example="5511954136548",
 *          description="Customer phone number",
 *     ),
 * )
 */
class CustomerController extends BaseController
{
    use ResponseTrait;

    /**
     * @OA\Get(
     *     path="/customers",
     *     summary="Get all customers",
     *     tags={"Customers"},
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
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="customerId",
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
     *      tags={"Customers"},
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/Customer")
     *      ),
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
     *      tags={"Customers"},
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
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/Customer")
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
     *      tags={"Customers"},
     *      @OA\Parameter(
     *         name="customerId",
     *         in="path",
     *         required=true,
     *         description="ID of the customer to delete",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *      ),
     *      @OA\Response(
     *          response="204",
     *          description="Success"
     *      ),
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