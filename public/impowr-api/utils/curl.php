<?php

/**
 * Curl Connection with api call
 * @param mixed $method
 * @param mixed $url
 * @param mixed $data
 * @throws \Exception
 * @return mixed
 **/

function callAPI ($method, $url, $data)
{
    try {
        //curl initialization
        $ch = curl_init ();

        //check if initialization had gone wrong
        if ( $ch === false ) {
            throw new Exception('failed to initialize');
        }

        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ($ch, CURLOPT_VERBOSE, 0);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt ($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt ($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt ($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query ($data, '', '&'));

        // EXECUTE:
        $result = curl_exec ($ch);

        $httpCode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
        if ( $httpCode != 200 ) {
            logs ("Return code is {$httpCode} \n"
                . curl_error ($ch), true);
        }

        if ( ! $result ) {
            logs ('Curl exec failed without result', true);
            throw new Exception(curl_error ($ch), curl_errno ($ch));
        }

        curl_close ($ch);
        $res = json_decode ($result, true);
        return $res;

    }
    catch ( Exception $e ) {
        //trigger_error (sprintf ('Curl failed with error #%d: %s', $e->getCode (), $e->getMessage ()), E_USER_ERROR);
        logs (sprintf ('Curl failed with error #%d: %s', $e->getCode (), $e->getMessage ()), true);
        return false;
    }
}
?>