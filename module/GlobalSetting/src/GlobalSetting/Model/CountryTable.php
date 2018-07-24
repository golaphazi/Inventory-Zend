<?php
	namespace GlobalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class CountryTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('gs_country');
				$select->order('COUNTRY ASC');
				
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new Country());
				
				// Create a new pagination adapter object
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
		
		public function getCountry($id) {
			$id 	= (int) $id;
			$rowSet = $this->tableGateway->select(array('COUNTRY_ID' => $id));
			$row	= $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function countryExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row 	= $rowSet->current();
			return $row;
		}
		public function getCountryForSelect() {
			$getTblDataSql   = "SELECT * FROM gs_country ORDER BY COUNTRY ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function saveCountry(Country $country) {
			$this->session 	= new SessionContainer('post_supply');
			$businessdate 	= $this->session->businessdate;
			$recdate 		= $this->session->recdate;
			$userid 		= $this->session->userid;
			$data = array(
				'COUNTRY' 		=> $country->COUNTRY,
				'SHORT_NAME' 	=> $country->SHORT_NAME,
				
				'BUSINESS_DATE' => $businessdate,
				'RECORD_DATE'	=> $recdate,
				'OPERATE_BY' 	=> $userid,
			);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'COUNTRY' => $country->COUNTRY,
			);
			$id = (int) $country->COUNTRY_ID;
			
			if($id == 0) {
				if($this->countryExist($existCheckData)) {
					throw new \Exception("Country ".$country->COUNTRY." already exist!");
				} else {
					if($this->tableGateway->insert($data)) {
						return true;	
					} else {
						return false;
					}
				}
			} else {
				if($this->getCountry($id)) {
					$existingCountryId	= '';
					$countryExist		= '';
					$countryExist 		= $this->countryExist($existCheckData);
					$existingCountryId	=  $countryExist->COUNTRY_ID;
					if((!empty($countryExist)) && ($id!=$existingCountryId)) {
						throw new \Exception("Country ".$country->COUNTRY." already exist!");
					} else {
						if($this->tableGateway->update($data,array('COUNTRY_ID' => $id))) {
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
		
		public function deleteCountry($id) {
			$this->tableGateway->delete(array('COUNTRY_ID' => $id));
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