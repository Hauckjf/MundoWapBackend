<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AddressesController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\AddressesController Test Case
 *
 * @uses \App\Controller\AddressesController
 */
class AddressesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Addresses',
    ];

    public function testEditVisitAndCreateNewAddress(): void
    {
        $data1 = [
            'visits' => [
                'date' => '2025-03-21',
                'forms' => 1,
                'products' => 1
            ],
            'address' => [
                'postal_code' => '01311300',
                'sublocality' => 'Bela Vista',
                'street' => 'Avenida Paulista',
                'street_number' => '1500'
            ]
        ];


        $this->post('/api/visits', $data1);
        $this->assertResponseCode(201);

        $responsePost = json_decode((string)$this->_response->getBody(), true)['data'];
        
        $this->assertEquals(20, $responsePost['visits']['duration']);
        $visitId = $responsePost['visits']['id'];
        $editData = [
            'visits' => [
                'date' => '2025-03-22',
                'completed' => 1,
                'forms' => 1,
                'products' => 1
            ],
            'address' => [
                'postal_code' => '01311300',
                'street_number' => '1501'
            ]
        ];

        $this->put("/api/visits/{$visitId}", $editData);
        $this->assertResponseCode(201);
        $responseEdit = json_decode((string)$this->_response->getBody(), true);
        
        $this->assertNotEquals($responsePost['address']['id'], $responseEdit['data']['address']['id']);
        $this->assertEquals(20, $responseEdit['data']['workdays']['duration']);
    }


}
