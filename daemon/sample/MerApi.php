<?php
/**
 * Created by CR524
 */

namespace App\Lib;

use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelEntryUserComment;
use lsolesen\pel\PelExif;
use lsolesen\pel\PelTiff;
use lsolesen\pel\PelEntryAscii;

class MerApi
{
    /* _ACCESS_TOKEN
     * ba8a823240abcb415b05ef87f990dfc3c76b69d1
     * 66220727fc8ac4cda14f869fe7ade9a81ca6b865
     * f19464e9197c0a5ee473020615de203b31e97a6c
     */
    const UUID = '0BE0193B6F274ECF898BEF542DC57186';
    const _GLOBAL_ACCESS_TOKEN = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE0ODA4MjQ2MDUsImp0aSI6ImlzUjJmRkYzNEtRdU1ISExUWjRlbkgiLCJpYXQiOjE0ODA4MjI4MDUsImd1aWQiOiJmNWZmNGU1MC1lNDNjLTExZTUtOWNhMS00NDhhNWIzNzM3MjgiLCJraW5kIjoiYWNjZXNzX3Rva2VuIiwic2VydmljZXMiOlt7InNlcnZpY2VOYW1lIjoibWVyY2FyaS1qcCIsInNlcnZpY2VJRCI6IjQwMjU5MzM5OCJ9XSwicnRpZCI6ImZXamViY1hpSFJtemJieFg5RzJlbkgifQ.D8v1jGrSsgRb1TD-7G-WG9GIzPSRD_pugVGrGy5VJvlSmUF-IIZqTWtCwrmn-dWZBuLYRSCSXlISArDTj6UxoQ';
    const HOST_URL = 'https://api.mercari.jp';
    const HOST = 'host: api.mercari.jp';
    const X_APP_VERSION = 'x-app-version: 2012';
    const X_PLATFORM = 'x-platform: ios';
    const IV_CERT1217 = 'E695E6B4+05E94B5D+8135B64B54C7B97A';
    const IV_CERT0126 = 'F4F10D269DFD4BF9A793506485F024A1';
    const BOUNDARY_STRING = 'Boundary+FA84BC2EC9927BF8';
    const _APP_VERSION = '2012';
    const EXHIBIT_TOKEN = '95973bd15ffcd5658814853d89c6485f';

    public $header = array(
        "Accept: application/json",
        "user-agent: Mercari_r/2012 (iPhone OS 10.2; en-CN; iPhone7,2)",
        self::X_APP_VERSION,
        self::X_PLATFORM
    );

