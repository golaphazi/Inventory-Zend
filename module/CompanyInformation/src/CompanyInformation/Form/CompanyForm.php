<?php
	namespace CompanyInformation\Form;
	use Zend\Form\Form;
	
	class CompanyForm extends Form {
		public function __construct($name = null) {
			parent::__construct($name);
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return addCompany();');
			$this->add(array(
				'name' => 'COMPANY_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'COMPANY_NAME',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'COMPANY_NAME',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Company Name : ',
				),
			));
			$this->add(array(
				'name' => 'COMPANY_CODE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'COMPANY_CODE',
					'class'=>'FormTextTypeInput',
					'maxlength'=> '3',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Company Code : ',
				),
			));
			$this->add(array(
				'name' => 'ADDRESS',
				'type' => 'Textarea',
				'attributes' => array(
					'id' => 'ADDRESS',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Address : ',
				),
			));
			$this->add(array(
				'name' => 'PHONE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'PHONE',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Phone Number : ',
				),
			));
			$this->add(array(
				'name' => 'FAX',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'FAX',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Fax Number : ',
				),
			));
			$this->add(array(
				'name' => 'EMAIL',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'EMAIL',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Email Address : ',
				),
			));
			$this->add(array(
				'name' => 'WEB',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'WEB',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Web Address : ',
				),
			));
			$this->add(array(
				'name' => 'ACTIVE_DEACTIVE',
				'type' => 'Hidden',
				'attributes' => array(
					'value' => 'y',
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