<?php

class ExternalValidatorException extends Exception {

    protected $_intakeFieldId = null;
    protected $_fieldId = null;
    protected $_data = array();

    // Getter and setter methods for _data, _intakeFieldId, and _fieldId

    public function getData() {
        return $this->_data;
    }

    public function setData($data) {
        $this->_data = $data;
        return $this;
    }

    public function getIntakeFieldId() {
        return $this->_intakeFieldId;
    }

    public function setIntakeFieldId($fieldId) {
        $this->_intakeFieldId = $fieldId;
        return $this;
    }

    public function getFieldId() {
        return $this->_fieldId;
    }

    public function setFieldId($fieldId) {
        $this->_fieldId = $fieldId;
        return $this;
    }
}
