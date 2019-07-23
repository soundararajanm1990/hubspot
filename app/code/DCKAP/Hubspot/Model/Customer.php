<?php
namespace DCKAP\Hubspot\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\TestFramework\Helper\Bootstrap;
class Customer extends \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
{ 

    public static function convertContactToCustomer($result = null) {
        $email = $lastName = $firstName = '';
        $data = array();
        if (!empty($result)) {
           $data['vid'] = $result['vid'];
            if (!empty($result['properties'])) {
                foreach ($result['properties'] as $key=>$properties) {
                    if ($key == 'email') {
                        $data['email'] = $email = $properties['value'];
                    } elseif ($key == 'firstname') {
                        $data['firstName'] = $firstName = $properties['value'];
                    } elseif ($key == 'lastname') {
                        $data['lastName'] = $lastName = $properties['value'];
                    } elseif ($key == 'company') {
                        $data['company'] = $properties['value'];
                    } elseif ($key == 'phone') {
                        $data['phone'] = $properties['value'];
                    } elseif ($key == 'street') {
                        $data['street'] = $properties['value'];
                    } elseif ($key == 'state') {
                        $data['state'] = $properties['value'];
                    } elseif ($key == 'city') {
                        $data['city'] = $properties['value'];
                    } elseif ($key == 'region') {
                        $data['region'] = $properties['value'];
                    } elseif ($key == 'country') {
                        $data['country'] =  $properties['value'];
                    } elseif ($key == 'zip') {
                        $data['zip'] = $properties['value'];
                    } elseif ($key == 'address') {
                        $data['address'] = $properties['value'];
                    }
                }
           }
        }
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $appState = $objectManager->get('\Magento\Framework\App\State');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $websiteId = $storeManager->getStore()->getWebsiteId();
        $firstName =  $data['firstName'];
        $lastName = $data['lastName'];
        $email = $data['email'];
        $password = '123456';
        $customer = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
        $customer->setWebsiteId($websiteId);
        if ($customer->loadByEmail($email)->getId()) {
            $message = 'Customer with email '.$email.' is already registered.';
        } else {
            try {
                $customer->setEmail($email);
                $customer->setFirstname($firstName);
                $customer->setLastname($lastName);
                $customer->setPassword($password);
                $customer->setForceConfirmed(true);
                $customer->setData('vid', $data['vid']);
                $customer->save();
                try {
                    $customerAddress = $objectManager->get('\Magento\Customer\Model\AddressFactory')->create();
                    $customerAddress->setCustomerId($customer->getId())
                        ->setFirstname($firstName)
                        ->setLastname($lastName);     
                    $customerAddress->setCountryId('US');              
                    if (isset($data['state'])) {
                        $customerAddress->setRegion($data['state']);
                    }                    
                    if (isset($data['zip'])) {
                         $customerAddress->setPostcode($data['zip']);
                    }
                    if (isset($data['city'])) {
                         $customerAddress->setCity($data['city']);
                    }
                    if (isset($data['phone'])) {
                         $customerAddress->setTelephone($data['phone']);
                    }
                    if (isset($data['company'])) {
                         $customerAddress->setCompany($data['company']);
                    }
                    if (isset($data['address'])) {
                         $customerAddress->setStreet(array(
                            '0' => $data['address']
                        ));
                    }  
                    $customerAddress->setIsDefaultBilling('1');
                    $customerAddress->setIsDefaultShipping('1');
                    $customerAddress->setSaveInAddressBook('1');
                    $message = 'saved success';
                    try {
                        $customerAddress->save();
                    } catch (Exception $e) {
                        $message = 'Cannot save customer address.';
                    }
                } catch (Exception $e) {
                    $message =  'Cannot save customer .';
                }
                //$customer->sendNewAccountEmail();
            } catch (Exception $e) {
                $message =  $e->getMessage();
            }
        }
        return $message;
    }
    
}
