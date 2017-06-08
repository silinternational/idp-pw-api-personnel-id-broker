<?php
require_once(__DIR__ . '/../../vendor/autoload.php');

use PHPUnit\Framework\TestCase;

use Sil\IdpPw\Common\Personnel\IdBroker\IdBroker;
use Sil\Idp\IdBroker\Client\IdBrokerClient;

use yii\base\NotSupportedException;

class IdBrokerTest extends TestCase
{

    public $baseUrl = 'http://broker';
    public $accessToken = 'abc123';

    public $userData1 = [
        "uuid" => "abc123abc123",
        "first_name" => "Test",
        "last_name" => "User",
        "display_name" => "Test User",
        "email" => "test_user@domain.org",
        "employee_id" => 123,
        "username" => "TEST_USER",
        "active" => "yes",
        "locked" => "no",
        "password" => [
            "created_utc" => "2017-06-01T20:24:40+00:00",
            "expiration_utc" => "2018-06-01T20:24:40+00:00",
            "grace_period_ends_utc" => "2018-06-30T20:24:40+00:00",
        ],
    ];

    public $userData2 = [
        "uuid" => "adsfasdf-adsf-adf",
        "first_name" => "Test",
        "last_name" => "User2",
        "display_name" => "Test User2",
        "email" => "test_user2@domain.org",
        "employee_id" => 124,
        "username" => "TEST_USER2",
        "active" => "yes",
        "locked" => "no",
        "password" => [
            "created_utc" => "2017-06-01T20:24:40+00:00",
            "expiration_utc" => "2018-06-01T20:24:40+00:00",
            "grace_period_ends_utc" => "2018-06-30T20:24:40+00:00",
        ],
    ];

    public function getConfig() {
        return [
            'baseUrl' => $this->baseUrl,
            'accessToken' => $this->accessToken,
        ];
    }

    private function getMockReturnValue()
    {
       return [
           "uuid" => "11111111-aaaa-1111-aaaa-111111111111",
           "employee_id" => "12345",
           "first_name" => "John",
           "last_name" => "Smith",
           "display_name" => "John Smith",
           "username" => "john_smith",
           "email" => "john_smith@example.com",
           "active" => "yes",
           "locked" => "no",
           "password" => [
               "created_utc" => "2017-05-24 14:04:51",
               "expiration_utc" => "2018-05-24 14:04:51",
               "grace_period_ends_utc" => "2018-06-23 14:04:51"
           ]
       ];
    }

    public function testFindByEmployeeId_Mocked()
    {
        $mockReturnValue = $this->getMockReturnValue();
        $brokerMock = $this->getMockBuilder('\Sil\IdpPw\Common\Personnel\IdBroker\IdBroker')
                           ->setMethods(['callIdBrokerGetUser'])
                           ->getMock();
        $brokerMock->expects($this->any())
                   ->method('callIdBrokerGetUser')
                   ->willReturn($mockReturnValue);

        $brokerMock->baseUrl = "some.site.org";
        $brokerMock->accessToken = "abc123";

        $employeeId = '123456';
        $results = $brokerMock->findByEmployeeId($employeeId);

        $expected = $mockReturnValue['username'];
        $msg = " *** Bad results for username";
        $this->assertEquals($expected, $results->username, $msg);
    }

    public function testFindByUsername_Exception()
    {
        $this->expectException('\yii\base\NotSupportedException');
        $this->expectExceptionCode(1496260356);

        $broker = new IdBroker();
        $broker->findByUsername('should-error');
    }

    public function testFindByEmail_Exception()
    {
        $this->expectException('\yii\base\NotSupportedException');
        $this->expectExceptionCode(1496260354);

        $broker = new IdBroker();
        $broker->findByEmail('should-error');
    }
    
    public function testFindByEmployeeId()
    {
        $employeeId = '12333';
        $firstName = 'Tommy';
        $lastName = 'Tester';
        $userName = 'tommy_tester';
        $email = $userName . '@any.org';

        // Setup
        $idBrokerClient = new IdBrokerClient($this->baseUrl, $this->accessToken);

        $newUserData = [
            'employee_id' => $employeeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $userName,
            'email' => $email,
        ];

        // In case the test is re-run and the database has not been refreshed
        try {
            $idBrokerClient->createUser($newUserData);
        } catch (Exception $e) {
            if ($e->getCode() != 1490802526) { // User already exists exception
                throw $e;
            }

        }

        $idBroker = new IdBroker([
            'baseUrl' => $this->baseUrl,
            'accessToken' => $this->accessToken
        ]);

        $expected = [
            'employeeId' => $employeeId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'username' => $userName,
            'email' => $email,
            'supervisorEmail' => null,
            'spouseEmail' => null,
        ];

        $results = get_object_vars($idBroker->findByEmployeeId($employeeId));
        $this->assertEquals($expected, $results);
    }

}
