<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;
use Validator;
use Mockery\Exception;
use Swagger\Annotations\Info;
use JWTAuth;

/**
 * @Info(
 *     title="订单系统",
 *     version="v0.0.1"
 * )
 * @Resource("用户模块", uri="/user")
 */
class UserController extends Controller
{

    /**
     * 增加/更新员工
     * 增加/更新员工，有employeeid为更新，没有为新增
     * @SWG\Post(
     * path="/lsx/public/index.php/v1/user/add",
     * tags={"用户模块"},
     * summary="增加/更新员工",
     * description="增加/更新员工，有employeeid为更新，没有为新增",
     * produces={"application/json"},
     * @SWG\Parameter (
     *      in="formData",
     *      name="name",
     *      type="string",
     *      description="员工姓名",
     *      required=true,
     * ),
     * @SWG\Parameter(
     *     in="formData",
     *      name="age",
     *      type="string",
     *      description="员工年龄",
     *      required=true,
     * ),
     * @SWG\Parameter(
     *     in="formData",
     *      name="mobile",
     *      type="string",
     *      description="手机号",
     *      required=true,
     * ),
     * @SWG\Parameter(
     *     in="formData",
     *      name="salary",
     *      type="string",
     *      description="薪资",
     *      required=true,
     * ),
     * @SWG\Parameter(
     *     in="formData",
     *      name="pwd",
     *      type="string",
     *      description="密码",
     *      required=true,
     * ),
     * @SWG\Parameter(
     *     in="formData",
     *      name="avatar",
     *      type="string",
     *      description="头像url",
     * ),
     * @SWG\Parameter(
     *      in="formData",
     *      name="employeeid",
     *      type="string",
     *      description="用户id，若传了这个参数，则为更新，不传则为新增用户",
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @Post("/add")
     * @Versions({"v1"})
     * @Transaction({
     * @Request(
     *     {"name": "小明",
     *     "pwd": "12456",
     *     "age":"12",
     *     "mobile":"13114253698",
     *     "salary":"9999.12",
     *     "avatar":"头像url",
     *     "employeeid":"用户id"}
     *     ),
     * @Response(200, body={"code":"0","id":"1001"}),
     * @Response(422, body={"code":"40401","msg":"错误原因"})
     * })
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'age' => 'required|max:200',
            'mobile' => 'required|max:11',
            'salary' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "缺少必要参数"]);
        }

        //验证手机号
        if(!preg_match("/^1[34578]{1}\d{9}$/",$request->input('mobile'))){
            return $this->response->array(array('code'=>40301, 'msg'=>'手机号格式错误'));
        }
        if ($request->has('employeeId') ) {
            $user = User::find($request->input('employeeid'));
        } else {
            $user = new User();
            $user->password = '123456';
        }

        $user->mobile = $request->input('mobile');
        $user->name = $request->input('name');
        $user->age = $request->input('age');
        $user->salary = $request->input('salary');
        // $user->avatar = asset('storage/upload/defaultheadimg.png');
        $user->avatar = "https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif";
        $user->role = array('employee');
        $user->save();
        return $this->response->array(array('code'=>200, 'id'=>$user->id));
    }

    /**
     * 删除用户接口
     * 通过用户id删除用户
     * @SWG\Post(
     * path="/lsx/public/index.php/v1/user/del",
     * tags={"用户模块"},
     * summary="删除用户接口",
     * description="通过用户id删除用户接口",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="employeeId",
     * type="string",
     * description="用户id",
     * required=true,
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @Post("/del")
     * @Versions({"v1"})
     * @Transaction({
     * @Request({"employeeId": "101"}),
     * @Response(200, body={"code": 200}),
     * })
     */
    public function del(Request $request)
    {
        $this->validate($request, [
            'employeeId' => 'required'
        ]);

        User::destroy($request->input('employeeId'));
        return $this->response->array(['code'=>200]);
    }

    /**
     * @SWG\Post(
     * path="/resetPassword",
     * tags={"用户模块"},
     * summary="修改密码",
     * description="修改密码",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="newpassword",
     * type="string",
     * description="新密码",
     * required=true,
     * ),
     *  @SWG\Parameter(
     * in="query",
     * name="token",
     * type="string",
     * description="用户token",
     * required=true,
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     */
    public function changePwd(Request $request)
    {
        $this->jwtAuth();
        $validator = Validator::make($request->all(), [
            'newpassword' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "参数错误"]);
        }

