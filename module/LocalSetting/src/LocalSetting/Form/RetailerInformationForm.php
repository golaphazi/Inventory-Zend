<?php
	namespace LocalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class RetailerInformationForm extends Form {
		protected $retailerInformationTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('retailerInformation');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationRetailerInformationForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'RETAILER_ID',
				'type' => 'Hidden',
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
					'class'=>'FormSelectTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(
					//'label' => 'Controller : ',
					'empty_option'  => '--- please choose ---',
					//'value_options' => $this->getEmployeeSelect(),
				),
			));
			
			$this->add(array(
				'name' => 'NAME',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'NAME',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'retailer name here'
				),
			));
			$this->add(array(
				'name' => 'SHOP_NAME',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'SHOP_NAME',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input short name here'
				),
			));
			$this->add(array(
				'name' => 'ADDRESS',
				'type' => 'textarea',
				'attributes' => array(					
					'id' => 'ADDRESS',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'retailer address here if available'
				),
				
			));
			$this->add(array(
				'name' => 'PHONE',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'PHONE',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input phone no.'
				),
			));
			$this->add(array(
				'name' => 'FAX',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'FAX',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input fax no.'
				),
			));
			$this->add(array(
				'name' => 'MOBILE',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'MOBILE',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input mobile no.'
				),
			));
			$this->add(array(
				'name' => 'WEB',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'WEB',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'web address here'
				),
			));
			$this->add(array(
				'name' => 'EMAIL',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'EMAIL',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'email address here'
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
	}
?>	