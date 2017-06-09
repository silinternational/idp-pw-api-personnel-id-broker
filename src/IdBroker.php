<?php
namespace Sil\IdpPw\Common\Personnel\IdBroker;

use Sil\Idp\IdBroker\Client\IdBrokerClient;
use Sil\IdpPw\Common\Personnel\NotFoundException;
use Sil\IdpPw\Common\Personnel\PersonnelInterface;
use Sil\IdpPw\Common\Personnel\PersonnelUser;
use yii\base\Component;
use yii\base\NotSupportedException;

class IdBroker extends Component implements PersonnelInterface
{

    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var string
     */
    public $accessToken;

    /**
     * @param $userData
     * @throws \Exception
     */
    private function assertRequiredAttributesPresent($userData)
    {
        $required = ['first_name', 'last_name', 'email', 'employee_id', 'username'];

        foreach ($required as $requiredAttr) {
            if ( ! array_key_exists($requiredAttr, $userData)) {
                throw new \Exception(
                    'Personnel attributes missing attribute: ' . $requiredAttr,
                    1496328234
                );
            }
        }
    }

    /**
     * @param string $employeeId
     * @return PersonnelUser
     * @throws NotFoundException
     */
    public function findByEmployeeId($employeeId): PersonnelUser
    {
        $results = $this->callIdBrokerGetUser($employeeId);
        return $this->returnPersonnelUserFromResponse('employeeId', $employeeId, $results);
    }

    public function callIdBrokerGetUser($employeeId)
    {

        $idBrokerClient = new IdBrokerClient(
            $this->baseUrl, // The base URI for the API.
            $this->accessToken, // Your HTTP header authorization bearer token.
            [
                'http_client_options' => [
                    'timeout' => 10, // An (optional) custom HTTP timeout, in seconds.
                ],
            ]
        );

        $results = $idBrokerClient->getUser($employeeId);
        if ($results === null) {
            throw new NotFoundException();
        }

        return $results;
    }

    public function returnPersonnelUserFromResponse($field, $value, $response): PersonnelUser
    {
        try {
            $this->assertRequiredAttributesPresent($response);
            $pUser = new PersonnelUser();
            $pUser->firstName = $response['first_name'];
            $pUser->lastName = $response['last_name'];
            $pUser->email = $response['email'];
            $pUser->employeeId = $response['employee_id'];
            $pUser->username = $response['username'];
            $pUser->supervisorEmail = null;
            $pUser->spouseEmail = null;

            return $pUser;
        } catch (\Exception $e) {
            throw new \Exception(
                sprintf('%s for %s=%s', $e->getMessage(), $field, $value),
                1496260921
            );
        }
    }

    /**
     * @param string $username
     * @return PersonnelUser
     * @throws NotSupportedException
     */
    public function findByUsername($username): PersonnelUser
    {
        throw new NotSupportedException(
            'ID Broker personnel store only supports findByEmployeeId',
            1496260356
        );
    }

    /**
     * @param string $email
     * @return PersonnelUser
     * @throws NotSupportedException
     */
    public function findByEmail($email): PersonnelUser
    {
        throw new NotSupportedException(
            'ID Broker personnel store only supports findByEmployeeId',
            1496260354
        );
    }

}