        User::where('id', $this->_user['id'])
            ->update(['password' => md5($request->input('newpassword'))]);

        return $this->response->array(['code'=>200]);
    }

    /**
     * 登陆接口
     * 登陆接口，返回token
     * @SWG\Post(
     * path="/user/login",
     * tags={"用户模块"},
     * summary="登陆接口",
     * description="发送雇员id和密码，返回token",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="employeeid",
     * type="string",
     * description="雇员id",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="formData",
     * name="pwd",
     * type="string",
     * description="密码",
     * required=true,
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @Post("/login")
     * @Versions({"v1"})
     * @Transaction({
     * @Request({"employeeid": "101", "pwd": "123456"}),
     * @Response(200, body={"code": 200, "token": "vbG9naWmpXIn0.7dleHpLyZWv5FK0xrdoy8GP3N_stoCgVwv0ejI3yf88"}),
     * })
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employeeid' => 'required',
            'pwd' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 40401, 'msg' => "缺少必要参数"]);
        }
        $user = User::where(['id'=>$request->input('employeeid'), 'password'=>md5($request->input('pwd'))])->first();
        if (empty($user)) {
            return $this->response->array(['code'=>40301, 'msg'=>'login error']);
        }
        $token = JWTAuth::fromUser($user);
        return $this->response->array(['code'=>200, 'token'=>$token]);
    }

    /**
     * 获取个人信息
     * 获取个人信息接口，如果token失效则会返回HTTPCODE500
     * @SWG\Get(
     * path="/user?token={token}",
     * tags={"用户模块"},
     * summary="获取个人信息接口",
     * description="获取个人信息接口，如果token失效则会返回HTTP CODE 500",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="query",
     * name="token",
     * type="string",
     * description="token",
     * required=true,
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @GET("/")
     * @Versions({"v1"})
     * @Transaction({
     * @Request({"token": "token"}),
     * @Response(200, body={})
     * })
     */
    public function get()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        $content = array(
            'role' =>$user->role,
            'avatar' => $user->avatar,
            'name' => $user->name,
            'userid' =>$user->id
        );
        return $this->response->array(['code'=>200, 'content'=>$content]);
    }

    /**
     * 获取员工列表
     * 获取员工列表
     * @SWG\Get(
     * path="/user/list",
     * tags={"用户模块"},
     * summary="获取员工列表",
     * description="获取员工列表",
     * produces={"application/json"},
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @Post("/status")
     * @Versions({"v1"})
     * @Transaction({
     * @Request({"employeeId": "101"}),
     * @Response(200, body={"code": 200}),
     * })
     */
    public function getList()
    {
        $users = User::select('id as employeeId', 'name', 'age', 'mobile', 'salary')->get()->toArray();
        return $this->response->array(['code'=>200, 'totale'=>count($users), 'items'=>$users]);
    }

    /**
     * 上传用户头像
     * 上传用户头像
     * @SWG\Post(
     * path="/img/upload",
     * tags={"用户模块"},
     * summary="上传用户头像跟在url后面的描述",
     * description="上传用户头像",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="img",
     * type="file",
     * description="上传的文件",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="query",
     * name="token",
     * type="string",
     * description="用户token",
     * required=true,
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @Post("/")
     * @Versions({"v1"})
     * @Transaction({
     * @Request({"name": "value"}),
     * @Response(200, body={"status_code": 200}),
     * })
     */
    public function imgUpload(Request $request)
    {
        // $this->jwtAuth();
        $file = $request->file('img');
        if ($file->getMimeType() != 'image/jpeg') {
            $this->response->errorForbidden('错误的文件类型');
        }
        if ($file->getSize() > 2000000) {
            $this->response->errorForbidden('文件尺寸过大,2M以内');
        }
        $path = $file->store('public/upload');
        $path = str_replace('public', 'storage', $path);
        return $this->response->array(['code'=>200, 'url'=>asset($path)]);
    }
}
