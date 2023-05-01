<?php

namespace App\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Fabricator;
use PHPUnit\Framework\Attributes\DataProvider;

final class CustomerModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $refresh = true;
    /*
     * insert
     * update
     * delete
     * find
     * paginate
     */

    public function validCustomerData(): array
    {
        return [
            [
                [
                    'name' => 'Jhon Doe',
                    'email' => 'jhondoe@gmail.com',
                    'phone' => '32929087824'
                ]
            ]
        ];
    }

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

    /**
     * @dataProvider validCustomerData
     */
    public function testInsertPersistCustomer(array $customerToBeSaved): void
    {
        $customerModel = new CustomerModel();

        $customerId = $customerModel->insert($customerToBeSaved);

        self::assertNotFalse($customerId);

        $customerSaved = $customerModel->find($customerId);
        self::assertNotNull($customerSaved, 'Cliente não foi encontrado na base de dados após operação de insert');
        foreach ($customerToBeSaved as $property => $persistedValue) {
            self::assertEquals(
                $persistedValue,
                $customerSaved->{$property},
                sprintf(
                    "Valor da propriedade %s do cliente salvo esta divergente. Valor Persistido: %s. Valor Encontrado: %s",
                    $property,
                    $persistedValue,
                    $customerSaved->{$property}
                )
            );
        }
    }

    /**
     * @dataProvider invalidCustomerData
     */
    public function testInsertThrowsDatabaseExceptionWhenCustomerDataIsInvalid(?string $name, ?string $email, ?string $phone): void
    {
        self::expectException(DatabaseException::class);
        $customerToBeSaved = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ];
        $customerModel = new CustomerModel();

        $inserted = $customerModel->insert($customerToBeSaved);

        self::assertFalse($inserted);
    }

    /**
     * @dataProvider validCustomerData
     */
    public function testFindRetursCustomerData(array $customerToBeSaved): void
    {
        $customerModel = new CustomerModel();
        $customerId = $customerModel->insert($customerToBeSaved);

        $customerSaved = $customerModel->find($customerId);

        self::assertNotNull($customerSaved);
        self::assertNotEmpty($customerSaved->id, 'Valor da propriedade "id" esta vazio');
        self::assertNotEmpty($customerSaved->name, 'Valor da propriedade "nam esta vazioe"');
        self::assertNotEmpty($customerSaved->email, 'Valor da propriedade "ema esta vazioil"');
        self::assertNotEmpty($customerSaved->phone, 'Valor da propriedade "pho esta vazione"');
        self::assertNotEmpty($customerSaved->created_at, 'Valor da propriedade "cre esta vazioated_at"');
        self::assertNotEmpty($customerSaved->updated_at, 'Valor da propriedade "upd esta vazioated_at"');
        self::assertNull($customerSaved->deleted_at, 'Valor da propriedade "del esta vazioeted_at"');
    }

    public function testFindReturnFalseWhenCustomerNotExists(): void
    {
        $customerModel = new CustomerModel();

        $customerSaved = $customerModel->find(rand());

        self::assertNull($customerSaved);
    }

    /**
     * @dataProvider validCustomerData
     */
    public function testDeleteRemovesPersistedCustomer(array $customerToBeSaved): void
    {
        $customerModel = new CustomerModel();
        $customerId = $customerModel->insert($customerToBeSaved);

        $customerModel->delete($customerId);

        $customerSaved = $customerModel->find($customerId);
        self::assertNull($customerSaved);
    }

    /**
     * @dataProvider validCustomerData
     */
    public function testUpdateReplaceCustomerData(array $customerToBeSaved): void
    {

        $customerModel = new CustomerModel();
        $customerId = $customerModel->insert($customerToBeSaved);

        $customerToBeSavedUpdated = [
            'name' => 'Test',
            'email' => 'test@gmail.com',
            'phone' => '119542356795'
        ];

        $customerModel->update($customerId, $customerToBeSavedUpdated);

        $customerPersisted = $customerModel->find($customerId);

        self::assertNotNull($customerPersisted);

        foreach ($customerToBeSavedUpdated as $property => $value) {
            self::assertEquals($value, $customerPersisted->{$property});
        }
    }
}