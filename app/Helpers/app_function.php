<?php

date_default_timezone_set('Africa/Lagos');

define('NGN', '<span style="font-size: 13px;">₦</span>');
define('NGN_RAW', '₦');

$live = 'false';
define('LIVE', $live);

if ($live == 'true') {
//    define live constants
//    define('TED', 'FLW34X');
} else {
    //    define non-live constants
//    define('TED', 'https://hi.flutterwave.com');

}

/**
 * @param $query
 */
function die_dump($query, $dont_exit = true)
{
    echo '<pre>';
    if (is_array($query)) {
        print_r($query);
    } else {
        var_dump($query);
    }
    if ($dont_exit) {
        exit();
    }
}

/**
 * @param $string
 * @param $your_desired_width
 * @return string
 */
function truncateString($string, $your_desired_width)
{
    if (strlen($string) <= $your_desired_width)
        return $string;
    $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
    $parts_count = count($parts);

    $length = 0;
    $last_part = 0;
    for (; $last_part < $parts_count; ++$last_part) {
        $length += strlen($parts[$last_part]);
        if ($length > $your_desired_width) {
            break;
        }
    }
    return implode(array_slice($parts, 0, $last_part)) . '...';
}



function encrypt_decrypt($action, $string)
{
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = '47366g@#we22';
    $secret_iv = 'yureihbrvwhejb';

    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function search_query_constructor($searchString, $col)
{
    $dataArray = (array_filter(explode(" ", trim($searchString))));
    $constructor_sql = "(";
    if (count($dataArray) < 1) {
        return " 1 ";
    }
    if (is_array($col)) {
        foreach ($col as $col_name) {
            if ($col_name !== $col[0]) {
                $constructor_sql .= " OR ";
            }
            for ($i = 0; $i < count($dataArray); $i++) {
                if (count($dataArray) - 1 === $i) {
                    $constructor_sql .= "$col_name LIKE '%{$dataArray[$i]}%' ";
                } else {
                    $constructor_sql .= "$col_name LIKE '%{$dataArray[$i]}%' OR ";
                }
            }
        }
    } else {
        for ($i = 0; $i < count($dataArray); $i++) {
            if (count($dataArray) - 1 === $i) {
                $constructor_sql .= "$col LIKE '%{$dataArray[$i]}%' ";
            } else {
                $constructor_sql .= "$col LIKE '%{$dataArray[$i]}%' OR ";
            }
        }
    }
    $constructor_sql .= ")";
    return $constructor_sql;
}

function multi_unset($array, $keys)
{
    if (is_array($array)) {
        foreach ($keys as $key) {
            unset($array[$key]);
        }

        return $array;

    } else {
        return null;
    }
}

function select_array_indexes($array, $keys)
{
    $val = [];
    if (is_array($array)) {
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $val[$key] = $array[$key];
            }

        }

        return $val;

    } else {
        return null;
    }
}


function sendEmail($message_data, $subject, $from, $to, $type = null)
{
    if (!$type) {
        $type = "default";
    }
    $info['message'] = $message_data;
    $info['from'] = $from;
    $info['email'] = $to;
    $info['subject'] = $subject;


    \Illuminate\Support\Facades\Mail::send('emails.' . $type, compact('message_data', 'info'), function ($message) use ($info) {
        $message->from("noreply@" . env('APP_NAME', 'Kwara State Government') . ".com", env('APP_NAME', 'Kwara State Government'));
        $message->bcc($info['email'])->subject($info['subject']);
    });

}

function problemResponse($message = null, $status_code = null, $request = null, $trace = null)
{
    $code = ($status_code != null) ? $status_code : "404";
    $body = [
        'message' => "$message",
        'code' => $code,
        'status_code' => $code,
        'status' => false
    ];


    if (!is_null($request)) {
        save_log($request, $body);
        if ($code == "500" && !is_null($trace)) {

            $message = 'URL : ' . $request->fullUrl() .
                '<br /> METHOD: ' . $request->method() .
                '<br /> DATA_PARAM: ' . json_encode($request->all()) .
                '<br /> RESPONSE: ' . json_encode($body) .
                '<br /> Trace Message: ' . $trace->getMessage() .
                '<br /> <b> Trace: ' . json_encode($trace->getTrace()) . "</b>";
            if (env("SEND_EMAIL_ON_500_ERR", false))
                sendEmail($message, 'API ERROR ALERT', env('APP_NAME', 'Kwara State Government'), env("500_ERR_EMAIL_RECIPIENT", 'george@initsng.com'));
        }
    }


    return response()->json($body)->setStatusCode("$code");
}

