<?php

namespace JosKoomen\AbstractApi;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait AbstractApiValidationTrait
{

    /**
     * @param $values : Illuminate\Database\Eloquent\Collection
     * @param $urlencode : boolean
     * @return array for json
     */
    public function validateValues($values, $urlencode = false)
    {
        $data = [];
        $data['code'] = 400;
        $data['success'] = false;

        if ($this->_isEncodingEnabled()) {
            if (!isset($values)) {

                $data['message'] = __('joskoomen.api.abstract.empty');
                return $data;
            };

            if (!isset($values['time']) || is_null($values['time'])) {
                $data['message'] = __('joskoomen.api.abstract.notime');
                return $data;
            };

            if (!$this->_validateTimeValue($values['time'])) {
                $data['message'] = __('joskoomen.api.abstract.outdated');
                return $data;
            }

            if (!isset($values['sig']) || is_null($values['sig'])) {
                $data['message'] = __('joskoomen.api.abstract.nosig');
                return $data;
            };

            $string = $this->_buildSigFromValues($values);

            if (!$this->_validateHashedValue($string, $values['sig'], $urlencode)) {
                $data['code'] = 401;
                $data['message'] = __('joskoomen.api.abstract.invalidsig');
                if ($this->_hasDebugMode()) {
                    $data['string'] = $string;
                    $data['hashKey'] = $this->_getHashSecret();
                    $data['values'] = $values;
                }
                return $data;
            }
        }

        $data['code'] = 200;
        $data['success'] = true;
        return $data;
    }

    public function addTimeAndSignature($values, $urlencode = false)
    {
        if ($this->_isEncodingEnabled()) {
            $values['time'] = time();

            $string = $this->_buildSigFromValues($values);

            if ($this->_hasDebugMode()) {
                Log::debug('AbstractApiValidationTrait::addTimeAndSignature given $values:"', $values);
                Log::debug('AbstractApiValidationTrait::addTimeAndSignature "$urlencode enabled:" ( ' . $urlencode . ' )');
                Log::debug('AbstractApiValidationTrait::addTimeAndSignature "$string:" ( ' . $string . ' )');
            }

            if ($urlencode !== false) {
                $string = urlencode($string);
                if ($this->_hasDebugMode()) {
                    Log::debug('AbstractApiValidationTrait::addTimeAndSignature "urlencoded $string:" ( ' . $string . ' )');
                }
            }

            $values['sig'] = $this->_buildHash($string);
            if ($this->_hasDebugMode()) {
                Log::debug('AbstractApiValidationTrait::addTimeAndSignature $values[\'sig\']:" ( ' . $values['sig'] . ' )');
            }
        }

        return $values;
    }

    /*
     |--------------------------------------------------------------------------
     | Protected methods
     |--------------------------------------------------------------------------
     */
    protected function getHashSecret()
    {
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::getHashSecret ( ' . config('joskoomen-abstractapi.hashsecret') . ' )');
        }
        return is_laravel() ? config('joskoomen-abstractapi.hashsecret') : env('JOSKOOMEN_ABSTRACT_API_HASH_SECRET');
    }

    /*
     |--------------------------------------------------------------------------
     | Private methods
     |--------------------------------------------------------------------------
     */
    private function _buildSigFromValues($values)
    {
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_buildSigFromValues "given values:"', $values);
        }
        $string = '';

        // Sort values alphabetic
        // @url http://php.net/manual/en/function.ksort.php
        ksort($values);

        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_buildSigFromValues "alphabetic sorted values:"', $values);
        }

        foreach ($values as $key => $value) {

            if ($key != 'sig') {
                switch ($key) {
                    default:
                        $string .= $key . '=' . json_encode(strval($value));
                        break;
                    case 'hashtype':
                    case 'string':
                    case 'hashkey':
                        break;
                }
            }
        }

        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_buildSigFromValues "string:" ( ' . $string . ' )');
        }

        return $string;
    }

    private function _validateTimeValue($time)
    {
        $date = Carbon::createFromTimestampUTC($time);

        $now = Carbon::now();
        $now->setTimezone('UTC');

        $difference = $now->diffInSeconds($date) < $this->_getMaxValidTimeDifference();

        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_validateTimeValue given $time:" ( ' . $time . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "$date [UTC]:" ( ' . $date . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "$now [UTC]:" ( ' . $now . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "$now->diffInSeconds($date):" ( ' . $now->diffInSeconds($date) . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "config time difference:" ( ' . $this->_getMaxValidTimeDifference() . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "returned value:" ( ' . $difference . ' )');
        }

        return $difference;
    }

    private function _validateHashedValue($string, $sig, $urlencode = false)
    {
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_validateHashedValue "given $sig:" ( ' . $sig . ' )');
            Log::debug('AbstractApiValidationTrait::_validateHashedValue "urlencode values?:" ( ' . $urlencode . ' )');
            Log::debug('AbstractApiValidationTrait::_validateHashedValue "given $string:" ( ' . $string . ' )');
        }

        if ($urlencode !== false) {
            $string = urlencode($string);
            if ($this->_hasDebugMode()) {
                Log::debug('AbstractApiValidationTrait::_validateHashedValue "urlencoded string:" ( ' . $string . ' )');
            }
        }

        $value = $this->_buildHash($string);

        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_validateHashedValue "$value:" ( ' . $value . ' )');
        }

        return $sig === $value;
    }

    private function _buildHash($string)
    {
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_buildHash "$string:" ( ' . $string . ' )');
        }

    }

    private function _getMaxValidTimeDifference()
    {
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_getMaxValidTimeDifference ( ' . config('joskoomen-abstractapi.timedifferences') . ' )');
        }
        return is_laravel() ? config('joskoomen-abstractapi.timedifferences') : env('JOSKOOMEN_ABSTRACT_API_TIME_DIFFERENCES');
    }

    private function _getHashType()
    {
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_getHashType ( ' . config('joskoomen-abstractapi.hashtype') . ' )');
        }

        
        return is_laravel() ? config('joskoomen-abstractapi.hashtype') : env('JOSKOOMEN_ABSTRACT_API_DEBUG');
    }

    private function _hasDebugMode()
    {
        return is_laravel() ? config('joskoomen-abstractapi.debug') : env('JOSKOOMEN_ABSTRACT_API_HASHTYPE');
    }

    private function _isEncodingEnabled()
    {
        $isDisabled = boolval(config('joskoomen-abstractapi.disable'));

        $isProduction = app()->environment('production');
        // !isDisabled = enabled
        // if enabled or when it's the production environment it always returns true
        return !$isDisabled || $isProduction;
    }
}
