<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Handlers\ImageUploadHandler;

class UsersController extends Controller
{
    //用户编辑个人资料页面
    public function edit(User $user){

        return view('users.edit',compact('user'));
    }

    //用户编辑个人资料逻辑
    public function update(UserRequest $request, ImageUploadHandler $uploader, User $user){

        $data = $request->all();

        if ($request->avatar) {
            $result = $uploader->save($request->avatar, 'avatars', $user->id, 362);
            if ($result) {
                $data['avatar'] = $result['path'];
            }
        }

        $user->update($data);

        return redirect()->route('root');
    }
}
