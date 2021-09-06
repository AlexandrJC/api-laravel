<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;
use App\Http\Requests\ApiRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\CurrencyController;

class TransactionController extends Controller
{

    /**
     * Default response error
     */
    private $ErrorResponse = ['success' => 0, 'message' => 'Неизвестная ошибка'];

    /**
     * Base part of success response
     */
    private $SuccessResponse = ['success' => 1];

    /**
     * Error Message Text
     */
    private $ErrorMessage = "";

    /**
    * Success part of update result
    */
    private $UpdateResult = [];

    /**
    * Success part of delete result response
    */
    private $DeleteResult = [];

    private CurrencyController $Currency;

    public function __construct()
    {
        $this->Currency=new CurrencyController();
    }

    /**
     * Success response json generator
     */
    public function Success(array $data) : string
    {
        return json_encode($this->SuccessResponse + $data);
    }

    /**
    * Error response json generator
    */
    public function Error($message = null) : string
    {

        if($message != null)
        {
            $this->ErrorResponse['message'] = $message;
        }

        return json_encode($this->ErrorResponse);
        
    }

    public function index()
    {
        $transactions = Transaction::orderBy('date_added','desc')->get();
        return response()->json($transactions);

    }


    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $validation_rules = [
            'from_currency' => 'required|digits:3',
            'to_currency'=>'required|digits:3',
            'amount' => 'required|numeric|gt:0'
        ];


        $json=$request->json();

        if ($json) {

            $input=$json->all();

            $validator = Validator::make($input, $validation_rules);

            if ($validator->fails()) 
            {
                return $this->Error("Ошибка входных данных\n");
            }


            $exchenge_data = $this->convert($input['from_currency'], $input['to_currency'], $input['amount']);

            $transaction = new Transaction();
            $transaction->from_currency=$input['from_currency'];
            $transaction->to_currency=$input['to_currency'];
            $transaction->amount=$input['amount'];
            $transaction->course=$exchenge_data['course'];
            $transaction->converted=$exchenge_data['converted'];
            $transaction->save();

            $new_transaction =  Transaction::where('id',$transaction->id)->get(['id','from_currency','to_currency','amount','converted','date_added'] ) -> toArray();
                        
            return $this->Success($new_transaction[0]);

        }
        
        return $this->Error("Отсутствуют параметры запроса");
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $json=$request->json();

        if ($json) {
            
            $input=$json->all();

            if(isset($input['deleted']) && is_array($input['deleted'])){

                if(!$this->TryDelete($input['deleted'])){
                    return $this->Error($this->ErrorMessage);
                }
            }

            if(isset($input['updated'])){
                if(!$this->TryUpdate($input['updated'])){
                    return $this->Error($this->ErrorMessage);
                }
            }

        }

        $success_data=[];

        if($this->DeleteResult!=[]){
            $success_data['deleted']=$this->DeleteResult;
        }

        if($this->UpdateResult!=[]){
            $success_data['updated']=$this->UpdateResult;
        }

        return $this->Success($success_data);

    }

    public function TryDelete(array $to_delete){

        $validation_rules = ['id'=>'required|integer'];

        $result=true;

        foreach ($to_delete as $item) {

            $validator = Validator::make($item, $validation_rules);

            if ($validator->fails()) 
            {
                $this->ErrorMessage.="Ошибка входных данных\n";
                $result=false;
                continue;
            }

            try{
                
                $transaction = Transaction::find($item['id']); 
                $transaction -> delete();

                array_push($this->DeleteResult, ['id'=>$item['id']]);

            } catch (\Throwable $e) {
                $result=false;
                $this->ErrorMessage.="Строка с ID ранее уже была удалена\n";
            } 

        }

        return $result;

    }

    public function TryUpdate(array $to_update){

        $validation_rules = [
            'id' => 'required|integer',
            'from_currency' => 'required|digits:3',
            'to_currency'=>'required|digits:3',
            'amount' => 'required|numeric|gt:0'
        ];

        $result=true;

        foreach ($to_update as $item) {

            $validator = Validator::make($item, $validation_rules);

            if ($validator->fails()) 
            {
                $this->ErrorMessage.="Ошибка входных данных\n";
                $result=false;
                continue;
            }

            try{
                
                $transaction = Transaction::find($item['id']); 

                $exchenge_data = $this->convert($item['from_currency'], $item['to_currency'], $item['amount']);

                $transaction->from_currency=$item['from_currency'];
                $transaction->to_currency=$item['to_currency'];
                $transaction->amount=$item['amount'];
                $transaction->course=$exchenge_data['course'];
                $transaction->converted=$exchenge_data['converted'];
                $transaction->save();

                array_push($this->UpdateResult, ['id'=>$item['id'],'converted'=>$exchenge_data['converted']]);
    
            } catch (\Throwable $e) {
                $result=false;
                $this->ErrorMessage.="Элемент для редактирования отсутствует\n";
            } 

        }

        return $result;

    }

    /**
     * Take conversion Data 
     */
    public function convert($from, $to, $amount){

        $data = [
            'course' => 0,
            'converted' => 0
        ];


        if($this->Currency->tryTakeConvertionRates()){

            if(array_key_exists($from, $this->Currency->ConversionRates) && array_key_exists($to, $this->Currency->ConversionRates)){

                $RatesFrom = $this->Currency->ConversionRates[$from];
                $RatesTo = $this->Currency->ConversionRates[$to];

                $OneItemFromPrice = round($RatesFrom['Value']/$RatesFrom['Nominal'], 6);
                $OneItemToPrice = round($RatesTo['Value']/$RatesTo['Nominal'],6);

                $data['course']=round($OneItemFromPrice/$OneItemToPrice,4);
                $data['converted'] = round($data['course']*$amount,4);
                
            }

        }

        return $data;

    }

}
