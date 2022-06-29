<?php

define("INPUT", "Nhập file chứa số: ");
define("AMOUNT_PHONE", "Số lượng số: ");

// errors
define("ERROR_GET_FILE", "File không tại!");
define("ERROR_EXP", "Lỗi ngoài ý muốn!");
define("ERROR_AMOUNT_PHONE", "Số lượng số không đủ, phải trên 1 hoặc bằng!");

// ask
define("ASK", "Bạn có muốn lưu lại thông tin? (y/n = break or exit program): ");
define("WHAT_FILE", "Nhập tên file muốn lưu: ");

//success
define("SUCCESS_SAVE", "Lưu thành công!\n");
// start

echo INPUT;
$getfile = (string) trim(fgets(STDIN));

if (file_exists($getfile)) {
    $exp_phone = explode("\n", file_get_contents($getfile));

    echo AMOUNT_PHONE . " " . count($exp_phone);
    echo "\n";
    if (count($exp_phone) < 1) {
        echo ERROR_AMOUNT_PHONE;
    } else {
        $stt = 0;

        $phonelive = [];
        $phonedie = [];
        $phoneunkw = [];
        foreach ($exp_phone as $value) {
            $result = json_decode(checkvaildphone($value));
            if ($result->code == 4) {
                $phonelive[] = $value;
            } elseif ($result->code == 3) {
                $phonedie[] = $value;
            } else {
                $phoneunkw[] = $value;
            }
            $stt++;

            echo "[" .
                $stt .
                "] => " .
                $value .
                " => " .
                $result->Message .
                "\n";
            sleep(1);
        }

        $output_info =
            "Live: " .
            count($phonelive) .
            " || Die: " .
            count($phonedie) .
            " || Unkw: " .
            count($phoneunkw) .
            "\n";

        echo $output_info;

        while (true) {
            echo ASK;
            $ask = trim(fgets(STDIN));
            switch ($ask) {
                case "y":
                    echo WHAT_FILE;
                    $save = (string) trim(fgets(STDIN));
                    $source = [];
                    save($phonelive, $phonedie, $phoneunkw, $source);
                    print_r($source);
                    $open = fopen($save, "a+");
                    foreach ($source as $value) {
                        fwrite($open, $value . "\n");
                    }
                    fclose($open);
                    echo SUCCESS_SAVE;
                    break;
                case "n":
                    die();
                default:
                    break;
            }
        }
    }
} else {
    die(ERROR_GET_FILE);
}

function save($phonelive, $phonedie, $phone_error, &$output_source)
{
    $arrmenu = [
        1 => "Lưu số tồn tại",
        2 => "Lưu số không tồn tại",
        3 => "Lưu số lỗi không check được",
    ];

    foreach ($arrmenu as $name => $value) {
        echo "[" . $name . "] => " . $value . "\n";
    }

    echo "Chọn số: ";
    $num = (int) fgets(STDIN);
    switch ($num) {
        case 1:
            $output_source = $phonelive;
            break;
        case 2:
            $output_source = $phonedie;
            break;
        case 3:
            $output_source = $phone_error;
            break;
        default:
            $output_source = $phone_error;
            break;
    }
}

function checkvaildphone($phone)
{
    $ch = curl_init();
    curl_setopt(
        $ch,
        CURLOPT_URL,
        "https://tronghoa.dev/api/vaild_phone.php?phone=840312345678"
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "authority" => "tronghoa.dev",
        "accept" =>
            "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
        "accept-language" => "en-US,en;q=0.9",
        "sec-ch-ua" =>
            '" Not;A Brand";v="99", "Microsoft Edge";v="103", "Chromium";v="103"',
        "sec-ch-ua-mobile" => "?0",
        "sec-ch-ua-platform" => '"Windows"',
        "sec-fetch-dest" => "document",
        "sec-fetch-mode" => "navigate",
        "sec-fetch-site" => "none",
        "sec-fetch-user" => "?1",
        "upgrade-insecure-requests" => "1",
        "user-agent" =>
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37",
        "Accept-Encoding" => "gzip",
    ]);

    $response = curl_exec($ch);

    curl_close($ch);
    return $response;
}
