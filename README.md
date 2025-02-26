# Order & Invoice Management API

Order & Invoice Management API adalah REST API berbasis Laravel yang digunakan untuk mengelola pesanan, faktur, dan produk dalam sistem e-commerce. API ini menyediakan fitur seperti autentikasi pengguna, manajemen produk, pembuatan pesanan, serta pembuatan faktur.

## 📌 Fitur Utama

-   🔑 **Autentikasi Pengguna** (Register, Login, Sanctum)
-   📦 **Manajemen Produk** (CRUD Produk)
-   🛒 **Manajemen Pesanan** (Pembuatan & Pemrosesan Pesanan)
-   📄 **Pembuatan Faktur** (Invoice untuk Pesanan)
-   📊 **Dokumentasi API** (Swagger & Postman)

---

## 🚀 Installation

1. **Clone repository ini**:
    ```sh
    git clone https://github.com/fadelrazh/invoice-api.git
    cd invoice-api
    ```
2. **Install dependencies**:
    ```sh
    composer install
    ```
3. **Atur file konfigurasi `.env`**:
    ```sh
    cp .env.example .env
    ```
4. **Generate application key**:
    ```sh
    php artisan key:generate
    ```
5. **Jalankan migrasi database**:
    ```sh
    php artisan migrate
    ```
6. **Jalankan server Laravel**:
    ```sh
    php artisan serve
    ```

💡 **Opsional:** Jika menggunakan Docker, jalankan dengan:

```sh
docker-compose up -d
```

---

## 🗃 Database Structure

### **Orders Table**

| Column        | Type          | Description                                              |
| ------------- | ------------- | -------------------------------------------------------- |
| id            | BIGINT (PK)   | Primary key                                              |
| customer_name | STRING        | Customer's name                                          |
| status        | STRING        | Status order (pending, processing, completed, cancelled) |
| total_price   | DECIMAL(10,2) | Total harga pesanan                                      |
| created_at    | TIMESTAMP     | Waktu dibuat                                             |
| updated_at    | TIMESTAMP     | Waktu diperbarui                                         |

### **Order Items Table**

| Column     | Type          | Description                |
| ---------- | ------------- | -------------------------- |
| id         | BIGINT (PK)   | Primary key                |
| order_id   | BIGINT (FK)   | Referensi ke `orders.id`   |
| product_id | BIGINT (FK)   | Referensi ke `products.id` |
| quantity   | INTEGER       | Jumlah produk dalam order  |
| price      | DECIMAL(10,2) | Harga per unit             |
| created_at | TIMESTAMP     | Waktu dibuat               |
| updated_at | TIMESTAMP     | Waktu diperbarui           |

### **Invoices Table**

| Column       | Type          | Description              |
| ------------ | ------------- | ------------------------ |
| id           | BIGINT (PK)   | Primary key              |
| order_id     | BIGINT (FK)   | Referensi ke `orders.id` |
| amount       | DECIMAL(10,2) | Total invoice            |
| invoice_date | DATE          | Tanggal faktur           |
| created_at   | TIMESTAMP     | Waktu dibuat             |
| updated_at   | TIMESTAMP     | Waktu diperbarui         |

### **Products Table**

| Column     | Type          | Description      |
| ---------- | ------------- | ---------------- |
| id         | BIGINT (PK)   | Primary key      |
| name       | STRING        | Nama produk      |
| price      | DECIMAL(10,2) | Harga per unit   |
| stock      | INTEGER       | Stok tersedia    |
| created_at | TIMESTAMP     | Waktu dibuat     |
| updated_at | TIMESTAMP     | Waktu diperbarui |

### **Users Table (Auth)**

| Column     | Type        | Description       |
| ---------- | ----------- | ----------------- |
| id         | BIGINT (PK) | Primary key       |
| name       | STRING      | Nama pengguna     |
| email      | STRING      | Email unik        |
| password   | STRING      | Password (hashed) |
| created_at | TIMESTAMP   | Waktu dibuat      |
| updated_at | TIMESTAMP   | Waktu diperbarui  |

---

## 📡 API Endpoints

### **Authentication (Auth)**

#### 🔹 Register

-   **POST** `/api/register`
-   **Request Body:**
    ```json
    {
        "name": "Fadel Razsiah",
        "email": "fadelrzsh@gmail.com",
        "password": "password123"
    }
    ```
-   **Response:**
    ```json
    {
        "message": "User registered successfully",
        "token": "jwt_token_here"
    }
    ```

#### 🔹 Login

-   **POST** `/api/login`
-   **Request Body:**
    ```json
    {
        "email": "fadelrzsh@gmail.com",
        "password": "password123"
    }
    ```
-   **Response:**
    ```json
    {
        "message": "Login successful",
        "token": "jwt_token_here"
    }
    ```

### **Products**

#### 🔹 Get All Products

-   **GET** `/api/products`
-   **Response:**
    ```json
    [
        {
            "id": 1,
            "name": "Product A",
            "price": 100.0,
            "stock": 10
        }
    ]
    ```

#### 🔹 Create Product

-   **POST** `/api/products`
-   **Request Body:**
    ```json
    {
        "name": "Product A",
        "price": 100.0,
        "stock": 10
    }
    ```

### **Orders**

#### 🔹 Create Order

-   **POST** `/api/orders`
-   **Request Body:**
    ```json
    {
        "customer_name": "John Doe",
        "items": [{ "product_id": 1, "quantity": 2 }]
    }
    ```
-   **Response:**
    ```json
    {
        "message": "Order created successfully",
        "order_id": 1,
        "total_price": 200.0,
        "status": "pending"
    }
    ```

### **Invoices**

#### 🔹 Generate Invoice

-   **POST** `/api/invoices`
-   **Request Body:**
    ```json
    {
        "order_id": 1,
        "invoice_date": "2024-07-15"
    }
    ```

---

## 📜 Dokumentasi Lengkap

### **Swagger**

-   Tersedia di endpoint `/api/documentation/`

### **Postman Collection**

-   Dokumentasi API tersedia dalam Postman Collection (**on file**)

---

## 🤝 Contributing

Pull request sangat disambut baik! Silakan buka issue terlebih dahulu sebelum melakukan perubahan besar.

---

## ⚖️ License

Proyek ini dilisensikan di bawah MIT License.
