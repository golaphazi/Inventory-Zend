<?php
//filename : module/Ibcia/src/Ibcia/Form/HolidayForm.php
namespace Ibcia\Form;
 
use Zend\Form\Form;
use Zend\InputFilter;
 
class HolidayForm extends Form
{
    public function __construct()
    {
        parent::__construct('login');
          
        $this->setAttribute('method', 'post');
        $this->setAttribute('onsubmit', 'return check()');
        $this->add(array(
            'name' => 'TYPE',
            'type' => 'Select',
			'attributes' => array(
				'value' => 'Public holiday',
				'id' => 'TYPE',
				'class'=>'FormSelectTypeInput',
				'data-modelproperty' => 'hType',
			),
			'options' => array(
				'value_options'=> array(
					'Public holiday' => 'Public holiday',
					'Weekend' => 'Weekend',
				),
			),
        ));
		
		$this->add(array(
            'name' => 'HOLIDAY',
            'type' => 'Text',
			'attributes' => array(
				'placeholder' => 'Date(dd-mm-yyyy)',
				'id' => 'HOLIDAY',
				'autocomplete' => 'off',
				'data-modelproperty' => 'hDate',
			),
        ));
		
		$this->add(array(
            'name' => 'YEAR',
            'type' => 'Text',
			'attributes' => array(
				'placeholder' => 'Year(yyyy)',
				'id' => 'YEAR',
				'autocomplete' => 'off',
				'maxlength' => '4',
			),
        ));
		
		/*$weekDays = array('Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday');
		foreach($weekDays as $key => $day) {
			$this->add(array(
				'name' => 'WEEKDAYS',
				'type' => 'Checkbox',
				'attributes' => array(
					'label' => $day,
					'value' => strtolower($day),
					'class' => 'weekdays',
				),
			));
		}*/
		$this->add(array(
			'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'WEEKDAYS',
            'options' => array(
                     'value_options' => array(
                             'friday' => 'Fri',
                             'saturday' => 'Sat',
                             'sunday' => 'Sun',
                             'monday' => 'Mon',
                             'tuesday' => 'Tue',
                             'wednesday' => 'Wed',
                             'thursday' => 'Thu',
                     ),
			),
			'attributes' => array(
				'class' => 'weekdays',
			),
		));
			 
        /*$this->add(array(
			'type' => 'Zend\Form\Element\Textarea',
            'name' => 'WEEKDAYS',
		));*/
        
		
		$this->add(array(
            'name' => 'DESCRIPTION',
            'type' => 'Text',
			'attributes' => array(
				'placeholder' => 'Description(150)',
				'id' => 'DESCRIPTION',
				'autocomplete' => 'off',
				'maxlength' => '150',
				'data-modelproperty' => 'hDesc',
			),
        ));
		
		$this->add(array(
            'name' => 'hType[]',
            'type' => 'Hidden',
			'attributes' => array(
				'isArray' => true, 
			),
        ));
		
		$this->add(array(
            'name' => 'hDate[]',
            'type' => 'Hidden',
			'attributes' => array(
				'isArray' => true, 
			),
        ));
		
		$this->add(array(
            'name' => 'hDesc[]',
            'type' => 'Hidden',
			'attributes' => array(
				'isArray' => true, 
			),
        ));
		
        $this->add(array(
            'name' => 'save',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Save',
				'class' => 'submit'
            ),
        ));
          
        //$this->setInputFilter($this->createInputFilter());
    }
    
    /*public function createInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
 
        //username
        $username = new InputFilter\Input('OPERATOR_NAME');
        $username->setRequired(true);
        $inputFilter->add($username);
         
        //password
        $password = new InputFilter\Input('OPERATOR_PASSWORD');
        $password->setRequired(true);
        $inputFilter->add($password);
 
        return $inputFilter;
    }*/
}