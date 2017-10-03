<?php
namespace Sil\IdpPw\Common\Personnel\IdBroker;

use IPBlock;
use Sil\Idp\IdBroker\Client\IdBrokerClient;
use Sil\IdpPw\Common\Personnel\NotFoundException;
use Sil\IdpPw\Common\Personnel\PersonnelInterface;
use Sil\IdpPw\Common\Personnel\PersonnelUser;
use yii\base\Component;

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
     * @var boolean
     */
    public $assertValidBrokerIp = true;

    /**
     * @var IPBlock[]
     */
    public $trustedIpRanges = [];

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

        $idBrokerClient = $this->getIdBrokerClient();

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
     * @throws NotFoundException
     * @throws \Exception
     */
    public function findByUsername($username): PersonnelUser
    {
        $idBrokerClient = $this->getIdBrokerClient();

        $results = $idBrokerClient->listUsers(null, ['username' => $username]);
        if (count($results) > 1) {
            throw new \Exception(
                sprintf('More than one user found when searching by username "%s"', $username),
                1497636205
            );
        } elseif (count($results) === 1) {
            if ($results[0]['username'] == $username) {
                return $this->returnPersonnelUserFromResponse('username', $username, $results[0]);
            }
        }

        throw new NotFoundException();
    }

    /**
     * @param string $email
     * @return PersonnelUser
     * @throws NotFoundException
     * @throws \Exception
     */
    public function findByEmail($email): PersonnelUser
    {
        $idBrokerClient = $this->getIdBrokerClient();

        $results = $idBrokerClient->listUsers(null, ['email' => $email]);
        if (count($results) > 1) {
            throw new \Exception(
                sprintf('More than one user found when searching by email "%s"', $email),
                1497636210
            );
        } elseif (count($results) === 1) {
            if ($results[0]['email'] == $email) {
                return $this->returnPersonnelUserFromResponse('email', $email, $results[0]);
            }
        }

        throw new NotFoundException();
    }

    /**
     * @return IdBrokerClient
     */
    private function getIdBrokerClient()
    {
        return new IdBrokerClient(
            $this->baseUrl, // The base URI for the API.
            $this->accessToken, // Your HTTP header authorization bearer token.
            [
                IdBrokerClient::TRUSTED_IPS_CONFIG => $this->trustedIpRanges,
                IdBrokerClient::ASSERT_VALID_BROKER_IP_CONFIG => $this->assertValidBrokerIp,
                'http_client_options' => [
                    'timeout' => 10, // An (optional) custom HTTP timeout, in seconds.
                ],
            ]
        );
    }

}
