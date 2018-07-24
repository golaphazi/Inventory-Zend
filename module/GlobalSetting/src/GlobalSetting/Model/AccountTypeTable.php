<?php
	namespace GlobalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class AccountTypeTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('gs_account_type');
				$select->order('ACCOUNT_TYPE ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new AccountType());
				// create a new pagination adapter object
				$paginatorAdapter 	= new DbSelect($select,$this->tableGateway->getAdapter(),$resultSetPrototype);
				$paginator 			= new Paginator($paginatorAdapter);
				return $paginator;
			}
			
			if (null === $select)
			$select	= new Select();
			$select->from($this->table);
			$resultSet = $this->selectWith($select);
			$resultSet->buffer();
			return $resultSet;
		}
		
		public function getAccountType($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('ACCOUNT_TYPE_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function accountTypeExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveAccountType(AccountType $accountType) {
			$this->session 	= new SessionContainer('post_supply');
			$businessdate 	= $this->session->businessdate;
			$recdate 		= $this->session->recdate;
			$userid 		= $this->session->userid;
			$data = array(
				'ACCOUNT_TYPE' 	=> $accountType->ACCOUNT_TYPE,
				'DESCRIPTION' 	=> $accountType->DESCRIPTION,
				'BUSINESS_DATE' => $businessdate,
				'RECORD_DATE' 	=> $recdate,
				'OPERATE_BY' 	=> $userid,	
			);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'ACCOUNT_TYPE' => $accountType->ACCOUNT_TYPE,
			);
			$id = (int) $accountType->ACCOUNT_TYPE_ID;
			
			if($id == 0) {
				if($this->accountTypeExist($existCheckData)) {
					throw new \Exception("Account type ".$accountType->ACCOUNT_TYPE." already exist!");
				} else {
					if($this->tableGateway->insert($data)) {
						return true;	
					} else {
						return false;	
					}
				}
			} else {
				if($this->getaccountType($id)) {
					$existingAccTypeId	= '';
					$accTypeExist		= '';
					$accTypeExist 		= $this->accountTypeExist($existCheckData);
					$existingaccTypeId 	=  $accTypeExist->ACCOUNT_TYPE_ID;
					if((!empty($accTypeExist)) && ($id!=$existingaccTypeId)) {
						throw new \Exception("Account type ".$accountType->ACCOUNT_TYPE." already exist!");
					} else {
						if($this->tableGateway->update($data,array('ACCOUNT_TYPE_ID' => $id))) {
							return true;
						} else {
							return false;	
						}
					}
				} else {
					throw new \Exception("ID $id does not exist!");
				}
			}
		}
		
		public function deleteAccountType($id) {
			$this->tableGateway->delete(array('ACCOUNT_TYPE_ID' => $id));
		}
		
		public function transectionStart() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->beginTransaction();
		}
		
		public function transectionEnd() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->commit();
		}
		
		public function transectionInterrupted() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->rollback();
		}
	}
?>	