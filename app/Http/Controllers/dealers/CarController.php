<?php

namespace App\Http\Controllers\dealers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dealers;
use App\Models\Cars;
use App\Models\CarPhotos;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class CarController extends Controller
{
    public function addCar(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'dealer_id'   => 'required',
            'car_codition' => [
                'required' ,
                Rule::in(['Old','New']),
            ],
            'car_name' => 'required||regex:/^[\pL\s]+$/u|min:3',
            'car_brand' => 'required',
            'year_register' => 'required',
            'milage' => 'required',
            'car_type' => [
                'required',
                Rule::in(['Manual','Automatic']),
            ],
            'fuel_type' => [
                'required',
                Rule::in(['Diesel','Petrol','Gas']),
            ],
            'no_seats' => 'required',
            'year_manufacture' => 'required',
            'ownership' => 'required',
            'insurance_validity' => 'required',
            'engin' => 'required',
            'kms_driven' => 'required',
            'price' => 'required|numeric',
            'description' => 'required',
            'image1' => 'required',
            'image2' => 'required',
            'image3' => 'required',
            'image4' => 'required',
            'image5' => 'required',
            'color' => 'required',
            'top_speed' => 'required'


            
        ]);
        
        if ($validator->fails()) {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  __('msg.user.validation.fail'),
                ],
                400
            );
        }
        try 
        {
            $dealer = DB::table('dealers')->where('id',$req->dealer_id)->first();
            // return $dealer;exit;
            if(!empty($dealer))
            {
                $car = [
                    'id' => Str::uuid(),'dealer_id' => $req->dealer_id, 'condition' => $req->car_codition, 'name' => $req->car_name,
                    'brand' => $req->car_brand, 'year_of_registration' => $req->year_register, 'milage' => $req->milage, 
                    'year_of_manufacturing' => $req->year_manufacture, 'type' => $req->car_type, 'fuel_type' => $req->fuel_type, 
                    'no_of_seats' => $req->no_seats, 'ownership' => $req->ownership, 'insurance_validity' => $req->insurance_validity,
                    'engin' => $req->engin, 'kms_driven' => $req->kms_driven, 'top_speed' => $req->top_speed, 'color' => $req->color,
                    'price' => $req->price, 'description' => $req->description, 'status' => 'active', 'created_at' => Carbon::now()
                ];
                $file1 = $req->file('image1');
                $extension1 = $file1->getClientOriginalName();
                $file_path1 = 'dealer_car_photos/';
                $filename1 = time() . '.' . $extension1;
                $upload1 = $file1->move($file_path1, $filename1);
                $image1 = 'dealer_car_photos/' . $filename1;

                $file2 = $req->file('image2');
                $extension2 = $file2->getClientOriginalName();
                $file_path2 = 'dealer_car_photos/';
                $filename2 = time() . '.' . $extension2;
                $upload2 = $file2->move($file_path2, $filename2);
                $image2 = 'dealer_car_photos/' . $filename2;

                $file3 = $req->file('image3');
                $extension3 = $file3->getClientOriginalName();
                $file_path3 = 'dealer_car_photos/';
                $filename3 = time() . '.' . $extension3;
                $upload3 = $file3->move($file_path3, $filename3);
                $image3 = 'dealer_car_photos/' . $filename3;

                $file4 = $req->file('image4');
                $extension4 = $file4->getClientOriginalName();
                $file_path4 = 'dealer_car_photos/';
                $filename4 = time() . '.' . $extension4;
                $upload4 = $file4->move($file_path4, $filename4);
                $image4 = 'dealer_car_photos/' . $filename4;

                $file5 = $req->file('image5');
                $extension5 = $file5->getClientOriginalName();
                $file_path5 = 'dealer_car_photos/';
                $filename5 = time() . '.' . $extension5;
                $upload5 = $file5->move($file_path5, $filename5);
                $image5 = 'dealer_car_photos/' . $filename5;

                $carImage = [
                    'id' => Str::uuid(),'car_id' => $car['id'],'photo1' => $image1, 'photo2' => $image2,'photo3' => $image3,
                    'photo4' => $image4,'photo5' => $image5, 'created_at' => Carbon::now()
                ];

                $saveCarimage = DB::table('car_photos')->where('car_id',$car['id'])->insert($carImage);
                $saveCar = DB::table('cars')->insert($car);
                $SavedCar = DB::table('cars')->leftJoin('brands','brands.id','=','cars.brand')->where('cars.id',$car['id'])->select('cars.*','brands.name as brand_name')->first();
                $SavedCar->Images = DB::table('car_photos')->where('id',$carImage['id'])->first();

                if($saveCar && $saveCarimage)
                {
                    return response()->json(
                        [
                            'status'    => 'success',
                            'message'   => __('msg.dealer.car.success'),
                            'data' => $SavedCar,
                        ],
                        200
                    );
                } else {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   => __('msg.dealer.car.fail'),
                        ],
                        400
                    );
                }
            }
            else 
            {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  __('msg.dealer.profile.dealernotfound'),
                    ],
                    400
                );
            }
        }
        catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
            ], 500);
        }
    }

    public function getCarbyID(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language'  => 'required',
            'car_id' => 'required',
            'dealer_id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try 
        {
            $car = DB::table('cars')->leftJoin('brands','brands.id','=','cars.brand')
            ->where('cars.id',$req->car_id)->where('cars.dealer_id',$req->dealer_id)
            ->select('cars.*','brands.name as brand_name')
            ->first();
            $carImages = DB::table('car_photos')->leftJoin('cars','cars.id','=','car_photos.car_id')
            ->where('car_photos.id',$req->car_id)->get(); 
            $carDetails = DB::table('dealer_plots')
            ->leftJoin('locations','locations.id','=','dealer_plots.location_id')
            ->leftJoin('plots','plots.id','=','dealer_plots.plot_id')
            ->leftJoin('dealers','dealers.id','=','dealer_plots.dealer_id')
            ->leftJoin('cars','cars.id','=','dealer_plots.car_id')
            ->where('dealer_plots.car_id',$req->car_id)
            ->where('dealer_plots.dealer_id',$req->dealer_id)
            ->select('dealer_plots.*','locations.name as location_name','plots.plot_number as plot_number','dealers.name as dealer_name','cars.name as car_name')
            ->first();
            $car_detail = $carDetails;
            $car_images = $carImages;
            $car->Details = $car_detail;
            $car->Images = $car_images;
            return $car;exit;
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function editCar(Request $req)
    {
        $param = $req->only('language','car_id','car_codition','car_name','car_brand','year_register','milage','car_type','fuel_type',
        'no_seats','year_manufacture','ownership','insurance_validity','engin','kms_driven','price','description','color','top_speed',
        'image1','image2','image3','image4','image5');
        $validator = Validator::make($param, [
            'language'  => 'required',
            'car_id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try 
        {
            $car = DB::table('cars')->find($req->car_id);
            // return $car->condition;exit;
            $carImage = DB::table('car_photos')->where('car_id',$req->car_id)->first();
            // return $carImage;exit;
            if(!empty($car))
            {
                $dealer_id = $req->dealer_id;
                
                $image1 = $req->image1;
                $image2 = $req->image2;
                $image3 = $req->image3;
                $image4 = $req->image4;
                $image5 = $req->image5;
                
                if ($image1) {
                    $image1 = optional($req->file('image1'))->getClientOriginalName();
                    $image1name = time() . '.' . $image1;
                    $req->file('image1')->move('dealer_car_photos', $image1name);
                }
                if ($image2) {
                    $image2 = optional($req->file('image2'))->getClientOriginalName();
                    $image2name = time() . '.' . $image2;
                    $req->file('image1')->move('dealer_car_photos', $image2name);
                }
                if ($image3) {
                    $image3 = optional($req->file('image3'))->getClientOriginalName();
                    $image3name = time() . '.' . $image3;
                    $req->file('image1')->move('dealer_car_photos', $image3name);
                }
                if ($image4) {
                    $image4 = optional($req->file('image4'))->getClientOriginalName();
                    $image4name = time() . '.' . $image4;
                    $req->file('image4')->move('dealer_car_photos', $image4name);
                }
                if ($image5) {
                    $image5 = optional($req->file('image5'))->getClientOriginalName();
                    $image5name = time() . '.' . $image5;
                    $req->file('image5')->move('dealer_car_photos', $image5name);
                }

                $data = [
                    'condition' => isset($req->car_codition) ? $req->car_codition : $car->condition,
                    'name' => isset($car_name) ? $car_name : $car->name,
                    'brand' => isset($car_brand) ? $car_brand : $car->brand,
                    'year_of_registration' => isset($year_register) ? $year_register : $car->year_of_registration,
                    'milage' => isset($milage) ? $milage : $car->milage,
                    'type' => isset($car_type) ? $car_type : $car->type,
                    'fuel_type' => isset($fuel_type) ? $fuel_type : $car->fuel_type,
                    'no_of_seats' => isset($no_seats) ? $no_seats : $car->no_of_seats,
                    'year_of_manufacturing' => isset($year_manufacture) ? $year_manufacture : $car->year_of_manufacturing,
                    'ownership' => isset($ownership) ? $ownership : $car->ownership,
                    'insurance_validity' => isset($insurance_validity) ? $insurance_validity : $car->insurance_validity,
                    'engin' => isset($engin) ? $engin : $car->engin,
                    'kms_driven' => isset($kms_driven) ? $kms_driven : $car->kms_driven,
                    'price' => isset($price) ? $price : $car->price,
                    'description' => isset($description) ? $description : $car->description,
                    'color' => isset($color) ? $color : $car->color,
                    'top_speed' => isset($top_speed) ? $top_speed : $car->top_speed,
                    'updated_at' => Carbon::now()
                ];
                // return $req->car_id;exit;
                $update = Cars::where('id',$req->car_id)->update($data);
                $images = [
                    'photo1' => isset($req->image1) ? ('dealer_car_photos/'.$image1name) : $carImage->photo1,
                    'photo2' => isset($req->image2) ? ('dealer_car_photos/'.$image2name) : $carImage->photo2,
                    'photo3' => isset($req->image3) ? ('dealer_car_photos/'.$image3name) : $carImage->photo3,
                    'photo4' => isset($req->image4) ? ('dealer_car_photos/'.$image4name) : $carImage->photo4,
                    'photo5' => isset($req->image5) ? ('dealer_car_photos/'.$image5name) : $carImage->photo5,
                    'updated_at' => Carbon::now()
                ];
                $updateImage = CarPhotos::where('car_id',$req->car_id)->update($images);
                // return $updateImage;exit;
                if($update && $updateImage)
                {
                    $carDetail = DB::table('cars')->leftJoin('brands','brands.id','=','cars.brand')->where('cars.id',$req->car_id)->select('cars.*','brands.name as brand_name')->first();
                    $carUpdatedImage = DB::table('car_photos')->where('car_id',$req->car_id)->first();
                    $carDetail->Images = $carUpdatedImage;
                    return response()->json(
                        [
                            'status'    => 'success',
                            'data' => $carDetail,
                            'message'   =>  __('msg.dealer.car.carupdated'),
                        ],
                        200
                    );
                }
                else
                {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  __('msg.dealer.car.carnotupdate'),
                        ],
                        400
                    );
                }

            }
            else
            {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  __('msg.dealer.car.carnotfound'),
                    ],
                    400
                );
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }
}
