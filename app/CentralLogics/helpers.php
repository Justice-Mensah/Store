<?php

namespace App\CentralLogics;

use App\Model\AddOn;
use App\Model\BusinessSetting;
use App\Model\Currency;
use App\Model\DMReview;
use App\Model\Order;
use App\Model\Review;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Helpers
{
    public static function error_processor($validator)
    {
        $err_keeper = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            array_push($err_keeper, ['code' => $index, 'message' => $error[0]]);
        }
        return $err_keeper;
    }

    public static function combinations($arrays)
    {
        $result = [[]];
        foreach ($arrays as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    public static function variation_price($product, $variation)
    {
        if (empty(json_decode($variation, true))) {
            $result = $product['price'];
        } else {
            $match = json_decode($variation, true)[0];
            $result = 0;
            foreach (json_decode($product['variations'], true) as $property => $value) {
                if ($value['type'] == $match['type']) {
                    $result = $value['price'];
                }
            }
        }
        return $result;
    }

    public static function product_data_formatting($data, $multi_data = false)
    {
        $storage = [];
        if ($multi_data == true) {
            foreach ($data as $item) {
                $variations = [];
                $item['category_ids'] = json_decode($item['category_ids']);
                $item['image'] = json_decode($item['image']);
                $item['attributes'] = json_decode($item['attributes']);
                $item['choice_options'] = json_decode($item['choice_options']);
                foreach (json_decode($item['variations'], true) as $var) {
                    array_push($variations, [
                        'type' => $var['type'],
                        'price' => (double)$var['price'],
                        'stock' => (double)$var['stock'],
                    ]);
                }
                $item['variations'] = $variations;

                if(count($item['translations'])){
                    foreach($item['translations'] as $translation)
                    {
                        if($translation->key == 'name')
                        {
                            $item['name'] = $translation->value;
                        }
                        if($translation->key == 'description')
                        {
                            $item['description'] = $translation->value;
                        }
                    }
                }
                unset($item['translations']);
                array_push($storage, $item);
            }
            $data = $storage;
        } else {
            $variations = [];
            $data['category_ids'] = json_decode($data['category_ids']);
            $data['image'] = json_decode($data['image']);
            $data['attributes'] = json_decode($data['attributes']);
            $data['choice_options'] = json_decode($data['choice_options']);
            foreach (json_decode($data['variations'], true) as $var) {
                array_push($variations, [
                    'type' => $var['type'],
                    'price' => (double)$var['price'],
                    'stock' => (double)$var['stock'],
                ]);
            }
            $data['variations'] = $variations;
            if(count($data['translations'])){
                foreach($data['translations'] as $translation)
                {
                    if($translation->key == 'name')
                    {
                        $data['name'] = $translation->value;
                    }
                    if($translation->key == 'description')
                    {
                        $data['description'] = $translation->value;
                    }
                }
            }
        }

        return $data;
    }

    public static function get_business_settings($name)
    {
        $config = null;
        foreach (BusinessSetting::all() as $setting) {
            if ($setting['key'] == $name) {
                $config = json_decode($setting['value'], true);
            }
        }
        return $config;
    }

    public static function currency_code()
    {
        $currency_code = BusinessSetting::where(['key' => 'currency'])->first()->value;
        return $currency_code;
    }

    public static function currency_symbol()
    {
        $currency_symbol = Currency::where(['currency_code' => Helpers::currency_code()])->first()->currency_symbol;
        return $currency_symbol;
    }

    public static function send_push_notif_to_device($fcm_token, $data)
    {
        /*https://fcm.googleapis.com/v1/projects/myproject-b5ae1/messages:send*/
        $key = BusinessSetting::where(['key' => 'push_notification_key'])->first()->value;
        /*$project_id = BusinessSetting::where(['key' => 'fcm_project_id'])->first()->value;*/

        $url = "https://fcm.googleapis.com/fcm/send";
        $header = array("authorization: key=" . $key . "",
            "content-type: application/json"
        );

        $postdata = '{
            "to" : "' . $fcm_token . '",
            "data" : {
                "title":"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $data['image'] . '",
                "order_id":"' . $data['order_id'] . '",
                "is_read": 0
              }
        }';

        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Get URL content
        $result = curl_exec($ch);
        // close handle to release resources
        curl_close($ch);

        return $result;
    }

    public static function send_push_notif_to_topic($data)
    {
        /*https://fcm.googleapis.com/v1/projects/myproject-b5ae1/messages:send*/
        $key = BusinessSetting::where(['key' => 'push_notification_key'])->first()->value;
        /*$topic = BusinessSetting::where(['key' => 'fcm_topic'])->first()->value;*/
        /*$project_id = BusinessSetting::where(['key' => 'fcm_project_id'])->first()->value;*/

        $url = "https://fcm.googleapis.com/fcm/send";
        $header = array("authorization: key=" . $key . "",
            "content-type: application/json"
        );
        $postdata = '{
            "to" : "/topics/market",
            "data" : {
                "title":"' . $data->title . '",
                "body" : "' . $data->description . '",
                "image" : "' . $data->image . '",
                "is_read": 0
              }
        }';

        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Get URL content
        $result = curl_exec($ch);
        // close handle to release resources
        curl_close($ch);

        return $result;
    }

    public static function rating_count($product_id, $rating)
    {
        return Review::where(['product_id' => $product_id, 'rating' => $rating])->count();
    }

    public static function dm_rating_count($deliveryman_id, $rating)
    {
        return DMReview::where(['delivery_man_id' => $deliveryman_id, 'rating' => $rating])->count();
    }

    public static function tax_calculate($product, $price)
    {
        if ($product['tax_type'] == 'percent') {
            $price_tax = ($price / 100) * $product['tax'];
        } else {
            $price_tax = $product['tax'];
        }
        return $price_tax;
    }

    public static function discount_calculate($product, $price)
    {
        if ($product['discount_type'] == 'percent') {
            $price_discount = ($price / 100) * $product['discount'];
        } else {
            $price_discount = $product['discount'];
        }
        return $price_discount;
    }

    public static function max_earning()
    {
        $data = Order::where(['order_status' => 'delivered'])->select('id', 'created_at', 'order_amount')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $max = 0;
        foreach ($data as $month) {
            $count = 0;
            foreach ($month as $order) {
                $count += $order['order_amount'];
            }
            if ($count > $max) {
                $max = $count;
            }
        }
        return $max;
    }

    public static function max_orders()
    {
        $data = Order::select('id', 'created_at')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $max = 0;
        foreach ($data as $month) {
            $count = 0;
            foreach ($month as $order) {
                $count += 1;
            }
            if ($count > $max) {
                $max = $count;
            }
        }
        return $max;
    }

    public static function order_status_update_message($status)
    {
        if ($status == 'pending') {
            $data = BusinessSetting::where('key', 'order_pending_message')->first()->value;
        } elseif ($status == 'confirmed') {
            $data = BusinessSetting::where('key', 'order_confirmation_msg')->first()->value;
        } elseif ($status == 'processing') {
            $data = BusinessSetting::where('key', 'order_processing_message')->first()->value;
        } elseif ($status == 'out_for_delivery') {
            $data = BusinessSetting::where('key', 'out_for_delivery_message')->first()->value;
        } elseif ($status == 'delivered') {
            $data = BusinessSetting::where('key', 'order_delivered_message')->first()->value;
        } elseif ($status == 'delivery_boy_delivered') {
            $data = BusinessSetting::where('key', 'delivery_boy_delivered_message')->first()->value;
        } elseif ($status == 'del_assign') {
            $data = BusinessSetting::where('key', 'delivery_boy_assign_message')->first()->value;
        } elseif ($status == 'ord_start') {
            $data = BusinessSetting::where('key', 'delivery_boy_start_message')->first()->value;
        } else {
            $data = '{"status":"0","message":""}';
        }

        $res = json_decode($data, true);

        if ($res['status'] == 0) {
            return 0;
        }
        return $res['message'];
    }

    public static function day_part()
    {
        $part = "";
        $morning_start = date("h:i:s", strtotime("5:00:00"));
        $afternoon_start = date("h:i:s", strtotime("12:01:00"));
        $evening_start = date("h:i:s", strtotime("17:01:00"));
        $evening_end = date("h:i:s", strtotime("21:00:00"));

        if (time() >= $morning_start && time() < $afternoon_start) {
            $part = "morning";
        } elseif (time() >= $afternoon_start && time() < $evening_start) {
            $part = "afternoon";
        } elseif (time() >= $evening_start && time() <= $evening_end) {
            $part = "evening";
        } else {
            $part = "night";
        }

        return $part;
    }

    public static  function remove_dir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") Helpers::remove_dir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static  function get_language_name($key)
    {
        $languages = Array(
            "af"=>"Afrikaans",
            "sq"=>"Albanian - shqip",
            "am"=>"Amharic - አማርኛ",
            "ar"=>"Arabic - العربية",
            "an"=>"Aragonese - aragonés",
            "hy"=>"Armenian - հայերեն",
            "ast"=>"Asturian - asturianu",
            "az"=>"Azerbaijani - azərbaycan dili",
            "eu"=>"Basque - euskara",
            "be"=>"Belarusian - беларуская",
            "bn"=>"Bengali - বাংলা",
            "bs"=>"Bosnian - bosanski",
            "br"=>"Breton - brezhoneg",
            "bg"=>"Bulgarian - български",
            "ca"=>"Catalan - català",
            "ckb"=>"Central Kurdish - کوردی (دەستنوسی عەرەبی)",
            "zh"=>"Chinese - 中文",
            "zh-HK"=>"Chinese (Hong Kong) - 中文（香港）",
            "zh-CN"=>"Chinese (Simplified) - 中文（简体）",
            "zh-TW"=>"Chinese (Traditional) - 中文（繁體）",
            "co"=>"Corsican",
            "hr"=>"Croatian - hrvatski",
            "cs"=>"Czech - čeština",
            "da"=>"Danish - dansk",
            "nl"=>"Dutch - Nederlands",
            "en"=>"English",
            "en-AU"=>"English (Australia)",
            "en-CA"=>"English (Canada)",
            "en-IN"=>"English (India)",
            "en-NZ"=>"English (New Zealand)",
            "en-ZA"=>"English (South Africa)",
            "en-GB"=>"English (United Kingdom)",
            "en-US"=>"English (United States)",
            "eo"=>"Esperanto - esperanto",
            "et"=>"Estonian - eesti",
            "fo"=>"Faroese - føroyskt",
            "fil"=>"Filipino",
            "fi"=>"Finnish - suomi",
            "fr"=>"French - français",
            "fr-CA"=>"French (Canada) - français (Canada)",
            "fr-FR"=>"French (France) - français (France)",
            "fr-CH"=>"French (Switzerland) - français (Suisse)",
            "gl"=>"Galician - galego",
            "ka"=>"Georgian - ქართული",
            "de"=>"German - Deutsch",
            "de-AT"=>"German (Austria) - Deutsch (Österreich)",
            "de-DE"=>"German (Germany) - Deutsch (Deutschland)",
            "de-LI"=>"German (Liechtenstein) - Deutsch (Liechtenstein)",
            "de-CH"=>"German (Switzerland) - Deutsch (Schweiz)",
            "el"=>"Greek - Ελληνικά",
            "gn"=>"Guarani",
            "gu"=>"Gujarati - ગુજરાતી",
            "ha"=>"Hausa",
            "haw"=>"Hawaiian - ʻŌlelo Hawaiʻi",
            "he"=>"Hebrew - עברית",
            "hi"=>"Hindi - हिन्दी",
            "hu"=>"Hungarian - magyar",
            "is"=>"Icelandic - íslenska",
            "id"=>"Indonesian - Indonesia",
            "ia"=>"Interlingua",
            "ga"=>"Irish - Gaeilge",
            "it"=>"Italian - italiano",
            "it-IT"=>"Italian (Italy) - italiano (Italia)",
            "it-CH"=>"Italian (Switzerland) - italiano (Svizzera)",
            "ja"=>"Japanese - 日本語",
            "kn"=>"Kannada - ಕನ್ನಡ",
            "kk"=>"Kazakh - қазақ тілі",
            "km"=>"Khmer - ខ្មែរ",
            "ko"=>"Korean - 한국어",
            "ku"=>"Kurdish - Kurdî",
            "ky"=>"Kyrgyz - кыргызча",
            "lo"=>"Lao - ລາວ",
            "la"=>"Latin",
            "lv"=>"Latvian - latviešu",
            "ln"=>"Lingala - lingála",
            "lt"=>"Lithuanian - lietuvių",
            "mk"=>"Macedonian - македонски",
            "ms"=>"Malay - Bahasa Melayu",
            "ml"=>"Malayalam - മലയാളം",
            "mt"=>"Maltese - Malti",
            "mr"=>"Marathi - मराठी",
            "mn"=>"Mongolian - монгол",
            "ne"=>"Nepali - नेपाली",
            "no"=>"Norwegian - norsk",
            "nb"=>"Norwegian Bokmål - norsk bokmål",
            "nn"=>"Norwegian Nynorsk - nynorsk",
            "oc"=>"Occitan",
            "or"=>"Oriya - ଓଡ଼ିଆ",
            "om"=>"Oromo - Oromoo",
            "ps"=>"Pashto - پښتو",
            "fa"=>"Persian - فارسی",
            "pl"=>"Polish - polski",
            "pt"=>"Portuguese - português",
            "pt-BR"=>"Portuguese (Brazil) - português (Brasil)",
            "pt-PT"=>"Portuguese (Portugal) - português (Portugal)",
            "pa"=>"Punjabi - ਪੰਜਾਬੀ",
            "qu"=>"Quechua",
            "ro"=>"Romanian - română",
            "mo"=>"Romanian (Moldova) - română (Moldova)",
            "rm"=>"Romansh - rumantsch",
            "ru"=>"Russian - русский",
            "gd"=>"Scottish Gaelic",
            "sr"=>"Serbian - српски",
            "sh"=>"Serbo-Croatian - Srpskohrvatski",
            "sn"=>"Shona - chiShona",
            "sd"=>"Sindhi",
            "si"=>"Sinhala - සිංහල",
            "sk"=>"Slovak - slovenčina",
            "sl"=>"Slovenian - slovenščina",
            "so"=>"Somali - Soomaali",
            "st"=>"Southern Sotho",
            "es"=>"Spanish - español",
            "es-AR"=>"Spanish (Argentina) - español (Argentina)",
            "es-419"=>"Spanish (Latin America) - español (Latinoamérica)",
            "es-MX"=>"Spanish (Mexico) - español (México)",
            "es-ES"=>"Spanish (Spain) - español (España)",
            "es-US"=>"Spanish (United States) - español (Estados Unidos)",
            "su"=>"Sundanese",
            "sw"=>"Swahili - Kiswahili",
            "sv"=>"Swedish - svenska",
            "tg"=>"Tajik - тоҷикӣ",
            "ta"=>"Tamil - தமிழ்",
            "tt"=>"Tatar",
            "te"=>"Telugu - తెలుగు",
            "th"=>"Thai - ไทย",
            "ti"=>"Tigrinya - ትግርኛ",
            "to"=>"Tongan - lea fakatonga",
            "tr"=>"Turkish - Türkçe",
            "tk"=>"Turkmen",
            "tw"=>"Twi",
            "uk"=>"Ukrainian - українська",
            "ur"=>"Urdu - اردو",
            "ug"=>"Uyghur",
            "uz"=>"Uzbek - o‘zbek",
            "vi"=>"Vietnamese - Tiếng Việt",
            "wa"=>"Walloon - wa",
            "cy"=>"Welsh - Cymraeg",
            "fy"=>"Western Frisian",
            "xh"=>"Xhosa",
            "yi"=>"Yiddish",
            "yo"=>"Yoruba - Èdè Yorùbá",
            "zu"=>"Zulu - isiZulu",
        );
        return array_key_exists($key, $languages)?$languages[$key]:$key;
    }

    public static function upload(string $dir, string $format, $image = null)
    {
        if ($image != null) {
            $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
            Storage::disk('public')->put($dir . $imageName, file_get_contents($image));
        } else {
            $imageName = 'def.png';
        }

        return $imageName;
    }

    public static function update(string $dir, $old_image, string $format, $image = null)
    {
        if (Storage::disk('public')->exists($dir . $old_image)) {
            Storage::disk('public')->delete($dir . $old_image);
        }
        $imageName = Helpers::upload($dir, $format, $image);
        return $imageName;
    }

    public static function delete($full_path)
    {
        if (Storage::disk('public')->exists($full_path)) {
            Storage::disk('public')->delete($full_path);
        }
        return [
            'success' => 1,
            'message' => 'Removed successfully !'
        ];
    }
}