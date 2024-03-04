<p align="center"><img src="https://raw.githubusercontent.com/santikzz/squad-rest/main/public/images/squad.png" width="400" alt="SQUAD LOGO"></p>


## About SQUAD

Squad is a comprehensive online platform designed to facilitate seamless collaboration among students of UNICEN by providing a centralized solution for finding study groups. Built with efficiency in mind, this application integrates all necessary functionalities into a single interface. Students can effortlessly create user profiles, form and search for groups, and join them, with the ability to filter by faculty, career, class, exams, or any other relevant criteria. 

## Tech stack
The backend API is developed using PHP Laravel, while the frontend is implemented with React.js, ensuring a robust and user-friendly experience.



## `POST` api/v1/login
###### Authenticate user and get access token
### Request
```json
{
    "email": "example@mail.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```
### Response
```json
{
    "status": "success",
    "message": "User logged in successfully",
    "ulid": "01hq8qaqpqmb6km9hkwnng63ce",
    "token": "6|v9mSgB0uXt1iVwzLeqY1TgWESVLRXiDCXVH3E3Gkd543d4ae"
}
```

## `GET` api/v1/user
###### (Auth required) Get self user data
### Response
```json
{
    "ulid": "01hq8qaqpqmb6km9hkwnng63ce",
    "name": "Julia",
    "surname": "Smith",
    "email": "example@email.com"
}
```
### Errors
```
401: Unauthorized
```

## `GET` api/v1/user/groups
###### (Auth required) Get self user owned groups
Response
```json
[
    {
        "ulid": "c85aa3806d30e2302b3502decff7be2c",
        "owner": {
            "ulid": "01hq8qaqpqmb6km9hkwnng63ce",
            "name": "Julia",
            "surname": "Smith"
        },
        "title": "Ullam repellendus voluptas tempora nobis sit nostrum occaecati hic.",
        "description": "Ad explicabo ad nemo velit possimus perferendis repellat. Et provident autem rerum aspernatur. Est possimus voluptatum cupiditate tempore enim. Nihil alias totam consectetur fuga et et.",
        "tags": [
            "final",
            "presencial"
        ],
        "maxMembers": null,
        "memersCount": 5,
        "privacy": "private",
        "members": []
    }
]
```
Errors
```
401: Unauthorized
```

## `GET` api/v1/users/{userUlid}
###### (Auth required) Get user data
### Response
```json
{
    "ulid": "116dbd01988a2f6d41c39413e547b362",
    "name": "Dr. Berneice Kessler PhD",
    "surname": "Larkin",
    "email": "eden.labadie@example.org"
}
```
### Errors
```
401: Unauthorized
404: Invalid user ulid
```

## `GET` api/v1/groups
###### (Auth required) Get groups listing
### Response
```json
[
    {
        "ulid": "a9bb46413ad595fd07cee2f804d19d8b",
        "owner": {
            "ulid": "116dbd01988a2f6d41c39413e547b362",
            "name": "Dr. Berneice Kessler PhD",
            "surname": "Larkin"
        },
        "title": "Odit nemo qui voluptatem voluptas quia consequatur eum.",
        "description": "Odio occaecati non quam est enim. Quibusdam in omnis repellat repellendus officia tempore aut.",
        "tags": [
            "final",
            "online"
        ],
        "maxMembers": null,
        "memersCount": 3,
        "privacy": "closed",
        "members": [
            {
                "ulid": "9f15ee7be6604a788757633f9f227f1c",
                "name": "Landen Nader",
                "surname": "Rutherford"
            },
            {
                "ulid": "a7fd8346de872fd999208d568a14c40b",
                "name": "Maximo Gislason",
                "surname": "Walter"
            },
            {
                "ulid": "67818123309f73a89938a95e715d1e1c",
                "name": "Prof. Kiley Wuckert DVM",
                "surname": "Glover"
            }
        ]
    } ...
```
### Errors
```
401: Unauthorized
```

## `GET` api/v1/groups/{groupUlid}
###### (Auth required) Get group detail
### Response
```json
    {
        "ulid": "a9bb46413ad595fd07cee2f804d19d8b",
        "owner": {
            "ulid": "116dbd01988a2f6d41c39413e547b362",
            "name": "Dr. Berneice Kessler PhD",
            "surname": "Larkin"
        },
        "title": "Odit nemo qui voluptatem voluptas quia consequatur eum.",
        "description": "Odio occaecati non quam est enim. Quibusdam in omnis repellat repellendus officia tempore aut.",
        "tags": [
            "final",
            "online"
        ],
        "maxMembers": null,
        "memersCount": 3,
        "privacy": "closed",
        "members": [
            {
                "ulid": "9f15ee7be6604a788757633f9f227f1c",
                "name": "Landen Nader",
                "surname": "Rutherford"
            },
            {
                "ulid": "a7fd8346de872fd999208d568a14c40b",
                "name": "Maximo Gislason",
                "surname": "Walter"
            },
            {
                "ulid": "67818123309f73a89938a95e715d1e1c",
                "name": "Prof. Kiley Wuckert DVM",
                "surname": "Glover"
            }
        ]
    }
```
### Errors
```
401: Unauthorized
404: Invalid group ulid
```

## `POST` api/v1/groups
###### (Auth required) Create group
### Request
```json
{
    "title": "This is a group!",
    "description": "an interesting description",
    "privacy": "open",
    "hasMaxMembers": 1,
    "maxMembers": 6
}
```

### Response
```json
{
    "ulid": "01hqb59waqazkn3mjg73kze4x1",
    "owner": {
        "ulid": "01hq8qaqpqmb6km9hkwnng63ce",
        "name": "Julia",
        "surname": "Smith"
    },
    "title": "This is a group!",
    "description": "an interesting description",
    "tags": [],
    "maxMembers": 6,
    "memersCount": 0,
    "privacy": "open",
    "members": []
}

```
### Errors
```
201: Success
400: Invalid request or bad parameters
```

## `PUT` api/v1/groups
###### (Auth required) Update group (only group owner)
### Request
```json
{
    "title": "This is a cool group!",
    "description": "not an interesting description",
    "privacy": "open",
    "hasMaxMembers": 1,
    "maxMembers": 6
}
```
### Response
```json
{
    "ulid": "01hqb59waqazkn3mjg73kze4x1",
    "owner": {
        "ulid": "01hq8qaqpqmb6km9hkwnng63ce",
        "name": "Julia",
        "surname": "Smith"
    },
    "title": "This is a cool group!",
    "description": "not an interesting description",
    "tags": [],
    "maxMembers": 6,
    "memersCount": 0,
    "privacy": "open",
    "members": []
}
```
### Errors
```
401: Unauthorized or not owner
201: Success
400: Invalid request or bad parameters
```

## `DELETE` api/v1/groups/{groupUlid}
###### (Auth required) Delete group (only group owner)
### Response
```json
{
    "message": "Group deleted successfully"
}
```
### Errors
```
401: Unauthorized or not owner
201: Success
```
