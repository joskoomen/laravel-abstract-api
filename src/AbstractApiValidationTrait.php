<?php

namespace Ypa\AbstractApi;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait AbstractApiValidationTrait
{

    /**
     * @param $pValues : Illuminate\Database\Eloquent\Collection
     * @param $pUrlencode : boolean
     * @return array for json
     */
    public function validateValues($pValues, $pUrlencode = false)
    {
        $data = [];
        $data['code'] = 400;
        $data['success'] = false;


        if ($this->_isEncodingEnabled()) {
            if (!isset($pValues)) {

                $data['message'] = __('ypa.api.abstract.empty');
                return $data;
            }

            if (!isset($pValues['time']) || is_null($pValues['time'])) {
                $data['message'] = __('ypa.api.abstract.notime');
                return $data;
            }

            if (!$this->_validateTimeValue($pValues['time'])) {
                $data['message'] = __('ypa.api.abstract.outdated');
                return $data;
            }

            if (!isset($pValues['sig']) || is_null($pValues['sig'])) {
                $data['message'] = __('ypa.api.abstract.nosig');
                return $data;
            }

            if (isset($pValues['hashkey'])) {
                $key = $pValues['hashkey'];
            } else {
                $key = null;
            }

            $string = $this->_buildSigFromValues($pValues);

            if (!$this->_validateHashedValue($string, $pValues['sig'], $key, $pUrlencode)) {
                $data['code'] = 401;
                $data['message'] = __('ypa.api.abstract.invalidsig');
                if ($this->_hasDebugMode()) {
                    $data['string'] = $string;
                    $data['hashKey'] = $this->getHashSecret($key);
                    $data['values'] = $pValues;
                }
                return $data;
            }
        }

        $data['code'] = 200;
        $data['success'] = true;
        return $data;
    }

    public function addTimeAndSignature($pValues, $pUrlencode = false)
    {
        if ($this->_isEncodingEnabled()) {
            $pValues['time'] = time();

            $string = $this->_buildSigFromValues($pValues);

            if ($this->_hasDebugMode()) {
                Log::debug('AbstractApiValidationTrait::addTimeAndSignature given $pValues:"', $pValues);
                Log::debug('AbstractApiValidationTrait::addTimeAndSignature "$pUrlencode enabled:" ( ' . $pUrlencode . ' )');
                Log::debug('AbstractApiValidationTrait::addTimeAndSignature "$string:" ( ' . $string . ' )');
            }

            if ($pUrlencode !== false) {
                $string = urlencode($string);
                if ($this->_hasDebugMode()) {
                    Log::debug('AbstractApiValidationTrait::addTimeAndSignature "urlencoded $string:" ( ' . $string . ' )');
                }
            }
            $hashkey = isset($pValues['hashkey']) ? $pValues['hashkey'] : null;
            $pValues['sig'] = $this->_buildHash($string, $hashkey);
            if ($this->_hasDebugMode()) {
                Log::debug('AbstractApiValidationTrait::addTimeAndSignature $pValues[\'sig\']:" ( ' . $pValues['sig'] . ' )');
            }
        }

        return $pValues;
    }

