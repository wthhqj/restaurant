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
     * path="/lsx/public/index.php/rest/v1/user/add",
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
            'pwd' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "缺少必要参数"]);
        }

        //验证手机号
        if(!preg_match("/^1[34578]{1}\d{9}$/",$request->input('mobile'))){
            return $this->response->array(array('code'=>40301, 'msg'=>'手机号格式错误'));
        }
        if ($request->has('employeeid') ) {
            $user = User::find($request->input('employeeid'));
        } else {
            $user = new User();
        }

        $user->mobile = $request->input('mobile');
        $user->name = $request->input('name');
        $user->age = $request->input('age');
        $user->salary = $request->input('salary');
        $user->avatar = asset('storage/upload/defaultheadimg.png');
        $user->role = array('admin');
        $user->password = md5($request->input('pwd'));
        $user->save();
        return $this->response->array(array('code'=>200, 'id'=>$user->id));
    }

    /**
     * 删除用户接口
     * 通过用户id删除用户
     * @SWG\Post(
     * path="/lsx/public/index.php/rest/v1/user/del",
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
     * 登陆接口
     * 登陆接口，返回token
     * @SWG\Post(
     * path="/lsx/public/index.php/rest/v1/user/login",
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
     * path="/lsx/public/index.php/rest/v1/user",
     * tags={"用户模块"},
     * summary="获取个人信息接口",
     * description="获取个人信息接口，如果token失效则会返回HTTP CODE 500",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
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

}
