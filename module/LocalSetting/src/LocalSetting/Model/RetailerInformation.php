<?php
	namespace LocalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class RetailerInformation implements InputFilterAwareInterface {
		public $RETAILER_ID;
		public $ZONE_ID;
		public $NAME;
		public $SHOP_NAME;
		public $ADDRESS;
		public $PHONE;
		public $FAX;
		public $MOBILE;
		public $WEB;
		public $EMAIL;
		public $BUSINESS_DATE;
		public $EMPLOYEE_ID;
		public $EMPLOYEE_NAME;
		public $DESIGNATION_ID;
		public $DESIGNATION;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->RETAILER_ID		= (!empty($data['RETAILER_ID'])) ? $data['RETAILER_ID'] : null;
			$this->ZONE_ID 			= (!empty($data['ZONE_ID'])) ? $data['ZONE_ID'] : null;
			$this->NAME 			= (!empty($data['NAME'])) ? $data['NAME'] : null;
			$this->SHOP_NAME 		= (!empty($data['SHOP_NAME'])) ? $data['SHOP_NAME'] : null;
			$this->ADDRESS 			= (!empty($data['ADDRESS'])) ? $data['ADDRESS'] : null;
			$this->PHONE 			= (!empty($data['PHONE'])) ? $data['PHONE'] : null;
			$this->FAX 				= (!empty($data['FAX'])) ? $data['FAX'] : null;
			$this->MOBILE 			= (!empty($data['MOBILE'])) ? $data['MOBILE'] : null;
			$this->WEB 				= (!empty($data['WEB'])) ? $data['WEB'] : null;
			$this->EMAIL 			= (!empty($data['EMAIL'])) ? $data['EMAIL'] : null;
			$this->BUSINESS_DATE	= (!empty($data['BUSINESS_DATE'])) ? $data['BUSINESS_DATE'] : null;
			$this->EMPLOYEE_ID		= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->EMPLOYEE_NAME	= (!empty($data['EMPLOYEE_NAME'])) ? $data['EMPLOYEE_NAME'] : null;
			$this->DESIGNATION_ID	= (!empty($data['DESIGNATION_ID'])) ? $data['DESIGNATION_ID'] : null;
			$this->DESIGNATION		= (!empty($data['DESIGNATION'])) ? $data['DESIGNATION'] : null;
			
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
		
		public function setInputFilter(InputFilterInterface $inputFilter) {
			throw new \Exception("Not used");
		}
		
		public function getInputFilter() {
			if(!$this->inputFilter) {
				$inputFilter = new InputFilter();
				$factory	 = new InputFactory();
				
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'RETAILER_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				/*$inputFilter->add($factory->createInput(array(
					'name' 		=> 'EMPLOYEE_ID',
					'required'	=> FALSE,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));*/
				$inputFilter->add($factory->createInput(array(
					'name' => 'NAME',
					'required' => true,
					'filters' => array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
					'validators' => array(
						array(
						  'name' =>'NotEmpty', 
							'options' => array(
								'messages' => array(
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Retailer Name can not be empty!' 
								),
							),
						),
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 150,
								'messages' => array(
									'stringLengthTooShort' => 'Please enter type!', 
									'stringLengthTooLong' => 'Please enter type!',
								),
							),
						),
					),
				)));
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	