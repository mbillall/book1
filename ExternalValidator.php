<?php

class ExternalValidator {

    const SERVICE_ERROR = 1;
    const INTAKE_FORM_UNKNOWN = 2;
    const INTAKE_FORM_UNKNOWN_CHECK_NUMBER = 3;
    const INTAKE_FORM_INCORRECT_CHECK_NUMBER = 4;
    const INTAKE_FORM_UNKNOWN_CHECK_DOB = 5;
    const INTAKE_FORM_INCORRECT_CHECK_DOB = 6;
    const EXCEED_BOOKING_LIMIT = 7; // New constant for booking limit error

    protected $_errors = array(
        self::SERVICE_ERROR => 'Invalid service is selected. Please select another service to continue booking.',
        self::INTAKE_FORM_UNKNOWN => 'Intake Forms are missing for this service',
        self::INTAKE_FORM_UNKNOWN_CHECK_NUMBER => '"Check number" field is missing.',
        self::INTAKE_FORM_INCORRECT_CHECK_NUMBER => '"Check number" field is incorrect.',
        self::INTAKE_FORM_UNKNOWN_CHECK_DOB => '"Date of birth" field is missing.',
        self::INTAKE_FORM_INCORRECT_CHECK_DOB => 'Incorrect date of birth',
        self::EXCEED_BOOKING_LIMIT => 'The booking duration cannot exceed 3 hours.', // New error message
    );

    protected $_fieldsNameMap = array(
        'checkNumber' => 'Check number',
        'checkString' => 'Some string',
        'dateOfBirth' => 'Date of birth',
    );

    public function validate($bookingData){
        try {
            $timeStart = microtime(true);
            $this->_log($bookingData);

            // Validate service
            if (!isset($bookingData['service_id']) || $bookingData['service_id'] != 9) {
                $this->_error(self::SERVICE_ERROR, 'service_id');
                return false;
            }

            // Validate intake form fields
            if (!isset($bookingData['additional_fields'])) {
                $this->_error(self::INTAKE_FORM_UNKNOWN);
                return false;
            }

            // Validate the booking duration (new check)
            if (!$this->_isBookingDurationValid($bookingData['start_datetime'], $bookingData['end_datetime'])) {
                $this->_error(self::EXCEED_BOOKING_LIMIT);
                return false;
            }

            // Existing checks for 'Check number' and 'Date of birth'
            $checkNumberField = $this->_findField('checkNumber', $bookingData['additional_fields'], $this->_fieldsNameMap);
            if (!$checkNumberField) {
                $this->_error(self::INTAKE_FORM_UNKNOWN_CHECK_NUMBER);
                return false;
            } else if ($checkNumberField['value'] != 112233445566) {
                $this->_error(self::INTAKE_FORM_INCORRECT_CHECK_NUMBER, null, $checkNumberField['id']);
                return false;
            }

            $dateOfBirthField = $this->_findField('dateOfBirth', $bookingData['additional_fields'], $this->_fieldsNameMap);
            if (!$dateOfBirthField) {
                $this->_error(self::INTAKE_FORM_UNKNOWN_CHECK_DOB);
                return false;
            } else if (!$this->_isBirthdayValid($dateOfBirthField['value'])) {
                $this->_error(self::INTAKE_FORM_INCORRECT_CHECK_DOB, null, $checkNumberField['id']);
                return false;
            }

            // Optionally change intake form fields
            $result = array(
                'checkString' => "replaced text",
            );

            $this->_log($result);
            $intakeFieldsResult = $this->_createFieldResult($result, $bookingData['additional_fields'], $this->_fieldsNameMap);
            $this->_log($intakeFieldsResult);

            $timeEnd = microtime(true);
            $executionTime = $timeEnd - $timeStart;
            $this->_log('Total Execution Time: '.$executionTime.' sec');

            if ($intakeFieldsResult) {
                return array(
                    'additional_fields' => $intakeFieldsResult,
                );
            }
            return array();
        } catch (ExternalValidatorException $e) {
            return $this->_sendError($e);
        } catch (Exception $e) {
            $result = array(
                'errors' => array($e->getMessage())
            );
            $this->_log($result);
            return $result;
        }
    }

    // New method to check if the booking duration exceeds 3 hours
    protected function _isBookingDurationValid($startDatetime, $endDatetime) {
        $start = strtotime($startDatetime);
        $end = strtotime($endDatetime);
        
        if (!$start || !$end) {
            return false; // Invalid date format
        }

        $duration = ($end - $start) / 3600; // Convert seconds to hours

        if ($duration > 3) {
            return false; // Booking duration exceeds 3 hours
        }

        return true;
    }

    // Existing methods for birthday validation, field lookup, logging, etc.
    // ...
}
