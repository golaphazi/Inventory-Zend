<?php
//filename : module/Ibcia/src/Ibcia/Factory/Storage/AuthStorageFactory.php
namespace Ibcia\Factory\Storage;
 
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ibcia\Storage\AuthStorage;
 
class AuthStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
		//echo "<pre>";print_r($serviceLocator);die();
        $storage = new AuthStorage('nsa1_user_info');
		//echo "<pre>";print_r($storage);die();
        $storage->setServiceLocator($serviceLocator);
        $storage->setDbHandler();
       //echo "<pre>";print_r($storage);die();
        return $storage;
    }
}
