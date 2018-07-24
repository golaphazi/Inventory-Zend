<?php
//filename : module/Ibcia/src/Ibcia/Factory/Controller/BusinessDateControllerServiceFactory.php
namespace Ibcia\Factory\Controller;
 
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ibcia\Controller\BusinessDateController;
 
class BusinessDateControllerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //$identityManager = $serviceLocator->getServiceLocator()->get('IdentityManager');
        $controller = new BusinessDateController();
         
        return $controller;
    }
}