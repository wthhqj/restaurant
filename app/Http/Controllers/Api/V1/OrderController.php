<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Order;
use Validator;
use App\Model\Desk;
use App\Model\Food;
use Illuminate\Support\Facades\DB;

/**
 * @Resource("订单模块", uri="v1/order")
 */
class OrderController extends Controller
{
    /**
     * 下单接口
     * 下单接口
     * @SWG\Post(
     * path="/lsx/public/index.php/v1/order/add",
     * tags={"订单模块"},
     * summary="下单接口",
     * description="下单接口",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="foods",
     * type="string",
     * description="菜品数组",
     * required=true,
     * ),
     * @SWG\Parameter(
     * in="formData",
     * name="deskId",
     * type="number",
     * description="餐桌编号",
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
        $this->jwtAuth();
        $validator = Validator::make($request->all(), [
            'foods' => 'required|array',
            'deskId' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "参数错误"]);
        }

        //验证餐桌号是否存在
        $exists = Desk::find($request->input('deskId'));
        if (empty($exists)) {
            return $this->response->array(['code'=>40403, 'msg'=> '餐桌号不存在']);
        }
        // 修改餐桌的状态为有人
        $desk = Desk::find($request->input('deskId'));
        $desk->status = '1';
        $desk->save();

        //验证foods格式并返回订单总价
        if (! $totalPrice = $this->_valid($request->input('foods'))) {
            return $this->response->array(['code'=>40402, 'msg'=> '参数错误']);
        }

        $order = new Order();
        $order->cart_list = $request->input('foods');
        $order->status = 1;
        $order->desk_id = $request->input('deskId');
        $order->employee = $this->_user->name;
        $order->money =$totalPrice;
        $order->date_str = date('Y-m-d');
        $order->save();
        return $this->response()->array(['code'=>200, 'orderId'=>$order->id]);
    }

    /**
     * 获取订单列表
     * 获取订单列表,需要传递页数，每页条数，起始时间和结束时间,都是可选参数有默认值
     * @SWG\Get(
     * path="/lsx/public/index.php/v1/order/list",
     * tags={"订单模块"},
     * summary="获取订单列表",
     * description="获取订单列表,需要传递页数，每页条数，起始时间和结束时间,都是可选参数有默认值",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="query",
     * name="page",
     * type="string",
     * description="页数，不传则默认为1",
     * ),
     * @SWG\Parameter(
     * in="query",
     * name="limit",
     * type="string",
     * description="每页条数，不传默认为10",
     * ),
     * @SWG\Parameter(
     * in="query",
     * name="st",
     * type="string",
     * description="查询的起始时间，不传则为当前时间-24小时",
     * ),
     * @SWG\Parameter(
     * in="query",
     * name="et",
     * type="string",
     * description="查询的结束时间，不传则为当前时间",
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
        $page = $request->input('page', 1);
        $perPage = $request->input('limit', 10);
        $st = $request->input('st', date('Y-m-d H:i:s',strtotime("-1 day")));
        $et = $request->input('et', date('Y-m-d H:i:s'));
        $orders = Order::select('id as orderId', 'desk_id as deskId',
            'employee', 'money', 'status', 'created_at as timestamp')
            ->where('created_at', '>=', $st)->where('created_at', '<', $et)->get();
        return $this->response->array(['code'=>200, 'items'=>$orders]);
    }

    /**
     * 获取订单详情
     * 获取订单详情
     * @SWG\Get(
     * path="/lsx/public/index.php/v1/order/detail",
     * tags={"订单模块"},
     * summary="获取订单详情",
     * description="获取订单详情",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="query",
     * name="orderId",
     * type="string",
     * description="订单id",
     * required=true,
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @Post("/detail")
     * @Versions({"v1"})
     * @Transaction({
     * @Request({"employeeId": "101"}),
     * @Response(200, body={"code": 200}),
     * })
     */
    public function detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderId' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 40401, 'msg' => "参数错误"]);
        }

        $order = Order::find($request->input('orderId'));
        if (empty($order)) {
            return $this->response->array(['code'=>40401, 'msg'=>'订单id错误']);
        }

        $goodList = array();
        foreach ($order->cart_list as $list) {
            $goodList[] = array(
                'id' => $list['id'],
                'title' => $list['title'],
                'quantity' => $list['count'],
                'realPrice' => $list['offprice'],
                'price' => $list['price'],
                'url' => $list['img'],
                'total' => $list['count'] * $list['offprice']
            );
        }

        return $this->response->array(['code'=>200, 'goodList'=> $goodList,
            'status'=> $order->status , 'money' => $order->money , 'deskId'=>$order->desk_id]);
    }

    /**
     * @SWG\Get(
     * path="getChartInfo",
     * tags={"订单模块"},
     * summary="获取一周订单数量",
     * description="获取一周订单数量，已结账，待付款，已取消",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="time",
     * type="string",
     * description="时间YYYY-mm-dd格式",
     * required=false,
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     */
    public function getChartInfo(Request $request)
    {
        $timestamp = $request->input('time', date('Y-m-d'));
        $timeRange = $this->_getOneWeek($timestamp);
        $orders =
            DB::select('SELECT count(*) as value,status FROM `orders` WHERE date_str >= ? and date_str <= ? group BY status',
                [$timeRange['st'], $timeRange['et']]);
        $res = [];
        $status = ['1'=>'待付款', '2'=>'已结账', '3'=>'已取消'];
        foreach ($orders as $order) {
            $res[] = ['value'=>$order->value, 'name'=>$status[$order->status]];
        }
        return $this->response->array(['code'=>200,'content'=>$res]);
    }


    /**
     * @SWG\Get(
     * path="/getMoneyInfo",
     * tags={"订单模块"},
     * summary="获取每星期的成交额情况接口",
     * description="获取每星期的成交额情况接口",
     * produces={"application/json"},
     * @SWG\Parameter(
     * in="formData",
     * name="time",
     * type="string",
     * description="时间YYYY-mm-dd格式",
     * required=false,
     * ),
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     */
    public function getMoneyInfo(Request $request)
    {
        $timestamp = $request->input('time', date('Y-m-d'));
        $timeRange = $this->_getOneWeek($timestamp);
        $orders =
            DB::select('SELECT sum(money) as money , date_str FROM `orders` WHERE date_str >= ? and date_str <= ?  AND  status=2 group BY date_str',
                [$timeRange['st'], $timeRange['et']]);
        //冒泡有小到大
        $orders =  json_decode(json_encode($orders),TRUE);
        for ($i=0; $i<count($orders); $i++) {
            for ($j=0;$j<count($orders)-$i-1; $j++) {
                if (strtotime($orders[$j]['date_str']) > strtotime($orders[$j+1]['date_str'])) {
                    $temp = $orders[$j];
                    $orders[$j] = $orders[$j+1];
                    $orders[$j+1] = $temp;
                }
            }
        }
        return $this->response->array(['code'=>200,'content'=>$orders]);
    }

    /**
     * 根据时间参数获取当周  周一到当前时间 YYY-mm-dd
     * @param $timestamp string 时间戳
     * @return array YYY-mm-dd H:i:s
     *
     */
    private function _getOneWeek($timestamp)
    {
        $timestamp = strtotime($timestamp);
        $weekNum = date('w', $timestamp) == 0? 6: date('w', $timestamp)-1;
        $st = date('Y-m-d', ($timestamp - $weekNum*86400));
        $et = date('Y-m-d', $timestamp);
        return ['st'=>$st, 'et'=>$et];
    }

    /**
     * 验证foods数组里应该有的字段,返回订单总金额，验证菜品id和deskid是否存在于数据表中
     * @param $foodArr
     * @return mixed
     */
    private function _valid($foodArr)
    {
        $totalPrice = 0.0;
        //foods数组里应该有的字段
        $needField = ['id', 'title', 'count', 'price', 'offprice'];
        foreach ($foodArr as $food) {
            //验证foodid是否存在
            $exists = Food::find($food['id']);
            if (empty($exists)) {
                return false;
            }
            //增加点餐数
            $updatefood = Food::find($food['id']);
            $updatefood->ordernum = $updatefood->ordernum + $food['count'];
            $updatefood->save();

            //遍历foods数组
            foreach($needField as $field) {
                //遍历应该拥有的字段
                if (!array_key_exists($field, $food)) {
                    return false;
                }
            }
            if ($food['offprice']< $food['price']) {
                $totalPrice += $food['count']*$food['offprice'];                
            } else {
                $totalPrice += $food['count']*$food['price']; 
            }
        }
        return $totalPrice;
    }
}
