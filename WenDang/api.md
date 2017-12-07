FORMAT: 1A

# restaurant

# 用户模块 [/user]

## 增加/更新员工
增加/更新员工，有employeeid为更新，没有为新增 [POST /user/add]


+ Request (application/json)
    + Body

            {
                "name": "小明",
                "pwd": "12456",
                "age": "12",
                "mobile": "13114253698",
                "salary": "9999.12",
                "avatar": "头像url",
                "employeeid": "用户id"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": "0",
                "id": "1001"
            }

+ Response 422 (application/json)
    + Body

            {
                "code": "40401",
                "msg": "错误原因"
            }

## 删除用户接口
通过用户id删除用户 [POST /user/del]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

## 登陆接口
登陆接口，返回token [POST /user/login]


+ Request (application/json)
    + Body

            {
                "employeeid": "101",
                "pwd": "123456"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200,
                "token": "vbG9naWmpXIn0.7dleHpLyZWv5FK0xrdoy8GP3N_stoCgVwv0ejI3yf88"
            }

## 获取个人信息
获取个人信息接口，如果token失效则会返回HTTPCODE500 [GET /user]


+ Request (application/json)
    + Body

            {
                "token": "token"
            }

+ Response 200 (application/json)
    + Body

            []

## 获取员工列表
获取员工列表 [POST /user/status]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

# 菜品模块 [/food]

## 新增/更新菜品
新增/更新菜品接口 [POST /food/add]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

## 删除菜品
根据菜品id删除菜品 [POST /food/del]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

## 修改菜品状态
修改菜品状态 [POST /food/status]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

## 搜索菜品
搜索菜品，可以根据title或type模糊查询 [POST /food/search]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

## 获取已上架菜品列表
获取已上架菜品列表(status==1) [POST /food/list]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

# 座位模块 [/v1/desk]

## 获取全部空座的id
获取全部空座的id [POST /v1/desk]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

# 订单模块 [/v1/order]

## 下单接口
下单接口 [POST /v1/order/add]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

## 获取订单列表
获取订单列表,需要传递页数，每页条数，起始时间和结束时间,都是可选参数有默认值 [POST /v1/order/list]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }

## 获取订单详情
获取订单详情 [POST /v1/order/detail]


+ Request (application/json)
    + Body

            {
                "employeeId": "101"
            }

+ Response 200 (application/json)
    + Body

            {
                "code": 200
            }