<?php
//filename : module/Ibcia/src/Ibcia/Storage/IdentityManager.php
namespace Ibcia\Storage;

use Zend\Authentication\AuthenticationService;

class IdentityManager implements IdentityManagerInterface
{
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function login($identity, $credential, $rememberMe = 0)
    {
        //echo 'asdfasd';die();
		$this->authService->getAdapter()
             ->setIdentity($identity)
             ->setCredential($credential);
		//$this->authService->getStorage()->setRememberMe($rememberMe);
		//$this->authService->setStorage($this->authService->getStorage());
		$this->authService->getStorage()->setExpirationSeconds($rememberMe);
        $result = $this->authService->authenticate();
		
		if (!$result->isValid()) {
            return false;
        }
		
		/*if ($result->isValid()) {
			//check if it has rememberMe :
			if ($rememberme == 1 ) {
				$this->getSessionStorage()
					 ->setRememberMe(1);
				//set storage again 
				$this->authService()->setStorage($this->getSessionStorage());
			}
			$this->authService()->getStorage()->write($request->getPost('username'));
		}*/
		
        return $this->authService->getAdapter()->getResultRowObject();
    }

    public function logout()
    {
        $this->authService->getStorage()->clear();
    }

    public function hasIdentity()
    {
        $sessionId = $this->authService->getStorage()->getSessionId();
/*print($this->authService->getStorage()
                    ->getSessionManager()
                    ->getSaveHandler()
                    ->read($sessionId));die();*/
        return $this->authService->getStorage()
                    ->getSessionManager()
                    ->getSaveHandler()
                    ->read($sessionId);
    }

    public function storeIdentity(array $identity)
    {
        $this->authService->getStorage()->write($identity);
    }
	
	public function noIdentity($cond)
    {
        $cond = 'signup';
		//$sessionId = $this->authService->getStorage()->getSessionId();
		return $cond;
        /*return $this->authService->getStorage()
                    ->getSessionManager()
                    ->getSaveHandler()
                    ->read($sessionId);*/
    }
}