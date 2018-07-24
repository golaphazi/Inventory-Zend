<?php
//filename : module/Ibcia/src/Ibcia/Service/Navigation/NavigationService.php
namespace Ibcia\Service\Navigation;

//use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;

class NavigationService extends DefaultNavigationFactory
{
	protected function getPages(ServiceLocatorInterface $serviceLocator)
	{
		$userInfo = \Zend\Json\Json::decode($serviceLocator->get('IdentityManager')->hasIdentity());
			
		if($userInfo->id) {
			if (null === $this->pages) {
			
				$fetchMenu = $serviceLocator->get('SystemNavTable')->getModule($userInfo->id, '2');
				
				$configuration['navigation'][$this->getName()] = array();
				
				foreach($fetchMenu as $key=>$row) {
					$configuration['navigation'][$this->getName()][$row['CONTROLLER']] = array(
						'label' => $row['CONTROLLER_NAME_UI'],
						'route' => strtolower(str_replace(' ','', $row['CONTROLLER'])),
					);
				}
				
				if(strtolower($userInfo->role) == 'administrator') {
					$configuration['navigation'][$this->getName()]['Eod'] = array(
						'label' => 'EOD',
						'route' => 'businessdate',
						'action' => 'eod',
					);
				}
				
				$configuration['navigation'][$this->getName()]['Logout'] = array(
					'label' => 'Logout',
					'route' => 'login',
					'action' => 'logout',
				);
				
				if (!isset($configuration['navigation'])) {
					throw new Exception\InvalidArgumentException('Could not find navigation configuration key');
				}
				
				if (!isset($configuration['navigation'][$this->getName()])) {
					throw new Exception\InvalidArgumentException(sprintf(
					'Failed to find a navigation container by the name "%s"',
					$this->getName()
					));
				}
				
				$application = $serviceLocator->get('Application');
				$routeMatch = $application->getMvcEvent()->getRouteMatch();
				$router = $application->getMvcEvent()->getRouter();
				$pages = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);
				
				$this->pages = $this->injectComponents($pages, $routeMatch, $router);
			}
		}
		
		return $this->pages;
	}
}