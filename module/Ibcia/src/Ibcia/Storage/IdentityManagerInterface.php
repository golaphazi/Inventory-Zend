<?php
//filename : module/Ibcia/src/Ibcia/Storage/IdentityManagerInterface.php
namespace Ibcia\Storage;

interface IdentityManagerInterface
{
    public function login($identity, $credential);
    public function logout();
    public function hasIdentity();
    public function storeIdentity(array $identity);
	public function noIdentity($cond);
}