function validResponse($message = null, $data = [], $request = null)
{
    $body = [
        'message' => "$message",
        'data' => $data,
        'status' => true,
        'status_code' => 200,
    ];

    if (!is_null($request)) {
        save_log($request, $body);
    }

    return response()->json($body);
}

function save_log($request, $response)
{
    return \App\Models\ApiLog::create([
        'url' => $request->fullUrl(),
        'method' => $request->method(),
        'data_param' => json_encode($request->all()),
        'response' => json_encode($response),
    ]);
}


function generic_logger($fullUrl = null, $method = null, $param, $response)
{
    \App\Models\ApiLog::create([
        'url' => $fullUrl,
        'method' => $method,
        'data_param' => json_encode($param),
        'response' => json_encode($response),
    ]);
}


function isValidEmail($email)
{
    return (boolean)filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email);
}

function generatePayerId($length = 8)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
//check if the string is selected
    $string_rand = \App\Models\PropertyOwner::pluck('payer_id')->toArray();

    if (in_array($randomString, $string_rand)) {
        $randomString = generatePayerId();
    }

    return $randomString;
}

function generateRandomString($length = 8)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

function ddd($var)
{
    $dump = $var;
    print_r($dump);
    exit;

}

function getcp()
{
    return session()->get('current_role_array');
}

