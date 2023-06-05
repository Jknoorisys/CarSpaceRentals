<?php

namespace App\Http\Controllers\dealers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cars;
use App\Models\CarPhotos;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class CarController extends Controller
{
    public function __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    // By Javeriya Kauser
    public function getCarBrands(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
           $db = DB::table('brands');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('name', 'LIKE', "%$search%");
            }
            
            $total = $db->count();

            $brands = $db->orderBy('name')
                        ->get();

            if (!($brands->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-brands.success'),
                    'total'     => $total,
                    'data'      => $brands
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-brands.failure'),
                    'data'      => [],
                ],200);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function addCar(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'dealer_id'   => 'required',
            'car_codition' => [
                'required' ,
                Rule::in(['Old','New']),
            ],
            'car_name' => 'required',
            'car_brand' => ['required','alpha_dash', Rule::notIn('undefined')],
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
            'no_seats' => 'required|numeric',
            'year_manufacture' => 'required',
            'ownership' => 'required',
            'insurance_validity' => 'required',
            'engin' => 'required',
            'kms_driven' => 'required',
            'price' => 'required|numeric',
            'description' => 'required',
            'image1'     => 'required_without_all:image2,image3,image4,image5||image||mimes:jpeg,png,jpg,svg',
            'image2'     => 'required_without_all:image1,image3,image4,image5||image||mimes:jpeg,png,jpg,svg',
            'image3'     => 'required_without_all:image2,image1,image4,image5||image||mimes:jpeg,png,jpg,svg',
            'image4'     => 'required_without_all:image2,image3,image1,image5||image||mimes:jpeg,png,jpg,svg',
            'image5'     => 'required_without_all:image2,image3,image4,image1||image||mimes:jpeg,png,jpg,svg',
            'color' => 'required',
            'top_speed' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status'    => 'failed',
                'errors'    =>  $validator->errors(),
                'message'   =>  __('msg.user.validation.fail'),
            ],400);
        }

        try 
        {
            $dealer = DB::table('dealers')->where('id',$req->dealer_id)->first();

            if(!empty($dealer)){
                $car = [
                    'id' => Str::uuid(),
                    'dealer_id' => $req->dealer_id, 
                    'condition' => $req->car_codition, 
                    'name' => $req->car_name,
                    'brand' => $req->car_brand, 
                    'year_of_registration' => $req->year_register, 
                    'milage' => $req->milage, 
                    'year_of_manufacturing' => $req->year_manufacture, 
                    'type' => $req->car_type, 
                    'fuel_type' => $req->fuel_type, 
                    'no_of_seats' => $req->no_seats, 
                    'ownership' => $req->ownership, 
                    'insurance_validity' => $req->insurance_validity,
                    'engin' => $req->engin, 
                    'kms_driven' => $req->kms_driven, 
                    'top_speed' => $req->top_speed, 
                    'color' => $req->color,
                    'price' => $req->price, 
                    'description' => $req->description, 
                    'status' => 'active', 
                    'created_at' => Carbon::now()
                ];

                $file1 = $req->file('image1');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename1 = time().'1.'.$extension;
                    $file1->move('assets/uploads/dealer_car_photos/', $filename1);
                    $photo1 = 'assets/uploads/dealer_car_photos/'.$filename1;
                }
                
                $file2 = $req->file('image2');
                if ($file2) {
                    $extension = $file2->getClientOriginalExtension();
                    $filename2 = time().'2.'.$extension;
                    $file2->move('assets/uploads/dealer_car_photos/', $filename2);
                    $photo2 = 'assets/uploads/dealer_car_photos/'.$filename2;
                }

                $file3 = $req->file('image3');
                if ($file3) {
                    $extension = $file3->getClientOriginalExtension();
                    $filename3 = time().'3.'.$extension;
                    $file3->move('assets/uploads/dealer_car_photos/', $filename3);
                    $photo3 = 'assets/uploads/dealer_car_photos/'.$filename3;
                }

                $file4 = $req->file('image4');
                if ($file4) {
                    $extension = $file4->getClientOriginalExtension();
                    $filename4 = time().'4.'.$extension;
                    $file4->move('assets/uploads/dealer_car_photos/', $filename4);
                    $photo4 = 'assets/uploads/dealer_car_photos/'.$filename4;
                }

                $file5 = $req->file('image5');
                if ($file5) {
                    $extension = $file5->getClientOriginalExtension();
                    $filename5 = time().'5.'.$extension;
                    $file5->move('assets/uploads/dealer_car_photos/', $filename5);
                    $photo5 = 'assets/uploads/dealer_car_photos/'.$filename5;
                }

                $carImage = [
                    'id' => Str::uuid(),
                    'car_id' => $car['id'],
                    'photo1' => $req->image1 ? $photo1 : '', 
                    'photo2' => $req->image2 ? $photo2 : '',
                    'photo3' => $req->image3 ? $photo3 : '',
                    'photo4' => $req->image4 ? $photo4 : '',
                    'photo5' => $req->image5 ? $photo5 : '', 
                    'created_at' => Carbon::now()
                ];
                
                $saveCar = DB::table('cars')->insert($car);   
                $saveCarimage = DB::table('car_photos')->insert($carImage);

                if($saveCar && $saveCarimage){
                    $SavedCar = DB::table('cars')->leftJoin('brands','brands.id','=','cars.brand')->where('cars.id',$car['id'])->select('cars.*','brands.name as brand_name')->first();
                    $SavedCar->Images = DB::table('car_photos')->where('id',$carImage['id'])->first();

                    return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.dealer.car.success'),
                            'data' => $SavedCar,
                        ],200);
                } else {
                    return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.dealer.car.fail'),
                        ],400);
                }
            } else {
                return response()->json([
                        'status'    => 'failed',
                        'message'   =>  __('msg.dealer.profile.dealernotfound'),
                    ],400);
            }
        }
        catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getCarbyID(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language'  => 'required',
            'car_id' => ['required','alpha_dash', Rule::notIn('undefined')],
            'dealer_id' => ['required','alpha_dash', Rule::notIn('undefined')]
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
            $dealer = DB::table('dealers')->where('id',$req->dealer_id)->first();
            if(!empty($dealer)){
                $dealer_car = DB::table('cars')->where('id',$req->car_id)->first();
                if(!empty($dealer_car)){
                    $cars = DB::table('cars')->leftJoin('brands','brands.id','=','cars.brand')
                                            // ->leftjoin('car_photos','car_photos.car_id','=','cars.id')
                                            ->where('cars.id',$req->car_id)->where('cars.dealer_id',$req->dealer_id)
                                            ->select('cars.*','brands.name as brand_name')
                                            ->first();
                                            // return $cars;

                    if(!empty($cars)){
                        // $carImages = DB::table('car_photos')->leftJoin('cars','cars.id','=','car_photos.car_id')
                                                        // ->where('car_photos.id',$req->car_id)->get();
                        // $carDetails = DB::table('bookings')
                        //                 ->leftJoin('locations','locations.id','=','bookings.location_id')
                        //                 ->leftJoin('plots','plots.id','=','bookings.plot_id')
                        //                 ->leftJoin('dealers','dealers.id','=','bookings.dealer_id')
                        //                 ->leftJoin('cars','cars.id','=','bookings.car_id')
                        //                 ->where('bookings.car_id',$req->car_id)
                        //                 ->where('bookings.dealer_id',$req->dealer_id)
                        //                 ->select('bookings.*','locations.name as location_name','plots.plot_name as plot_name','cars.name as car_name',
                        //                 'dealers.name as dealer_name','dealers.email as dealer_email',
                        //                 'dealers.mobile as dealer_mobile_no','dealers.company as dealer_company')
                        //                 ->get();
                        // foreach ($cars as $car) {
                            $cars->location = DB::table('bookings')->where('car_id', '=', $req->car_id)
                                                    ->leftJoin('plots','plots.id','=','bookings.plot_id')
                                                    ->leftJoin('locations','locations.id','=','bookings.location_id')
                                                    ->leftJoin('cars','cars.id','=','bookings.car_id')
                                                    ->leftJoin('dealers','dealers.id','=','bookings.dealer_id')
                                                    ->first(['bookings.*','locations.name as location_name','plots.plot_name as plot_name','cars.name as car_name',
                                            'dealers.name as dealer_name','dealers.email as dealer_email','locations.lat as location_latitude','locations.long as location_longitude','locations.location as location_address',
                                            'dealers.mobile as dealer_mobile_no','dealers.company as dealer_company']);
                            $cars->photos = DB::table('car_photos')->where('car_id', '=', $req->car_id)->first(['id','car_id','photo1','photo2','photo3','photo4','photo5']);
                        // }
        
                        // $car_detail = $carDetails;
                        // $car_images = $carImages;
                        // $car->Details = $car_detail;
                        // $car->Images = $car_images;

                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.dealer.car.cardetail'),
                            'data' => $cars,
                        ],200);
                    } else {
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.dealer.car.fail'),
                        ],200);
                    }
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   =>  __('msg.dealer.car.carnotfound'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   =>  __('msg.dealer.profile.dealernotfound'),
                ],400);
            }
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
        
        $validator = Validator::make($req->all(), [
            'language'  => 'required',
            'car_id' => ['required','alpha_dash', Rule::notIn('undefined')],
            'image1' => 'image|mimes:jpeg,png,jpg,svg',
            'image2' => 'image|mimes:jpeg,png,jpg,svg',
            'image3' => 'image|mimes:jpeg,png,jpg,svg',
            'image4' => 'image|mimes:jpeg,png,jpg,svg',
            'image5' => 'image|mimes:jpeg,png,jpg,svg'
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
            $carImage = DB::table('car_photos')->where('car_id',$req->car_id)->first();

            if(!empty($car))
            {                
                $file1 = $req->file('image1');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename1 = time().'1.'.$extension;
                    $file1->move('assets/uploads/dealer_car_photos/', $filename1);
                    $photo1 = 'assets/uploads/dealer_car_photos/'.$filename1;
                }
                
                $file2 = $req->file('image2');
                if ($file2) {
                    $extension = $file2->getClientOriginalExtension();
                    $filename2 = time().'2.'.$extension;
                    $file2->move('assets/uploads/dealer_car_photos/', $filename2);
                    $photo2 = 'assets/uploads/dealer_car_photos/'.$filename2;
                }

                $file3 = $req->file('image3');
                if ($file3) {
                    $extension = $file3->getClientOriginalExtension();
                    $filename3 = time().'3.'.$extension;
                    $file3->move('assets/uploads/dealer_car_photos/', $filename3);
                    $photo3 = 'assets/uploads/dealer_car_photos/'.$filename3;
                }

                $file4 = $req->file('image4');
                if ($file4) {
                    $extension = $file4->getClientOriginalExtension();
                    $filename4 = time().'4.'.$extension;
                    $file4->move('assets/uploads/dealer_car_photos/', $filename4);
                    $photo4 = 'assets/uploads/dealer_car_photos/'.$filename4;
                }

                $file5 = $req->file('image5');
                if ($file5) {
                    $extension = $file5->getClientOriginalExtension();
                    $filename5 = time().'5.'.$extension;
                    $file5->move('assets/uploads/dealer_car_photos/', $filename5);
                    $photo5 = 'assets/uploads/dealer_car_photos/'.$filename5;
                }

                $data = [
                    'condition' => isset($req->car_condition) ? $req->car_condition : $car->condition,
                    'name' => isset($req->car_name) ? $req->car_name : $car->name,
                    'brand' => isset($req->car_brand) ? $req->car_brand : $car->brand,
                    'year_of_registration' => isset($req->year_register) ? $req->year_register : $car->year_of_registration,
                    'milage' => isset($req->milage) ? $req->milage : $car->milage,
                    'type' => isset($req->car_type) ? $req->car_type : $car->type,
                    'fuel_type' => isset($req->fuel_type) ? $req->fuel_type : $car->fuel_type,
                    'no_of_seats' => isset($req->no_seats) ? $req->no_seats : $car->no_of_seats,
                    'year_of_manufacturing' => isset($req->year_manufacture) ? $req->year_manufacture : $car->year_of_manufacturing,
                    'ownership' => isset($req->ownership) ? $req->ownership : $car->ownership,
                    'insurance_validity' => isset($req->insurance_validity) ? $req->insurance_validity : $car->insurance_validity,
                    'engin' => isset($req->engin) ? $req->engin : $car->engin,
                    'kms_driven' => isset($req->kms_driven) ? $req->kms_driven : $car->kms_driven,
                    'price' => isset($req->price) ? $req->price : $car->price,
                    'description' => isset($req->description) ? $req->description : $car->description,
                    'color' => isset($req->color) ? $req->color : $req->color,
                    'top_speed' => isset($req->top_speed) ? $req->top_speed : $car->top_speed,
                    'updated_at' => Carbon::now()
                ];
               
                $update = Cars::where('id',$req->car_id)->update($data);
                $images = [
                    'photo1' => isset($req->image1) ? ('assets/uploads/dealer_car_photos/'.$filename1) : $carImage->photo1,
                    'photo2' => isset($req->image2) ? ('assets/uploads/dealer_car_photos/'.$filename2) : $carImage->photo2,
                    'photo3' => isset($req->image3) ? ('assets/uploads/dealer_car_photos/'.$filename3) : $carImage->photo3,
                    'photo4' => isset($req->image4) ? ('assets/uploads/dealer_car_photos/'.$filename4) : $carImage->photo4,
                    'photo5' => isset($req->image5) ? ('assets/uploads/dealer_car_photos/'.$filename5) : $carImage->photo5,
                    'updated_at' => Carbon::now()
                    
                ];

                $updateImage = CarPhotos::where('car_id',$req->car_id)->update($images);
                if($update && $updateImage){
                    $carDetail = DB::table('cars')->leftJoin('brands','brands.id','=','cars.brand')->where('cars.id',$req->car_id)->select('cars.*','brands.name as brand_name')->first();
                    $carUpdatedImage = DB::table('car_photos')->where('car_id',$req->car_id)->first();
                    $carDetail->Images = $carUpdatedImage;
                    return response()->json([
                        'status'    => 'success',
                        'data' => $carDetail,
                        'message'   =>  __('msg.dealer.car.carupdated'),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   =>  __('msg.dealer.car.carnotupdate'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   =>  __('msg.dealer.car.carnotfound'),
                ],400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }
    
    // By Javeriya Kauser
    public function assignCarToPlot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'   => 'required',
            'car_id'     => ['required','alpha_dash', Rule::notIn('undefined')],
            'booking_id' => ['required','alpha_dash', Rule::notIn('undefined')],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $booking_id = $request->booking_id;
            $car_id = $request->car_id;

            $car = validateCar($car_id);
            if (empty($car) || $car->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-car'),
                ],400);
            }

            $booking = DB::table('bookings')->where('id', '=', $booking_id)->whereIn('status', ['active', 'upcoming'])->first();
            if (!empty($booking)) {
                $data = [
                    'car_id' => $car_id,
                    'updated_at' => Carbon::now()
                ];

                $assign = DB::table('bookings')->where('id', '=', $booking_id)->update($data);
                if ($assign) {
                    DB::table('cars')->where('id', '=', $car_id)->update(['is_assgined' => 'yes']);
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.dealer.assign-car.success'),
                    ],400);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.dealer.assign-car.failure'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.dealer.assign-car.invalid'),
                ],400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function unassignCarFromPlot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'   => 'required',
            'car_id'     => ['required','alpha_dash', Rule::notIn('undefined')],
            'booking_id' => ['required','alpha_dash', Rule::notIn('undefined')],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $booking_id = $request->booking_id;
            $car_id = $request->car_id;

            $car = validateCar($car_id);
            if (empty($car) || $car->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-car'),
                ],400);
            }

            $booking = DB::table('bookings')->where('id', '=', $booking_id)->where('car_id', '=', $car_id)->first();
            if (!empty($booking)) {
                $data = [
                    'car_id' => '',
                    'updated_at' => Carbon::now()
                ];

                $unassign = DB::table('bookings')->where('id', '=', $booking_id)->update($data);
                if ($unassign) {
                    DB::table('cars')->where('id', '=', $car_id)->update(['is_assgined' => 'no']);
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.dealer.unassign-car.success'),
                    ],400);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.dealer.unassign-car.failure'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.dealer.unassign-car.invalid'),
                ],400);
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
