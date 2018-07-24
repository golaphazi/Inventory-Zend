<?php
	namespace GlobalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class CityTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('gs_city');
				$select->join('gs_country','gs_city.COUNTRY_ID=gs_country.COUNTRY_ID','COUNTRY');
				$select->order('gs_country.COUNTRY, gs_city.CITY ASC');
				
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new City());
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
		
		public function getCity($id) {
			$id 	= (int) $id;
			$rowSet = $this->tableGateway->select(array('CITY_ID' => $id));
			$row 	= $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function cityExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row 	= $rowSet->current();
			return $row;
		}
		public function getCityForSelect() {
			$getTblDataSql   = "SELECT * FROM gs_city ORDER BY CITY ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function saveCity(City $city) {
			$this->session 	= new SessionContainer('post_supply');
			$businessdate	= $this->session->businessdate;
			$recdate 		= $this->session->recdate;
			$userid 		= $this->session->userid;
			$data = array(
				'COUNTRY_ID' 	=> $city->COUNTRY_ID,
				'CITY' 			=> $city->CITY,
				'BUSINESS_DATE' => $businessdate,
				'RECORD_DATE'	 => $recdate,
				'OPERATE_BY' 	=> $userid,				
			);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'COUNTRY_ID' 	=> $city->COUNTRY_ID,
				'CITY' 			=> $city->CITY,
			);
			$id = (int) $city->CITY_ID;
			
			if($id == 0) {
				if($this->cityExist($existCheckData)) {
					throw new \Exception("City ".$city->CITY." already exist!");
				} else {
					if($this->tableGateway->insert($data)) {
						return true;	
					} else {
						return false;	
					}
				}
			} else {
				if($this->getCity($id)) {
					$existingCityId	= '';
					$cityExists		= '';
					$cityExists 	= $this->cityExist($existCheckData);
					$existingCityId	=  $cityExists->CITY_ID;
					if((!empty($cityExists)) && ($id!=$existingCityId)) {
						throw new \Exception("City ".$city->CITY." already exist!");
					} else {
						if($this->tableGateway->update($data,array('CITY_ID' => $id))) {
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
		
		public function deleteCity($id) {
			$this->tableGateway->delete(array('CITY_ID' => $id));
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