function hasRoleAndPermission($permission_name)
{
    if (!is_null(session()->get('current_role_array')) && !empty(session()->get('current_role_array'))) {
        $currentSelectedRole = session()->get('current_role_array');

        $cPermissions = PermissionRole::with(['permission'])->where('role_id', $currentSelectedRole['id'])->get()->toArray();
        $permissionList = [];
        foreach ($cPermissions as $cPermission) {
            $permissionList[$cPermission['permission_id']] = trim($cPermission['permission']['name']);
        }

        if (in_array(trim($permission_name), $permissionList)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Formats a JSON string for pretty printing
 *
 * @param string $json The JSON to make pretty
 * @param bool $html Insert nonbreaking spaces and <br />s for tabs and linebreaks
 * @return string The prettified output
 * @author Jay Roberts
 */
function _format_json($json, $html = false)
{
    $tabcount = 0;
    $result = '';
    $inquote = false;
    $ignorenext = false;
    if ($html) {
        $tab = "&nbsp;&nbsp;&nbsp;&nbsp;";
        $newline = "<br/>";
    } else {
        $tab = "\t";
        $newline = "\n";
    }
    for ($i = 0; $i < strlen($json); $i++) {
        $char = $json[$i];
        if ($ignorenext) {
            $result .= $char;
            $ignorenext = false;
        } else {
            switch ($char) {
                case '[':
                case '{':
                    $tabcount++;
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case ']':
                case '}':
                    $tabcount--;
                    $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                    break;
                case ',':
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case '"':
                    $inquote = !$inquote;
                    $result .= $char;
                    break;
                case '\\':
                    if ($inquote) $ignorenext = true;
                    $result .= $char;
                    break;
                default:
                    $result .= $char;
            }
        }
    }
    return $result;
}


if (!function_exists('array_group_by')) {
    /**
     * Groups an array by a given key.
     *
     * Groups an array into arrays by a given key, or set of keys, shared between all array members.
     *
     * Based on {@author Jake Zatecky}'s {@link https://github.com/jakezatecky/array_group_by array_group_by()} function.
     * This variant allows $key to be closures.
     *
     * @param array $array The array to have grouping performed on.
     * @param mixed $key,... The key to group or split by. Can be a _string_,
     *                       an _integer_, a _float_, or a _callable_.
     *
     *                       If the key is a callback, it must return
     *                       a valid key from the array.
     *
     *                       If the key is _NULL_, the iterated element is skipped.
     *
     *                       ```
     *                       string|int callback ( mixed $item )
     *                       ```
     *
     * @return array|null Returns a multidimensional array or `null` if `$key` is invalid.
     */
    function array_group_by(array $array, $key)
    {
        if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
            trigger_error('array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR);
            return null;
        }
        $func = (!is_string($key) && is_callable($key) ? $key : null);
        $_key = $key;
        // Load the new array, splitting by the target key
        $grouped = [];
        foreach ($array as $value) {
            $key = null;
            if (is_callable($func)) {
                $key = call_user_func($func, $value);
            } elseif (is_object($value) && isset($value->{$_key})) {
                $key = $value->{$_key};
            } elseif (isset($value[$_key])) {
                $key = $value[$_key];
            }
            if ($key === null) {
                continue;
            }
            $grouped[$key][] = $value;
        }
        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $params = array_merge([$value], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $params);
            }
        }
        return $grouped;
    }
}


function hasPermissionsForCurrentRole($user_id)
{
    if (!is_null(session()->get('current_role_array')) && !empty(session()->get('current_role_array'))) {
        $currentSelectedRole = session()->get('current_role_array');

        $cPermissions = PermissionRole::where('role_id', $currentSelectedRole['id'])->count();
        if ($cPermissions) {
            return true;
        }
    }

    return false;

}

function debug(Exception $e, $return = false)
{
    $error_key = "KWSG-" . microtime();
    $err = (['error_key' => $error_key, 'error_message' => $e->getMessage(), 'error_trace_message' => $e->getTraceAsString()]);


    \Illuminate\Support\Facades\Log::info($err);
    if ($return)
        return $error_key;

    dd($err);
}

function clean_up_string($string)
{
    $string = str_replace(['%', '^', '#', '!', '@', '$', '*', '1', '2', '3', '4', '5', '6', '7', '8', '9'], '', $string);
    // trim
    $string = array_filter(array_map(static function ($entry) {
        $entry = trim($entry);
        return $entry;
    }, explode(' ', trim(strtolower($string)))));
    $string = strtoupper(implode(' ', $string));

    return $string;
}

function array_keys_prepend_text($arr, $prepend_text, $separator = "_", $uppercase_final_key_case = true)
{
    $holder = [];
    $prepend_text = clean_up_string($prepend_text);
    foreach ($arr as $key => $val) {
        $new_key = "$prepend_text" . "$separator" . $key;

        if ($uppercase_final_key_case === true) {
            $new_key = strtoupper($new_key);
        } elseif ($uppercase_final_key_case === false) {
            /** @var String $new_key */
            $new_key = strtolower($new_key);
        }

        $holder[$new_key] = $val;
    }


    return $holder;
}

function testNIBSSsync()
{
    $notice = \App\Models\Notice::where('id', 5)->whereHas('property')->first();

    if (!$notice) dd("Invalid Notice");


//    $worked = \App\Meta\NoticeHelper::post_notice_to_nibbs($notice, null);
//
//    if ($worked) echo "posted successfully";

    $notice->land_charge = $notice->land_charge + 200;
    $notice->save();
    echo "now update\n";
    $updated = \App\Meta\NoticeHelper::update_notice_in_nibbs($notice, null);

    if ($updated) {
        echo "updated successfully\n";
    } else {
        echo "update unsuccessfully\n";
    }

}

function count_change($money, $coins)
{
//    $counter = 0;
//
//    foreach ($coins as $coin_) {
//        //pick the first number
//        $p = $coin_;
//        // iterate the whole change array
//        for ($i = 0; $i < count($coins); $i++) {
//            // pick the index number to add to the first number
//            $nth_nuber = $coins[$i];
//
//            //add the nth number to the first number Y times till you get  Y
//            for ($j = 1; $j <= $money; $j++) {
//                $q = ($nth_nuber * $j) + ($p);
//
//                // handle manipulation when the value is higher than Y
//                //and the next index of the coin is available
//                if ($q > $money && isset($coins[$i + 1])) {
//                    // take the coin index to the next and continue the summation
//                    $nth_nuber = $coins[$i + 1];
//
//                    // deduct the excess from the coin
//                    $p = $p - $nth_nuber;
//                    continue;
//                }
//                //and the next index of the coin is not available
//                if ($q > $money && !isset($coins[$i + 1])) {
//                    $nth_nuber = $coins[$i];
//                    continue;
//                }
//
//                // break out if money value is gotten
//                if ($q == $money) {
//                    $counter++;
//                    break;
//                }
//
//
//            }
//
//        }
//    }
//
//    return $counter;
}