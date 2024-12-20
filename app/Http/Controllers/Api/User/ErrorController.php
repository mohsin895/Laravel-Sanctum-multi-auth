<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth ;
use Illuminate\Support\Facades\DB ;
use App\Helpers\Response;
use App\Models\Error;
use App\Models\Rating;

class ErrorController extends Controller
{
    public function dataInfoAddOrUpdate(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
    
            // Validate the input
            $request->validate([
                'error' => 'required|string', 
                'productId' => 'required|integer', 
            ]);
    
          $ErrorCount = Error::where('product_id',$request->productId)->count();
          if($ErrorCount > 0){
            $errorDelete = Error::where('product_id',$request->productId)->get();
            foreach($errorDelete as $data){
                Error::where('id',$data->id)->delete();
            }
          }
            $errors = json_decode($request->error, true);
    
          
            if (!is_array($errors)) {
                throw new Exception("The 'error' field must be a valid JSON array.");
            }
    
            $product_id = $request->productId;
    
         
            foreach ($errors as $errorMessage) {
                $dataInfo = Error::firstOrNew([
                    'product_id' => $product_id,
                    'error' => $errorMessage, 
                ]);
                $dataInfo->save();
            }
    
            DB::commit();
    
            $msg = $dataInfo->wasRecentlyCreated
                ? 'Successfully added data.'
                : 'Successfully updated data.';
    
            return Response::successResponse($msg, $dataInfo);
        } catch (Exception $err) {
            DB::rollBack();
            $msg = $request->all();
            return Response::errorResponse($err, $msg);
        }
    }
    
    
}
