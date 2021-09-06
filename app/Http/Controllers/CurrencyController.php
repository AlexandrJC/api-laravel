<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use \Symfony\Component\DomCrawler\Crawler;

class CurrencyController extends Controller
{
    /**
    * Метод запроса
    */
    private $RequestMethod = 'GET';

    /**
    * Время ожидания ответа веб сервера
    */
    private $TimeOut = 60;

    /**
     * Аддрес запроса
     */
    private $Url="https://www.cbr-xml-daily.ru/daily_json.js";

    private $Content='';

    public $ConversionRates = null;

    private $Response = [
        'header' => [
            'id' => 0,
            'name' => 1
        ],
        'data' => []
    ];

    public function index()
    {
        if($this->tryParceWebContent()){

            return json_encode($this->Response);

        }
 
    }

    public function tryTakeConvertionRates() : bool
    {
        if($this->ConversionRates != null){
            return true;
        }

        try {

            if(!$this->tryTakeWebContent()){
                return false;
            }

            $data = json_decode($this->Content, true);

            if($data !==null){

                $this->ConversionRates = [];

                if(!isset($data['Valute']) || !is_array($data['Valute'])){
                    return false;
                }

                foreach ($data['Valute'] as $value) {
                    
                    if(!isset($value['NumCode']) || !isset($value['Nominal']) || !isset($value['Value'])){
                        continue;
                    }

                    $item = [
                        $value['NumCode'] => 
                        [
                            'Nominal' => $value['Nominal'],
                            'Value' => $value['Value']
                        ]
                    ];

                    $this->ConversionRates = $this->ConversionRates + $item;
                    
                }

            }


        } catch (\Throwable $th) {
            return false;
        }

        return true;


    }
    
    /**
    * Запуск процедуры скрапинга
    */
    public function tryParceWebContent(): bool
    {

        try {

            if(!$this->tryTakeWebContent()){
                return false;
            }

            $data = json_decode($this->Content, true);

            if($data !==null){

                if(!isset($data['Valute']) || !is_array($data['Valute'])){
                    return false;
                }

                foreach ($data['Valute'] as $value) {
                    
                    if(!isset($value['NumCode']) || !isset($value['Name'])){
                        continue;
                    }

                    array_push($this->Response['data'], array($value['NumCode'],$value['Name']));

                }

            }


        } catch (\Throwable $th) {
            return false;
        }

        return true;

    }

    public function tryTakeWebContent() : bool
    {

        try {

            $client = new Client(HttpClient::create(['timeout' => $this->TimeOut]));

            $reader = $client->request($this->RequestMethod, $this->Url);
    
            $code = $client->getInternalResponse()->getStatusCode();
            
            if($code!=200){
                return false;                
            }

            $this->Content = $client->getInternalResponse()->getContent();
    
        } catch (\Throwable $e) {
            return false;                
        }

        return true;

    }


}


