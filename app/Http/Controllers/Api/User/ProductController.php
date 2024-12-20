<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth ;
use Illuminate\Support\Facades\DB ;
use App\Helpers\Response;
use App\Http\Requests\ProductRequest;
use App\Models\ProductImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    protected $image_files = [];
    public function index(): JsonResponse
    {
        DB::beginTransaction();
        try {
            $dataList = Product::with('images','ratings','buyers','errors')->where('user_id', Auth::guard('sanctum')->user()->id)
                ->whereNull('deleted_at')
                ->get();
    
            if ($dataList->isNotEmpty()) {
                DB::commit();
                $msg = 'Data Found Success.';
                return Response::successResponse($msg, $dataList);
            } else {
                DB::commit();
                $msg = 'Data not Found .';
                return Response::successResponse($msg, $dataList);
            }
        } catch (\Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }
    

    public function dataInfoAddOrUpdate(ProductRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
    
        
            $validatedData = $request->validated();
    
            $dataInfo = Product::find($request->dataId) ?? new Product();
    
            // Fill product data
            $dataInfo->fill(array_merge($validatedData, [
                'user_id' => Auth::guard('sanctum')->user()->id,
            ]));
            $dataInfo->save();
    
            // Handle image upload if images are provided
            if ($request->has('image') && is_array($request->image)) {
                // Delete old images
                ProductImage::where('product_id', $dataInfo->id)->delete();
    
                // Save new images
                if (!$this->saveProductImage($request, $dataInfo)) {
                    DB::rollBack();
                    return Response::failedResponse('Failed to upload images.');
                }
            }
    
            DB::commit();
            $msg =  $dataInfo ? 'Successfully updated product.' : 'Successfully added product.';
            return Response::successResponse($msg, $dataInfo);
    
        } catch (Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }
    

    protected function nameGenerate($file)
    {
        $name = base64_encode(rand(10000, 99999) . time());
        $name = preg_replace('/[^A-Za-z0-9\-]/', '', $name);
        $extension = $file->getClientOriginalExtension(); 
        return strtolower($name) . '.' . $extension;
    }
    
    
    // Save a single product image
    public function productImages($image, &$imageFiles)
    {
        if ($image) {
            $imageName = $this->nameGenerate($image);
            $manager = new ImageManager(new Driver());
            $path = 'images/products/' . $imageName;
    
            // Resize and save the image
            $manager->read($image)->resize(660, 565)->toJpeg(80)->save(public_path($path));
    
            // Append image path to array
            $imageFiles[] = $path;
        }
    }
    
    // Handle multiple product images
    public function saveProductImage($request, $productInfo)
    {
        if ($request->has('image')) {
            $imageFiles = [];
    
            foreach ($request->image as $image) {
                $this->productImages($image, $imageFiles);

                $productImage = new ProductImage();
                $productImage->image = end($imageFiles);
                $productImage->product_id = $productInfo->id;
                $productImage->save();
            }
    
            return count($imageFiles) > 0;
        }
    
        return true; 
    }


   
    public function updateSellingStatus(Request $request, $dataId=null): JsonResponse
    {
        try {
            DB::beginTransaction();
    
            $dataInfo = Product::find($dataId);
            if($dataInfo){
                $dataInfo->buying_status = $request->buying_status;
                $dataInfo->save();

                $msg = 'Successfully Update Status.';
                return Response::successResponse($msg, $dataInfo);

            }else{
                DB::commit();
                $msg = 'Product not available.';
                return Response::successResponse($msg, $dataInfo);
            }
    
           
           
    
        } catch (Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }


      public function dataInfoDelete($dataId = null):JsonResponse{
        try{
            DB::beginTransaction();
    
            $dataInfo = Product::find($dataId);
           
    
            if(empty($dataInfo)){
                return response()->json([
                    'status' => false,
                    'message' => 'Question Not found.',
                ], 200);
            }
    
           
    
            if($dataInfo->delete()){
                DB::commit();
                $msg = 'Successfully Delete data.';
              return Response::successResponse($msg, $dataInfo);
            } else {
                DB::rollBack();
                $msg = 'Failed to Delete data. Please try again.';
                    return Response::failedResponse($msg, $dataInfo);  
                 }
        } catch (Exception $err) {
            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
      }


}