    function __construct()
    {
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    public function printDebug($data)
    {
        $debugFile = WWW_ROOT . 'logs/debug.log';
        $handle = fopen($debugFile, "a");
        fwrite($handle, $data . "\n");
        fclose($handle);
    }

    public function generate_uuid()
    {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function getAccessToken($uuid)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/auth/create_token?uuid=' . $uuid);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);

        return $response;
    }

    public function getGlobalToken($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/global_token/get?_access_token=' . $accessToken);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);

        return $response;
    }

    public function login($accessToken, $globalToken, $userName, $password)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/users/login?_global_access_token=' . urlencode($globalToken) . '&_access_token=' . urlencode($accessToken),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'iv_cert=' . urlencode(self::IV_CERT0126) . '&email=' . $userName . '&revert=check' . '&password=' . $password,
            CURLOPT_HTTPHEADER => array(
                'cache-control: no-cache',
                'content-type: application/x-www-form-urlencoded',
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getProfile($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/users/get_profile?_access_token=' . $accessToken . '&_user_format=profile');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function updateProfile($accessToken, $introduction, $name, $photo)
    {
        $photoField = '';
        if (!empty($photo)) {
            $imageData = file_get_contents($photo);
            if (empty($imageData)) {
                echo "Error: No such image in the indicated path\n";
                return null;
            }
            $photoField = "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo\"; filename=\"photo.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData . "\r\n";
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/users/update_profile?_access_token=' . $accessToken,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"introduction\"\r\n\r\n" . $introduction . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"name\"\r\n\r\n" . $name . "\r\n" .
                $photoField .
                "--" . self::BOUNDARY_STRING . "--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=" . self::BOUNDARY_STRING,
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    private function getRandomStr($len)
    {
        $str = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
        $randomStr = null;
        for ($i = 0; $i < $len; $i++) {
            $randomStr .= $str[rand(0, count($str) - 1)];
        }

        return $randomStr;
    }

    private function setJpegComment($filename)
    {
        $pelJpeg = new PelJpeg($filename);

        $pelExif = $pelJpeg->getExif();
        if ($pelExif == null) {
            $pelExif = new PelExif();
            $pelJpeg->setExif($pelExif);
        }

        $pelTiff = $pelExif->getTiff();
        if ($pelTiff == null) {
            $pelTiff = new PelTiff();
            $pelExif->setTiff($pelTiff);
        }

        $pelIfd0 = $pelTiff->getIfd();
        if ($pelIfd0 == null) {
            $pelIfd0 = new PelIfd(PelIfd::IFD0);
            $pelTiff->setIfd($pelIfd0);
        }

        $pelIfd0->addEntry(new PelEntryAscii(
            PelTag::IMAGE_DESCRIPTION, 'DSC' . time() . $this->getRandomStr(4)));

        $pelSubIfdGps = new PelIfd(PelIfd::GPS);
        $pelIfd0->addSubIfd($pelSubIfdGps);

        $pelJpeg->saveFile($filename);
    }

    public function submitExhibit($accessToken, $exhibit)
    {
        $this->setJpegComment($exhibit->photo_1);
        $imageData0 = file_get_contents($exhibit->photo_1);
        if (null === $imageData0) {
            echo "Error: No such image(s) in the indicated path\n";
            return null;
        }
        $photoField = "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_1\"; filename=\"photo_1.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData0 . "\r\n";

        if (null != $exhibit->photo_2) {
            $this->setJpegComment($exhibit->photo_2);
            $imageData1 = file_get_contents($exhibit->photo_2);
            if (null === $imageData1) {
                echo "Error: No such image(s) in the indicated path\n";
                return null;
            }
            $photoField .= "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_2\"; filename=\"photo_2.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData1 . "\r\n";
            if (null != $exhibit->photo_3) {
                $this->setJpegComment($exhibit->photo_3);
                $imageData2 = file_get_contents($exhibit->photo_3);
                if (null === $imageData2) {
                    echo "Error: No such image(s) in the indicated path\n";
                    return null;
                }
                $photoField .= "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_3\"; filename=\"photo_3.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData2 . "\r\n";

                if (null != $exhibit->photo_4) {
                    $this->setJpegComment($exhibit->photo_4);
                    $imageData3 = file_get_contents($exhibit->photo_4);
                    if (null === $imageData3) {
                        echo "Error: No such image(s) in the indicated path\n";
                        return null;
                    }
                    $photoField .= "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_4\"; filename=\"photo_4.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData3 . "\r\n";
                }
            }
        }

        $brandName = '';
        if (0 != $exhibit->brand_name) {
            $brandName = "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"brand_name\"\r\n\r\n" . $exhibit->brand_name . "\r\n";
        }

        $size = '';
        if (0 != $size) {
            $size = "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"size\"\r\n\r\n" . $exhibit->size . "\r\n";
        }

        $exhibit_token = md5(mt_rand());

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/sellers/sell?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"_ignore_warning\"\r\n\r\nfalse\r\n" .
                $brandName .
                $size .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"category_id\"\r\n\r\n" . $exhibit->category_id . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"description\"\r\n\r\n" . $exhibit->description . $this->getRandomStr(2) . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"exhibit_token\"\r\n\r\n" . $exhibit_token . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"item_condition\"\r\n\r\n" . $exhibit->item_condition . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"name\"\r\n\r\n" . $exhibit->name . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"price\"\r\n\r\n" . $exhibit->price . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"sales_fee\"\r\n\r\n" . $exhibit->sales_fee . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"shipping_duration\"\r\n\r\n" . $exhibit->shipping_duration . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"shipping_from_area\"\r\n\r\n" . $exhibit->shipping_from_area . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"shipping_method\"\r\n\r\n" . $exhibit->shipping_method . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"shipping_payer\"\r\n\r\n" . $exhibit->shipping_payer . "\r\n" .
                $photoField .
                "--" . self::BOUNDARY_STRING . "--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=" . self::BOUNDARY_STRING,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function editExhibit($accessToken, $exhibit)
    {
        $photoFieldWithValue = '';
        $photoFieldWithBinary = '';

        if (1 === strlen($exhibit->photo_1)) {
            $photoFieldWithValue = "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_1\"\r\n\r\n" . $exhibit->photo_1 . "\r\n";
        } else {
            $imageData = file_get_contents($exhibit->photo_1);
            if (null !== $imageData) {
                $photoFieldWithBinary = "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_1\"; filename=\"photo_1.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData . "\r\n";
            }
        }

        if ($exhibit->photo_2 != null) {
            if (1 === strlen($exhibit->photo_2)) {
                $photoFieldWithValue .= "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_2\"\r\n\r\n" . $exhibit->photo_2 . "\r\n";
            } else {
                $imageData = file_get_contents($exhibit->photo_2);
                if (null !== $imageData) {
                    $photoFieldWithBinary .= "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_2\"; filename=\"photo_2.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData . "\r\n";
                }
            }
            if ($exhibit->photo_3 != null) {
                if (1 === strlen($exhibit->photo_3)) {
                    $photoFieldWithValue .= "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_3\"\r\n\r\n" . $exhibit->photo_3 . "\r\n";
                } else {
                    $imageData = file_get_contents($exhibit->photo_3);
                    if (null !== $imageData) {
                        $photoFieldWithBinary .= "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_3\"; filename=\"photo_3.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData . "\r\n";
                    }
                }
                if ($exhibit->photo_4 != null) {
                    if (1 === strlen($exhibit->photo_4)) {
                        $photoFieldWithValue .= "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_4\"\r\n\r\n" . $exhibit->photo_4 . "\r\n";
                    } else {
                        $imageData = file_get_contents($exhibit->photo_4);
                        if (null !== $imageData) {
                            $photoFieldWithBinary .= "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"photo_4\"; filename=\"photo_4.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n" . $imageData . "\r\n";
                        }
                    }
                }
            }
        }

        $brandName = '';
        if (0 != $exhibit->brand_name) {
            $brandName = "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"brand_name\"\r\n\r\n" . $exhibit->brand_name . "\r\n";
        }

        $size = '';
        if (0 != $size) {
            $size = "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"size\"\r\n\r\n" . $exhibit->size . "\r\n";
        }

        $exhibit_token = md5(mt_rand());

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/items/edit?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"_ignore_warning\"\r\n\r\nfalse\r\n" .
                $brandName .
                $size .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"category_id\"\r\n\r\n" . $exhibit->category_id . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"description\"\r\n\r\n" . $exhibit->description . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"exhibit_token\"\r\n\r\n" . $exhibit_token . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"id\"\r\n\r\n" . $exhibit->id . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"item_condition\"\r\n\r\n" . $exhibit->item_condition . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"name\"\r\n\r\n" . $exhibit->name . "\r\n" .
                $photoFieldWithValue .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"price\"\r\n\r\n" . $exhibit->price . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"sales_fee\"\r\n\r\n" . $exhibit->sales_fee . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"shipping_class\"\r\n\r\n" . $exhibit->shipping_class . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"shipping_duration\"\r\n\r\n" . $exhibit->shipping_duration . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"shipping_from_area\"\r\n\r\n" . $exhibit->shipping_from_area . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"shipping_method\"\r\n\r\n" . $exhibit->shipping_method . "\r\n" .
                "--" . self::BOUNDARY_STRING . "\r\nContent-Disposition: form-data; name=\"shipping_payer\"\r\n\r\n" . $exhibit->shipping_payer . "\r\n" .
                $photoFieldWithBinary .
                "--" . self::BOUNDARY_STRING . "--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=" . self::BOUNDARY_STRING,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            echo 'Error:' . $response;
            $response = null;
        }

        curl_close($curl);

        return $response;
    }

    public function updateExhibitStatus($accessToken, $exhibitId, $status)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/items/update_status?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'item_id=' . $exhibitId . '&status=' . $status,
            CURLOPT_HTTPHEADER => array(
                'cache-control: no-cache',
                'content-type: application/x-www-form-urlencoded',
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /*
     * status: {'on_sale', 'trading', 'sold_out'}
     */
    public function getExhibitItem($accessToken, $sellerId, $limit, $maxPagerId, $status)
    {
        $maxPagerIdValue = '';
        if (!empty($maxPagerId)) {
            $maxPagerIdValue = '&max_pager_id=' . $maxPagerId;
        }
        $curl = curl_init();

//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/items/get_items?_access_token=' . $accessToken .
            '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN .
            $maxPagerIdValue . '&seller_id=' . $sellerId . '&status=' . $status . '&limit=' . $limit);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getExhibitItemDetail($accessToken, $id)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/items/get?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN . '&id=' . $id);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getExhibitComment($accessToken, $itemId)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/comments/gets?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN . '&item_id=' . $itemId);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function setExhibitComment($accessToken, $itemId, $message)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/comments/add?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'item_id=' . $itemId . '&message=' . $message,
            CURLOPT_HTTPHEADER => array(
                'cache-control: no-cache',
                'content-type: application/x-www-form-urlencoded',
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getTransactionEvidence($accessToken, $itemId)
    {
        $curl = curl_init();
        $milliseconds = round(microtime(true) * 1000);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/transaction_evidences/get?item_id=' . $itemId . '&_datetime_format=U&_app_version=' . self::_APP_VERSION . '&_platform=ios&t=' . $milliseconds . '&_access_token=' . $accessToken);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            echo 'Error:' . curl_error($curl);
            $response = null;
        }

        return $response;
    }

    public function setTransactionEvidence($accessToken, $id)
    {
        $curl = curl_init();
        $milliseconds = round(microtime(true) * 1000);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/transaction_evidences/shipped',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'transaction_evidence_id=' . $id . '&_app_version=' . self::_APP_VERSION . '&_platform=ios&t=' . $milliseconds . '&_access_token=' . $accessToken,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getTransactionMessage($accessToken, $itemId)
    {
        $curl = curl_init();
        $milliseconds = round(microtime(true) * 1000);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/transaction_messages/get_messages?item_id=' . $itemId . '&_use_ssl=1&_app_version=' . self::_APP_VERSION . '&_platform=ios&t=' . $milliseconds . '&_access_token=' . $accessToken);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            echo 'Error:' . curl_error($curl);
            $response = null;
        }

        return $response;
    }

    public function setTransactionMessage($accessToken, $itemId, $message)
    {
        $curl = curl_init();
        $milliseconds = round(microtime(true) * 1000);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/transaction_messages/post',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'item_id=' . $itemId . '&body=' . $message . '&_app_version=' . self::_APP_VERSION . '&_platform=ios&t=' . $milliseconds . '&_access_token=' . $accessToken,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function setReview($accessToken, $itemId, $toUserId, $fame, $message)
    {
        $curl = curl_init();
        $milliseconds = round(microtime(true) * 1000);

//        $referer = 'https://frontend.mercari.jp/item-transactions.html?item_id=' . $itemId .
//            '&_access_token=' . self::_ACCESS_TOKEN . '&user_id=869441239' . '&_app_version=' . self::_APP_VERSION . '&_platform=ios';
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/reviews/post',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'item_id=' . $itemId . '&subject=buyer' . '&to_user_id=' . $toUserId .
                '&fame=' . $fame . '&message=' . $message . '&_app_version=' . self::_APP_VERSION . '&_platform=ios&t=' . $milliseconds .
                '&_access_token=' . $accessToken,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getNews($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/news/gets?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            echo 'Error:' . curl_error($curl);
            $response = null;
        }

        curl_close($curl);

        return $response;
    }

    public function getNewsDetail($accessToken, $id)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/news/get?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN . '&id=' . $id);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            echo 'Error:' . curl_error($curl);
            $response = null;
        }

        curl_close($curl);

        return $response;
    }

    public function getLikes($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/likes/history?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN . '&status=on_sale%2Ctrading%2Csold_out');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            echo 'Error:' . curl_error($curl);
            $response = null;
        }

        curl_close($curl);

        return $response;
    }

