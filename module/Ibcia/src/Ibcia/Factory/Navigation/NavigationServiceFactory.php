<?php
//filename : module/Ibcia/src/Ibcia/Factory/Navigation/NavigationServiceFactory.php
namespace Ibcia\Factory\Navigation;

use Zend\ServiceManager\ServiceLocatorInterface;
use Ibcia\Service\Navigation\NavigationService;

class NavigationServiceFactory extends NavigationService
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$navService = new NavigationService();
		return $navService->createService($serviceLocator);
	}
}