    /*
     |--------------------------------------------------------------------------
     | Protected methods
     |--------------------------------------------------------------------------
     */
    protected function getHashSecret($pKey = null)
    {
        if (is_null($pKey)) {
            $key = is_laravel() ? config('ypa-abstractapi.hashsecret') : env('YPA_ABSTRACT_API_HASH_SECRET');
        } else {
            $key = env($pKey);
        }
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::getHashSecret ( ' . $key . ' )');
        }
        return $key;
    }

    /*
     |--------------------------------------------------------------------------
     | Private methods
     |--------------------------------------------------------------------------
     */
    private function _buildSigFromValues($pValues, $pPrefix = '')
    {
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_buildSigFromValues "given values:"', $pValues);
        }
        $string = '';

        // Sort values alphabetic
        // @url http://php.net/manual/en/function.ksort.php
        ksort($pValues);

        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_buildSigFromValues "alphabetic sorted values:"', $pValues);
        }

        foreach ($pValues as $key => $value) {

            if ($key != 'sig') {
                switch ($key) {
                    default:
                        $prefix = empty($pPrefix) ? $pPrefix : $pPrefix . '-';
                        if (is_array($value)) {
                            $string .= $this->_buildSigFromValues($value, $prefix . $key);
                        } else {
                            $string .= $prefix . $key . '=' . json_encode(strval($value));
                        }
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

    private function _validateTimeValue($pTime)
    {
        $date = Carbon::createFromTimestampUTC($pTime);

        $now = Carbon::now();
        $now->setTimezone('UTC');

        $difference = $now->diffInSeconds($date) < $this->_getMaxValidTimeDifference();

        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_validateTimeValue given $pTime:" ( ' . $pTime . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "$date [UTC]:" ( ' . $date . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "$now [UTC]:" ( ' . $now . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "$now->diffInSeconds($date):" ( ' . $now->diffInSeconds($date) . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "config time difference:" ( ' . $this->_getMaxValidTimeDifference() . ' )');
            Log::debug('AbstractApiValidationTrait::_validateTimeValue "returned value:" ( ' . $difference . ' )');
        }

        return $difference;
    }

    private function _validateHashedValue($pString, $pSig, $pKey = null, $pUrlencode = false)
    {
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_validateHashedValue "given $pSig:" ( ' . $pSig . ' )');
            Log::debug('AbstractApiValidationTrait::_validateHashedValue "urlencode values?:" ( ' . $pUrlencode . ' )');
            Log::debug('AbstractApiValidationTrait::_validateHashedValue "given $pString:" ( ' . $pString . ' )');
            Log::debug('AbstractApiValidationTrait::_validateHashedValue "given $pKey:" ( ' . $pKey . ' )');
        }

        if ($pUrlencode !== false) {
            $pString = urlencode($pString);
            if ($this->_hasDebugMode()) {
                Log::debug('AbstractApiValidationTrait::_validateHashedValue "urlencoded string:" ( ' . $pString . ' )');
            }
        }

        $value = $this->_buildHash($pString, $pKey);

        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_validateHashedValue "$value:" ( ' . $value . ' )');
        }

        return $pSig === $value;
    }

    private function _buildHash($pString, $pKey = null)
    {
        if ($this->_hasDebugMode()) {
            Log::debug('AbstractApiValidationTrait::_buildHash "$pString:" ( ' . $pString . ' )');
            Log::debug('AbstractApiValidationTrait::_buildHash "$pKey:" ( ' . $pKey . ' )');
        }

        return hash(strval($this->_getHashType()), $pString . $this->getHashSecret($pKey));
    }

    private function _getMaxValidTimeDifference()
    {
        if ($this->_hasDebugMode()) {
            if (is_laravel()) Log::debug('AbstractApiValidationTrait::_getMaxValidTimeDifference ( ' . config('ypa-abstractapi.timedifferences') . ' )');
            if (is_lumen()) Log::debug('AbstractApiValidationTrait::_getMaxValidTimeDifference ( ' . env('YPA_ABSTRACT_API_TIME_DIFFERENCES') . ' )');
        }
        return is_laravel() ? config('ypa-abstractapi.timedifferences') : env('YPA_ABSTRACT_API_TIME_DIFFERENCES');
    }

    private function _getHashType()
    {
        if ($this->_hasDebugMode()) {
            if (is_laravel()) Log::debug('AbstractApiValidationTrait::_getHashType ( ' . config('ypa-abstractapi.hashtype') . ' )');
            if (is_lumen()) Log::debug('AbstractApiValidationTrait::_getHashType ( ' . env('YPA_ABSTRACT_API_HASHTYPE') . ' )');
        }

        if (is_lumen()) {
            return env('YPA_ABSTRACT_API_HASHTYPE');
        }
        return config('ypa-abstractapi.hashtype');
    }

    private function _hasDebugMode()
    {
        $value = is_laravel() ? (strtolower(strval(config('ypa-abstractapi.debug'))) === 'true') : (strtolower(strval(env('YPA_ABSTRACT_API_DEBUG'))) === 'true');
        return (boolean)$value;
    }

    private function _isEncodingEnabled()
    {
        $isDisabled = is_laravel() ? (strtolower(strval(config('ypa-abstractapi.disable'))) === 'true') : (strtolower(strval(env('YPA_ABSTRACT_API_DISABLE'))) === 'true');

        $isProduction = app()->environment('production');
        // !isDisabled = enabled
        // if enabled or when it's the production environment it always returns true
        return !$isDisabled || $isProduction;
    }

}
