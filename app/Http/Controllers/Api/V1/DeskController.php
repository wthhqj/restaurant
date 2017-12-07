<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Model\Desk;

/**
 * @Resource("座位模块", uri="/v1/desk")
 */
class DeskController extends Controller
{
    /**
     * 获取全部空座的id
     * 获取全部空座的id
     * @SWG\Get(
     * path="/lsx/public/index.php/v1/desk",
     * tags={"座位模块"},
     * summary="获取全部空座的id",
     * description="获取全部空座的id",
     * produces={"application/json"},
     * @SWG\Response(
     * response="200",
     * description="请求成功"
     * )
     * )
     * dingo 注释
     * @Post("/")
     * @Versions({"v1"})
     * @Transaction({
     * @Request({"employeeId": "101"}),
     * @Response(200, body={"code": 200}),
     * })
     */
    public function get()
    {
        $desk = Desk::select('id')->where('status', '0')->get()->toArray();
        $content = array_column($desk, 'id');
        return $this->response->array(['code'=>200, 'content'=>$content]);
    }
}
