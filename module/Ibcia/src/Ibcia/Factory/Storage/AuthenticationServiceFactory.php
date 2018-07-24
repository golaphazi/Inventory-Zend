<?php
//filename : module/Ibcia/src/Ibcia/Factory/Storage/AuthenticationServiceFactory.php
namespace Ibcia\Factory\Storage;
 
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
 
class AuthenticationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dbAdapter           = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $dbTableAuthAdapter      = new DbTableAuthAdapter($dbAdapter, 'up_is_operator',
                                                  'OPNAME','OPPASS');
		
		$authService = new AuthenticationService($serviceLocator->get('AuthStorage'),
                                                                $dbTableAuthAdapter);
         
        return $authService;
    }
}
