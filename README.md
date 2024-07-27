# Recipe API

## Introduction

Hello and thank you for evaluating my submission for the PHP Developer Test. This file outlines the setup process, project structure, and how to interact with the API endpoints using Postman. The goal of this project was to build a simple, production-ready Recipes API conforming to REST practices.

## Prerequisites

To run this application, you will need:
- Docker
- Docker Compose

## Project Structure

```
PHP-TEST/
├── docker/
│   └── nginx/
│       └── default.conf
├── web/
│   ├── auth.php
│   ├── error.log
│   ├── index.php
│   └── validation.php
├── db.php
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## Setup Instructions

1. **Clone the Repository**

   If you received a zip file, unzip the contents into your desired directory. If you are cloning from a Git repository, use the following command:

   ```sh  'git@github.com:shibbu04/php-test.git'
   git clone 'https://github.com/shibbu04/php-test.git'
   ```

2. **Navigate to the Project Directory**

   ```sh
   cd PHP-TEST
   ```

3. **Start Docker Containers**

   Build and start the Docker containers using Docker Compose:

   ```sh
   docker-compose up --d
   ```

   This will set up the necessary containers for the web server, MySQL database, PostgreSQL database, Redis, and phpMyAdmin.

4. **Access the Application**

   Once the containers are running, you can access the application at `http://localhost:8080`.

5. **Database Access**

   - MySQL: 
     - Host: localhost
     - Port: 3307               // Change as per your need by default(3306) , if you are using 3306 or any other then make sure to do changes inside docker-compode.yml
     - Username: hellofresh
     - Password: hellofresh
     - Database: hellofresh

   - PostgreSQL:
     - Host: localhost
     - Port: 5432
     - Username: hellofresh
     - Password: hellofresh
     - Database: hellofresh

   - phpMyAdmin:
     - URL: http://localhost:8081
     - Server: mysql
     - Username: root
     - Password: hellofresh

## API Endpoints

### Recipes

1. **List Recipes**

   - **Method:** GET
   - **URL:** `/recipes`
   - **Protected:** No
   - **Description:** Retrieves a list of all recipes.
   - **Pagination:** Use `page` and `per_page` query parameters, e.g., `/recipes?page=1&per_page=10`

2. **Create Recipe**

   - **Method:** POST
   - **URL:** `/recipes`
   - **Protected:** Yes
   - **Description:** Creates a new recipe. Requires authentication.

3. **Get Recipe**

   - **Method:** GET
   - **URL:** `/recipes/{id}`
   - **Protected:** No
   - **Description:** Retrieves a specific recipe by ID.

4. **Update Recipe**

   - **Method:** PUT
   - **URL:** `/recipes/{id}`
   - **Protected:** Yes
   - **Description:** Updates a specific recipe by ID. Requires authentication.

5. **Delete Recipe**

   - **Method:** DELETE
   - **URL:** `/recipes/{id}`
   - **Protected:** Yes
   - **Description:** Deletes a specific recipe by ID. Requires authentication.

6. **Rate Recipe**

   - **Method:** POST
   - **URL:** `/recipes/{id}/rating`
   - **Protected:** No
   - **Description:** Adds a rating to a specific recipe.

### Search Recipes

- **Method:** GET
- **URL:** `/recipes/search?q={search_term}`
- **Protected:** No
- **Description:** Searches for recipes based on a name.

### Authentication

- **Login**
  - **Method:** POST
  - **URL:** `/login`
  - **Description:** Authenticates a user and returns a JWT token.

Protected endpoints require authentication. Include a valid JWT token in the `Authorization` header of your requests to access protected endpoints.

## Example Requests Using Postman

1. **Login**

   - **Method:** POST
   - **URL:** `http://localhost:8080/login`
   - **Headers:** 
     - `Content-Type: application/json`
   - **Body:** 
     ```json
     {
       "username": "your_username",
       "password": "your_password"
     }
     ```

2. **Create Recipe**

   - **Method:** POST
   - **URL:** `http://localhost:8080/recipes`
   - **Headers:** 
     - `Authorization: Bearer <your_jwt_token>`
     - `Content-Type: application/json`
   - **Body:** 
     ```json
     {
       "name": "Spaghetti Carbonara",
       "prep_time": 30,
       "difficulty": 2,
       "vegetarian": false
     }
     ```

3. **List Recipes**

   - **Method:** GET
   - **URL:** `http://localhost:8080/recipes?page=1&per_page=10`
   - **Headers:** None
   - **Body:** None

## Additional Notes

- **Database:** The application can use both MySQL and PostgreSQL for data persistence.
- **Caching:** Redis is set up and can be used for caching if implemented.
- **Logging:** Error logging is implemented. Check `web/error.log` for any errors.
- **Input Validation:** Input validation is implemented in `web/validation.php`.
- **Security:** Best security practices are followed, including input validation and protected endpoints.

## Troubleshooting

- If you encounter any issues with database connections, ensure that the database containers are running and that the connection details in `db.php` match those in `docker-compose.yml`.
- For any permission issues, make sure that the web server has the necessary permissions to write to the `error.log` file.

## Conclusion

Thank you for taking the time to review my submission. If you have any questions or need further clarification, please feel free to reach out.