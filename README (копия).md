# RestFull
# API для работы с пользователями

## Методы

### Создание пользователя

- **URL**: /api/users
- **Метод**: POST
- **Тело запроса**:

json
{
"email": "user@example.com",
"password": "password"
}

### Авторизация пользователя

- **URL**: /api/login
- **Метод**: POST
- **Тело запроса**:

json
{
"email": "user@example.com",
"password": "password"
}

### Обновление информации пользователя

- **URL**: /api/users/{id}
- **Метод**: PUT
- **Тело запроса**:

json
{
"email": "new_email@example.com"
}
- **Заголовок**: Authorization: Bearer YOUR_JWT_TOKEN

### Удаление пользователя

- **URL**: /api/users/{id}
- **Метод**: DELETE
- **Заголовок**: Authorization: Bearer YOUR_JWT_TOKEN
    