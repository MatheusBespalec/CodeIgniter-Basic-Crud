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

    public function invalidCustomerData(): array
    {
        return [
            'name_is_null' => [
                'name' => null,
                'email' => 'jhondoe@gmail.com',
                'phone' => '32929087824'
            ],
            'phone_is_null' => [
                'name' => 'Jhon Doe',
                'email' => 'jhondoe@gmail.com',
                'phone' => null,
            ],
            'email_is_null' => [
                'name' => 'Jhon Doe',
                'emial' => null,
                'phone' => '32929087824'
            ],
        ];
    }

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
            self::assertValidCustomerResource($customer);
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
        self::assertValidCustomerResource($responseBody->customer);
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

    public static function assertValidCustomerResource($customer): void
    {
        self::assertIsInt($customer->id, 'Propriedade "id" possui um valor inválido');
        self::assertNotEmpty($customer->name, 'Propriedade "name" retornou com valor vazia');
        self::assertNotEmpty($customer->email, 'Propriedade "email" retornou com valor vazia');
        self::assertNotEmpty($customer->phone, 'Propriedade "phone" retornou com valor vazia');
        self::assertNotEmpty($customer->created_at, 'Propriedade "created_at" retornou com valor vazia');
        self::assertNotEmpty($customer->updated_at, 'Propriedade "updated_at" retornou com valor vazia');
    }

    public function testCreateReturnsCustomerResource(): void
    {
        $fabricator = new Fabricator(CustomerModel::class, null, 'pt_BR');
        $customerTobeSavedData = $fabricator->makeArray();
        $customerModelMock = $this->createMock(CustomerModel::class);
        $customerModelMock->method('insert')
            ->withAnyParameters()
            ->willReturn(rand());
        $customerModelMock->method('find')
            ->withAnyParameters()
            ->willReturn($fabricator->create());

        Factories::injectMock('models', CustomerModel::class, $customerModelMock);

        $response = $this->withBody(json_encode($customerTobeSavedData))
            ->controller(CustomerController::class)
            ->execute('create')
            ->response();

        self::assertEquals(201, $response->getStatusCode(), 'Status HTTP diferente de "201 Created"');

        $responseBody = json_decode($response->getJSON());

        self::assertIsObject(
            $responseBody->customer,
            'Propriedade "customer" não está vindo como um objeto contendo os dados do cliente'
        );
        self::assertValidCustomerResource($responseBody->customer);
    }

    /**
     * @dataProvider invalidCustomerData
     */
    public function testCreateReturnsBadRequestWhenCustomerDataIsInvalid(?string $name, ?string $email, ?string $phone): void
    {
        $response = $this->controller(CustomerController::class)
            ->withBody(json_encode([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
            ]))
            ->execute('create')
            ->response();

        self::assertEquals(400, $response->getStatusCode(), 'Status HTTP diferente de "201 Created"');
    }
}