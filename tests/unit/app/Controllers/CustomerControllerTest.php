<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\Fabricator;
use Config\Services;

/**
 * @internal
 */
final class CustomerControllerTest extends \CodeIgniter\Test\CIUnitTestCase
{
    use ControllerTestTrait;

    public function testListReturnsListOfCustomers()
    {
        $fabricator = new Fabricator(CustomerModel::class, null, 'pt_BR');

        $customerModelMock = $this->createMock(CustomerModel::class);
        $customerModelMock->method('paginate')
            ->willReturn($fabricator->create(5, true));
        $customerModelMock->pager = Services::pager();

        Factories::injectMock('models', CustomerModel::class, $customerModelMock);

        $results = $this->controller(CustomerController::class)
            ->execute('list');

        self::assertTrue($results->isOK(), 'Status de retorno diferente de "200 OK"');
        $responseBody = json_decode($results->response()->getJSON());

        self::assertTrue(
            property_exists($responseBody, 'customers'),
            'Propriedade "customers" não esta sendo informada no restorno de sucesso do endpoint'
        );
        self::assertIsArray(
            $responseBody->customers,
            '"customers" não esta retornando um array com os dados do cliente'
        );
        self::assertNotEmpty($responseBody->customers, 'Para este teste a lista de clientes não deve estar vazia');

        foreach ($responseBody->customers as $customer) {
            self::assetValidCustomerResource($customer);
        }
    }

    public function testFindByIdResturnsCustormerWhenSuccessful()
    {
        $fabricator = new Fabricator(CustomerModel::class, null, 'pt_BR');

        $customerModelMock = $this->createMock(CustomerModel::class);
        $customerModelMock->method('find')
            ->willReturn($fabricator->create(mock: true));

        Factories::injectMock('models', CustomerModel::class, $customerModelMock);

        $results = $this->controller(CustomerController::class)
            ->execute('findById', rand());

        self::assertTrue($results->isOK(), 'Status de retorno diferente de "200 OK"');

        $responseBody = json_decode($results->response()->getJSON());

        self::assertTrue(
            property_exists($responseBody, 'customer'),
            'Propriedade "customer" não esta sendo informada no retorno'
        );
        self::assetValidCustomerResource($responseBody->customer);
    }

    public function testFindByIdResturnsFailNotFoundWhenCustomerNotExists()
    {
        $customerModelMock = $this->createMock(CustomerModel::class);

        Factories::injectMock('models', CustomerModel::class, $customerModelMock);

        $results = $this->controller(CustomerController::class)
            ->execute('findById', rand());

        self::assertFalse($results->isOK(), 'Status de retorno diferente de "200 OK"');

        $response = $results->response();
        self::assertEquals(404, $response->getStatusCode());

        $responseBody = json_decode($response->getJSON());

        self::assertTrue(
            property_exists($responseBody, 'messages'),
            'As mensagens de erro não estão sendo enviadas'
        );
        self::assertContains(
            'Customer not found',
            (array) $responseBody->messages,
            'A mensagem "Customer not found" não esta sendo exibida'
        );
    }

    public static function assetValidCustomerResource($customer)
    {
        self::assertTrue(
            property_exists($customer, 'id'),
            'Propriedade "id" do cliente não esta sendo enviada'
        );
        self::assertIsInt($customer->id, 'Propriedade "id" está vindo com valor inválido');

        self::assertTrue(
            property_exists($customer, 'name'),
            'Propriedade "name" do cliente não esta sendo enviada'
        );
        self::assertNotEmpty($customer->name, 'Propriedade "name" está vindo vazia');

        self::assertTrue(
            property_exists($customer, 'email'),
            'Propriedade "email" do cliente não esta sendo enviada'
        );
        self::assertNotEmpty($customer->email, 'Propriedade "email" está vindo vazia');

        self::assertTrue(
            property_exists($customer, 'phone'),
            'Propriedade "phone" do cliente não esta sendo enviada'
        );
        self::assertNotEmpty($customer->phone, 'Propriedade "phone" está vindo vazia');

        self::assertTrue(
            property_exists($customer, 'created_at'),
            'Propriedade "created_at" do cliente não esta sendo enviada'
        );
        self::assertNotEmpty($customer->created_at, 'Propriedade "created_at" está vindo vazia');

        self::assertTrue(
            property_exists($customer, 'updated_at'),
            'Propriedade "updated_at" do cliente não esta sendo enviada'
        );
        self::assertNotEmpty($customer->updated_at, 'Propriedade "updated_at" está vindo vazia');
    }
}