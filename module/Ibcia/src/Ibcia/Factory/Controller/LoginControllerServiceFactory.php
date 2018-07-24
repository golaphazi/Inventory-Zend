<?php
//filename : module/Ibcia/src/Ibcia/Factory/Controller/LoginControllerServiceFactory.php
namespace Ibcia\Factory\Controller;
 
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ibcia\Controller\LoginController;
 
class LoginControllerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $identityManager = $serviceLocator->getServiceLocator()->get('IdentityManager');
        $controller = new LoginController($identityManager);
         
        return $controller;
    }
}