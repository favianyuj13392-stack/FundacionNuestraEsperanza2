# API Endpoints Guide

This guide documents the available API endpoints for the application. All endpoints are prefixed with `/api`.

## Authentication (Public)

### 1. Register
**Endpoint**: `POST /api/auth/register`

**Body**:
```json
{
  "name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "ci": "1234567"
}
```

### 2. Login
**Endpoint**: `POST /api/auth/login`

**Body**:
```json
{
  "email": "john.doe@example.com",
  "password": "password123"
}
```

**Response (200)**:
```json
{
  "message": "Login successful",
  "data": {
    "token": "1|laravel_sanctum_token...",
    "user": {
      "id": 1,
      "name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "roles": ["viewer"]
    }
  }
}
```

---

## User Profile (Protected)
Requires Header: `Authorization: Bearer <token>`

### 3. Get Current User
**Endpoint**: `GET /api/auth/me`

### 4. Logout
**Endpoint**: `POST /api/auth/logout`

### 5. Change Password
**Endpoint**: `POST /api/auth/change-password`

**Body**:
```json
{
  "current_password": "password123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

---

## Donor Panel (Protected)
Requires Header: `Authorization: Bearer <token>`

### 6. My Donations
**Endpoint**: `GET /api/auth/donations/my`

**Response (200)**:
```json
{
  "data": [
    {
      "id": 10,
      "date": "2023-10-25 14:30:00",
      "amount": "100.00",
      "formatted_amount": "100,00 Bs.",
      "status": "succeeded",
      "certificate": {
        "folio": "DON-0010",
        "download_link": "http://localhost/certificates/download/DON-0010",
        "issued_on": "2023-10-25 14:30:00"
      }
    }
  ]
}
```

---

## Administration (Protected: Admin Role)
Requires Header: `Authorization: Bearer <token>` AND User must have `admin` role.

### 7. List Roles
**Endpoint**: `GET /api/roles`

### 8. Assign Role to User
**Endpoint**: `POST /api/users/{userId}/roles/assign`

**Body**:
```json
{
  "roles": ["admin", "editor"]
}
```

### 9. Revoke Role from User
**Endpoint**: `POST /api/users/{userId}/roles/revoke`

**Body**:
```json
{
  "roles": ["editor"]
}
```
