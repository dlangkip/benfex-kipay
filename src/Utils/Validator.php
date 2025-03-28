<?php
namespace Kipay\Utils;

/**
 * Validator Class for Kipay Payment Gateway
 * 
 * This class handles validation of data.
 */
class Validator
{
    /**
     * @var array Validation errors
     */
    protected $errors = [];
    
    /**
     * Validate an email address
     * 
     * @param string $email Email to validate
     * @return bool True if valid
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate a URL
     * 
     * @param string $url URL to validate
     * @return bool True if valid
     */
    public function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate an IP address
     * 
     * @param string $ip IP address to validate
     * @param int $flags FILTER_FLAG_IPV4 or FILTER_FLAG_IPV6
     * @return bool True if valid
     */
    public function validateIp(string $ip, int $flags = null): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
    }
    
    /**
     * Validate required fields
     * 
     * @param array $data Data to validate
     * @param array $fields Required fields
     * @return array Missing fields
     */
    public function validateRequired(array $data, array $fields): array
    {
        $missing = [];
        
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
    
    /**
     * Validate a string length
     * 
     * @param string $string String to validate
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @return bool True if valid
     */
    public function validateLength(string $string, int $min = 0, int $max = PHP_INT_MAX): bool
    {
        $length = mb_strlen($string);
        return ($length >= $min && $length <= $max);
    }
    
    /**
     * Validate a numeric value
     * 
     * @param mixed $value Value to validate
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @return bool True if valid
     */
    public function validateNumeric($value, float $min = PHP_FLOAT_MIN, float $max = PHP_FLOAT_MAX): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $value = (float) $value;
        return ($value >= $min && $value <= $max);
    }
    
    /**
     * Validate an integer value
     * 
     * @param mixed $value Value to validate
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @return bool True if valid
     */
    public function validateInteger($value, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): bool
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return false;
        }
        
        $value = (int) $value;
        return ($value >= $min && $value <= $max);
    }
    
    /**
     * Validate a date
     * 
     * @param string $date Date to validate
     * @param string $format Date format
     * @return bool True if valid
     */
    public function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $dt = \DateTime::createFromFormat($format, $date);
        return $dt && $dt->format($format) === $date;
    }
    
    /**
     * Validate against a regular expression
     * 
     * @param string $value Value to validate
     * @param string $pattern Regular expression pattern
     * @return bool True if valid
     */
    public function validateRegex(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }
    
    /**
     * Validate a credit card number
     * 
     * @param string $number Credit card number
     * @return bool True if valid
     */
    public function validateCreditCard(string $number): bool
    {
        // Remove non-digits
        $number = preg_replace('/\D/', '', $number);
        
        // Check length
        $length = strlen($number);
        if ($length < 13 || $length > 19) {
            return false;
        }
        
        // Luhn algorithm
        $sum = 0;
        $alt = false;
        
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            
            if ($alt) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $alt = !$alt;
        }
        
        return ($sum % 10 === 0);
    }
    
    /**
     * Validate JSON
     * 
     * @param string $json JSON string to validate
     * @return bool True if valid
     */
    public function validateJson(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Validate a phone number
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid
     */
    public function validatePhone(string $phone): bool
    {
        // Remove non-digits except + at the beginning
        $phone = preg_replace('/^\+/', '00', $phone);
        $phone = preg_replace('/\D/', '', $phone);
        
        // Check length
        $length = strlen($phone);
        return ($length >= 10 && $length <= 15);
    }
    
    /**
     * Validate a password
     * 
     * @param string $password Password to validate
     * @param int $minLength Minimum length
     * @param bool $requireUppercase Require uppercase letters
     * @param bool $requireLowercase Require lowercase letters
     * @param bool $requireNumbers Require numbers
     * @param bool $requireSpecial Require special characters
     * @return bool True if valid
     */
    public function validatePassword(
        string $password,
        int $minLength = 8,
        bool $requireUppercase = true,
        bool $requireLowercase = true,
        bool $requireNumbers = true,
        bool $requireSpecial = false
    ): bool {
        // Check length
        if (strlen($password) < $minLength) {
            return false;
        }
        
        // Check character types
        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        if ($requireSpecial && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate a complete data set against a set of rules
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return bool True if valid
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            // Skip if field is not required and not present
            if (!isset($data[$field]) && !in_array('required', $fieldRules)) {
                continue;
            }
            
            // Get the field value (null if not set)
            $value = $data[$field] ?? null;
            
            // Apply each rule to the field
            foreach ($fieldRules as $rule) {
                // Handle rules with parameters
                if (is_string($rule) && strpos($rule, ':') !== false) {
                    list($ruleName, $ruleParams) = explode(':', $rule, 2);
                    $ruleParams = explode(',', $ruleParams);
                } else {
                    $ruleName = $rule;
                    $ruleParams = [];
                }
                
                // Apply the rule
                switch ($ruleName) {
                    case 'required':
                        if (!isset($data[$field]) || (is_string($value) && trim($value) === '')) {
                            $this->addError($field, 'The ' . $field . ' field is required.');
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !$this->validateEmail($value)) {
                            $this->addError($field, 'The ' . $field . ' field must be a valid email address.');
                        }
                        break;
                        
                    case 'url':
                        if (!empty($value) && !$this->validateUrl($value)) {
                            $this->addError($field, 'The ' . $field . ' field must be a valid URL.');
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $this->addError($field, 'The ' . $field . ' field must be numeric.');
                        }
                        break;
                        
                    case 'integer':
                        if (!empty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
                            $this->addError($field, 'The ' . $field . ' field must be an integer.');
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value)) {
                            $min = $ruleParams[0] ?? 0;
                            if (is_numeric($value) && (float) $value < $min) {
                                $this->addError($field, 'The ' . $field . ' field must be at least ' . $min . '.');
                            } elseif (is_string($value) && mb_strlen($value) < $min) {
                                $this->addError($field, 'The ' . $field . ' field must be at least ' . $min . ' characters.');
                            }
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value)) {
                            $max = $ruleParams[0] ?? PHP_INT_MAX;
                            if (is_numeric($value) && (float) $value > $max) {
                                $this->addError($field, 'The ' . $field . ' field may not be greater than ' . $max . '.');
                            } elseif (is_string($value) && mb_strlen($value) > $max) {
                                $this->addError($field, 'The ' . $field . ' field may not be greater than ' . $max . ' characters.');
                            }
                        }
                        break;
                        
                    case 'between':
                        if (!empty($value)) {
                            $min = $ruleParams[0] ?? 0;
                            $max = $ruleParams[1] ?? PHP_INT_MAX;
                            if (is_numeric($value) && ((float) $value < $min || (float) $value > $max)) {
                                $this->addError($field, 'The ' . $field . ' field must be between ' . $min . ' and ' . $max . '.');
                            } elseif (is_string($value) && (mb_strlen($value) < $min || mb_strlen($value) > $max)) {
                                $this->addError($field, 'The ' . $field . ' field must be between ' . $min . ' and ' . $max . ' characters.');
                            }
                        }
                        break;
                        
                    case 'in':
                        if (!empty($value) && !in_array($value, $ruleParams)) {
                            $this->addError($field, 'The selected ' . $field . ' is invalid.');
                        }
                        break;
                        
                    case 'date':
                        if (!empty($value)) {
                            $format = $ruleParams[0] ?? 'Y-m-d';
                            if (!$this->validateDate($value, $format)) {
                                $this->addError($field, 'The ' . $field . ' field must be a valid date.');
                            }
                        }
                        break;
                        
                    case 'regex':
                        if (!empty($value)) {
                            $pattern = $ruleParams[0] ?? '';
                            if (!$this->validateRegex($value, $pattern)) {
                                $this->addError($field, 'The ' . $field . ' field format is invalid.');
                            }
                        }
                        break;
                        
                    case 'alpha':
                        if (!empty($value) && !ctype_alpha($value)) {
                            $this->addError($field, 'The ' . $field . ' field may only contain letters.');
                        }
                        break;
                        
                    case 'alpha_num':
                        if (!empty($value) && !ctype_alnum($value)) {
                            $this->addError($field, 'The ' . $field . ' field may only contain letters and numbers.');
                        }
                        break;
                        
                    case 'alpha_dash':
                        if (!empty($value) && !preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
                            $this->addError($field, 'The ' . $field . ' field may only contain letters, numbers, dashes, and underscores.');
                        }
                        break;
                        
                    case 'json':
                        if (!empty($value) && !$this->validateJson($value)) {
                            $this->addError($field, 'The ' . $field . ' field must be a valid JSON string.');
                        }
                        break;
                        
                    case 'phone':
                        if (!empty($value) && !$this->validatePhone($value)) {
                            $this->addError($field, 'The ' . $field . ' field must be a valid phone number.');
                        }
                        break;
                        
                    case 'credit_card':
                        if (!empty($value) && !$this->validateCreditCard($value)) {
                            $this->addError($field, 'The ' . $field . ' field must be a valid credit card number.');
                        }
                        break;
                        
                    case 'password':
                        if (!empty($value)) {
                            $minLength = $ruleParams[0] ?? 8;
                            $requireUppercase = $ruleParams[1] ?? true;
                            $requireLowercase = $ruleParams[2] ?? true;
                            $requireNumbers = $ruleParams[3] ?? true;
                            $requireSpecial = $ruleParams[4] ?? false;
                            
                            if (!$this->validatePassword($value, $minLength, $requireUppercase, $requireLowercase, $requireNumbers, $requireSpecial)) {
                                $this->addError($field, 'The ' . $field . ' field must be at least ' . $minLength . ' characters and meet complexity requirements.');
                            }
                        }
                        break;
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Add a validation error
     * 
     * @param string $field Field name
     * @param string $message Error message
     * @return void
     */
    protected function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get validation errors
     * 
     * @return array Validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get the first error for each field
     * 
     * @return array First error for each field
     */
    public function getFirstErrors(): array
    {
        $firstErrors = [];
        
        foreach ($this->errors as $field => $errors) {
            if (!empty($errors)) {
                $firstErrors[$field] = $errors[0];
            }
        }
        
        return $firstErrors;
    }
}