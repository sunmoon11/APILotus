<?php
/**
 * Created by CR524
 */

namespace App\Lib;

include __DIR__ . '/simple_html_dom.php';

class FuriruApi
{
    const API_HOST_URL = "https://api.fril.jp";
    const WEB_HOST_URL = "https://web.fril.jp";
    const API_HOST = "Host: api.fril.jp";
    const WEB_HOST = "Host: web.fril.jp";
    const USER_AGENT = 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 10_1 like Mac OS X) AppleWebKit/602.2.14 (KHTML, like Gecko) Mobile/14B72 Fril/6.0.0.1';
    const BOUNDARY_STRING = 'Boundary+EE84562EC5627BF5';

    function __construct()
    {
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    private function getHttpHeader2Array($rawheader)
    {
        $header_array = array();
        $header_rows = explode("\n", $rawheader);
        for ($i = 0; $i < count($header_rows); $i++) {
            $fields = explode(":", $header_rows[$i]);

            if ($i != 0 && !isset($fields[1])) {
                if (substr($fields[0], 0, 1) == "\t") {
                    end($header_array);
                    $header_array[key($header_array)] .= "\r\n\t" . trim($fields[0]);
                } else {
                    end($header_array);
                    $header_array[key($header_array)] .= trim($fields[0]);
                }
            } else {
                $field_title = trim($fields[0]);
                if (!isset($header_array[$field_title])) {
                    if (!empty($fields[1])) {
                        $header_array[$field_title] = trim($fields[1]);
                    }
                } else if (is_array($header_array[$field_title])) {
                    $header_array[$field_title] = array_merge($header_array[$fields[0]], array(trim($fields[1])));
                } else {
                    $header_array[$field_title] = array_merge(array($header_array[$fields[0]]), array(trim($fields[1])));
                }
            }
        }
        return $header_array;
    }

    private function getCsrfToken($url)
    {
        $token = '';
        $html = file_get_html($url);

        foreach ($html->find('meta[name="csrf-token"]') as $e) {
            $token = $e->content;
        }

        $html->clear();
        unset($html);

        return $token;
    }

    private function getCsrfTokenNCookie($url)
    {
        $csrfToken = '';

        // Get csrf-token and Cookie
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $headers = array();
        $headers[] = self::API_HOST;
        $headers[] = self::USER_AGENT;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            $err = '{"result": "Error", "message": ' . $http . '}';
            curl_close($curl);
            return $err;
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
//        $body = substr($response, $header_size);

        $data = self::getHttpHeader2Array($header);
        $str = $data['Set-Cookie'];

        $pos = strpos($str, '; path=/');
        $cookie = substr($str, 0, $pos);

//        echo "<br>Cookie(1st): " . $cookie . " " . "<br>"; // TODO: For debugging, Must be deleted

        $html = str_get_html($response);

        foreach ($html->find('meta[name="csrf-token"]') as $e) {
            $csrfToken = $e->content;
        }

        $html->clear();
        unset($html);

        curl_close($curl);

        $data['cookie'] = $cookie;
        $data['csrfToken'] = $csrfToken;

        return $data;
    }

    private function signinFril($email, $password, $csrfToken, $cookie)
    {
        $cookieTail = '; __zlcmid=dKfxXE0aktI7wb; __utmt=1; __utmz=141037413.1477678516.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __utmc=141037413; __utmb=141037413.1.10.1477678516; __utma=141037413.1206047598.1477678516.1477678516.1477678516.1';
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/users/sign_in',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'utf8=%E2%9C%93' . '&authenticity_token=' . $csrfToken . '&user[email]=' . $email .
                '&user[password]=' . $password . '&commit=メールアドレスでログイン' .
                '&installation_id=32A24F11-9972-4C6F-8618-42E0F035B324&os_type=ios' .
                '&installation_id=32A24F11-9972-4C6F-8618-42E0F035B324&os_type=ios' .
                '&installation_id=32A24F11-9972-4C6F-8618-42E0F035B324&os_type=ios' .
                '&installation_id=32A24F11-9972-4C6F-8618-42E0F035B324&os_type=ios' .
                '&installation_id=32A24F11-9972-4C6F-8618-42E0F035B324&os_type=ios' .
                '&installation_id=32A24F11-9972-4C6F-8618-42E0F035B324&os_type=ios',
            CURLOPT_HTTPHEADER => array(
                self::API_HOST,
                "Content-Type: application/x-www-form-urlencoded",
                "Origin: " . self::API_HOST_URL,
                "Cookie: " . $cookie,
                "Connection: keep-alive",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                self::USER_AGENT,
                "Accept-Language: en-us",
                "Accept-Encoding: gzip, deflate"
            ),
        ));

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (302 != $http) {
            $err = '{"result": "Error", "message": "Must be the status code 302("' . curl_error($curl) . ')"}';
            curl_close($curl);
            return $err;
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $data = self::getHttpHeader2Array($header);
        if (empty($data['Set-Cookie'])) {
            $err = '{"result": "Error", "message": "Cannot get the Set-Cookie"}';
            curl_close($curl);
            return $err;
        }

        $str = $data['Set-Cookie'][1];
        $pos = strpos($str, '; path=/');
        $cookie = substr($str, 0, $pos);

//        echo "<br>Cookie(2nd): $cookie <br>"; // TODO: For debugging, Must be deleted

        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $err = '{"result": "Error", "message": ' . curl_error($curl) . '}';
            return $err;
        }

        $data['cookie'] = $cookie;

        return $data;
    }

    private function getAuthToken($url, $cookie)
    {
        $token = '';
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $headers = array();
        $headers[] = self::API_HOST;
        $headers[] = self::USER_AGENT;
        $headers[] = "Cookie: request_method=POST; " . $cookie;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            $err = '{"result": "Error", "message": ' . curl_error($curl) . '}';
            curl_close($curl);
            return $err;
        }


        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
