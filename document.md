FORMAT: 1A

# restaurant

# AppHttpControllersApiV1UserController

## dingo 接口名称
dingo 接口描述 [POST /]


+ Request (application/json)
    + Body

            {
                "username": "foo",
                "password": "bar"
            }

+ Response 200 (application/json)
    + Body

            {
                "id": 10,
                "username": "foo"
            }

+ Response 422 (application/json)
    + Body

            {
                "error": {
                    "username": [
                        "Username is already taken."
                    ]
                }
            }