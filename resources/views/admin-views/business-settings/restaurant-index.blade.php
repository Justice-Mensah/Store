@extends('layouts.admin.app')

@section('title','Settings')

@push('css_or_js')
<style>

.closebtn {
  margin-left: 15px;
  color: white;
  font-weight: bold;
  float: right;
  font-size: 22px;
  line-height: 20px;
  cursor: pointer;
  transition: 0.3s;
}

.closebtn:hover {
  color: black;
}
</style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="alert alert-warning sticky-top" id="alert_box" style="display:none;">
            <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
            <strong>Warning!</strong> {{trans('messages.language_warning')}} For documentaion <a href="#" target="_blank">click here</a>.
        </div>
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{trans('messages.restaurant')}} {{trans('messages.setup')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.update-setup')}}" method="post"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        @php($name=\App\Model\BusinessSetting::where('key','restaurant_name')->first()->value)
                        <div class="col-md-8 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.restaurant')}} {{trans('messages.name')}}</label>
                                <input type="text" name="restaurant_name" value="{{$name}}" class="form-control"
                                       placeholder="New market" required>
                            </div>
                        </div>
                        @php($currency_code=\App\Model\BusinessSetting::where('key','currency')->first()->value)
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.currency')}}</label>
                                <select name="currency" class="form-control js-select2-custom">
                                    @foreach(\App\Model\Currency::orderBy('currency_code')->get() as $currency)
                                        <option
                                            value="{{$currency['currency_code']}}" {{$currency_code==$currency['currency_code']?'selected':''}}>
                                            {{$currency['currency_code']}} ( {{$currency['currency_symbol']}} )
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @php($phone=\App\Model\BusinessSetting::where('key','phone')->first()->value)
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.phone')}}</label>
                                <input type="text" value="{{$phone}}"
                                       name="phone" class="form-control"
                                       placeholder="" required>
                            </div>
                        </div>
                        @php($email=\App\Model\BusinessSetting::where('key','email_address')->first()->value)
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.email')}}</label>
                                <input type="email" value="{{$email}}"
                                       name="email" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>
                        @php($address=\App\Model\BusinessSetting::where('key','address')->first()->value)
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.address')}}</label>
                                <input type="text" value="{{$address}}"
                                       name="address" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>

                        @php($mov=\App\Model\BusinessSetting::where('key','minimum_order_value')->first()->value)
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.min')}} {{trans('messages.order')}} {{trans('messages.value')}} ( {{\App\CentralLogics\Helpers::currency_symbol()}} )</label>
                                <input type="number" min="1" value="{{$mov}}"
                                       name="minimum_order_value" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            @php($delivery=\App\Model\BusinessSetting::where('key','delivery_charge')->first()->value)
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.delivery')}} {{trans('messages.charge')}}</label>
                                <input type="number" min="1" max="10000" name="delivery_charge" value="{{$delivery}}"
                                       class="form-control" placeholder="100" required>
                            </div>
                        </div>
                        @php($value=\App\Model\BusinessSetting::where('key','point_per_currency')->first()->value)
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1"> <strong>1 ( {{\App\CentralLogics\Helpers::currency_symbol()}} ) = {{$value}} {{trans('messages.internal')}} {{trans('messages.points')}}</strong>  </label>
                                <input type="number" min="1" value="{{$value}}"
                                       name="point_per_currency" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-12">
                        @php($sp=\App\Model\BusinessSetting::where('key','self_pickup')->first()->value)
                            <div class="form-group">
                                <label>{{trans('messages.self_pickup')}}</label><small style="color: red">*</small>
                                <div class="input-group input-group-md-down-break">
                                    <!-- Custom Radio -->
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="1" name="self_pickup"
                                                   id="sp1" {{$sp==1?'checked':''}}>
                                            <label class="custom-control-label" for="sp1">{{trans('messages.on')}}</label>
                                        </div>
                                    </div>
                                    <!-- End Custom Radio -->

                                    <!-- Custom Radio -->
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="0" name="self_pickup"
                                                   id="sp2" {{$sp==0?'checked':''}}>
                                            <label class="custom-control-label" for="sp2">{{trans('messages.off')}}</label>
                                        </div>
                                    </div>
                                    <!-- End Custom Radio -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            @php($ev=\App\Model\BusinessSetting::where('key','email_verification')->first()->value)
                            <div class="form-group">
                                <label>{{trans('messages.email')}} {{trans('messages.verification')}}</label><small style="color: red">*</small>
                                <div class="input-group input-group-md-down-break">
                                    <!-- Custom Radio -->
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="1" name="email_verification"
                                                   id="ev1" {{$ev==1?'checked':''}}>
                                            <label class="custom-control-label" for="ev1">{{trans('messages.on')}}</label>
                                        </div>
                                    </div>
                                    <!-- End Custom Radio -->

                                    <!-- Custom Radio -->
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="0" name="email_verification"
                                                   id="ev2" {{$ev==0?'checked':''}}>
                                            <label class="custom-control-label" for="ev2">{{trans('messages.off')}}</label>
                                        </div>
                                    </div>
                                    <!-- End Custom Radio -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.product')}} {{trans('messages.and')}} {{trans('messages.category')}} {{trans('messages.translation')}}</label>
                                <select name="language[]" id="language" data-maximum-selection-length="3" class="form-control js-select2-custom" required multiple=true>
                                    <option value="en">English(default)</option>
                                    <option value="af">Afrikaans</option>
                                    <option value="sq">Albanian - shqip</option>
                                    <option value="am">Amharic - ????????????</option>
                                    <option value="ar">Arabic - ??????????????</option>
                                    <option value="an">Aragonese - aragon??s</option>
                                    <option value="hy">Armenian - ??????????????</option>
                                    <option value="ast">Asturian - asturianu</option>
                                    <option value="az">Azerbaijani - az??rbaycan dili</option>
                                    <option value="eu">Basque - euskara</option>
                                    <option value="be">Belarusian - ????????????????????</option>
                                    <option value="bn">Bengali - ???????????????</option>
                                    <option value="bs">Bosnian - bosanski</option>
                                    <option value="br">Breton - brezhoneg</option>
                                    <option value="bg">Bulgarian - ??????????????????</option>
                                    <option value="ca">Catalan - catal??</option>
                                    <option value="ckb">Central Kurdish - ?????????? (???????????????? ????????????)</option>
                                    <option value="zh">Chinese - ??????</option>
                                    <option value="zh-HK">Chinese (Hong Kong) - ??????????????????</option>
                                    <option value="zh-CN">Chinese (Simplified) - ??????????????????</option>
                                    <option value="zh-TW">Chinese (Traditional) - ??????????????????</option>
                                    <option value="co">Corsican</option>
                                    <option value="hr">Croatian - hrvatski</option>
                                    <option value="cs">Czech - ??e??tina</option>
                                    <option value="da">Danish - dansk</option>
                                    <option value="nl">Dutch - Nederlands</option>
                                    <option value="en-AU">English (Australia)</option>
                                    <option value="en-CA">English (Canada)</option>
                                    <option value="en-IN">English (India)</option>
                                    <option value="en-NZ">English (New Zealand)</option>
                                    <option value="en-ZA">English (South Africa)</option>
                                    <option value="en-GB">English (United Kingdom)</option>
                                    <option value="en-US">English (United States)</option>
                                    <option value="eo">Esperanto - esperanto</option>
                                    <option value="et">Estonian - eesti</option>
                                    <option value="fo">Faroese - f??royskt</option>
                                    <option value="fil">Filipino</option>
                                    <option value="fi">Finnish - suomi</option>
                                    <option value="fr">French - fran??ais</option>
                                    <option value="fr-CA">French (Canada) - fran??ais (Canada)</option>
                                    <option value="fr-FR">French (France) - fran??ais (France)</option>
                                    <option value="fr-CH">French (Switzerland) - fran??ais (Suisse)</option>
                                    <option value="gl">Galician - galego</option>
                                    <option value="ka">Georgian - ?????????????????????</option>
                                    <option value="de">German - Deutsch</option>
                                    <option value="de-AT">German (Austria) - Deutsch (??sterreich)</option>
                                    <option value="de-DE">German (Germany) - Deutsch (Deutschland)</option>
                                    <option value="de-LI">German (Liechtenstein) - Deutsch (Liechtenstein)</option>
                                    <option value="de-CH">German (Switzerland) - Deutsch (Schweiz)</option>
                                    <option value="el">Greek - ????????????????</option>
                                    <option value="gn">Guarani</option>
                                    <option value="gu">Gujarati - ?????????????????????</option>
                                    <option value="ha">Hausa</option>
                                    <option value="haw">Hawaiian - ????lelo Hawai??i</option>
                                    <option value="he">Hebrew - ??????????</option>
                                    <option value="hi">Hindi - ??????????????????</option>
                                    <option value="hu">Hungarian - magyar</option>
                                    <option value="is">Icelandic - ??slenska</option>
                                    <option value="id">Indonesian - Indonesia</option>
                                    <option value="ia">Interlingua</option>
                                    <option value="ga">Irish - Gaeilge</option>
                                    <option value="it">Italian - italiano</option>
                                    <option value="it-IT">Italian (Italy) - italiano (Italia)</option>
                                    <option value="it-CH">Italian (Switzerland) - italiano (Svizzera)</option>
                                    <option value="ja">Japanese - ?????????</option>
                                    <option value="kn">Kannada - ???????????????</option>
                                    <option value="kk">Kazakh - ?????????? ????????</option>
                                    <option value="km">Khmer - ???????????????</option>
                                    <option value="ko">Korean - ?????????</option>
                                    <option value="ku">Kurdish - Kurd??</option>
                                    <option value="ky">Kyrgyz - ????????????????</option>
                                    <option value="lo">Lao - ?????????</option>
                                    <option value="la">Latin</option>
                                    <option value="lv">Latvian - latvie??u</option>
                                    <option value="ln">Lingala - ling??la</option>
                                    <option value="lt">Lithuanian - lietuvi??</option>
                                    <option value="mk">Macedonian - ????????????????????</option>
                                    <option value="ms">Malay - Bahasa Melayu</option>
                                    <option value="ml">Malayalam - ??????????????????</option>
                                    <option value="mt">Maltese - Malti</option>
                                    <option value="mr">Marathi - ???????????????</option>
                                    <option value="mn">Mongolian - ????????????</option>
                                    <option value="ne">Nepali - ??????????????????</option>
                                    <option value="no">Norwegian - norsk</option>
                                    <option value="nb">Norwegian Bokm??l - norsk bokm??l</option>
                                    <option value="nn">Norwegian Nynorsk - nynorsk</option>
                                    <option value="oc">Occitan</option>
                                    <option value="or">Oriya - ???????????????</option>
                                    <option value="om">Oromo - Oromoo</option>
                                    <option value="ps">Pashto - ????????</option>
                                    <option value="fa">Persian - ??????????</option>
                                    <option value="pl">Polish - polski</option>
                                    <option value="pt">Portuguese - portugu??s</option>
                                    <option value="pt-BR">Portuguese (Brazil) - portugu??s (Brasil)</option>
                                    <option value="pt-PT">Portuguese (Portugal) - portugu??s (Portugal)</option>
                                    <option value="pa">Punjabi - ??????????????????</option>
                                    <option value="qu">Quechua</option>
                                    <option value="ro">Romanian - rom??n??</option>
                                    <option value="mo">Romanian (Moldova) - rom??n?? (Moldova)</option>
                                    <option value="rm">Romansh - rumantsch</option>
                                    <option value="ru">Russian - ??????????????</option>
                                    <option value="gd">Scottish Gaelic</option>
                                    <option value="sr">Serbian - ????????????</option>
                                    <option value="sh">Serbo-Croatian - Srpskohrvatski</option>
                                    <option value="sn">Shona - chiShona</option>
                                    <option value="sd">Sindhi</option>
                                    <option value="si">Sinhala - ???????????????</option>
                                    <option value="sk">Slovak - sloven??ina</option>
                                    <option value="sl">Slovenian - sloven????ina</option>
                                    <option value="so">Somali - Soomaali</option>
                                    <option value="st">Southern Sotho</option>
                                    <option value="es">Spanish - espa??ol</option>
                                    <option value="es-AR">Spanish (Argentina) - espa??ol (Argentina)</option>
                                    <option value="es-419">Spanish (Latin America) - espa??ol (Latinoam??rica)</option>
                                    <option value="es-MX">Spanish (Mexico) - espa??ol (M??xico)</option>
                                    <option value="es-ES">Spanish (Spain) - espa??ol (Espa??a)</option>
                                    <option value="es-US">Spanish (United States) - espa??ol (Estados Unidos)</option>
                                    <option value="su">Sundanese</option>
                                    <option value="sw">Swahili - Kiswahili</option>
                                    <option value="sv">Swedish - svenska</option>
                                    <option value="tg">Tajik - ????????????</option>
                                    <option value="ta">Tamil - ???????????????</option>
                                    <option value="tt">Tatar</option>
                                    <option value="te">Telugu - ??????????????????</option>
                                    <option value="th">Thai - ?????????</option>
                                    <option value="ti">Tigrinya - ????????????</option>
                                    <option value="to">Tongan - lea fakatonga</option>
                                    <option value="tr">Turkish - T??rk??e</option>
                                    <option value="tk">Turkmen</option>
                                    <option value="tw">Twi</option>
                                    <option value="uk">Ukrainian - ????????????????????</option>
                                    <option value="ur">Urdu - ????????</option>
                                    <option value="ug">Uyghur</option>
                                    <option value="uz">Uzbek - o???zbek</option>
                                    <option value="vi">Vietnamese - Ti???ng Vi???t</option>
                                    <option value="wa">Walloon - wa</option>
                                    <option value="cy">Welsh - Cymraeg</option>
                                    <option value="fy">Western Frisian</option>
                                    <option value="xh">Xhosa</option>
                                    <option value="yi">Yiddish</option>
                                    <option value="yo">Yoruba - ??d?? Yor??b??</option>
                                    <option value="zu">Zulu - isiZulu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.time_zone')}}</label>
                                <select name="time_zone" id="time_zone" data-maximum-selection-length="3" class="form-control js-select2-custom">
                                    <option value='Pacific/Midway' >(UTC-11:00) Midway Island</option>
                                    <option value='Pacific/Samoa' >(UTC-11:00) Samoa</option>
                                    <option value='Pacific/Honolulu' >(UTC-10:00) Hawaii</option>
                                    <option value='US/Alaska' >(UTC-09:00) Alaska</option>
                                    <option value='America/Los_Angeles' >(UTC-08:00) Pacific Time (US &amp; Canada)</option>
                                    <option value='America/Tijuana' >(UTC-08:00) Tijuana</option>
                                    <option value='US/Arizona' >(UTC-07:00) Arizona</option>
                                    <option value='America/Chihuahua' >(UTC-07:00) Chihuahua</option>
                                    <option value='America/Chihuahua' >(UTC-07:00) La Paz</option>
                                    <option value='America/Mazatlan' >(UTC-07:00) Mazatlan</option>
                                    <option value='US/Mountain' >(UTC-07:00) Mountain Time (US &amp; Canada)</option>
                                    <option value='America/Managua' >(UTC-06:00) Central America</option>
                                    <option value='US/Central' >(UTC-06:00) Central Time (US &amp; Canada)</option>
                                    <option value='America/Mexico_City' >(UTC-06:00) Guadalajara</option>
                                    <option value='America/Mexico_City' >(UTC-06:00) Mexico City</option>
                                    <option value='America/Monterrey' >(UTC-06:00) Monterrey</option>
                                    <option value='Canada/Saskatchewan' >(UTC-06:00) Saskatchewan</option>
                                    <option value='America/Bogota' >(UTC-05:00) Bogota</option>
                                    <option value='US/Eastern' >(UTC-05:00) Eastern Time (US &amp; Canada)</option>
                                    <option value='US/East-Indiana' >(UTC-05:00) Indiana (East)</option>
                                    <option value='America/Lima' >(UTC-05:00) Lima</option>
                                    <option value='America/Bogota' >(UTC-05:00) Quito</option>
                                    <option value='Canada/Atlantic' >(UTC-04:00) Atlantic Time (Canada)</option>
                                    <option value='America/Caracas' >(UTC-04:30) Caracas</option>
                                    <option value='America/La_Paz' >(UTC-04:00) La Paz</option>
                                    <option value='America/Santiago' >(UTC-04:00) Santiago</option>
                                    <option value='Canada/Newfoundland' >(UTC-03:30) Newfoundland</option>
                                    <option value='America/Sao_Paulo' >(UTC-03:00) Brasilia</option>
                                    <option value='America/Argentina/Buenos_Aires' >(UTC-03:00) Buenos Aires</option>
                                    <option value='America/Argentina/Buenos_Aires' >(UTC-03:00) Georgetown</option>
                                    <option value='America/Godthab' >(UTC-03:00) Greenland</option>
                                    <option value='America/Noronha' >(UTC-02:00) Mid-Atlantic</option>
                                    <option value='Atlantic/Azores' >(UTC-01:00) Azores</option>
                                    <option value='Atlantic/Cape_Verde' >(UTC-01:00) Cape Verde Is.</option>
                                    <option value='Africa/Casablanca' >(UTC+00:00) Casablanca</option>
                                    <option value='Europe/London' >(UTC+00:00) Edinburgh</option>
                                    <option value='Etc/Greenwich' >(UTC+00:00) Greenwich Mean Time : Dublin</option>
                                    <option value='Europe/Lisbon' >(UTC+00:00) Lisbon</option>
                                    <option value='Europe/London' >(UTC+00:00) London</option>
                                    <option value='Africa/Monrovia' >(UTC+00:00) Monrovia</option>
                                    <option value='UTC' >(UTC+00:00) UTC</option>
                                    <option value='Europe/Amsterdam' >(UTC+01:00) Amsterdam</option>
                                    <option value='Europe/Belgrade' >(UTC+01:00) Belgrade</option>
                                    <option value='Europe/Berlin' >(UTC+01:00) Berlin</option>
                                    <option value='Europe/Berlin' >(UTC+01:00) Bern</option>
                                    <option value='Europe/Bratislava' >(UTC+01:00) Bratislava</option>
                                    <option value='Europe/Brussels' >(UTC+01:00) Brussels</option>
                                    <option value='Europe/Budapest' >(UTC+01:00) Budapest</option>
                                    <option value='Europe/Copenhagen' >(UTC+01:00) Copenhagen</option>
                                    <option value='Europe/Ljubljana' >(UTC+01:00) Ljubljana</option>
                                    <option value='Europe/Madrid' >(UTC+01:00) Madrid</option>
                                    <option value='Europe/Paris' >(UTC+01:00) Paris</option>
                                    <option value='Europe/Prague' >(UTC+01:00) Prague</option>
                                    <option value='Europe/Rome' >(UTC+01:00) Rome</option>
                                    <option value='Europe/Sarajevo' >(UTC+01:00) Sarajevo</option>
                                    <option value='Europe/Skopje' >(UTC+01:00) Skopje</option>
                                    <option value='Europe/Stockholm' >(UTC+01:00) Stockholm</option>
                                    <option value='Europe/Vienna' >(UTC+01:00) Vienna</option>
                                    <option value='Europe/Warsaw' >(UTC+01:00) Warsaw</option>
                                    <option value='Africa/Lagos' >(UTC+01:00) West Central Africa</option>
                                    <option value='Europe/Zagreb' >(UTC+01:00) Zagreb</option>
                                    <option value='Europe/Athens' >(UTC+02:00) Athens</option>
                                    <option value='Europe/Bucharest' >(UTC+02:00) Bucharest</option>
                                    <option value='Africa/Cairo' >(UTC+02:00) Cairo</option>
                                    <option value='Africa/Harare' >(UTC+02:00) Harare</option>
                                    <option value='Europe/Helsinki' >(UTC+02:00) Helsinki</option>
                                    <option value='Europe/Istanbul' >(UTC+02:00) Istanbul</option>
                                    <option value='Asia/Jerusalem' >(UTC+02:00) Jerusalem</option>
                                    <option value='Europe/Helsinki' >(UTC+02:00) Kyiv</option>
                                    <option value='Africa/Johannesburg' >(UTC+02:00) Pretoria</option>
                                    <option value='Europe/Riga' >(UTC+02:00) Riga</option>
                                    <option value='Europe/Sofia' >(UTC+02:00) Sofia</option>
                                    <option value='Europe/Tallinn' >(UTC+02:00) Tallinn</option>
                                    <option value='Europe/Vilnius' >(UTC+02:00) Vilnius</option>
                                    <option value='Asia/Baghdad' >(UTC+03:00) Baghdad</option>
                                    <option value='Asia/Kuwait' >(UTC+03:00) Kuwait</option>
                                    <option value='Europe/Minsk' >(UTC+03:00) Minsk</option>
                                    <option value='Africa/Nairobi' >(UTC+03:00) Nairobi</option>
                                    <option value='Asia/Riyadh' >(UTC+03:00) Riyadh</option>
                                    <option value='Europe/Volgograd' >(UTC+03:00) Volgograd</option>
                                    <option value='Asia/Tehran' >(UTC+03:30) Tehran</option>
                                    <option value='Asia/Muscat' >(UTC+04:00) Abu Dhabi</option>
                                    <option value='Asia/Baku' >(UTC+04:00) Baku</option>
                                    <option value='Europe/Moscow' >(UTC+04:00) Moscow</option>
                                    <option value='Asia/Muscat' >(UTC+04:00) Muscat</option>
                                    <option value='Europe/Moscow' >(UTC+04:00) St. Petersburg</option>
                                    <option value='Asia/Tbilisi' >(UTC+04:00) Tbilisi</option>
                                    <option value='Asia/Yerevan' >(UTC+04:00) Yerevan</option>
                                    <option value='Asia/Kabul' >(UTC+04:30) Kabul</option>
                                    <option value='Asia/Karachi' >(UTC+05:00) Islamabad</option>
                                    <option value='Asia/Karachi' >(UTC+05:00) Karachi</option>
                                    <option value='Asia/Tashkent' >(UTC+05:00) Tashkent</option>
                                    <option value='Asia/Calcutta' >(UTC+05:30) Chennai</option>
                                    <option value='Asia/Kolkata' >(UTC+05:30) Kolkata</option>
                                    <option value='Asia/Calcutta' >(UTC+05:30) Mumbai</option>
                                    <option value='Asia/Calcutta' >(UTC+05:30) New Delhi</option>
                                    <option value='Asia/Calcutta' >(UTC+05:30) Sri Jayawardenepura</option>
                                    <option value='Asia/Katmandu' >(UTC+05:45) Kathmandu</option>
                                    <option value='Asia/Almaty' >(UTC+06:00) Almaty</option>
                                    <option value='Asia/Dhaka' >(UTC+06:00) Dhaka</option>
                                    <option value='Asia/Yekaterinburg' >(UTC+06:00) Ekaterinburg</option>
                                    <option value='Asia/Rangoon' >(UTC+06:30) Rangoon</option>
                                    <option value='Asia/Bangkok' >(UTC+07:00) Bangkok</option>
                                    <option value='Asia/Bangkok' >(UTC+07:00) Hanoi</option>
                                    <option value='Asia/Jakarta' >(UTC+07:00) Jakarta</option>
                                    <option value='Asia/Novosibirsk' >(UTC+07:00) Novosibirsk</option>
                                    <option value='Asia/Hong_Kong' >(UTC+08:00) Beijing</option>
                                    <option value='Asia/Chongqing' >(UTC+08:00) Chongqing</option>
                                    <option value='Asia/Hong_Kong' >(UTC+08:00) Hong Kong</option>
                                    <option value='Asia/Krasnoyarsk' >(UTC+08:00) Krasnoyarsk</option>
                                    <option value='Asia/Kuala_Lumpur' >(UTC+08:00) Kuala Lumpur</option>
                                    <option value='Australia/Perth' >(UTC+08:00) Perth</option>
                                    <option value='Asia/Singapore' >(UTC+08:00) Singapore</option>
                                    <option value='Asia/Taipei' >(UTC+08:00) Taipei</option>
                                    <option value='Asia/Ulan_Bator' >(UTC+08:00) Ulaan Bataar</option>
                                    <option value='Asia/Urumqi' >(UTC+08:00) Urumqi</option>
                                    <option value='Asia/Irkutsk' >(UTC+09:00) Irkutsk</option>
                                    <option value='Asia/Tokyo' >(UTC+09:00) Osaka</option>
                                    <option value='Asia/Tokyo' >(UTC+09:00) Sapporo</option>
                                    <option value='Asia/Seoul' >(UTC+09:00) Seoul</option>
                                    <option value='Asia/Tokyo' >(UTC+09:00) Tokyo</option>
                                    <option value='Australia/Adelaide' >(UTC+09:30) Adelaide</option>
                                    <option value='Australia/Darwin' >(UTC+09:30) Darwin</option>
                                    <option value='Australia/Brisbane' >(UTC+10:00) Brisbane</option>
                                    <option value='Australia/Canberra' >(UTC+10:00) Canberra</option>
                                    <option value='Pacific/Guam' >(UTC+10:00) Guam</option>
                                    <option value='Australia/Hobart' >(UTC+10:00) Hobart</option>
                                    <option value='Australia/Melbourne' >(UTC+10:00) Melbourne</option>
                                    <option value='Pacific/Port_Moresby' >(UTC+10:00) Port Moresby</option>
                                    <option value='Australia/Sydney' >(UTC+10:00) Sydney</option>
                                    <option value='Asia/Yakutsk' >(UTC+10:00) Yakutsk</option>
                                    <option value='Asia/Vladivostok' >(UTC+11:00) Vladivostok</option>
                                    <option value='Pacific/Auckland' >(UTC+12:00) Auckland</option>
                                    <option value='Pacific/Fiji' >(UTC+12:00) Fiji</option>
                                    <option value='Pacific/Kwajalein' >(UTC+12:00) International Date Line West</option>
                                    <option value='Asia/Kamchatka' >(UTC+12:00) Kamchatka</option>
                                    <option value='Asia/Magadan' >(UTC+12:00) Magadan</option>
                                    <option value='Pacific/Fiji' >(UTC+12:00) Marshall Is.</option>
                                    <option value='Asia/Magadan' >(UTC+12:00) New Caledonia</option>
                                    <option value='Asia/Magadan' >(UTC+12:00) Solomon Is.</option>
                                    <option value='Pacific/Auckland' >(UTC+12:00) Wellington</option>
                                    <option value='Pacific/Tongatapu' >(UTC+13:00) Nuku'alofa</option>

                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @php($footer_text=\App\Model\BusinessSetting::where('key','footer_text')->first()->value)
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.footer')}} {{trans('messages.text')}}</label>
                                <input type="text" value="{{$footer_text}}"
                                       name="footer_text" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>
                    </div>

                    @php($logo=\App\Model\BusinessSetting::where('key','logo')->first()->value)
                    <div class="form-group">
                        <label>{{trans('messages.logo')}}</label><small style="color: red">* ( {{trans('messages.ratio')}} 3:1 )</small>
                        <div class="custom-file">
                            <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="custom-file-label" for="customFileEg1">{{trans('messages.choose')}} {{trans('messages.file')}}</label>
                        </div>
                        <hr>
                        <center>
                            <img style="height: 100px;border: 1px solid; border-radius: 10px;" id="viewer"
                                 onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'"
                                 src="{{asset('storage/app/public/ecommerce/'.$logo)}}" alt="logo image"/>
                        </center>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">{{trans('messages.submit')}}</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
    @php($time_zone=\App\Model\BusinessSetting::where('key','time_zone')->first())
    @php($time_zone = $time_zone->value ?? null)
    $('[name=time_zone]').val("{{$time_zone}}");

    @php($language=\App\Model\BusinessSetting::where('key','language')->first())
    @php($language = $language->value ?? null)
    let language = <?php echo($language); ?>;
    $('[id=language]').val(language);

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });
        $("#language").on("change", function(){
            $("#alert_box").css("display","block");
        });

    </script>
@endpush