    public function getNotifications($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/notifications/gets?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            echo 'Error:' . curl_error($curl);
            $response = null;
        }

        curl_close($curl);

        return $response;
    }

    public function getNotificationsCount($accessToken, $pagerId)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/notifications/get_count?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN . '&min_pager_id=' . $pagerId);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            echo 'Error:' . curl_error($curl);
            $response = null;
        }

        curl_close($curl);

        return $response;
    }

    public function contact($accessToken, $message, $email, $itemId, $name)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/contact/support',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'item_id=' . $itemId . '&body=' . $message . '&_app_version=' . self::_APP_VERSION . '&_platform=ios&_access_token=' . $accessToken . '&email=' . $email . '&name=' . $name,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getCurrentSales($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/sales/get_current_sales?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getSaleHistory($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/sales/histories?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getBankAccounts($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/bank_accounts/get?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http) {
            echo 'Error:' . curl_error($curl);
            $response = null;
        }

        curl_close($curl);

        return $response;
    }

    public function saveBankAccount($accessToken, $accountNumber, $birthday, $branchId, $familyName, $bankId, $kind, $addressId, $firstName)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/bank_accounts/save?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'account_number=' . $accountNumber . '&birthday=' . $birthday .
                '&branch_id=' . $branchId . '&family_name=' . $familyName . '&bank_id=' . $bankId . '&kind=' . $kind .
                '&address_id=' . $addressId . '&first_name=' . $firstName,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getDeliverAddress($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/deliver_addresses/get_list?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function addDeliverAddress($accessToken, $zipCode1, $familyName, $telephone, $address2, $firstName, $city,
                                      $firstNameKana, $zipCode2, $prefecture, $address1, $familyNameKana)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/deliver_addresses/add?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'zip_code1=' . $zipCode1 . '&family_name=' . $familyName .
                '&telephone=' . $telephone . '&address2=' . $address2 . '&first_name=' . $firstName . '&city=' . $city .
                '&first_name_kana=' . $firstNameKana . '&zip_code2=' . $zipCode2 . '&prefecture=' . $prefecture .
                '&address1=' . $address1 . '&family_name_kana=' . $familyNameKana,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function lookupUserAddress($accessToken, $zipCode1, $zipCode2)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/users_address/lookup_address?zip_code1=' . $zipCode1 .
            '&zip_code2=' . $zipCode2 . '&_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function bill($accessToken, $accountNumber, $branchId, $familyName, $kind, $bankId, $amountClaimed, $paymentFee, $firstName)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::HOST_URL . '/bills/request?_access_token=' . $accessToken . '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'account_number=' . $accountNumber . '&branch_id=' . $branchId . '&family_name=' . $familyName .
                '&kind=' . $kind . '&bank_id=' . $bankId . '&amount_claimed=' . $amountClaimed . '&payment_fee=' . $paymentFee .
                '&first_name=' . $firstName,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                self::HOST,
                self::X_APP_VERSION,
                self::X_PLATFORM
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getTodoListCount($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/todolists/get_count?_access_token=' . $accessToken .
            '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);

        return $response;
    }

    public function getTodoLists($accessToken)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, self::HOST_URL . '/todolists/gets?_access_token=' . $accessToken .
            '&_global_access_token=' . self::_GLOBAL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        $response = curl_exec($curl);

        return $response;
    }

    public function convertTransactionEvidenceStatus($transactionEvidenceStatus)
    {
        $transactionEvidenceStatusStr = "";
        switch ($transactionEvidenceStatus) {
            case 'wait_shipping':
                $transactionEvidenceStatusStr = "発送待ち";
                break;
            case 'wait_review':
                $transactionEvidenceStatusStr = "受取評価待ち";
                break;
            case 'wait_done':
                $transactionEvidenceStatusStr = "評価待ち";
                break;
            case 'wait_payment':
                $transactionEvidenceStatusStr = "未入金/入金待ち";
                break;
            case 'done':
                $transactionEvidenceStatusStr = "done";
                break;
            default:
                $transactionEvidenceStatusStr = $transactionEvidenceStatus;
        }

        return $transactionEvidenceStatusStr;
    }
}
