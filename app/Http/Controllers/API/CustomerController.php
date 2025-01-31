<?php

namespace LandMS\Http\Controllers\API;

use Illuminate\Http\Request;
use LandMS\Http\Controllers\Controller;
use LandMS\Customer;
use Carbon\Carbon;
// use Illuminate\Support\Facades\Input;
use Storage;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return Customer::where('type', '=', 'active' )->latest()->paginate(10);
        return response()
            ->json([
                'collection' => Customer::advancedFilter()
            ]);
    }

    public function getCustomers(){
        return Customer::get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'firstname'  =>  'required|string|max:191',
            'lastname'  =>  'required|string|max:191',
            'dob'   =>  'date|before:today|nullable',
            'type'  =>  'nullable'
        ]);

        $first = Customer::where('firstname', '=', $request['firstname'] )->exists();
        $last = Customer::where('lastname', '=', $request['lastname'] )->exists();

        if ($first && $last) {
            return response()->json(['existed' => 'Customer\'s name already existed.']);
        } else {
            if (empty($request['type'])) {
                $request['type'] = 'active';
            }
            if($customer = Customer::create($request->all())){
                Storage::makeDirectory('/customers/' . $customer->id);
                return response()->json(['success' => 'Customer\'s added in successfully.', 'id' => $customer->id]);
            } else {
                return response()->json(['error' => 'Data can not insert.']);
            }
        }

        // if ($first && $last)   {
        //     return response(['status' => 'Customer already existed!']);
        // } else {      
        //     Customer::create([
        //         'lastname'       =>  $request['lastname'],
        //         'firstname'      =>  $request['firstname'],
        //         'sex'            =>  $request['sex'],
        //         'companyname'    =>  $request['companyname'],
        //         'dob'            =>  $request['dob'],
        //         'type'           =>  $request['type'],
        //         'businessphone'  =>  $request['businessphone'],
        //         'personalphone'  =>  $request['personalphone'],
        //         'fax'            =>  $request['fax'],
        //         'email'          =>  $request['email'],
        //         'website'        =>  $request['website'],
        //         'twitter'        =>  $request['twitter'],
        //         'line'           =>  $request['line'],
        //         'remarkcontact'  =>  $request['remarkcontact'],
        //         'postalcode'     =>  $request['postalcode'],
        //         'street'         =>  $request['street'],
        //         'city'           =>  $request['city'],
        //         'country'        =>  $request['country'],
        //         'remarkaddress'  =>  $request['remarkaddress'],
        //     ]);
        //     return response(['status' => 'Customer added successfully!!']);
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Customer::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $this->validate($request, [
            'firstname'  =>  'required|string|max:191',
            'lastname'  =>  'required|string|max:191',
            'dob'   =>  'date|before:today|nullable',
            'type'  =>  'nullable'
        ]);

        $first = Customer::where('firstname', '=', $request['firstname'] )->exists();
        $last = Customer::where('lastname', '=', $request['lastname'] )->exists();

        if ($customer->firstname == $request['firstname'] && $customer->lastname == $request['lastname']) {
            $customer->update($request->all());
            // return 'Customer\'s update in successfully.';
            return response()->json(['success' => 'Customer\'s update in successfully.']);
        } else {
            if ($first && $last) {
                return response()->json(['existed' => 'Customer\'s name already existed.']);
            } else {
                $customer->update($request->all());
                // return 'Customer\'s update in successfully...';
                return response()->json(['success' => 'Customer\'s update in successfully.']);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        // Delete the user
        $customer->delete();
        return ['status' => 'Customer Deleted'];
    }

    public function avatar(Request $request, $id){
        $customer = Customer::findOrFail($id);
        $currentPhoto = $customer->photo;

        if ($request->photo) {
            $extension = $request->photo->getClientOriginalExtension();
            $name = str_random(32).'.'.$extension;

            $path = $file->storeAs(
                'customers/'. $customer->id, $name
            );

            $request->merge(['photo' => $name]);

            if (Storage::exists($currentPhoto)) {
                Storage::delete('customers/'.$customer->id.'/'.$currentPhoto);
            }
            $customer->update($request->all());
            return ['status' => 'Customer Avatar upload in successfully.'];
        }
    }
}
