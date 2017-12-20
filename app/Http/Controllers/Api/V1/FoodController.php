<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Food;
use Illuminate\Validation\Rule;
use Validator;

/**
 * @Resource("菜品模块", uri="/food")
 */
class FoodController extends Controller
{
    /**
     * 新增/更新菜品
     * 新增/更新菜品接口
     * @SWG\Post(
     * path="/lsx/public/index.php/v1/food/add",
     * tags={"菜品模块"},
     * summary="新增/更新菜品接口",
     * description="新增/更新菜品",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="foodId",
     * type="string",
     * description="菜品id，不传为新增，传了为更新",
     * ),
     * @SWG\Parameter(
     * in="formData",
     * name="title",
     * type="string",
     * description="菜名",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="formData",
     * name="img",
     * type="string",
     * description="图片",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="formData",
     * name="type",
     * type="string",
     * description="类别",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="formData",
     * name="price",
     * type="string",
     * description="价格",
     * required=true,
     * ),
     *  @SWG\Parameter(
     * in="formData",
     * name="offprice",
     * type="string",
     * description="折扣价",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="formData",
     * name="status",
     * type="string",
     * description="状态",
     * required=true,
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
     * @Request({"employeeId": "101"}),
     * @Response(200, body={"code": 200}),
     * })
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'img' => 'required',
            'type' => 'required',
            'price' => 'required',
            'offprice' => 'required',
            'status' => ['required', Rule::in(['onshelf', 'offshelf'])]
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "参数错误"]);
        }

        if ($request->has('foodId')) {
            $food = Food::find($request->input('foodId'));
        } else {
            $food = new Food();
            $food->ordernum = 0;
        }

        $food->title = $request->input('title');
        $food->price = $request->input('price');
        $food->offprice = $request->input('offprice');
        $food->type = $request->input('type');
        $food->img = $request->input('img');
        $food->status = $request->input('status');
        $food->save();

        return $this->response()->array(['code'=>200, 'foodId'=>$food->id]);
    }

    /**
     * 删除菜品
     * 根据菜品id删除菜品
     * @SWG\Post(
     * path="/lsx/public/index.php/v1/food/del",
     * tags={"菜品模块"},
     * summary="删除菜品",
     * description="删除菜品",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="foodId",
     * type="string",
     * description="删除菜品",
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
        $validator = Validator::make($request->all(), [
            'foodId' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "参数错误"]);
        }
        Food::destroy($request->input('foodId'));
        return $this->response->array(['code'=>200]);
    }

    /**
     * 修改菜品状态
     * 修改菜品状态
     * @SWG\Post(
     * path="/lsx/public/index.php/v1/food/status",
     * tags={"菜品模块"},
     * summary="修改菜品状态",
     * description="修改菜品状态",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="foodId",
     * type="string",
     * description="菜品id",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="formData",
     * name="status",
     * type="string",
     * description="状态onshelf/offshelf",
     * required=true,
     * ),
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
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'foodId' => 'required',
            'status' => ['required', Rule::in(['onshelf', 'offshelf'])]
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "参数错误"]);
        }
        $food = Food::find($request->input('foodId'));
        $food->status = $request->input('status');
        $food->save();
        return $this->response->array(['code'=>200]);
    }


    /**
     * 搜索菜品
     * 搜索菜品，可以根据title或type模糊查询
     * @SWG\Get(
     * path="/lsx/public/index.php/v1/food/search",
     * tags={"菜品模块"},
     * summary="获取菜品列表",
     * description="获取菜品列表，可以根据title或type模糊查询",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="query",
     * name="page",
     * type="number",
     * description="获取第几页数据",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="query",
     * name="limit",
     * type="number",
     * description="每页多少条数据",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="query",
     * name="title",
     * type="string",
     * description="菜品名称",
     * ),
     * @SWG\Parameter(
     * in="query",
     * name="type",
     * type="string",
     * description="菜品类型",
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @Post("/search")
     * @Versions({"v1"})
     * @Transaction({
     * @Request({"employeeId": "101"}),
     * @Response(200, body={"code": 200}),
     * })
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required',
            'limit' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "参数错误"]);
        }

        $query = Food::select('id as foodId', 'title', 'img', 'price', 'offprice', 'type', 'status', 'ordernum');
        if ($request->has('title')) {
            $query->where('title', 'like', '%' .$request->input('title') . '%');
        }
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }
        $foods = $query->get()->forPage($request->input('page'), $request->input('limit'));
        $count = $query->count();
        return $this->response->array(['code'=>200, 'total'=>$count, 'items'=> $foods]);
    }

    /**
     * 获取已上架菜品列表
     * 获取已上架菜品列表(status==1)
     * @SWG\Get(
     * path="/lsx/public/index.php/v1/food/list",
     * tags={"菜品模块"},
     * summary="获取已上架菜品列表",
     * description="获取已上架菜品列表(status==1)",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="query",
     * name="page",
     * type="number",
     * description="获取第几页数据",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="query",
     * name="limit",
     * type="number",
     * description="每页多少条数据",
     * required=true,
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @Post("/list")
     * @Versions({"v1"})
     * @Transaction({
     * @Request({"employeeId": "101"}),
     * @Response(200, body={"code": 200}),
     * })
     */
    public function getList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required',
            'limit' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "参数错误"]);
        }
;
        $foods = Food::where('status', 1)->get()
            ->forPage($request->input('page'), $request->input('limit'));
        $allPage = Food::where('status', 1)->count();
        $allPage = ceil($allPage / $request->input('limit'));
        return $this->response
            ->array(['code'=>200, 'allPage'=>$allPage,
                'page'=>$request->input('page'), 'items'=> $foods]);
    }
}
