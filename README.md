# Shipment Tracking Service

## 📌 Overview

This is a Laravel-based shipment tracking service that integrates with Shippo's API. It provides:

- A REST API to fetch shipment tracking details.
- Persistence of tracking data in a database.
- Logging of shipment events.
- Email notifications for lost shipments.

## 🚀 Features

- **GET /api/shipments/{tracking_number}**: Retrieves the shipment status from Shippo.
- **Webhook Listener (/api/webhooks/shippo)**: Receives real-time tracking updates from Shippo (TODO: verify the
  signature).
- **Error Handling**: Uses locally stored shipment data if Shippo is unavailable.
- **Email Notification**: Sends an email when a shipment is marked as "Lost".

## ⚙️ Setup Instructions

### 1️⃣ Install Dependencies

Ensure you have **PHP 8.1+**, **Composer**, and **MySQL/PostgreSQL** installed.

```sh
composer install
```

### 2️⃣ Configure Environment

Copy the `.env.example` file and update your database and Shippo credentials:

```sh
cp .env.example .env
```

Edit `.env` and update:

```ini
SHIPPO_API_TOKEN = your_shippo_api_token
```

### 3️⃣ Run Migrations

```sh
php artisan migrate --seed
```

### 4️⃣ Start Docker

```sh
docker-compose up -d
```

### 5️⃣ Start the Server

```sh
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`.

## 🛠 Usage

### ✅ **Track a Shipment**

```sh
GET /api/shipments/{tracking_number}
```

#### 📥 **Example Response**:

```json
{
    "tracking_number": "SHIP12345",
    "status": "In Transit",
    "message": "Tracking details fetched successfully",
    "data": {
        ...
    }
}
```

### ✅ **Receive Webhook Events from Shippo**

Set up Shippo to send webhooks to:

```
POST /api/webhooks/shippo
```

Example Payload:

```json
{
    "event": "track_updated",
    "data": {
        "tracking_number": "SHIP12345",
        "tracking_status": {
            "status": "Delivered"
        }
    }
}
```

## 🧪 Running Tests

### ✅ Run All Tests

```sh
php artisan test
```

## 🛠 Implementation Details

### 📌 Key Components:

1. **Controllers:**
    - `ShipmentController.php`: Fetches tracking data from Shippo and updates the database.
    - `ShipmentWebhookController.php`: Processes webhook updates.
2. **Services:**
    - `ShippoService.php`: Handles API calls to Shippo.
    - `ShipmentService.php`: Handles business logic like mapping statuses.
3. **Models:**
    - `Shipment.php`: Stores shipment details.
    - `ShipmentEvent.php`: Logs shipment events.
4. **Notifications:**
    - `LostShipmentNotification.php`: Sends an email when a shipment is lost.

## 📌 Assumptions

1. **A Shippo API Token is required** to make tracking requests.
2. **Lost shipments trigger an email notification** to the customer.
3. **If the Shippo API is unavailable,** the service returns the latest known tracking status.
4. **No authentication is required** for the API endpoints.
5. **Webhook requests are assumed to be from Shippo** (signature verification should be added in production).

