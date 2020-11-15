<?php

namespace App\Http\Controllers\Dashboard;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\Facades\Image;


class UserController extends Controller
{
    public function __construct()
    {
        //create read update delete

    }//end of constructor

    public function index(Request $request)
    {
        $users = User::whereRoleIs('admin')->where(function ($q) use ($request) {

            return $q->when($request->search, function ($query) use ($request) {

                return $query->where('totalname', 'like', '%' . $request->search . '%');


            });

        })->latest()->paginate(5);
        $user = User::get();

        return view('dashboard.users.index', compact('users'));
        return view('layouts.dashboard.app.', compact('user'));

    }//end of index

    public
    function create()
    {
        return view('dashboard.users.create');

    }//end of create

    public
    function store(Request $request)
    {
        $request->validate([
            'totalname' => 'required|unique:users',
            'image' => 'image',
            'neth' => 'required',
            'phone' => 'required' ,
            'email' => 'required' ,
            'account' => 'required',

            'password' => 'required|confirmed',

        ]);


        $request_data = $request->except(['password', 'password_confirmation', 'image']);
        $request_data['password'] = bcrypt($request->password);
        $user = new User();
        $user->password2 = 'password';

        if ($request->image) {

            Image::make($request->image)
                ->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(public_path('uploads/user_images/' . $request->image->hashName()));

            $request_data['image'] = $request->image->hashName();

        }//end of if

        $user = User::create($request_data);
        $user->attachRole('admin');


        session()->flash('success', __('site.added_successfully'));
        return redirect()->route('dashboard.users.index');

    }//end of store

    public
    function edit(User $user)
    {
        return view('dashboard.users.edit', compact('user'));

    }//end of user

    public
    function update(Request $request, User $user)
    {






        if ($request->password == ""||$request->password == null){
            $request_data = $request->except([ 'image','password']);
            $request->validate([

                'totalname' => 'required',
                'image' => 'image',
                'neth' => '',
                'phone' => 'required' ,
                'email' => 'required' ,
                'account' => 'required' ,


            ]);
        } else {
            $request_data = $request->except([ 'image']);
            $request->validate([

                'totalname' => 'required',
                'image' => 'image',
                'neth' => '',
                'phone' => 'required' ,
                'email' => 'required' ,
                'account' => 'required' ,
                'password' => 'required|confirmed',


            ]);

        $request_data['password'] = bcrypt($request->password);
        }

        if ($request->image) {

            if ($user->image != 'default.png') {

                Storage::disk('public_uploads')->delete('/user_images/' . $user->image);

            }//end of inner if

            Image::make($request->image)
                ->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(public_path('uploads/user_images/' . $request->image->hashName()));

            $request_data['image'] = $request->image->hashName();

        }//end of external if
         #endregion

        $user->update($request_data);


        session()->flash('success', __('site.updated_successfully'));
        return redirect()->route('dashboard.welcome');

    }//end of update

    public
    function destroy(User $user)
    {
        if ($user->image != 'default.png') {

            Storage::disk('public_uploads')->delete('/user_images/' . $user->image);

        }//end of if

        $user->delete();
        session()->flash('success', __('site.deleted_successfully'));
        return redirect()->route('dashboard.users.index');

    }//end of destroy

    public function admin($id)
    {
        $user = User::find($id);
        $user->admin = 1;
        $user->save();
        return redirect()->route('dashboard.users.index');
    }

    public function notAdmin($id)
    {
        $user = User::find($id);
        $user->admin = 0;
        $user->save();
        return redirect()->route('dashboard.users.index');
    }


}//end of controller
