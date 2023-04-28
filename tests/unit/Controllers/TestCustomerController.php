<?php

use App\Controllers\CustomerController;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class TestCustomerController extends \CodeIgniter\Test\CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;

    public function testIfListReturnsListOfCustomers()
    {
        $results = $this->controller(CustomerController::class)
            ->execute('list');

        $this->assertTrue($results->isOK());


        $responseBody = json_decode($results->response()->getJSON());
        $this->assertIsArray($responseBody->customers);

        foreach ($responseBody->customers as $customer) {
            $this->assertTrue(property_exists($customer, 'id'), 'Propriedade "id" do cliente não esta sendo enviada');
            $this->assertEmpty($customer->id, 'Propriedade "id" está vindo vazia');

            $this->assertTrue(property_exists($customer, 'name'), 'Propriedade "name" do cliente não esta sendo enviada');
            $this->assertEmpty($customer->name, 'Propriedade "name" está vindo vazia');

            $this->assertTrue(property_exists($customer, 'email'), 'Propriedade "email" do cliente não esta sendo enviada');
            $this->assertEmpty($customer->email, 'Propriedade "email" está vindo vazia');

            $this->assertTrue(property_exists($customer, 'phone'), 'Propriedade "phone" do cliente não esta sendo enviada');
            $this->assertEmpty($customer->phone, 'Propriedade "phone" está vindo vazia');

            $this->assertTrue(property_exists($customer, 'created_at'), 'Propriedade "created_at" do cliente não esta sendo enviada');
            $this->assertEmpty($customer->created_at, 'Propriedade "created_at" está vindo vazia');

            $this->assertTrue(property_exists($customer, 'updated_at'), 'Propriedade "updated_at" do cliente não esta sendo enviada');
            $this->assertEmpty($customer->updated_at, 'Propriedade "updated_at" está vindo vazia');

            $this->assertTrue(property_exists($customer, 'deleted_at'), 'Propriedade "deleted_at" do cliente não esta sendo enviada');
            $this->assertNotNull($customer->deleted_at, 'Estão sendo enviados clietes que já foram excluídos');
        }
    }
}