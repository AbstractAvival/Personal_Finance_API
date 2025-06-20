# Personal Finance API

-- Version: 0.1.0 -- 

:chart_with_upwards_trend: Status: In development

## :clipboard: Overview
The **Personal Finance API** is a simple backend that helps users manage their income and expenses. Please note that this is **NOT** a production ready application.

## :wrench: Tech Stack
- **Backend:** Laravel 12
- **Database:** MySQL
- **Containerization:** Docker

## 🐳 Getting Started with Docker

To run this project locally using Docker:

### Prerequisites

- Docker installed on your machine
- (Preferred) Docker Compose

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/AbstractAvival/Personal_Finance_API.git
   cd your-repo-name

2. **Configure Environment Variables**
   
    This project utilizes 2 important environment files.

   **These files must be created first! Please use the given templates (env.example) located at both required locations!**

   The docker environment file should be located in the /docker/environment folder and is used by docker compose to properly configure the Laravel and MySQL containers. The laravel environment file should be located at the project root and is used to configure the Laravel application. The recommended approach is to just copy and replace the variables that are in each of the .env.example files into the new .env files that were created and replace the desired values. The following variables in the Laravel .env file should be modified so that the containers can communicate:
   
   **The subsituted values should match the values in the docker environment file and the /docker/secrets folder.**
   ```bash
   DB_CONNECTION=mysql
   DB_HOST=host.docker.internal (Otherwise the MySQL container cant be easily reached)
   DB_PORT={mysql_container_port}
   DB_DATABASE={mysql_database_name}
   DB_USERNAME={mysql_username}
   DB_PASSWORD={mysql_password}

3. **Configure Docker Secrets**

   I utilized docker secrets for sensitive data that is required by docker compose to configure the Laravel and MySQL containers. **Create the /docker/secrets folder first.** I recommend creating the /docker/secrets folder by simply copying the existing /docker/secrets_example folder, renaming it to /docker/secrets and replacing the values inside the text files for the desired variables. These variable values should be located in text files inside the /docker/secrets folder **which, if you haven't already, must be created**. The secrets folder should have the following structure:
   ```bash
   your-project/
    ├── docker/
    │   ├── secrets/
    │   │   ├── database/
    │   │   │   ├── mysql/
    │   │   │       ├── password.txt
    │   │   │       ├── root_password.txt
    │   │   │       └── username.txt
    │   │   └── security/
    │   │   │   ├── pepper.txt

4. **Build and run the containers**
   
   ```bash
   docker compose -f ./docker/docker-compose.yaml --env-file ./docker/environment/.env up -d

5. **Access Laravel container and run migrations**

   Using Docker Desktop:

   **Click on Laravel container name and go to the Exec tab**
   ```bash
   php artisan migrate
   ```
  
   OR
  
   Using cli:
   ```bash
   docker exec -it <laravel-container-name-or-id> <shell-executable>
   php artisan migrate
   ```

6. **Access API**
   
   You should now be able to properly send requests to the Personal Finance API.

## 🧪 Testing

   To run API tests, access the Laravel container through the docker desktop UI or the cli and run:
   ```bash
   php artisan test
