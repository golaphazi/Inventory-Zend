<?php
//filename : module/Ibcia/src/Ibcia/Factory/Controller/HolidayControllerServiceFactory.php
namespace Ibcia\Factory\Controller;
 
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ibcia\Controller\HolidayController;
 
class HolidayControllerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $controller = new HolidayController();
         
        return $controller;
    }
}