<?php
declare(strict_types=1);

namespace App\Helpers;


use App\Models\Company;
use App\Models\Membership;
use MasterZero\Nextcloud\Api;
use function MongoDB\BSON\toRelaxedExtendedJSON;

class NextCloudHelper
{

    protected $_api = false;

    /**
     * Create a new api instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_api = new Api([
            'login'=> env('NEXTCLOUD_API_LOGIN', 'admin2'),
            'password'=> env('NEXTCLOUD_API_PASSWORD', 'CfQYfp9mpDtJLLP'),
            'baseUrl'=> env('NEXTCLOUD_API_URL', 'http://localhost'),
            'sslVerify' => false,
        ]);
    }

    /**
     * Creates folder in the NextCloud for the company
     * @param Company $company
     * @return array|void
     */
    public function createTempFolder(Company $company)
    {
	
        try {
            $data = $this->_api->getUserList();
            $pass = $this->_generateRandomPassword();
            $data = $this->_api->createUser($company->temporary_folder, $pass);
            if ($data['success']) {
                return [
                    'result'   => true,
                    'login'    => $company->temporary_folder,
                    'password' => $pass,
                    'webDAV'   => env('NEXTCLOUD_API_URL', 'http://localhost') . '/remote.php/dav/files/' . $company->temporary_folder . '/',
                ];

            }
        } catch (\Exception $e) {
            return [
                'result' => false,
                'description' => $e->getMessage(),
            ];
        }
    }

    /**
     * Creates or replaces folder in the NextCloud for the company
     */
    public function createOrReplaceTempFolder(Company $company): array
    {
        try {
            $data = $this->_api->getUserList();
            if (in_array($company->temporary_folder, $data['users'])) {
                $this->_api->deleteUser($company->temporary_folder);
            }
            return $this->createTempFolder($company);

        } catch (\Exception $e) {
            return [
                'result' => false,
                'description' => $e->getMessage(),
            ];
        }
    }

    protected function _generateRandomPassword($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Updates the limit of company folder's size in mb
     */
    public function updateTempFolderLimit(Company $company, int $limit):bool
    {
        $data =  $this->_api->editUser($company->temporary_folder,'quota', $limit.' MB');

        return $data['success'];
    }

    /**
     * Deletes user with it's folder and data
     */
    public function deleteTempFolder(Company $company):bool
    {
        $data =  $this->_api->deleteUser($company->temporary_folder);
        return $data['success'];
    }

    /**
     * Disables user with it's folder and data
     */
    public function disableTempFolder(Company $company):bool
    {
        $data =  $this->_api->disableUser($company->temporary_folder);
        return $data['success'];
    }

    /**
     * Enables user with it's folder and data
     */
    public function activateTempFolder(Company $company):bool
    {
        $data =  $this->_api->enableUser($company->temporary_folder);
        return $data['success'];
    }


}
