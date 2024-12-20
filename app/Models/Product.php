<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
   protected $fillable =[
               'tree_space',
                'cut_type',
                'stack_number',
                'log_length',
                'average_diameter', 
                'log_count',
                'stack_placement',
                'property_name',
                'volume', 
                'geo_location',
                'user_id',
   ];

   public function images()
   {
       return $this->hasMany(ProductImage::class, 'product_id');
   }
   public function ratings()
   {
       return $this->hasOne(Rating::class, 'product_id');
   }
   public function buyers()
   {
       return $this->hasOne(Buyer::class, 'product_id');
   }
   public function errors()
   {
       return $this->hasMany(Error::class, 'product_id');
   }


   protected static function booted()
   {
       static::deleting(function ($product) {
           // Check if soft delete is enabled
           if ($product->isForceDeleting()) {
               $product->images()->forceDelete();
               $product->ratings()->forceDelete();
               $product->errors()->forceDelete();
               $product->buyers()->forceDelete();
           } else {
               $product->images()->delete(); 
               $product->ratings()->delete();
               $product->errors()->delete();
               $product->buyers()->delete();
           }
       });

       static::restoring(function ($product) {
           $product->images()->restore();
           $product->ratings()->delete(); 
           $product->errors()->delete(); 
           $product->buyers()->delete(); 
       });
   }
}
