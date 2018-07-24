<?php
	namespace LocalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class ZoneInformationForm extends Form {
		protected $zoneInformationTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('zoneInformation');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationZoneInformationForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'ZONE_ID',
				'type' => 'Hidden',
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
					'placeholder' => 'input zone name here'
				),
			));
			$this->add(array(
				'name' => 'SHORT_NAME',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'SHORT_NAME',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'short name here'
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
					'placeholder' => 'address here if available'
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
	}
?>	