//        $body = substr($response, $header_size);

        $data = self::getHttpHeader2Array($header);
        $str = $data['Set-Cookie'];

        $pos = strpos($str[0], '; path=/');
        $cookie = substr($str[0], 0, $pos);

        $html = str_get_html($response);

        foreach ($html->find('div[id="authentication_token"]') as $e) {
            $token = $e->innertext;
        }

        $html->clear();
        unset($html);

        curl_close($curl);

        $data = '{"result": "OK", "token": "' . $token . '", "cookie": "' . $cookie . '"}';

        return $data;
    }

    public function login($email, $password)
    {
        $url = self::API_HOST_URL . "/start";

        // Step 1: Get csrf-token and cookie
        $data = self::getCsrfTokenNCookie($url);
        $csrfToken = $data['csrfToken'];
        $cookie = $data['cookie'];

        $csrfToken = urlencode($csrfToken); // VERY IMPORTANCE

        // Step 2: Sign-in and get cookie
        $data = self::signinFril($email, $password, $csrfToken, $cookie);
        if (empty($data['cookie'])) {
            $err = '{"result": "Error", "message": "Failed to sign in"}';
            return $err;
        }
        $cookie = $data['cookie'];

        // Step 3: Get token with cookie taken by step 2
        $authToken = self::getAuthToken($url, $cookie);
        if ('' == $authToken) {
            $err = '{"result": "Error", "message": "Cannot get the authentication token from Fril"}';
            return $err;
        }

        return $authToken;
    }

    public function getUserSession1()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, 'https://fril.jp' . '/cp/second_cm_sell_cp?app=true&code=f68b1c9b63b09e1bf9319cffb3f3129a&from=pop2');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $headers = array();
        $headers[] = 'Host: fril.jp';
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (302 != $http) {
            curl_close($curl);
            $data['result'] = false;
            return $data;
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);

        $data = self::getHttpHeader2Array($header);
        $str = $data['Set-Cookie'];

        $pos = strpos($str, '; domain=');
        $cookie = substr($str, 0, $pos);

        curl_close($curl);

        $data['result'] = true;
        $data['cookie'] = $cookie;

        return $data;
    }

    public function getUserSession2($preCookie)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, 'https://fril.jp' . '/cp/second_cm_sell_cp?app=true');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $headers = array();
        $headers[] = 'Host: fril.jp';
        $headers[] = 'Cookie: ' . $preCookie;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);

        $data = self::getHttpHeader2Array($header);
        $str = $data['Set-Cookie'];

        $pos = strpos($str, '; domain=');
        $cookie = substr($str, 0, $pos);

        $pos = strpos($str, 'expires=');
        $pos1 = strpos($str, '; secure');
        $expires = substr($str, $pos + 13, 11); // 13 = strlen('expires=Wed, ');

        curl_close($curl);

        $data['result'] = true;
        $data['cookie'] = $cookie;
        $data['expires'] = $expires;

        return $data;
    }

    /*
     * Get the user's profile including shop information
     */
    public function getUserProfile($authToken, $cookie)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::API_HOST_URL . '/api/v2/users?auth_token=' . $authToken);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = self::API_HOST;
        $headers[] = "Cookie: " . $cookie;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getUserShopProfile($authToken, $userId)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::API_HOST_URL . '/api/v3/shop/show?user_id=' . $userId . '&auth_token=' . $authToken);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = self::API_HOST;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function updateShopProfile($authToken, $shopName, $screenName, $aliasName, $bio)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/v3/shop/update',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'auth_token=' . $authToken . '&shop_name=' . $shopName . '&screen_name=' . $screenName .
                '&alias_name=' . $aliasName . '&bio=' . $bio,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /*
     * $image: 640*640
     */
    public function updateShopProfileImage($authToken, $image)
    {
        $imageData = file_get_contents($image);
        if (null === $imageData) {
            return '{"error": "Cannot get the contents of image"}';
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/v3/shop/image',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"auth_token\"\r\n\r\n" . $authToken . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"image\"; filename=\"image.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData . "\r\n" .
                "--" . self::BOUNDARY_STRING . "--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=" . self::BOUNDARY_STRING
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    // TODO:
    public function updateUserProfile()
    {

    }

    /*
     * $image: 120*120
     */
    public function updateUserProfileImage($authToken, $image)
    {
        $imageData = file_get_contents($image);
        if (null === $imageData) {
            $err = '{"result": "Error", "message": "Cannot get the contents of image"}';
            return $err;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/v3/user/image',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"auth_token\"\r\n\r\n" . $authToken . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"image\"; filename=\"image.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData . "\r\n" .
                "--" . self::BOUNDARY_STRING . "--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=" . self::BOUNDARY_STRING
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    /*
     * Submit/edit the exhibit
     * If item_id of $exhibit is not 0, submit. otherwise, edit
     */
    public function exhibit($cookie, $exhibit)
    {
        $brand = '';
        $delivery_method = '';
        if (0 != $exhibit->brand) {
            $brand = '&brand=' . $exhibit->brand;
        }

        if (0 != $exhibit->delivery_method) {
            $delivery_method = '&delivery_method=' . $exhibit->delivery_method;
        }

        $updateRequest = false; // edit
        if (0 != $exhibit->item_id) {
            $updateRequest = true;
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/items/request',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'detail=' . $exhibit->detail .
                '&category=' . $exhibit->category .
                '&auth_token=' . $exhibit->auth_token .
                $brand .
                $delivery_method .
                '&delivery_date=' . $exhibit->delivery_date .
                '&carriage=' . $exhibit->carriage .
                '&item_id=' . $exhibit->item_id .
                '&title=' . $exhibit->title .
                '&size=' . $exhibit->size .
                '&size_name=' . $exhibit->size_name .
                '&request_required=' . ((true == $exhibit->request_required)?1:0) .
                '&sell_price=' . $exhibit->sell_price .
                '&delivery_area=' . $exhibit->delivery_area .
                '&status=' . $exhibit->status .
                '&p_category=' . $exhibit->p_category,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "Cookie: " . $cookie
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $response;
        }

        $jsonData = json_decode($response, true);
        $item_result = $jsonData['item_result'];
        if (false == $item_result) {
            $err = '{"result": "Error", "message": "Failed to submit/edit an exhibit"}';
            return $err;
        }
        $item_id = $jsonData['item_id'];
        $exhibit->item_id = $item_id;

        $imageId = '';
        // Request images
        for ($i = 0; $i < $exhibit->image_total_num; $i++) {
            $imageData = file_get_contents($exhibit->image[$i]);
            if (true == $imageData) {
                $imageFieldWithBinary = "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"image\"; filename=\"image.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData . "\r\n";
            } else {
                $err = '{"result": "Error", "message": "Cannot get the contents of image"}';
                return $err;
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            $url = self::API_HOST_URL . ((true == $updateRequest) ? '/api/items/update_img' : '/api/items/request_img');
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>
                    "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"auth_token\"\r\n\r\n" . $exhibit->auth_token . "\r\n" .
                    "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"current_num\"\r\n\r\n" . ($i + 1) . "\r\n" .
                    "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"item_id\"\r\n\r\n" . $exhibit->item_id . "\r\n" .
                    "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"total_num\"\r\n\r\n" . $exhibit->image_total_num . "\r\n" .
                    $imageFieldWithBinary .
                    "--" . self::BOUNDARY_STRING . "--",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: multipart/form-data; boundary=" . self::BOUNDARY_STRING,
                    "Cookie: " . $cookie
                ),
            ));

            $response = curl_exec($curl);

            $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if (200 != $http) {
                return $response;
            }

            $jsonData = json_decode($response, true);
            if (true != $jsonData["img_result"]) {
                $err = '{"result": "Error", "message": "Failed to submit/edit an exhibit"}';
                return $err;
            }
            if (0 == $i) {
                $imageId = $jsonData["img_id"];
            }
        }

        $data = '{"result": "OK", "item_id": ' . $exhibit->item_id . ', "photourl": ' . $imageId . '}';
        return $data;
    }

    public function deleteExhibit($authToken, $cookie, $itemId)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/items/delete',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'item_id=' . $itemId . '&auth_token=' . $authToken,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "Cookie: " . $cookie
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getExhibitItemsAllByUserId($authToken, $cookie, $includeSoldOut, $limit, $maxId, $userId)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::API_HOST_URL . '/api/v3/items/list?auth_token=' . $authToken .
            '&include_sold_out=' . $includeSoldOut . '&limit=' . $limit . '&max_id=' . $maxId . '&user_id=' . $userId);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = self::API_HOST;
        $headers[] = "Cookie: " . $cookie;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /*
     * status: {'selling', 'trading', 'sold'}
     */
    public function getExhibitItem($authToken, $cookie, $status)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::API_HOST_URL . '/api/v3/items/sell?auth_token=' . $authToken . '&status=' . $status);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = self::API_HOST;
        $headers[] = "Cookie: " . $cookie;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getExhibitItemDetail($authToken, $cookie, $itemId)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::API_HOST_URL . '/api/v3/items/show?auth_token=' . $authToken . '&item_id=' . $itemId);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = self::API_HOST;
        $headers[] = "Cookie: " . $cookie;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function createExhibitComment($authToken, $cookie, $item_id, $comment)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/v2/comments/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'item_id=' . $item_id . '&comment=' . $comment . '&auth_token=' . $authToken,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "Cookie: " . $cookie
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getExhibitComment($authToken, $cookie, $itemId)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::API_HOST_URL . '/api/v2/comments?auth_token=' . $authToken . '&item_id=' . $itemId);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = self::API_HOST;
        $headers[] = "Cookie: " . $cookie;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function userConfirmed($authToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/user/confirmed',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'auth_token=' . $authToken,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function transaction($authToken, $webSession, $itemId)
    {
        $csrfToken = '';

        // Get csrf-token and Cookie
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::WEB_HOST_URL . '/transaction?item_id=' . $itemId . '&auth_token=' . $authToken);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $headers = array();
        $headers[] = self::API_HOST;
        if (false == is_null($webSession)) {
            $headers[] = "Cookie: " . $webSession;
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (302 != $http) {
            curl_close($curl);
            $data['result'] = false;
            return $data;
        }

        if (false == is_null($webSession)) {
            curl_close($curl);

            $data['result'] = true;
            $data['cookie'] = $webSession; // We can request the (fril)/v2/sale/shipping/item :)

            return $data;
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);

        $data = self::getHttpHeader2Array($header);
        $str = $data['Set-Cookie'];

        $pos = strpos($str, '; path=/');
        $cookie = substr($str, 0, $pos);

        curl_close($curl);

        $data['result'] = true;
        $data['cookie'] = $cookie;

        return $data;
    }

    public function getShippingItem($itemId, $cookie)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::WEB_HOST_URL . '/v2/sale/shipping/item?item_id=' . $itemId);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $headers = array();
        $headers[] = self::WEB_HOST;
        $headers[] = self::USER_AGENT;
        $headers[] = "Cookie: " . $cookie;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);

        $frilWebCookie = null;
        $data = self::getHttpHeader2Array($header);
        if (isset($data['Set-Cookie'])) {
            $str = $data['Set-Cookie'];

            $pos = strpos($str, '; path=/');
            $frilWebCookie = substr($str, 0, $pos);
            if (false == $frilWebCookie) {
                $frilWebCookie = null;
            }
        }

        curl_close($curl);

        $html = str_get_html($response);

        $shipAuthenticityToken = '';
        $reviewAuthenticityToken = '';
        $commentAuthenticityToken = '';
        $commentAuthToken = '';
        $itemShippingStatus = '';
        $stayInStatus = '';
        $itemName = '';
        $price = '';
        $shippingFeeStr = '';
        $shippingFee = '';
        $statusTitle = '';
        $dataLimit = '';
        $userId = '';
        $orderId = '';
        $shippingAddr = '';
        $receipt = '';

        $messages = array();

        $itemImage = '';
        foreach ($html->find('div[class="item_image"]') as $e) {
            $itemImageStr = $e->style;
            $itemImage = substr($itemImageStr, strpos($itemImageStr, '(') + 1, -1);
        }

        foreach ($html->find('h2[class="item_name"]') as $e) {
            $itemName = $e->innertext;
        }

        foreach ($html->find('span[class="item_price"]') as $e) {
            $price = $e->innertext;
        }

        foreach ($html->find('span[class="shipping_fee"]') as $e) {
            $shippingFeeStr = $e->innertext;
        }

        foreach ($html->find('h5[class="status-title"]') as $e) {
            $statusTitle = $e->innertext;
        }

        foreach ($html->find('h5[class="status-title off"]') as $e) {
            $statusTitle = $e->innertext;
        }

        foreach ($html->find('span[class="large-text"]') as $e) {
            $dataLimit = $e->innertext;
        }

        foreach ($html->find('a') as $e) {
            if (false !== strpos($e->innertext, "商品の発送を通知する")) {
                $itemShippingStatus = 'waiting_shipping'; // 発送通知
            }

            if (false !== strpos($e->innertext, "評価を投稿する")) {
                $itemShippingStatus = 'review'; // 評価を投稿する
            }
        }

        foreach ($html->find('input[name="user_id"]') as $e) {
            $userId = $e->value;
        }

        foreach ($html->find('input[name="order_id"]') as $e) {
            $orderId = $e->value;
        }

        foreach ($html->find('form[id="ship-form"]') as $e) {
            foreach ($html->find('input[name="authenticity_token"]') as $e1) {
                $shipAuthenticityToken = $e1->value;
            }
        }

        foreach ($html->find('form[id="review-form"]') as $e) {
            foreach ($html->find('input[name="authenticity_token"]') as $e1) {
                $reviewAuthenticityToken = $e1->value;
            }
        }

        foreach ($html->find('form[id="comment-form"]') as $e) {
            foreach ($html->find('input[name="authenticity_token"]') as $e1) {
                $commentAuthenticityToken = $e1->value;
            }
            foreach ($html->find('input[name="auth_token"]') as $e1) {
                $commentAuthToken = $e1->value;
            }
        }

        foreach ($html->find('div[class="row"]') as $e) {
            if (false !== strpos($e->innertext, "col s12 balloon-right")) {
                $m = $e->innertext;
                if (false !== strpos($e->innertext, "/assets/common/")) {
                    $m = str_replace("/assets/common/", "/img/", $e->innertext);
                }
                array_push($messages, $m);
            }

            if (false !== strpos($e->innertext, "col s12 balloon-left")) {
                $m = $e->innertext;
                if (false !== strpos($e->innertext, "/assets/common/")) {
                    $m = str_replace("/assets/common/", "/img/", $e->innertext);
                }
                array_push($messages, $m);
            }

            foreach ($e->find('div[class="col s12"]') as $e1) {
                if (false !== strpos($e1->innertext, "〒")) {
                    $shippingAddr = $e1->innertext;
                } else if (false !== strpos($e1->innertext, "日:")) {
                    $stayInStatus = $e1->innertext;
                }
            }

            if (false !== strpos($e->innertext, "販売手数料")) {
                foreach ($e->find('div[class="col s6 right-align grey-text"]') as $e1) {
                    $shippingFee = $e1->innertext;
                }
            }

            if (false !== strpos($e->innertext, "受取代金")) {
                foreach ($e->find('div[class="col s6 right-align"]') as $e1) {
                    $receipt = $e1->innertext;
                }
            }
        }

        $data['frilWebCookie'] = $frilWebCookie;
        $data['shipAuthenticityToken'] = $shipAuthenticityToken;
        $data['reviewAuthenticityToken'] = $reviewAuthenticityToken;
        $data['commentAuthenticityToken'] = $commentAuthenticityToken;
        $data['commentAuthToken'] = $commentAuthToken;
        $data['itemImage'] = $itemImage;
        $data['itemName'] = $itemName;
        $data['price'] = $price;
        $data['shippingFeeStr'] = $shippingFeeStr;
        $data['shippingFee'] = $shippingFee;
        $data['receipt'] = $receipt;
        $data['shippingAddr'] = $shippingAddr;
        $data['statusTitle'] = $statusTitle;
        $data['dataLimit'] = $dataLimit;
        $data['itemShippingStatus'] = $itemShippingStatus;
        $data['stayInStatus'] = $stayInStatus;
        $data['itemId'] = $itemId;
        $data['userId'] = $userId;
        $data['orderId'] = $orderId;
        $data['messages'] = $messages;
        $data['result'] = true;

        return $data;
    }

    public function orderShipping($authToken, $itemId, $userId, $orderId, $frilWebCookie, $frilUserCookie)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::WEB_HOST_URL . '/v2/order/shipping',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'utf8=%E2%9C%93' .
                '&authenticity_token=' . urlencode($authToken) .
                '&item_id=' . $itemId .
                '&user_id=' . $userId .
                '&order_id=' . $orderId,
            CURLOPT_HTTPHEADER => array(
                self::WEB_HOST,
                "Content-Type: application/x-www-form-urlencoded",
                "Origin: " . self::WEB_HOST_URL,
                "Cookie: " . $frilWebCookie . ';' . $frilUserCookie,
                "Connection: keep-alive",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                self::USER_AGENT,
                "Accept-Language: en-us",
                "Accept-Encoding: gzip, deflate"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return '{"result": "OK"}';
    }

    public function orderReview($authToken, $itemId, $orderId, $tStatus, $review, $comment, $frilWebCookie, $frilUserCookie)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::WEB_HOST_URL . '/v2/order/review',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'utf8=%E2%9C%93' .
                '&authenticity_token=' . urlencode($authToken) .
                '&item_id=' . $itemId .
                '&order_id=' . $orderId .
                '&t_status=' . $tStatus .
                '&review=' . $review .
                '&comment=' . $comment,
            CURLOPT_HTTPHEADER => array(
                self::WEB_HOST,
                "Content-Type: application/x-www-form-urlencoded",
                "Origin: " . self::WEB_HOST_URL,
                "Cookie: " . $frilWebCookie . ';' . $frilUserCookie,
                "Connection: keep-alive",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                self::USER_AGENT,
                "Accept-Language: en-us",
                "Accept-Encoding: gzip, deflate"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response; // '{"result": "OK"}';
    }

    public function createSaleShippingComment($itemId, $frilWebCookie, $authenticityToken, $authToken, $orderId, $comment)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/order/comment/add',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'utf8=%E2%9C%93' .
                '&authenticity_token=' . urlencode($authenticityToken) .
                '&auth_token=' . urlencode($authToken) .
                '&order_id=' . $orderId .
                '&callback=callback' .
                '&comment=' . $comment,
            CURLOPT_HTTPHEADER => array(
                self::API_HOST,
                "Content-Type: application/x-www-form-urlencoded",
                "Origin: " . self::WEB_HOST_URL,
                "Cookie: " . $frilWebCookie,
                "Connection: keep-alive",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                self::USER_AGENT,
                "Accept-Language: en-us",
                "Accept-Encoding: gzip, deflate",
                "Accept-Charset: UTF-8"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /*
     * $types:
     *  1: あなた宛
     *  2: 取引
     * $mothod:
     *  0 (default)
     * $pos:
     *  0 (default)
     */
    public function getNotification($authToken, $cookie, $type, $method = 0, $pos = 0)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/notification',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'auth_token=' . $authToken .
                '&method=' . $method .
                '&pos=' . $pos .
                '&types=' . $type,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "Cookie: " . $cookie
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getBalance($authToken, $cookie)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/balance/show',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'auth_token=' . $authToken,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "Cookie: " . $cookie
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getBankInfo($authToken, $cookie)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/bank',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'auth_token=' . $authToken,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "Cookie: " . $cookie
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function updateBankInfo($authToken, $cookie, $accountNumber, $bankId, $branchCode,
                                   $depositType, $firstName, $lastName)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_HOST_URL . '/api/notification',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'account_number=' . $accountNumber .
                '&auth_token=' . $authToken .
                '&bank_id=' . $bankId .
                '&branch_code=' . $branchCode .
                '&deposit_type=' . $depositType .
                '&first_name=' . $firstName .
                '&last_name=' . $lastName,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "Cookie: " . $cookie
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function preWithdrawal($authToken, $cookie, $cookieUser)
    {
        // Get csrf-token and Cookie
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::WEB_HOST_URL . '/balance/operation/withdrawal?auth_token=' . $authToken);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $headers = array();
        $headers[] = self::WEB_HOST;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (302 != $http) {
            curl_close($curl);
            return null;
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);

        $data = self::getHttpHeader2Array($header);
        $str = $data['Set-Cookie'];

        $pos = strpos($str, '; path=/');
        $cookie = substr($str, 0, $pos);

        curl_close($curl);

        $data = '{"result": true, "cookie": "' . $cookie . '"}';

        return $data;
    }

    public function getBalanceWithdrawalAuthToken($cookie, $cookieUser)
    {
        $csrfToken = '';

        // Get csrf-token and Cookie
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::WEB_HOST_URL . "/balance/withdrawal/request");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $headers = array();
        $headers[] = self::WEB_HOST;
        $headers[] = 'If-None-Match: W/\"33735e67b42aba53dd488a0bc2ee80cb\"'; // ETag, cf. login
        $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        $headers[] = "Cookie: " . $cookie . "; shows_app_review_dialog=true; ". $cookieUser;
        $headers[] = self::USER_AGENT;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            curl_close($curl);
            $data['csrfToken'] = null;
            return $data;
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);

        $data = self::getHttpHeader2Array($header);
        $str = $data['Set-Cookie'];

        $pos = strpos($str, '; path=/');
        $cookie = substr($str, 0, $pos);

        $html = str_get_html($response);

        foreach ($html->find('meta[name="csrf-token"]') as $e) {
            $csrfToken = $e->content;
        }

        $html->clear();
        unset($html);

        curl_close($curl);

        $data['csrfToken'] = $csrfToken;
        $data['cookieWeb'] = $cookie;

        return $data;
    }

    public function getBalanceWithdrawalConfirm($authToken, $cookieWeb, $cookieUser, $transfer)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::WEB_HOST_URL . '/balance/withdrawal/confirm',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'utf8=%E2%9C%93' .
                '&authenticity_token=' . urlencode($authToken) .
                '&transfer=' . $transfer,
            CURLOPT_HTTPHEADER => array(
                self::WEB_HOST,
                "Accept-Language: en-us",
                self::USER_AGENT,
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Referer: https://web.fril.jp/balance/withdrawal/request",
                "Content-Type: application/x-www-form-urlencoded",
                "Connection: keep-alive",
                "Cookie: " . $cookieWeb . "; shows_app_review_dialog=true; ". $cookieUser,
                "Origin: " . self::WEB_HOST_URL,
                "Accept-Encoding: gzip, deflate"
            ),
        ));

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 == $http) {
            $data = '{"result": true}';
        } else {
            $data = '{"result": false}';
        }

        curl_close($curl);

        return $data;
    }

    public static function convertTransactionEvidenceStatus($transactionEvidenceStatus)
    {
        $transactionEvidenceStatusNipon = "";
        switch ($transactionEvidenceStatus) {
            case '0':
                $transactionEvidenceStatusNipon = "";
                break;
            case '1':
                $transactionEvidenceStatusNipon = "";
                break;
            case '2':
                $transactionEvidenceStatusNipon = "";
                break;
            case '3':
                $transactionEvidenceStatusNipon = "商品の発送";
                break;
            case '4':
                $transactionEvidenceStatusNipon = "受取確認待ち";
                break;
            case '5':
                $transactionEvidenceStatusNipon = "取引相手の評価";
                break;
            default:
                $transactionEvidenceStatusNipon = $transactionEvidenceStatus;
        }

        return $transactionEvidenceStatusNipon;
    }
}
