<?php
//filename : module/Ibcia/src/Ibcia/Controller/LoginController.php
namespace Ibcia\Controller;
 
use Zend\Mvc\Controller\AbstractActionController;
use Ibcia\Storage\IdentityManagerInterface;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
class SignupController extends AbstractActionController
{
    protected $identityManager;
    
    //we will inject identityManager via factory
    /*public function __construct(IdentityManagerInterface $identityManager)
    {
        $this->identityManager = $identityManager;
    }*/
    
    public function indexAction()
    {
       echo 'asdfads';die();
	   $this->identityManager->noIdentity('signup');
    }
}