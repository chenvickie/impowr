<?php

function getInsertFieldValue ($string)
{
    if ( is_array ($string) && (count ($string) > 0) ) {
        return '';
    } elseif ( isset ($string) && ($string !== null) ) {
        return ms_escape_string ($string);
    } else {
        return '';
    }
}

function convertParams2QueryValues ($params, $excludes = [])
{
    global $fkeys;

    $updated = [];
    foreach ($params as $index => $value) {
        if ( is_array ($value) || gettype ($value) == 'array' ) {
            $updated[$index] = ''; //empty array
        } else if ( ! in_array ($index, $excludes) ) {
            if ( strpos ($value, "fk_") !== false ) {
                $value           = str_replace ("fk_", "", $value);
                $updated[$index] = $fkeys[$value] ? $fkeys[$value] : '0000'; //replace fkey
            } else {
                $updated[$index] = getInsertFieldValue ($value); //escape string
            }
        } else {
            $updated[$index] = $value; //doesnt need to change
        }
    }
    return $updated;
}

function ms_escape_string ($data)
{
    if ( ! isset ($data) or empty ($data) ) {
        return '';
    }

    if ( is_numeric ($data) ) {
        return $data;
    }

    $non_displayables = array(
        '/%0[0-8bcef]/',
        // url encoded 00-08, 11, 12, 14, 15
        '/%1[0-9a-f]/',
        // url encoded 16-31
        '/[\x00-\x08]/',
        // 00-08
        '/\x0b/',
        // 11
        '/\x0c/',
        // 12
        '/[\x0e-\x1f]/',
        // 14-31
        '/\27/',
    );
    foreach ($non_displayables as $regex) {
        $data = preg_replace ($regex, '', $data);
    }
    $reemplazar = array( '"', "'" ); //array('"',"'",'=');
    $data       = str_replace ($reemplazar, "*", $data);
    return $data;
}

function logs ($msg, $inFile = true, $inPrint = false, $inReport = false)
{
    if ( $msg == '' ) {
        return false;
    }

    if ( $inFile ) {
        error_log ($msg . "\n");
    }

    if ( $inPrint ) {
        print_r ($msg . "\n");
    }
    return true;
}

function utf8ize ($mixed)
{
    if ( is_array ($mixed) ) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize ($value);
        }
        $mixed = array_change_key_case ($mixed, CASE_UPPER);
    } elseif ( is_string ($mixed) ) {
        return mb_convert_encoding ($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}


function emptyDataFields ($data = [], $blankFields = [])
{
    if ( count ($blankFields) > 0 ) {
        for ( $i = 0; $i < count ($data); $i++ ) {
            if ( isset ($data[$i]) ) {
                foreach ($data[$i] as $k => $value) {
                    if ( in_array ($k, $blankFields) && $k != "record_id" ) {
                        $data[$i][$k] = "";
                    }
                }
            }
        }
    }
    return $data;
}

function isPastToday ($aDate)
{
    // $date  = new DateTime($aDate);
    $today  = date ("Y-m-d H:i:s");
    $isPast = $aDate < $today;
    return $isPast;
}