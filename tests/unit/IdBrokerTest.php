<?php
require_once(__DIR__ . '/../../vendor/autoload.php');

use PHPUnit\Framework\TestCase;

use Sil\IdpPw\Common\Personnel\NotFoundException;
use Sil\IdpPw\Common\Personnel\IdBroker\IdBroker;
use Sil\Idp\IdBroker\Client\IdBrokerClient;

use yii\base\NotSupportedException;
use GuzzleHttp\Command\Exception\CommandException;

class IdBrokerTest extends TestCase
{

    public $baseUrl = 'http://broker';
    public $accessToken = 'abc123';

    public function getConfig() {
        return [
            'baseUrl' => $this->baseUrl,
            'accessToken' => $this->accessToken,
        ];
    }
    
    protected function ensureUserExists($userInfo)
    {
        $idBrokerClient = new IdBrokerClient($this->baseUrl, $this->accessToken, [
            IdBrokerClient::ASSERT_VALID_BROKER_IP_CONFIG => false,
        ]);
        
        $i = 0;
        $e = null;
        
        $userExistsCode = 1490802526;
        
        // Make sure broker container is available to deal with requests
        while ($i < 60) {
            $i++;
        
            try {
                $idBrokerClient->createUser($userInfo);
                $e = null;
                break;
            } catch (Exception $e) {
                // If broker not available, wait longer
                if ($e instanceof GuzzleHttp\Command\Exception\CommandException) {
                    sleep(1);
                
                    // if user already created, just continue
                } else if ($e->getCode() == $userExistsCode) {
                    $e = null;
                    break;
                } else {
                    throw $e;
                }
            }
        }
        
        if ($e !== null) {
            throw $e;
        }
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

    public function testReturnPersonnelUserFromResponse_Mocked() {
        $mockReturnValue = $this->getMockReturnValue();
        unset($mockReturnValue['email']);
        $brokerMock = $this->getMockBuilder('\Sil\IdpPw\Common\Personnel\IdBroker\IdBroker')
            ->setMethods(['callIdBrokerGetUser'])
            ->getMock();
        $brokerMock->expects($this->any())
            ->method('callIdBrokerGetUser')
            ->willReturn($mockReturnValue);

        $employeeId = '123456';
        $this->expectExceptionCode(1496260921);
        $this->expectExceptionMessage(
            'Personnel attributes missing attribute: email for employeeId=' .
            $employeeId);
        $brokerMock->findByEmployeeId($employeeId);
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

    public function testFindByUsername()
    {
        $employeeId = '12333';
        $firstName = 'Tommy';
        $lastName = 'Tester';
        $userName = 'tommy_tester';
        $email = $userName . '@any.org';

        // Setup
        $this->ensureUserExists([
            'employee_id' => $employeeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $userName,
            'email' => $email,
        ]);

        $idBroker = new IdBroker([
            'baseUrl' => $this->baseUrl,
            'accessToken' => $this->accessToken,
            'assertValidBrokerIp' => false,
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

        $results = get_object_vars($idBroker->findByUsername($userName));
        $this->assertEquals($expected, $results);
    }

    public function testFindByEmail()
    {
        $employeeId = '12333';
        $firstName = 'Tommy';
        $lastName = 'Tester';
        $userName = 'tommy_tester';
        $email = $userName . '@any.org';

        // Setup
        $this->ensureUserExists([
            'employee_id' => $employeeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $userName,
            'email' => $email,
        ]);

        $idBroker = new IdBroker([
            'baseUrl' => $this->baseUrl,
            'accessToken' => $this->accessToken,
            'assertValidBrokerIp' => false,
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

        $results = get_object_vars($idBroker->findByEmail($email));
        $this->assertEquals($expected, $results);
    }

    public function testFindByEmployeeId()
    {
        $employeeId = '12333';
        $firstName = 'Tommy';
        $lastName = 'Tester';
        $userName = 'tommy_tester';
        $email = $userName . '@any.org';

        // Setup
        $this->ensureUserExists([
            'employee_id' => $employeeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $userName,
            'email' => $email,
        ]);

        $idBroker = new IdBroker([
            'baseUrl' => $this->baseUrl,
            'accessToken' => $this->accessToken,
            'assertValidBrokerIp' => false,
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


    public function testFindByEmployeeId_MissingUser()
    {
        // Setup
        $employeeId = time();
        $idBroker = new IdBroker([
            'baseUrl' => $this->baseUrl,
            'accessToken' => $this->accessToken,
            'assertValidBrokerIp' => false,
        ]);

        $this->expectException(NotFoundException::class);
        $idBroker->findByEmployeeId($employeeId);
    }

}
