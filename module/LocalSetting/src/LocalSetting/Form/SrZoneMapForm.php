<?php
	namespace LocalSetting\Form;
	
	use Zend\Form\Form;	
	use Zend\InputFilter;
	use Zend\Form\Element;
	use Zend\Filter\File\Rename;
	
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	
	class SrZoneMapForm extends Form {		
		protected $accountDetailsTable;
		protected $dbAdapter;
		protected $postedValues;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('srZoneMap');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationSrZoneMapForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'SR_ZONE_MAP_ID',
				'type' => 'Hidden',
			));
					
			$this->add(array(
				'name' => 'DESIGNATION_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'DESIGNATION_ID',
					'class' => 'FormSelectTypeInput', 
					'onchange'=> 'getEmployeeName(this.value);',
					'style' => 'width:220px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getDesignationSelect(),
				),
			));
			
						
			$this->add(array(
				'name' => 'EMPLOYEE_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'EMPLOYEE_ID',
					'class' => 'FormSelectTypeInput', 
					//'onchange'=> 'getAssetNumber(this.value); getMFCompanyList(this.value);',
					'style' => 'width:220px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					   'autocomplete' => 'off',
        			   'inarrayvalidator' => false,
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'disable_inarray_validator' => true
				),
			));
			
			
			$this->add(array(
				'name' => 'ZONE_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'ZONE_ID',
					'class'=>'FormSelectTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(
					//'label' => 'Controller : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getZoneSelect(),
				),
			));
			
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style'=> "font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
			));
			
			
			
		}
		
		public function getDesignationSelect() {
			$systemNavSql = "SELECT 
							  d.DESIGNATION_ID,
							  d.DESIGNATION
						
							FROM 
								 gs_designation d
							ORDER BY 
							d.DESIGNATION ASC ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['DESIGNATION_ID']] = $selectOption['DESIGNATION'];
			}
			return $selectData;
		}
		
		
		
		public function getZoneSelect() {
			$systemNavSql = "
								SELECT 
										z.ZONE_ID,
										z.NAME
								FROM 
										 ls_zone_info z
							    ORDER BY z.NAME ASC
							  ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['ZONE_ID']] = $selectOption['NAME'];
			}
			return $selectData;
		}
	}
?>	