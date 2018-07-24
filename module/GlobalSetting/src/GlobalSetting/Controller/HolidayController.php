<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
		
	use Ibcia\Model\Holiday;
	use GlobalSetting\Form\HolidayForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;

	class HolidayController extends AbstractActionController {
		protected $holidayTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getHolidayTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'holidays' 		=> $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new HolidayForm('holiday', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			$request = $this->getRequest();
			if($request->isPost()) {
				$holiday = new Holiday();
				$form->setInputFilter($holiday->getInputFilter());
				$form->setData($request->getPost());
				if($form->isValid()) {
					$postedData	= $request->getPost();
					$this->getHolidayTable()->transectionStart();
					$holidayData	= array();
					$holyDayType 		= $postedData["HOLIDAY_TYPE"];
					if($holyDayType == 'Weekend'){
						$status1 		= 0;
						$selectedYears 		= $postedData['yearValue'];
						$weekendList 		= $postedData["weekendList"];
						for($m=0;$m<sizeof($weekendList);$m++) {
							$weekend_day			= $weekendList[$m];
							for($i=1;$i<=12;$i++) {
								$calToDays = cal_days_in_month(CAL_GREGORIAN, $i, $selectedYears);
								for($j=1;$j<=$calToDays;$j++) {
									$jd=cal_to_jd(CAL_GREGORIAN,$i,$j,$selectedYears);
									$dayOfTheDate=jddayofweek($jd,1);
									if(strtolower($weekend_day)==strtolower($dayOfTheDate)) {
										$flag=1;
										if($flag){
											$weekend_date="{$selectedYears}-{$i}-{$j}";
											$holidayData 	= array(
												'HOLIDAY_DATE' 			=> $weekend_date,				
												'HOLIDAY_TYPE' 			=> $postedData["HOLIDAY_TYPE"],								
												'HOLIDAY_DESCRIPTION' 	=> $postedData["HOLIDAY_DESCRIPTION"],	
											);
											$holiday->exchangeArray($holidayData);
											//echo "<pre>"; print_r($holiday);die();
											if($status = $this->getHolidayTable()->saveHoliday($holiday)) {
												$status1 = 1;
											} else {
											}
										}
									} else {
									}
									
								}
							}
							
						}
					} else {
						$holidayData	= array();
						$holidayData 	= array(
							'HOLIDAY_DATE' 			=> $postedData["HOLIDAY_DATE"],				
							'HOLIDAY_TYPE' 			=> $postedData["HOLIDAY_TYPE"],								
							'HOLIDAY_DESCRIPTION' 	=> $postedData["HOLIDAY_DESCRIPTION"],	
						);
						
						$holiday->exchangeArray($holidayData);
						if($status = $this->getHolidayTable()->saveHoliday($holiday)) {
							$status1 = 1;
						} else {
						}
					}
					//echo $status1;die();
					//echo "<pre>"; print_r($holiday); die();
					if($status1) {
						$this->getHolidayTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Holiday [ ".$postedData["HOLIDAY_TYPE"]." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('holiday');		
					} else {
						$this->getHolidayTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Holiday [ ".$postedData["HOLIDAY_DATE"]." ] Already Exist or Error!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('holiday');									
					}
				}
			}
			
			return array('form' => $form);
		}
		
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
		   	$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('holiday',array('action' => 'add'));
			}
			
			try {
				$city = $this->getHolidayTable()->getHolidayDate($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('holiday', array('action' => 'index'));
			}
			
			$form = new HolidayForm('holiday', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($city);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$holiday = new Holiday();
				$form->setInputFilter($holiday->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$postedData	= $request->getPost();
					//echo "<pre>"; print_r($postedData); die();
					$this->getHolidayTable()->transectionStart();
					
					$holidayData	= array();
					$holidayData 	= array(
						'HOLIDAY_ID' 			=> $postedData["HOLIDAY_ID"],
						'HOLIDAY_DATE' 			=> $postedData["HOLIDAY_DATE"],				
						'HOLIDAY_TYPE' 			=> $postedData["HOLIDAY_TYPE"],								
						'HOLIDAY_DESCRIPTION' 	=> $postedData["HOLIDAY_DESCRIPTION"],	
					);
					
					$holiday->exchangeArray($holidayData);
					//echo "<pre>"; print_r($holiday); die();
					if($this->getHolidayTable()->editHoliday($holiday)) {
						$this->getHolidayTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Holiday [ ".$postedData["HOLIDAY_DATE"]." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('holiday');		
					} else {
						$this->getHolidayTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Holiday [ ".$postedData["HOLIDAY_DATE"]." ] couldn't edit successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('holiday');									
					}
				}
			}
			
			return array(
				'id' => $id,
				'form' => $form,
			);
		}
		
		private function getHolidayTable() {
			if(!$this->holidayTable) {
				$sm = $this->getServiceLocator();
				$this->holidayTable = $sm->get('HolidayTable');
			}
			return $this->holidayTable;
		}
	}
?>	
