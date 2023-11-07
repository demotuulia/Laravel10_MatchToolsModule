<img src="docs/img/logoBlue.png" width="400">

###  Contents

* [Introduction](#intro-section)
  * [Used Technology](#intro-technology-section)
  * [Roles](#intro-roles-section)
* [Code](#code-code-section)
  * [Code](#code-code-section)
  * [Tests](#code-tests-section)
  * [Matches](#code-matches-section)
  * [Indexing profiles](#code-indexing-profiles-section)
* [Database](#database-section)
* [Endpoints](#endpoints-section)
  * [Registrate](#endpoints-registrate)
  * [Login](#endpoints-login)
  * [Create profile form](#endpoints-forms-store)
  * [Create profile](#endpoints-profiles-store)
  * [Get profile form](#endpoints-profiles-show)
  * [Update profile form](#endpoints-profiles-update)
  * [Find matches](#endpoints-profiles-matches)
* [Install](#install-section)

<a name='intro-section'></a>
# Match Tools API

 This is my research work with Laravel 10. I have implement a match tools module. This can be
used as an API to search matches between profiles.
 The profiles are question forms with fields which are called matches.  For a profile, to be selected
all matches must match with the profile of the opposite party.

To get a quick overview what this module does,
I advise to have a look of the [Demo Test](/main/Modules/Matches/Tests/Integration/DemoTest.php) or 
the readme file  (TODO) of the front end part of my project (TODO).

This application can be used to define matches between companies and clients.
In my example  I use companies with vacancies and professionals with profiles.
With this tool both of the parties can find matches for their profiles.
As well this could be used for example for rental apartments or boats.\
\
The core of this project are the dynamic match forms. The match forms
are filled by both parties and the search of matches is based on these forms.
The administrator of the site can define the forms.
\
The matches search can be defined in two ways.
1) <b>Exact match</b>
In this mode all the matches must match to be selected.
2) <b>Score match</b>
In this mode all the profiles which have at least one match are selected.
Each profile gets a score by the amount of matches. If the matches are exact the score is 100% for the profile.
In the results the profiles are sorted by the score.

This file is not a full documentation of the project. It gives an overview.
Also this is not a complete project. It is focused in the Matches module. In the app module are some controllers
for the user management and authorization by standard Laravel.


<a name='intro-technology-section'></a>
## Used technology

* Laravel 10
* Php 8
* Mysql with Eloquent
* Php Unit Tests
* Code sniffer with Php Psr standards
* Phpstan


<a name='intro-roles-section'></a>
## Roles

| Role         | Description                                                                    |
|--------------|--------------------------------------------------------------------------------|
| admin        | Can login to the admin module and edit forms                                   |
| company      | Can login to the front end and manage his vacancies and find matches for them  |
| professional | Can login to the front end and manage his/her profile and find matches for it. |


<a name='code-section'></a>
# Code
<a name='code-code-section'></a>
## Code
This module can be found in the folder ```Modules/Matches```.
In the app module are some controllers for the user management and authorization by standard Laravel.
It is written as much as possible by Laravel 10 standards.
The best way to understand this are the
[Tests](https://github.com/demotuulia/Laravel10_MatchToolsModule/tree/main/Modules/Matches/Tests)

<a name='code-tests-section'></a>
## Tests
This project has been written by test driven development and the tests are found in
```Modules/Matches/Tests```
I the test ```Modules/Matches/Tests/Integration/DemoTest.php``` gives an overview of this project.
For the rest there are basically integration test per controller. The matches search is tested in
```Modules/Matches/Tests/Features```

<a name='code-matches-section'></a>
## Matches

The matches are realized by the following factory design pattern:

<img src="docs/img/uml.png">

Each match type has its own service class is which is crated by MatchesTypeManager by the method 'make'.

Each service hase the following functions:

| Function             | Description                                                                                                   |
|----------------------|---------------------------------------------------------------------------------------------------------------|
| getSearchQuery       | Return the part of the sql, when the matches are searched by joins from each match. This is very slow method. |
| getViewSearchQuery   | Return the part of the sql, which is used to search matches from the flat view table.                         |
| isMatch | This used to define if the match is exact and shoud get a score point.                                        |
| statistics | Return the quries to render the admin dasboard statistics.                                                    |

<a name='code-indexing-profiles-section'></a>
## Indexing Profiles
At the moment the indexing of the profiles is done by dynamic views. Each match has its column in the view. By this way we avoid slow and
complex views.
Each time a match is deleted or added a job is stared to reindex the view.
For more details see [SearchViewService](/Modules/Matches/Services/Search/SearchViewService.php#L32).


<a name='database-section'></a>
# Database
The tables used for the Matches module are shown below.
<img src="docs/img/dbDiagaram.jpg" width="100%">

### matches_form

 A form to include the matches. The administrator can add, delete and modify these fields. 
The form is rendered on the client side. There can be several forms, fore-example on per item category.

### matches

One particular match in the form, like 'years of experience'\ 
The match can have different types like

  * EQUAL 
  * BIGGER_THAN
  * MENU
  * DATE_FROM

There is more detailed documentation in the section Match Types.

#### Most important fields
| Field           | Description                                                                                           |
|-----------------|-------------------------------------------------------------------------------------------------------|
| db_code         | This is an unique field name to be use in indexing. The name is created automatically from the label. |
| label           | Name of filed to be rendered in the forms.                                                            |
| ordering        | The fields are ordered by this filed, and can be managed by the administrator.                        |
| match_type      | The type of the match. See a 'matches types'                                                          |
| matches_form_id | Foreign key to refer to matches_form                                                                  |

### matches_profile

 Each profile gets its own profile. This profile has the matches values in 
the tables ```matches_values``` and     ```matches_option_values```

#### Most important fields
| Field           | Description                                                                                      |
|-----------------|--------------------------------------------------------------------------------------------------|
| user_id         | Foreign key, which refers to the Laravel standard table ```users```                                  |
| is_professional | Values 0 or 1. All profiles are saved in one form. This separates the clients and the companies. |
| name            | Name of the profile                                                                              |
| description     | Description                                                                                      |
| tags            | All of the keywords of the profile.                                                              |

### matches_values

Each match of a profile having unique values is saved on this table.

#### Most important fields
| Field             | Description                                               |
|-------------------|-----------------------------------------------------------|
| matches_pofile_id | Foreign key, which refers to the table ```matches_profile``` |
| matches_id        | Foreign key, which refers to the table ```matches```         |
| value             | Current value                                             |


### matches_option_values

Each match of a profile having mulitiple values is saved on this table. At the moment
this is used by the match type 'MULTIPLE_CHOOSE' This includes all the options per profile
and match, which are currently selected.

#### Most important fields
| Field             | Description                                               |
|-------------------|-----------------------------------------------------------|
| matches_pofile_id | Foreign key, which refers to the table ```matches_profile``` |
| matches_id        | Foreign key, which refers to the table ```matches```         |
| option_id         | Foreign key, which refers to the table ```matches_options``` |


### matches_options

This table is used for the matches having multiple options like ```MULTIPLE_CHOOSE```
and ```MENU```.

#### Most important fields
| Field      | Description                                      |
|------------|--------------------------------------------------|
| matches_id | Foreign key, which refers to the table ```matches``` |
| value      | Value to be rendered on the forms                |


### values_form_x

This is a view table, which is used to index the matches and aggregate them
in to a flat table.
  This view is created dynamically. Each time a new match is added or a match is deleted
this view is recreated. Each match has its own column.

#### Most important fields
| Field              | Description                                                                                         |
|--------------------|-----------------------------------------------------------------------------------------------------|
| matches_profile_id | Foreign key, which refers to the table ```matches_profile```                                        |
| name               | Value to be rendered on the forms.                                                                  |
| match_aggregation_field_n              | aggregation of one column. The name of the column is defined from the column ```matches_db_label``` |




## Match types
The match types are defined as enums. Currently the system supports the following match types
fields

| Field              | Description                                                                                         |
|--------------------|-----------------------------------------------------------------------------------------------------|
| CHECK_BOX | A field to be rendered a checkbox with one option and is saved as ```true``` or ```false```                                       |
| MULTIPLE_CHOOSE              | A group of check box options. For a match all selected options must be the same.                                                                 |
| EQUAL              | A numeric field, which must be equal for a match. |
| BIGGER_THAN               | A numeric field, which must be bigger than the value in the opposite party. 
| SMALLER_THAN               | A numeric field, which must be smaller than the value in the opposite party. 
| DATE_FROM               | A date field, which must be later than the value in the opposite party. 
| DATE_TO               | A date field, which must be before the value in the opposite party. 
| MENU               |A select menu with one single value. For a match this must be equal. 







<a name='endpoints-registrate'></a>
## Registrate (registrate)

Registration
endpoint: ```registrate```\
uri:```/api/register```\
method: ```POST```\
\
Body:

```name
required parameters
-------------------
name
email
password
c_password
role (company or professional)        
```

Example:
```
name:Test Name
email:test@test.nl
password:er#45Y23U
c_password:er#45Y23U
role:company
```

Example response:
```
{
   "success":true,
   "data":{
      "token":"56|es7Sc4td1zWGS0de0mygafnWMSMS20wMXNHnhgMLfa9a7e86",
      "name":"Test Name",
      "id":57
   },
   "message":"User register successfully."
}
```


<a name='endpoints-login'></a>
## Login (login)

Login
endpoint: ```login```\
uri:```/api/register```\
method: POST\
\
Body:

```name
required parameters
-------------------
email
password     
```

Example:
```
email:test@test.nl
password:er#45Y23U
```

Example response:
```
{
   "success":true,
   "data":{
      "token":"57|FA1Blojde16GdbLVnOT4MgSznEFGZolytea2UDcq3a1bfbb6",
      "name":"admin",
      "role":"admin",
      "id":1
   },
   "message":"User login successfully."
}
```



<a name='endpoints-forms-store'></a>
## Create profile form (forms.store)

Create e new profile form. With this endpoint you can also insert  the matches
endpoint: ```forms.store```\
uri:```/api/matches/forms```\
method: POST\


<a name='endpoints-authorization-and-headers'></a>
Authorization and headers:
```
--header 'Accept: application/json' \
--header 'Authorization: Bearer TOKEN_FROM_LOGIN_RESPONSE' \
```
Authorized roles: ```admin```

\
Body:
```name
required parameters
-------------------
name

optional params
-------------------
matches  
   label
   match_type (one of the used match types like "BIGGER_THAN", "DATE_TO")    
```

Example:
```
{
   "name":"Demo Form",
   "matches":{
      "0":{
         "label":"Years of experience",
         "match_type":"BIGGER_THAN"
      },
       "1":{
         "label":"Available from",
         "match_type": "DATE_TO"
      }
   }
}
```

Example response:
```
{
    "data": [
        "meta": {
            "status": 201,
            "count": 1
        },
        {
            "id": 3,
            "name": "Demo Form",
            "created_at": "2023-08-27T04:39:45.000000Z",
            "updated_at": "2023-08-27T04:39:45.000000Z",
            "matches": [
                {
                    "id": 8,
                    "db_code": "Yearsofexperience653b3f",
                    "label": "Years of experience",
                    "match_type": "BIGGER_THAN",
                    "ordering": 1,
                    "created_at": "2023-10-27T04:39:45.000000Z",
                    "updated_at": "2023-10-27T04:39:45.000000Z",
                    "matches_form_id": 3,
                    "options": []
                },
                {
                    "id": 9,
                    "db_code": "Availablefrom653b3f",
                    "label": "Available from",
                    "match_type": "DATE_TO",
                    "ordering": 2,
                    "created_at": "2023-10-27T04:39:45.000000Z",
                    "updated_at": "2023-10-27T04:39:45.000000Z",
                    "matches_form_id": 3,
                    "options": []
                }
            ]
        }
    ]
}
```


<a name='endpoints-profiles-store'></a>
## Create profile (profiles.store)

Create e new profile for a user or a vacancy. This creates a profile form with initially null values.
You can update this and post it back by the endpoint ```profiles.update```.\
\
endpoint: ```profiles.store```\
uri:```/api/matches/profiles```\
method: ```POST```\
Authorization and headers:
See [Authorization and headers](#endpoints-authorization-and-headers)\
Authorized roles: ```admin, company, professioal```
\
\
Body:


```name
required parameters
-------------------
name
user_id             id of the current user
matches_form_id     form id of the profile to be added
is_professional     0 for companies, 1 for professionals
 
```

Example:
```
{
    "name" : "demo profile",
    "user_id" : 2,
    "matches_form_id" : 1,
    "is_professional" : 1
}
```
<a name='endpoints-authorization-example-profile-response'></a>
Example: response
```
{
    "meta": {
        "status": 201,
        "count": 1
    }
    "data": [ 
        {
            "id": 102,
            "user_id": 2,
            "is_professional": 1,
            "name": "demo profile",
            "description": null,
            "tags": "demo profile",
            "created_at": "2023-10-27T05:13:55.000000Z",
            "updated_at": "2023-10-27T05:13:55.000000Z",
            "matches": [
                {
                    "id": 8,
                    "db_code": "Yearsofexperience653b3f",
                    "label": "Years of experience",
                    "match_type": "BIGGER_THAN",
                    "ordering": 8,
                    "created_at": "2023-10-27T04:39:45.000000Z",
                    "updated_at": "2023-10-27T04:39:45.000000Z",
                    "matches_form_id": 3,
                    "values": [
                        {
                            "id": 506,
                            "matches_profile_id": 102,
                            "matches_id": 8,
                            "value": null,
                            "order": 0,
                            "created_at": "2023-10-27T05:13:55.000000Z",
                            "updated_at": "2023-10-27T05:13:55.000000Z"
                        }
                    ],
                    "option_values": [],
                    "options": []
                },
                {
                    "id": 9,
                    "db_code": "Availablefrom653b3f",
                    "label": "Available from",
                    "match_type": "DATE_TO",
                    "ordering": 9,
                    "created_at": "2023-10-27T04:39:45.000000Z",
                    "updated_at": "2023-10-27T04:39:45.000000Z",
                    "matches_form_id": 3,
                    "values": [
                        {
                            "id": 507,
                            "matches_profile_id": 102,
                            "matches_id": 9,
                            "value": null,
                            "order": 1,
                            "created_at": "2023-10-27T05:13:55.000000Z",
                            "updated_at": "2023-10-27T05:13:55.000000Z"
                        }
                    ],
                    "option_values": [],
                    "options": []
                }
            ]
        }
    ]
}
```

<a name='endpoints-profiles-show'></a>
## Get profile form (profiles.show)

Get a profile form to be edited

endpoint: ```profiles.show```\
uri:```/api/matches/profiles```\
method: ```GET```\
Authorization and headers:
See [Authorization and headers](#endpoints-authorization-and-headers)\
Authorized roles: ```admin, company, professioal```
\
\
Uri
```
/api/matches/profiles/{PROFILE_ID}

PROFILE_ID      id of the profile to ge a form  
```


Example:
```
/api/matches/profiles/1
```

Example response:

See [Example profile response](#endpoints-authorization-example-profile-response)



<a name='endpoints-profiles-update'></a>
## Update profile form (profiles.update)

You  update a profile form by getting it by the endpoint ```profiles.show```
and  you modify and send back with by the endpoint ```profiles.update```
Normally only you need to modify the matches values

<img src="docs/img/matches-values.png" width="100%">

endpoint: ```profiles.update```\
uri:```/api/matches/profiles```\
method: ```PUT```\
Authorization and headers:
See [Authorization and headers](#endpoints-authorization-and-headers)\
Authorized roles: ```admin, company, professioal```
\
\
Uri
```
/api/matches/profiles/{PROFILE_ID}

PROFILE_ID      id of the profile to ge a form  
```

Body:
```
You modify the response you get by by the endoint profiles.show by
the values, which needs to be updated and send it back in the body in the same format.
```
See [Example profile response](#endpoints-authorization-example-profile-response)

Example uri
```
/api/matches/profiles/1
```

Example response:

See [Example profile response](#endpoints-authorization-example-profile-response)



<a name='endpoints-profiles-matches'></a>
## Find Matches

Find matches for the given profile.

endpoint: ```search```\
uri:```api/matches/search```\
method: ```GET```\
Authorization and headers:
See [Authorization and headers](#endpoints-authorization-and-headers)\
Authorized roles: ```admin, company, professioal```
\
\
Query parameters:
```
required:
profile_id      id of the profile to search matches

optional:
page_size       number of items per page to show (default 15)
page            current page (defailt 1)

```


Example response:

```
/api/matches/search?profile_id=1&page=1&page_size=2

{
 "meta": {
        "count": 2,
        "total_count": 34,
        "page": 1,
        "page_size": 2,
        "number_of_pages": 12,
        "status": 200
    }
    "data": [
        {
            "id": 87,
            "user_id": 55,
            "is_professional": 0,
            "name": "Rituals Vacancy 7",
            "description": "Justo nunc, i",
             "tags": ",.Net,Java,aenean,..."
             "created_at": "2023-10-26T14:59:56.000000Z",
            "updated_at": "2023-10-26T14:59:57.000000Z",
            "score": 80,
            "values": [
                {
                    "id": 431,
                    "matches_profile_id": 87,
                    "matches_id": 1,
                    "value": "3",
                    "order": null,
                    "created_at": "2023-10-26T14:59:56.000000Z",
                    "updated_at": "2023-10-26T14:59:57.000000Z"
                },
                {
                    "id": 432,
                    "matches_profile_id": 87,
                    "matches_id": 2,
                    "value": "0",
                    "order": null,
                    "created_at": "2023-10-26T14:59:56.000000Z",
                    "updated_at": "2023-10-26T14:59:57.000000Z"
                },
            ]
        },
        {
            "id": 51,
            "user_id": 52,
            "is_professional": 0,
            "name": "Bol.com Vacancy 1",
            "description": "Sem sem pharetra conubia",
            "tags": ",Java,Php,ac,ad,..."
            "created_at": "2023-10-26T14:59:42.000000Z",
            "updated_at": "2023-10-26T14:59:43.000000Z",
            "score": 60,
            "values": [
                {
                    "id": 251,
                    "matches_profile_id": 51,
                    "matches_id": 1,
                    "value": "3",
                    "order": null,
                    "created_at": "2023-10-26T14:59:42.000000Z",
                    "updated_at": "2023-10-26T14:59:43.000000Z"
                },
                {
                    "id": 252,
                    "matches_profile_id": 51,
                    "matches_id": 2,
                    "value": "0",
                    "order": null,
                    "created_at": "2023-10-26T14:59:43.000000Z",
                    "updated_at": "2023-10-26T14:59:43.000000Z"
                },
            ]
        }
    ],
   
}
```



<a name='install-section'></a>
# Install

Below are the instructions to install this on you local server.


## Docker and Git
Install docker,  git  and nginx
*  GIT https://git-scm.com/book/en/v2/Getting-Started-Installing-Git
* Docker https://docs.docker.com/engine/install/
* Nginx https://www.nginx.com/resources/wiki/start/topics/tutorials/install/


## Clone and build
1) Clone the project to your chosen directory by :
```git clone https://github.com/demotuulia/Laravel10_MatchToolsModule.git```

2) Go to the folder and do:
```
docker-compose up -d
```

## Install composer files and configure

1) You can log in the container by the command:
```./scripts/ssh.sh./scripts/ssh.sh```


2) Install vendor files
```composer update```
3) Copy .env.example  for the development database
```cp .env.example .env```
You don't need to modify anything.

4) Copy .env.example  for the test database
   ```cp .env.example .env.testing```
   And change:
```
   APP_ENV=local ==> APP_ENV=testing
   DB_DATABASE=laravel_db_name  ==>  DB_DATABASE=laravel_db_name_test
 
```
5) Generate Keys
```_
php artisan key:generate
php artisan config:cache
php artisan key:generate --env==testing
php artisan config:cache --env==testing
   ```
6) Install databases
This command install all databases and runs all of the migrations and seeds.
It creates a demo content by random names. All passwords are '123'
```
./scripts/installDatabases.sh
```


7) Test it works
```
./scripts/testAll.sh
```

Open
http://localhost:8000/

or try curl command
```
curl --location 'http://localhost:8000/api/login' \
--form 'email="testAdmin@test.nx"' \
--form 'password="123"'
```

7) Start the queue server
```
./scripts/startWorker.sh
```

