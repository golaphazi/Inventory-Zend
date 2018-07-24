<?php
//filename : module/Ibcia/src/Ibcia/Factory/Storage/IdentityManagerFactory.php
namespace Ibcia\Factory\Storage;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ibcia\Storage\IdentityManager;

class IdentityManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authService = $serviceLocator->get('AuthService');
        $identityManager = new IdentityManager($authService);

        return $identityManager;
    }
}